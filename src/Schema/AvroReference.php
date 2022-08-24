<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Schema;

use Assert\Assertion;

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
        Assertion::notBlank($subject);
        $this->name = $name;
        $this->subject = $subject;
        $this->version = $version;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'name' => (string) $this->name,
            'subject' => $this->subject,
            'version' => $this->version,
        ];
    }
}
