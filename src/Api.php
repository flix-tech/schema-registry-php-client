<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use FlixTech\SchemaRegistryApi\Model\Schema\Schema;
use FlixTech\SchemaRegistryApi\Model\Schema\Id;
use FlixTech\SchemaRegistryApi\Model\Subject\Subject;
use FlixTech\SchemaRegistryApi\Model\Subject\Name;

class Api implements SchemaRegistry
{
    /**
     * @var AsyncHttpClient
     */
    private $asyncClient;

    /**
     * @param AsyncHttpClient $asyncClient
     */
    public function __construct(AsyncHttpClient $asyncClient)
    {
        $this->asyncClient = $asyncClient;
    }

    public function schema(Id $id): Schema
    {
        return Schema::createAsync($this->asyncClient, $id);
    }

    public function registeredSubjectNames(): array
    {
        return Subject::registeredSubjects($this->asyncClient);
    }

    public function subject(Name $name): Subject
    {
        return new Subject($this->asyncClient, $name);
    }

    public function registerNewSchema(Name $name, RawSchema $initialSchema): Schema
    {
        // TODO: Implement registerSubject() method.
    }

    public function defaultCompatibility(): Compatibility
    {
        // TODO: Implement defaultCompatibility() method.
    }
}
