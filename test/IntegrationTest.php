<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test;

use FlixTech\SchemaRegistryApi\Constants;
use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use FlixTech\SchemaRegistryApi\Exception\IncompatibleAvroSchemaException;
use FlixTech\SchemaRegistryApi\Exception\InvalidAvroSchemaException;
use FlixTech\SchemaRegistryApi\Exception\InvalidVersionException;
use FlixTech\SchemaRegistryApi\Exception\SchemaNotFoundException;
use FlixTech\SchemaRegistryApi\Exception\SubjectNotFoundException;
use FlixTech\SchemaRegistryApi\Exception\VersionNotFoundException;
use FlixTech\SchemaRegistryApi\Json;
use FlixTech\SchemaRegistryApi\Requests;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @group integration
 */
class IntegrationTest extends TestCase
{
    public const SUBJECT_NAME = 'integration-test';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $baseSchema = <<<SCHEMA
{
  "namespace": "example.avro",
  "type": "record",
  "name": "user",
  "fields": [
    {"name": "name", "type": "string"},
    {"name": "favorite_number",  "type": "int"}
  ]
}
SCHEMA;

    /**
     * @var string
     */
    private $compatibleSchemaEvolution = <<<COMPAT
{
  "namespace": "example.avro",
  "type": "record",
  "name": "user",
  "fields": [
    {"name": "name", "type": "string"},
    {"name": "favorite_number",  "type": "int"},
    {"name": "favorite_color", "type": "string", "default": "green"}
  ]
}
COMPAT;

    /**
     * @var string
     */
    private $incompatibleSchemaEvolution = <<<INCOMPATIBLE
{
  "namespace": "example.avro",
  "type": "record",
  "name": "user",
  "fields": [
    {"name": "name", "type": "string"},
    {"name": "favorite_number",  "type": "int"},
    {"name": "favorite_color", "type": "string"}
  ]
}
INCOMPATIBLE;

    /**
     * @var string
     */
    private $invalidSchema = '{"invalid": "invalid"}';


    protected function setUp(): void
    {
        if ((bool) getenv('ENABLE_INTEGRATION_TEST') === false) {
            self::markTestSkipped('Integration tests are disabled');
        }

        $this->client = new Client([
            'base_uri' => sprintf(
                'http://%s:%s',
                getenv('TEST_SCHEMA_REGISTRY_HOST'),
                getenv('TEST_SCHEMA_REGISTRY_PORT')
            )
        ]);
    }

