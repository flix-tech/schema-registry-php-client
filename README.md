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

The documentation is currently being written, but until then you can have a look into the unit and integration tests.

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
