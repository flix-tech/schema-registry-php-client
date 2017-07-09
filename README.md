# Confluent Schema Registry PHP API

[![Build Status](https://travis-ci.org/flix-tech/schema-registry-php-client.svg?branch=2.0.2)](https://travis-ci.org/flix-tech/schema-registry-php-client)

A PHP 7.0+ library to consume the Confluent Schema Registry REST API. It provides low level functions to create PSR-7
compliant requests that can be used as well as high level abstractions to ease developer experience.

#### Contents

- [Requirements](#requirements)
  - [Hard Dependencies](#hard-dependencies)
  - [Optional Dependencies](#optional-dependencies)
- [Installation](#installation)
- [Usage](#usage)
  - [Asynchronous API](#asynchronous-api)
  - [Synchronous API](#synchronous-api)
  - [Caching](#caching)
- [Testing](#testing)
  - [Unit tests, Coding standards and static analysis](#unit-tests-coding-standards-and-static-analysis)
  - [Integration tests](#integration-tests)
- [Contributing](#contributing)

## Requirements

### Hard dependencies

| Dependency | Version | Reason |
|:--- |:---:|:--- |
| **`php`** | ~7.0 | Anything lower has reached EOL |
| **`guzzlephp/guzzle`** | ~6.2 | Using `Request` to build PSR-7 `RequestInterface` |
| **`beberlei/assert`** | ~2.7 | The de-facto standard assertions library for PHP |
| **`rg/avro-php`** | ~1.8 | The only Avro PHP implementation I have found so far. |

### Optional dependencies

| Dependency | Version | Reason |
|:--- |:---:|:--- |
| **`doctrine/cache`** | ~1.3 | If you want to use the `DoctrineCacheAdapter` |
| **`raphhh/trex-reflection`** | ~1.0 | If you want to use the `RequestCallbackValidator`s |

## Installation

This library is installed via [`composer`](http://getcomposer.org).

```bash
composer require "flix-tech/confluent-schema-registry-api=~2.0"
```

## Usage

### Asynchronous API

[Interface declaration](src/AsynchronousRegistry.php)

#### Example `PromisingRegistry`

```php
<?php

use GuzzleHttp\Client;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use Psr\Http\Message\RequestInterface;

$registry = new PromisingRegistry(
    new Client(['base_uri' => 'registry.example.com'])
);

// Register a schema with a subject
$schema = AvroSchema::parse('{"type": "string"}');

// The promise will either contain a schema id as int when fulfilled,
// or a SchemaRegistryException instance when rejected.
// If the subject does not exist, it will be created implicitly
$promise = $registry->register('test-subject', $schema);

// The promises have some default rejection/fulfillment callbacks, those are added here as an example
$promise = $promise->then(
    function (int $schemaId) {
        return $schemaId;
    },
    function (SchemaRegistryException $exception) {
        // maybe do some logging instead of throwing
        throw $exception;
    }
);

// Resolve the promise
$schemaId = $promise->wait();


// Get a schema by schema id
$promise = $registry->schemaForId($schemaId);
// As above you could add additional callbacks to the promise
$schema = $promise->wait();

// Get the version of a schema for a given subject.
// All methods also have a request callback third parameter.
// It takes a `Psr\Http\Message\RequestInterface` and should return a `Psr\Http\Message\RequestInterface`
$version = $registry->schemaVersion(
    'test-subject',
    $schema,
    function (RequestInterface $request) {
        return $request->withAddedHeader('Cache-Control', 'no-cache');
    }
)->wait();

// You can also get a schema by subject and version
$schema = $registry->schemaForSubjectAndVersion('test-subject', $version)->wait();

// Sometimes you want to find out the global schema id for a given schema
$schemaId = $registry->schemaId('test-subject', $schema)->wait();
```

### Synchronous API

[Interface declaration](src/SynchronousRegistry.php)

#### Example `BlockingRegistry`

```php
<?php

use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;

$registry = new BlockingRegistry(
    new PromisingRegistry(
        new Client(['base_uri' => 'registry.example.com'])
    )
);

// What the blocking registry does is actually resolving the promises
// with `wait` and adding a throwing rejection callback.
$schema = AvroSchema::parse('{"type": "string"}');

// This will be an int, and not a promise
$schemaId = $registry->register('test-subject', $schema);
```

### Caching

There is a `CachedRegistry` that accepts a `CacheAdapter` together with a `Registry`.
It supports both async and sync APIs.

#### Example

```php
<?php

use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\Cache\DoctrineCacheAdapter;
use Doctrine\Common\Cache\ArrayCache;
use GuzzleHttp\Client;

$asyncApi = new PromisingRegistry(
    new Client(['base_uri' => 'registry.example.com'])
);

$syncApi = new BlockingRegistry($asyncApi);

$doctrineCachedSyncApi = new CachedRegistry(
    $syncApi,
    new DoctrineCacheAdapter(
        new ArrayCache()
    )
);

// All adapters support both APIs, for async APIs additional fulfillment callbacks will be registered.
$avroObjectCachedAsyncApi = new CachedRegistry(
    $syncApi,
    new AvroObjectCacheAdapter()
);
```

## Testing

This library uses a `Makefile` to run the test suite.

#### Unit tests, Coding standards and static analysis

```bash
make quick-test
```

#### Integration tests

This library uses a `docker-compose` configuration to fire up a schema registry for integration testing, hence
`docker-compose` from version 1.13.0 is required to run those tests.

```bash
make integration-test
make clean
```

## Contributing

In order to contribute to this library, follow this workflow:

- Fork the repository
- Create a feature branch
- Work on the feature 
- Run tests to verify that the tests are passing
- Open a PR to the upstream
- Be happy about contributing to open source!
