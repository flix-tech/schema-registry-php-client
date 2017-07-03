<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Compatibility;

use FlixTech\SchemaRegistryApi\AsyncHttpClient;
use FlixTech\SchemaRegistryApi\Exception\ExceptionMap;
use FlixTech\SchemaRegistryApi\Model\Subject\Name;
use function FlixTech\SchemaRegistryApi\Requests\changeDefaultCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\changeSubjectCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\defaultCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectCompatibilityLevelRequest;

class Compatibility
{
    /**
     * @var \FlixTech\SchemaRegistryApi\AsyncHttpClient
     */
    private $client;

    public static function create(AsyncHttpClient $client): Compatibility
    {
        $instance = new self();
        $instance->client = $client;

        return $instance;
    }

    protected function __construct()
    {
    }

    public function defaultLevel(): Level
    {
        return Promised\Level::withPromise(
            $this->client
                ->send(defaultCompatibilityLevelRequest())
                ->otherwise(new ExceptionMap())
        );
    }

    public function changeDefaultLevel(Level $level): Level
    {
        return Promised\Level::withPromise(
            $this->client
                ->send(changeDefaultCompatibilityLevelRequest(\GuzzleHttp\json_encode($level)))
                ->otherwise(new ExceptionMap())
        );
    }

    public function subjectLevel(Name $subjectName): Level
    {
        return Promised\Level::withPromise(
            $this->client
                ->send(subjectCompatibilityLevelRequest((string) $subjectName))
                ->otherwise(new ExceptionMap())
        );
    }

    public function changeSubjectLevel(Name $subjectName, Level $level): Level
    {
        return Promised\Level::withPromise(
            $this->client
                ->send(changeSubjectCompatibilityLevelRequest((string) $subjectName, \GuzzleHttp\json_encode($level)))
                ->otherwise(new ExceptionMap())
        );
    }
}