    /**
     * @test
     */
    public function managing_subjects_and_versions(): void
    {
        $this->client
            ->sendAsync(Requests::allSubjectsRequest())
            ->then(
                function (ResponseInterface $request) {
                    $this->assertEmpty(Json::decode($request->getBody()->getContents()));
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::registerNewSchemaVersionWithSubjectRequest($this->baseSchema, self::SUBJECT_NAME))
            ->then(
                function (ResponseInterface $request) {
                    $this->assertEquals(1, Json::decode($request->getBody()->getContents())['id']);
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::schemaRequest('1'))
            ->then(
                function (ResponseInterface $request) {
                    $decodedBody = Json::decode($request->getBody()->getContents());

                    $this->assertJsonStringEqualsJsonString($this->baseSchema, $decodedBody['schema']);
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::checkIfSubjectHasSchemaRegisteredRequest(self::SUBJECT_NAME, $this->baseSchema))
            ->then(
                function (ResponseInterface $request) {
                    $decodedBody = Json::decode($request->getBody()->getContents());

                    $this->assertEquals(1, $decodedBody['id']);
                    $this->assertEquals(1, $decodedBody['version']);
                    $this->assertEquals(self::SUBJECT_NAME, $decodedBody['subject']);
                    $this->assertJsonStringEqualsJsonString($this->baseSchema, $decodedBody['schema']);
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::singleSubjectVersionRequest(self::SUBJECT_NAME, Constants::VERSION_LATEST))
            ->then(
                function (ResponseInterface $request) {
                    $decodedBody = Json::decode($request->getBody()->getContents());

                    $this->assertEquals(self::SUBJECT_NAME, $decodedBody['subject']);
                    $this->assertEquals(1, $decodedBody['version']);
                    $this->assertJsonStringEqualsJsonString($this->baseSchema, $decodedBody['schema']);
                    $this->assertEquals(1, $decodedBody['id']);
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::checkSchemaCompatibilityAgainstVersionRequest(
                $this->compatibleSchemaEvolution,
                self::SUBJECT_NAME,
                Constants::VERSION_LATEST
            ))->then(
                function (ResponseInterface $request) {
                    $decodedBody = Json::decode($request->getBody()->getContents());

                    $this->assertTrue($decodedBody['is_compatible']);
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::checkSchemaCompatibilityAgainstVersionRequest(
                $this->incompatibleSchemaEvolution,
                self::SUBJECT_NAME,
                Constants::VERSION_LATEST
            ))->otherwise(
                function (RequestException $exception) {
                    $this->assertInstanceOf(
                        IncompatibleAvroSchemaException::class,
                        (ExceptionMap::instance())($exception)
                    );
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::registerNewSchemaVersionWithSubjectRequest($this->invalidSchema, self::SUBJECT_NAME))
            ->otherwise(
                function (RequestException $exception) {
                    $this->assertInstanceOf(
                        InvalidAvroSchemaException::class,
                        (ExceptionMap::instance())($exception)
                    );
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::singleSubjectVersionRequest('INVALID', Constants::VERSION_LATEST))
            ->otherwise(
                function (RequestException $exception) {
                    $this->assertInstanceOf(
                        SubjectNotFoundException::class,
                        (ExceptionMap::instance())($exception)
                    );
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::singleSubjectVersionRequest(self::SUBJECT_NAME, 'INVALID'))
            ->otherwise(
                function (RequestException $exception) {
                    $this->assertInstanceOf(
                        InvalidVersionException::class,
                        (ExceptionMap::instance())($exception)
                    );
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::singleSubjectVersionRequest(self::SUBJECT_NAME, '5'))
            ->otherwise(
                function (RequestException $exception) {
                    $this->assertInstanceOf(
                        VersionNotFoundException::class,
                        (ExceptionMap::instance())($exception)
                    );
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::schemaRequest('6'))
            ->otherwise(
                function (RequestException $exception) {
                    $this->assertInstanceOf(
                        SchemaNotFoundException::class,
                        (ExceptionMap::instance())($exception)
                    );
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::registerNewSchemaVersionWithSubjectRequest($this->compatibleSchemaEvolution, self::SUBJECT_NAME))
            ->then(
                function (ResponseInterface $request) {
                    $this->assertEquals(2, Json::decode($request->getBody()->getContents())['id']);
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::allSubjectVersionsRequest(self::SUBJECT_NAME))
            ->then(
                function (ResponseInterface $request) {
                    $this->assertEquals([1, 2], Json::decode($request->getBody()->getContents()));
                }
            )->wait();
    }

    /**
     * @test
     */
    public function managing_compatibility_levels(): void
    {
        $this->client
            ->sendAsync(Requests::defaultCompatibilityLevelRequest())
            ->then(
                function (ResponseInterface $request) {
                    $decodedBody = Json::decode($request->getBody()->getContents());

                    $this->assertEquals(
                        Constants::COMPATIBILITY_BACKWARD,
                        $decodedBody['compatibilityLevel']
                    );
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::changeDefaultCompatibilityLevelRequest(Constants::COMPATIBILITY_FULL))
            ->then(
                function (ResponseInterface $request) {
                    $decodedBody = Json::decode($request->getBody()->getContents());

                    $this->assertEquals(
                        Constants::COMPATIBILITY_FULL,
                        $decodedBody['compatibility']
                    );
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::changeSubjectCompatibilityLevelRequest(self::SUBJECT_NAME, Constants::COMPATIBILITY_FORWARD))
            ->then(
                function (ResponseInterface $request) {
                    $decodedBody = Json::decode($request->getBody()->getContents());

                    $this->assertEquals(
                        Constants::COMPATIBILITY_FORWARD,
                        $decodedBody['compatibility']
                    );
                }
            )->wait();

        $this->client
            ->sendAsync(Requests::subjectCompatibilityLevelRequest(self::SUBJECT_NAME))
            ->then(
                function (ResponseInterface $request) {
                    $decodedBody = Json::decode($request->getBody()->getContents());

                    $this->assertEquals(
                        Constants::COMPATIBILITY_FORWARD,
                        $decodedBody['compatibilityLevel']
                    );
                }
            )->wait();
    }
}
