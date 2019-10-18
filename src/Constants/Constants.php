<?php

namespace FlixTech\SchemaRegistryApi\Constants;

const COMPATIBILITY_NONE = 'NONE';
const COMPATIBILITY_BACKWARD = 'BACKWARD';
const COMPATIBILITY_BACKWARD_TRANSITIVE = 'BACKWARD_TRANSITIVE';
const COMPATIBILITY_FORWARD = 'FORWARD';
const COMPATIBILITY_FORWARD_TRANSITIVE = 'FORWARD_TRANSITIVE';
const COMPATIBILITY_FULL = 'FULL';
const COMPATIBILITY_FULL_TRANSITIVE = 'FULL_TRANSITIVE';
const VERSION_LATEST = 'latest';
const ACCEPT_HEADER = ['Accept' => 'application/vnd.schemaregistry.v1+json'];
const CONTENT_TYPE_HEADER = ['Content-Type' => 'application/vnd.schemaregistry.v1+json'];

