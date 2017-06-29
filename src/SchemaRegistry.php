<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use FlixTech\SchemaRegistryApi\Model\Schema\Schema;
use FlixTech\SchemaRegistryApi\Model\Schema\Id;
use FlixTech\SchemaRegistryApi\Model\Subject\Subject;
use FlixTech\SchemaRegistryApi\Model\Subject\Name;

interface SchemaRegistry
{
    public function schema(Id $id): Schema;
    public function registeredSubjectNames(): array;
    public function subject(Name $name): Subject;
    public function registerNewSchema(Name $name, RawSchema $initialSchema): Schema;
    public function defaultCompatibility(): Compatibility;
}
