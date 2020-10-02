<?php /** @noinspection AdditionOperationOnArraysInspection */

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Test\Requests;

use FlixTech\SchemaRegistryApi\Constants;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use const FlixTech\SchemaRegistryApi\Constants\ACCEPT;
use const FlixTech\SchemaRegistryApi\Constants\ACCEPT_HEADER;
use const FlixTech\SchemaRegistryApi\Constants\CONTENT_TYPE;
use const FlixTech\SchemaRegistryApi\Constants\CONTENT_TYPE_HEADER;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectsRequest;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectVersionsRequest;
use function FlixTech\SchemaRegistryApi\Requests\changeDefaultCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\changeSubjectCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\checkIfSubjectHasSchemaRegisteredRequest;
use function FlixTech\SchemaRegistryApi\Requests\checkSchemaCompatibilityAgainstVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\defaultCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\deleteSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\deleteSubjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\prepareCompatibilityLevelForTransport;
use function FlixTech\SchemaRegistryApi\Requests\prepareJsonSchemaForTransfer;
use function FlixTech\SchemaRegistryApi\Requests\registerNewSchemaVersionWithSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\schemaRequest;
use function FlixTech\SchemaRegistryApi\Requests\singleSubjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\validateCompatibilityLevel;
use function FlixTech\SchemaRegistryApi\Requests\validateSchemaId;
use function FlixTech\SchemaRegistryApi\Requests\validateSchemaStringAsJson;
use function FlixTech\SchemaRegistryApi\Requests\validateVersionId;

