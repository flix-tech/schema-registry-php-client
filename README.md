# Confluent Schema Registry PHP API

[![Build Status](https://travis-ci.org/flix-tech/schema-registry-php-client.svg?branch=master)](https://travis-ci.org/flix-tech/schema-registry-php-client)

A PHP 7.0+ library to consume the Confluent Schema Registry REST API. It only provides PSR-7 compatible requests via
functions that can be used in conjunction with any client that is able to handle any requests that implement the PSR-7
`RequestInterface`.

## Requirements

| Dependency | Version | Reason |
|:--- |:---:|:--- |
| **`php`** | ~7.0 | Anything lower has reached EOL |
| **`guzzlephp/guzzle`** | ~6.0 | Using `Request` to build PSR-7 `RequestInterface` |
| **`beberlei/assert`** | ~2.7 | The de-facto standard assertions library for PHP |
| **`roave/security-advisories`** | dev-master | Because security, right? |

## Installation

This library is installed via [`composer`](http://getcomposer.org).

```bash
composer require "flix-tech/confluent-schema-registry-api=~1.0"
```

## Usage

This library is best explained with a few examples. Since this library provides low level requests for PSR-7 compatible
clients, we first need a client that can send the requests.

#### Registering an initial Avro Schema with a Subject

```php
<?php

use FlixTech\SchemaRegistryApi\Requests as RequestFunctions;

// Create a PSR-7 compatible client
$client = new \GuzzleHttp\Client(['base_uri' => 'registry.example.com']);

$schema = '{"type":"test"}';
$subjectName = 'test-subject';

$promise = $client->sendAsync(
    // Use the request 
    RequestFunctions\registerNewSchemaVersionWithSubjectRequest($schema, $subjectName)
)->then(
    function (\Psr\Http\Message\ResponseInterface $response) {
        // Response mapping to an object model will come in the 2.0 release
        return \GuzzleHttp\json_decode($response->getBody()->getContents(), true)['id'];
    },
    // This will map API error codes to internal exceptions.
    // They won't be thrown, but returned from the promise which leaves you the freedom to handle it the way you want
    new \FlixTech\SchemaRegistryApi\Exception\ExceptionMap()
);

// This is either the globally unique id for the schema or the mapped exception instance
$result = $promise->wait();
```

## Testing

This library uses a `Makefile` to run the test suite.

#### Unit tests

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
