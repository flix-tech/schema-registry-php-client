<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use FlixTech\SchemaRegistryApi\Exception\InternalSchemaRegistryException;
use FlixTech\SchemaRegistryApi\Exception\InvalidAvroSchemaException;
use FlixTech\SchemaRegistryApi\Exception\InvalidVersionException;
use FlixTech\SchemaRegistryApi\Exception\SubjectNotFoundException;
use FlixTech\SchemaRegistryApi\Exception\VersionNotFoundException;
use FlixTech\SchemaRegistryApi\Model\Schema\Id;
use FlixTech\SchemaRegistryApi\Model\Schema\Promised\Id as PromisedId;
use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use function FlixTech\SchemaRegistryApi\Requests\checkSchemaCompatibilityRequest;
use function FlixTech\SchemaRegistryApi\Requests\hasSchemaRequest;
use function FlixTech\SchemaRegistryApi\Requests\registerSchemaWithSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectVersionsRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectsRequest;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

final class Subject
{
    /**
     * @var AsyncHttpClient
     */
    private $client;

    /**
     * @var Name
     */
    private $name;

    /**
     * @var array
     */
    private $versions = [];

    public static function registeredSubjects(AsyncHttpClient $client): array
    {
        return $client->send(subjectsRequest())
            ->then(
                function (ResponseInterface $response) {
                    return array_map(
                        function (string $subjectName) {
                            return Name::create($subjectName);
                        },
                        \GuzzleHttp\json_decode($response->getBody()->getContents(), true)
                    );
                },
                function () {
                    throw InternalSchemaRegistryException::create();
                }
            )->wait();
    }

    public function __construct(AsyncHttpClient $client, Name $name)
    {
        $this->client = $client;
        $this->name = $name;
    }

    public function versions(): array
    {
        if ($this->versions) {
            return $this->versions;
        }

        $this->versions = $this->client
            ->send(subjectVersionsRequest((string) $this->name))
            ->then(
                function (ResponseInterface $response) {
                    return array_map(
                        function (int $version) {
                            return VersionId::create($version);
                        },
                        \GuzzleHttp\json_decode($response->getBody()->getContents(), true)
                    );
                },
                function (RequestException $e) {
                    if (404 === $e->getResponse()->getStatusCode()) {
                        throw SubjectNotFoundException::create($this->name);
                    }

                    throw InternalSchemaRegistryException::create();
                }
            )->wait();

        return $this->versions;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function version(VersionId $id): Version
    {
        $promise = $this->client
            ->send(subjectVersionRequest((string) $this->name, (string) $id))
            ->otherwise(
                function (RequestException $e) use ($id) {
                    $decodedResponse = \GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents(), true);

                    if (!array_key_exists('error_code', $decodedResponse) || 50001 === $decodedResponse['error_code']) {
                        throw InternalSchemaRegistryException::create();
                    }

                    if (422 === $e->getResponse()->getStatusCode()) {
                        throw InvalidVersionException::create($id);
                    }

                    if (40402 === $decodedResponse['error_code']) {
                        throw VersionNotFoundException::create($id);
                    }

                    throw SubjectNotFoundException::create($this->name);
                }
            );

        return Promised\Version::withPromise($promise);
    }

    public function registerSchema(RawSchema $schema): Id
    {
        return PromisedId::withPromise(
            $this->client->send(registerSchemaWithSubjectRequest((string) $this->name, \GuzzleHttp\json_encode($schema)))
        );
    }

    public function checkCompatibilityWithVersion(RawSchema $schema, VersionId $id): bool
    {
        return $this->client
            ->send(checkSchemaCompatibilityRequest((string) $this, (string) $id, \GuzzleHttp\json_encode($schema)))
            ->then(
                function (ResponseInterface $response) {
                    return \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['is_compatible'];
                },
                function (RequestException $e) use ($id) {
                    $errorCode = \GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents(), true)['error_code'];

                    switch ($errorCode) {
                        case 40401: throw SubjectNotFoundException::create($this->name);
                        case 40402: throw VersionNotFoundException::create($id);
                        case 42201: throw InvalidAvroSchemaException::create();
                        case 42202: throw InvalidVersionException::create($id);
                        default: throw InternalSchemaRegistryException::create();
                    }
                }
            )->wait();
    }

    public function hasSchema(RawSchema $rawSchema): VersionedSchema
    {
        $promise = $this->client
            ->send(hasSchemaRequest((string) $this, \GuzzleHttp\json_encode($rawSchema)))
            ->otherwise(
                function (RequestException $e) {
                    $errorCode = \GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents(), true)['error_code'];

                    if (40401 === $errorCode) {
                        throw SubjectNotFoundException::create($this->name);
                    }

                    throw InternalSchemaRegistryException::create();
                }
            );

        return Promised\VersionedSchema::withPromise($promise);
    }

    public function __toString(): string
    {
        return $this->name->name();
    }
}