class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_produce_a_Request_to_get_all_subjects(): void
    {
        $request = allSubjectsRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/subjects', $request->getUri());
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_Request_to_get_all_subject_versions(): void
    {
        $request = allSubjectVersionsRequest('test');

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/subjects/test/versions', $request->getUri());
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_Request_to_get_a_specific_subject_version(): void
    {
        $request = singleSubjectVersionRequest('test', '3');

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/subjects/test/versions/3', $request->getUri());
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_register_a_new_schema_version(): void
    {
        $request = registerNewSchemaVersionWithSubjectRequest('{"type": "string"}', 'test');

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/subjects/test/versions', $request->getUri());
        self::assertEquals(
            [CONTENT_TYPE => [CONTENT_TYPE_HEADER[CONTENT_TYPE]]] + [ACCEPT => [ACCEPT_HEADER[ACCEPT]]],
            $request->getHeaders()
        );
        self::assertEquals('{"schema":"{\"type\":\"string\"}"}', $request->getBody()->getContents());

        $request = registerNewSchemaVersionWithSubjectRequest('{"schema": "{\"type\": \"string\"}"}', 'test');

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/subjects/test/versions', $request->getUri());
        self::assertEquals(
            [CONTENT_TYPE => [CONTENT_TYPE_HEADER[CONTENT_TYPE]]] + [ACCEPT => [ACCEPT_HEADER[ACCEPT]]],
            $request->getHeaders()
        );
        self::assertEquals('{"schema":"{\"type\": \"string\"}"}', $request->getBody()->getContents());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_check_schema_compatibility_against_a_subject_version(): void
    {
        $request = checkSchemaCompatibilityAgainstVersionRequest(
            '{"type":"test"}',
            'test',
            Constants::VERSION_LATEST
        );

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/compatibility/subjects/test/versions/latest', $request->getUri());
        self::assertEquals('{"schema":"{\"type\":\"test\"}"}', $request->getBody()->getContents());
        self::assertEquals(
            [CONTENT_TYPE => [CONTENT_TYPE_HEADER[CONTENT_TYPE]]] + [ACCEPT => [ACCEPT_HEADER[ACCEPT]]],
            $request->getHeaders()
        );
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_check_if_a_subject_already_has_a_schema(): void
    {
        $request = checkIfSubjectHasSchemaRegisteredRequest('test', '{"type":"test"}');

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('/subjects/test', $request->getUri());
        self::assertEquals('{"schema":"{\"type\":\"test\"}"}', $request->getBody()->getContents());
        self::assertEquals(
            [CONTENT_TYPE => [CONTENT_TYPE_HEADER[CONTENT_TYPE]]] + [ACCEPT => [ACCEPT_HEADER[ACCEPT]]],
            $request->getHeaders()
        );
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_get_a_specific_schema_by_id(): void
    {
        $request = schemaRequest('3');

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/schemas/ids/3', $request->getUri());
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_get_the_global_compatibility_level(): void
    {
        $request = defaultCompatibilityLevelRequest();

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/config', $request->getUri());
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
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
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_request_to_get_the_subject_compatibility_level(): void
    {
        $request = subjectCompatibilityLevelRequest('test');

        self::assertEquals('GET', $request->getMethod());
        self::assertEquals('/config/test', $request->getUri());
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
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
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_validate_a_JSON_schema_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$schema must be a valid JSON string');

        self::assertJsonStringEqualsJsonString('{"type":"test"}', validateSchemaStringAsJson('{"type":"test"}'));

        validateSchemaStringAsJson('INVALID');
    }

    /**
     * @test
     */
    public function it_should_prepare_a_JSON_schema_for_transfer(): void
    {
        self::assertJsonStringEqualsJsonString(
            '{"schema":"{\"type\":\"string\"}"}',
            prepareJsonSchemaForTransfer('{"type": "string"}')
        );

        self::assertJsonStringEqualsJsonString(
            '{"schema":"{\"type\": \"string\"}"}',
            prepareJsonSchemaForTransfer('{"schema":"{\"type\": \"string\"}"}')
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
            validateCompatibilityLevel(Constants::COMPATIBILITY_NONE)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_FULL,
            validateCompatibilityLevel(Constants::COMPATIBILITY_FULL)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_FULL_TRANSITIVE,
            validateCompatibilityLevel(Constants::COMPATIBILITY_FULL_TRANSITIVE)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_BACKWARD,
            validateCompatibilityLevel(Constants::COMPATIBILITY_BACKWARD)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_BACKWARD_TRANSITIVE,
            validateCompatibilityLevel(Constants::COMPATIBILITY_BACKWARD_TRANSITIVE)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_FORWARD,
            validateCompatibilityLevel(Constants::COMPATIBILITY_FORWARD)
        );
        self::assertEquals(
            Constants::COMPATIBILITY_FORWARD_TRANSITIVE,
            validateCompatibilityLevel(Constants::COMPATIBILITY_FORWARD_TRANSITIVE)
        );

        validateCompatibilityLevel('INVALID');
    }

    /**
     * @test
     */
    public function it_should_prepare_compatibility_string_for_transport(): void
    {
        self::assertEquals(
            '{"compatibility":"NONE"}',
            prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_NONE)
        );
        self::assertEquals(
            '{"compatibility":"BACKWARD"}',
            prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_BACKWARD)
        );
        self::assertEquals(
            '{"compatibility":"BACKWARD_TRANSITIVE"}',
            prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_BACKWARD_TRANSITIVE)
        );
        self::assertEquals(
            '{"compatibility":"FORWARD"}',
            prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_FORWARD)
        );
        self::assertEquals(
            '{"compatibility":"FORWARD_TRANSITIVE"}',
            prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_FORWARD_TRANSITIVE)
        );
        self::assertEquals(
            '{"compatibility":"FULL"}',
            prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_FULL)
        );
        self::assertEquals(
            '{"compatibility":"FULL_TRANSITIVE"}',
            prepareCompatibilityLevelForTransport(Constants::COMPATIBILITY_FULL_TRANSITIVE)
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
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
    }

    /**
     * @test
     */
    public function it_should_produce_a_valid_subject_version_deletion_request(): void
    {
        $request = deleteSubjectVersionRequest('test', Constants::VERSION_LATEST);

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/subjects/test/versions/latest', $request->getUri());
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());

        $request = deleteSubjectVersionRequest('test', '5');

        self::assertEquals('DELETE', $request->getMethod());
        self::assertEquals('/subjects/test/versions/5', $request->getUri());
        self::assertEquals([ACCEPT => [ACCEPT_HEADER[ACCEPT]]], $request->getHeaders());
    }
}
