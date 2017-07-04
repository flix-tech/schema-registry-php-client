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

    public static function create(AsyncHttpClient $client, Name $name): Subject
    {
        $instance = new self();
        $instance->client = $client;
        $instance->name = $name;

        return $instance;
    }

    protected function __construct()
    {
    }

    public function versions(): array
    {
        return $this->client
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
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function version(VersionId $id): Version
    {
        return Promised\Version::withPromise(
            $this->client
                ->send(subjectVersionRequest((string) $this->name, (string) $id))
                ->otherwise(new ExceptionMap())
        );
    }

    public function registerSchema(RawSchema $schema): Id
    {
        return PromisedId::withPromise(
            $this->client
                ->send(registerSchemaWithSubjectRequest((string) $this->name, \GuzzleHttp\json_encode($schema)))
                ->otherwise(new ExceptionMap())
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
        return Promised\VersionedSchema::withPromise(
            $this->client
                ->send(hasSchemaRequest((string) $this, \GuzzleHttp\json_encode($rawSchema)))
                ->otherwise(new ExceptionMap())
        );
    }

    public function __toString(): string
    {
        return $this->name->name();
    }
}
