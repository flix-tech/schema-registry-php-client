# Confluent Schema Registry PHP API

A PHP 7.0+ library to consume the Confluent Schema Registry REST API. It only provides PSR-7 compatible requests via
functions that can be used in conjunction with any client that is able to handle any requests that implement the PSR-7
`RequestInterface`.

## Requirements

| Dependency | Version | Reason |
|:---:|:---:|:--- |
| **`php`** | ~7.0 | Anything lower has reached EOL |
| **`guzzlephp/guzzle`** | ~6.0 | Using `Request` to build PSR-7 `RequestInterface` |
| **`beberlei/assert`** | ~2.0 | The de-facto standard assertions library for PHP |
| **`roave/security-advisories`** | dev-master | Because security, right? |

## Installation

This library is installed via [`composer`](http://getcomposer.org).

```bash
$ composer require "flix-tech/confluent-schema-registry-api=~1.0"
```
