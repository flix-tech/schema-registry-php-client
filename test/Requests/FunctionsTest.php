<?php /** @noinspection AdditionOperationOnArraysInspection */

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Requests;

use FlixTech\SchemaRegistryApi\Constants;
use FlixTech\SchemaRegistryApi\Json;
use FlixTech\SchemaRegistryApi\Requests;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function FlixTech\SchemaRegistryApi\Requests\changeDefaultCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\changeSubjectCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\deleteSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\deleteSubjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\validateSchemaId;
use function FlixTech\SchemaRegistryApi\Requests\validateVersionId;

class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_produce_a_Request_to_get_all_subjects(): void
    {
        $request = Requests::allSubjectsRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/subjects', $request->getUri());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_Request_to_get_all_subject_versions(): void
    {
        $request = Requests::allSubjectVersionsRequest('test');

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/subjects/test/versions', $request->getUri());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_Request_to_get_a_specific_subject_version(): void
    {
        $request = Requests::singleSubjectVersionRequest('test', '3');

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/subjects/test/versions/3', $request->getUri());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_register_a_new_schema_version(): void
    {
        $request = Requests::registerNewSchemaVersionWithSubjectRequest('{"type": "string"}', 'test');

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/subjects/test/versions', $request->getUri());
        self::assertEquals(
            [Constants::CONTENT_TYPE => [Constants::CONTENT_TYPE_HEADER[Constants::CONTENT_TYPE]]] + [Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]],
            $request->getHeaders()
        );
        self::assertEquals('{"schema":"{\"type\":\"string\"}"}', $request->getBody()->getContents());

        $request = Requests::registerNewSchemaVersionWithSubjectRequest('{"schema": "{\"type\": \"string\"}"}', 'test');

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/subjects/test/versions', $request->getUri());
        self::assertEquals(
            [Constants::CONTENT_TYPE => [Constants::CONTENT_TYPE_HEADER[Constants::CONTENT_TYPE]]] + [Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]],
            $request->getHeaders()
        );
        self::assertEquals('{"schema":"{\"type\": \"string\"}"}', $request->getBody()->getContents());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_check_schema_compatibility_against_a_subject_version(): void
    {
        $request = Requests::checkSchemaCompatibilityAgainstVersionRequest(
            '{"type":"test"}',
            'test',
            Constants::VERSION_LATEST
        );

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/compatibility/subjects/test/versions/latest', $request->getUri());
        self::assertEquals('{"schema":"{\"type\":\"test\"}"}', $request->getBody()->getContents());
        self::assertEquals(
            [Constants::CONTENT_TYPE => [Constants::CONTENT_TYPE_HEADER[Constants::CONTENT_TYPE]]] + [Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]],
            $request->getHeaders()
        );
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_check_if_a_subject_already_has_a_schema(): void
    {
        $request = Requests::checkIfSubjectHasSchemaRegisteredRequest('test', '{"type":"test"}');

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/subjects/test', $request->getUri());
        self::assertEquals('{"schema":"{\"type\":\"test\"}"}', $request->getBody()->getContents());
        self::assertEquals(
            [Constants::CONTENT_TYPE => [Constants::CONTENT_TYPE_HEADER[Constants::CONTENT_TYPE]]] + [Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]],
            $request->getHeaders()
        );
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_get_a_specific_schema_by_id(): void
    {
        $request = Requests::schemaRequest('3');

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/schemas/ids/3', $request->getUri());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_get_the_global_compatibility_level(): void
    {
        $request = Requests::defaultCompatibilityLevelRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/config', $request->getUri());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_change_the_global_compatibility_level(): void
    {
        $request = changeDefaultCompatibilityLevelRequest(Constants::COMPATIBILITY_FULL);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/config', $request->getUri());
        self::assertEquals('{"compatibility":"FULL"}', $request->getBody()->getContents());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_get_the_subject_compatibility_level(): void
    {
        $request = subjectCompatibilityLevelRequest('test');

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/config/test', $request->getUri());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_change_the_subject_compatibility_level(): void
    {
        $request = changeSubjectCompatibilityLevelRequest('test', Constants::COMPATIBILITY_FORWARD);

        self::assertEquals('PUT', $request->getMethod());
        self::assertEquals('/config/test', $request->getUri());
        self::assertEquals('{"compatibility":"FORWARD"}', $request->getBody()->getContents());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_validate_a_JSON_schema_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$schema must be a valid JSON string');

        self::assertJsonStringEqualsJsonString('{"type":"test"}', Json::validateStringAsJson('{"type":"test"}'));

        Json::validateStringAsJson('INVALID');
    }

    /**
     * @test
     */
    public function it_should_prepare_a_JSON_schema_for_transfer(): void
    {
        self::assertJsonStringEqualsJsonString(
            '{"schema":"{\"type\":\"string\"}"}',
            Requests::prepareJsonSchemaForTransfer('{"type": "string"}')
        );

        self::assertJsonStringEqualsJsonString(
            '{"schema":"{\"type\": \"string\"}"}',
            Requests::prepareJsonSchemaForTransfer('{"schema":"{\"type\": \"string\"}"}')
        );
    }

    /**
     * @test
     */
    public function it_should_validate_a_compatibility_level_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$level must be one of NONE, BACKWARD, BACKWARD_TRANSITIVE, FORWARD, FORWARD_TRANSITIVE, FULL, FULL_TRANSITIVE');

        self::assertEquals(
            Constants::COMPATIBILITY_NONE,
            Requests::validateCompatibilityLevel(Constants::COMPATIBILITY_NONE)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_FULL,
            Requests::validateCompatibilityLevel(Constants::COMPATIBILITY_FULL)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_FULL_TRANSITIVE,
            Requests::validateCompatibilityLevel(Constants::COMPATIBILITY_FULL_TRANSITIVE)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_BACKWARD,
            Requests::validateCompatibilityLevel(Constants::COMPATIBILITY_BACKWARD)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_BACKWARD_TRANSITIVE,
            Requests::validateCompatibilityLevel(Constants::COMPATIBILITY_BACKWARD_TRANSITIVE)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_FORWARD,
            Requests::validateCompatibilityLevel(Constants::COMPATIBILITY_FORWARD)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_FORWARD_TRANSITIVE,
            Requests::validateCompatibilityLevel(Constants::COMPATIBILITY_FORWARD_TRANSITIVE)
        );

        Requests::validateCompatibilityLevel('INVALID');
    }

    /**
     * @test
     */
    public function it_should_prepare_compatibility_string_for_transport(): void
    {
        self::assertEquals(
            '{"compatibility":"NONE"}',
            Requests::prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_NONE)
        );
        self::assertEquals(
            '{"compatibility":"BACKWARD"}',
            Requests::prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_BACKWARD)
        );
        self::assertEquals(
            '{"compatibility":"BACKWARD_TRANSITIVE"}',
            Requests::prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_BACKWARD_TRANSITIVE)
        );
        self::assertEquals(
            '{"compatibility":"FORWARD"}',
            Requests::prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_FORWARD)
        );
        self::assertEquals(
            '{"compatibility":"FORWARD_TRANSITIVE"}',
            Requests::prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_FORWARD_TRANSITIVE)
        );
        self::assertEquals(
            '{"compatibility":"FULL"}',
            Requests::prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_FULL)
        );
        self::assertEquals(
            '{"compatibility":"FULL_TRANSITIVE"}',
            Requests::prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_FULL_TRANSITIVE)
        );
    }

    /**
     * @test
     */
    public function it_should_validate_version_id_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$versionId must be an integer of type int or string');

        validateVersionId([3]);
    }

    /**
     * @test
     */
    public function it_should_validate_version_id_overflow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$versionId must be between 1 and 2^31 - 1');

        validateVersionId(2 ** 31);
    }

    /**
     * @test
     */
    public function it_should_validate_version_id_less_than_one(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$versionId must be between 1 and 2^31 - 1');

        validateVersionId(0);
    }

    /**
     * @test
     */
    public function it_should_validate_valid_version_id(): void
    {
        self::assertSame(Constants::VERSION_LATEST, validateVersionId(Constants::VERSION_LATEST));
        self::assertSame('3', validateVersionId(3));
        self::assertSame('3', validateVersionId('3'));
    }

    /**
     * @test
     */
    public function it_should_validate_valid_schema_ids(): void
    {
        self::assertSame('3', validateSchemaId(3));
        self::assertSame('3', validateSchemaId('3'));
    }

    /**
     * @test
     */
    public function it_should_produce_a_valid_subject_deletion_request(): void
    {
        $request = deleteSubjectRequest('test');

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/subjects/test', $request->getUri());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_valid_subject_version_deletion_request(): void
    {
        $request = deleteSubjectVersionRequest('test', Constants::VERSION_LATEST);

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/subjects/test/versions/latest', $request->getUri());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());

        $request = deleteSubjectVersionRequest('test', '5');

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/subjects/test/versions/5', $request->getUri());
        self::assertEquals([Constants::ACCEPT => [Constants::ACCEPT_HEADER[Constants::ACCEPT]]], $request->getHeaders());
    }
}
