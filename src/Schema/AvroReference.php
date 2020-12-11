<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schema;

final class AvroReference implements \JsonSerializable
{
    /**
     * @var AvroName
     */
    private $name;


    /**
     * @var string
     */
    private $subject;

    /**
     * @var string|int
     */
    private $version;

    /**
     * @param AvroName $name
     * @param string $subject
     * @param int|string $version
     */
    public function __construct(AvroName $name, string $subject, $version)
    {
        $this->name = $name;
        $this->subject = $subject;
        $this->version = $version;
    }

    public function jsonSerialize()
    {
        return [
            'name' => (string) $this->name,
            'subject' => $this->subject,
            'version' => $this->version,
        ];
    }
}
