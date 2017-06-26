<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Model\Subject;

use FlixTech\SchemaRegistryApi\Model\Schema\RawSchema;

class Version
{
    /**
     * @var RawSchema
     */
    protected $schema;

    /**
     * @var VersionId
     */
    protected $id;

    /**
     * @var Name
     */
    protected $subjectName;

    public static function create(Name $subjectName, VersionId $id, RawSchema $schema): Version
    {
        $instance = new self();

        $instance->subjectName = $subjectName;
        $instance->id = $id;
        $instance->schema = $schema;

        return $instance;
    }

    final protected function __construct()
    {
    }

    public function id(): VersionId
    {
        return $this->id;
    }

    public function subjectName(): Name
    {
        return $this->subjectName;
    }

    public function schema(): RawSchema
    {
        return $this->schema;
    }
}
