<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi;

final class Constants
{
    public const COMPATIBILITY_NONE = 'NONE';
    public const COMPATIBILITY_BACKWARD = 'BACKWARD';
    public const COMPATIBILITY_BACKWARD_TRANSITIVE = 'BACKWARD_TRANSITIVE';
    public const COMPATIBILITY_FORWARD = 'FORWARD';
    public const COMPATIBILITY_FORWARD_TRANSITIVE = 'FORWARD_TRANSITIVE';
    public const COMPATIBILITY_FULL = 'FULL';
    public const COMPATIBILITY_FULL_TRANSITIVE = 'FULL_TRANSITIVE';

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}
