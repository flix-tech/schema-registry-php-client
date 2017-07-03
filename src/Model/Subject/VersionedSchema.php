<?php

namespace FlixTech\SchemaRegistryApi\Model\Subject;

use FlixTech\SchemaRegistryApi\Model\Schema\Schema;

class VersionedSchema
{
    /**
     * @var VersionId
     */
    protected $versionId;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var Name
     */
    protected $subjectName;

    public static function create(VersionId $versionId, Name $subjectName, Schema $schema): VersionedSchema
    {
        $instance = new self();
        $instance->versionId = $versionId;
        $instance->schema = $schema;
        $instance->subjectName = $subjectName;

        return $instance;
    }

    final protected function __construct()
    {
    }

    public function versionId(): VersionId
    {
        return $this->versionId;
    }

    public function schema(): Schema
    {
        return $this->schema;
    }

    public function subjectName(): Name
    {
        return $this->subjectName;
    }
}
