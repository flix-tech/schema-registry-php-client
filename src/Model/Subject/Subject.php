<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use FlixTech\SchemaRegistryApi\Model\Schema\Id;
use FlixTech\SchemaRegistryApi\Model\Schema\Promised\Id as PromisedId;
use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use Psr\Http\Message\ResponseInterface;
use function FlixTech\SchemaRegistryApi\Requests\checkSchemaCompatibilityRequest;
use function FlixTech\SchemaRegistryApi\Requests\hasSchemaRequest;
use function FlixTech\SchemaRegistryApi\Requests\registerSchemaWithSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectVersionsRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectsRequest;

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
                new ExceptionMap()
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
                new ExceptionMap()
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
            ->otherwise(new ExceptionMap());

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
                new ExceptionMap()
            )->wait();
    }

    public function hasSchema(RawSchema $rawSchema): VersionedSchema
    {
        $promise = $this->client
            ->send(hasSchemaRequest((string) $this, \GuzzleHttp\json_encode($rawSchema)))
            ->otherwise(new ExceptionMap());

        return Promised\VersionedSchema::withPromise($promise);
    }

    public function __toString(): string
    {
        return $this->name->name();
    }
}
