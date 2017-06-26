<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;
use FlixTech\SchemaRegistryApi\Model\Schema\Schema;
use FlixTech\SchemaRegistryApi\Model\Schema\SchemaId;
use FlixTech\SchemaRegistryApi\Model\Subject\Subject;
use FlixTech\SchemaRegistryApi\Model\Subject\SubjectName;

interface SchemaRegistry
{
    public function schema(SchemaId $id): Schema;
    public function registeredSubjectNames(): array;
    public function subject(SubjectName $name): Subject;
    public function registerSubject(SubjectName $name, RawSchema $initialSchema): Subject;
    public function defaultCompatibility(): Compatibility;
}
