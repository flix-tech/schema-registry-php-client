# Confluent Schema Registry PHP API

[![Build Status](https://travis-ci.org/flix-tech/schema-registry-php-client.svg?branch=master)](https://travis-ci.org/flix-tech/schema-registry-php-client)

A PHP 7.0+ library to consume the Confluent Schema Registry REST API. It provides low level functions to create PSR-7
compliant requests that can be used as well as high level abstractions to ease developer experience

## Requirements

### Hard dependencies

| Dependency | Version | Reason |
|:--- |:---:|:--- |
| **`php`** | ~7.0 | Anything lower has reached EOL |
| **`guzzlephp/guzzle`** | ~6.0 | Using `Request` to build PSR-7 `RequestInterface` |
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

**TBD** (I need to create examples, the test suites are a pretty good place to see how this works)

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
