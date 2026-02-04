# dependency-injection

PoC for **automatic dependency injection** in PHP: the container instantiates a class by recursively resolving constructor parameter types (reflection) and respecting interface → implementation bindings.


## How it works

- **Container**: `get($class)` creates the instance (singleton), resolves constructor parameters via **ClassAnalyzer**, and uses **bindings** for interfaces.
- **ClassAnalyzer**: analyzes the constructor (typed parameters) and caches the result (invalidation by source file modification time).
- **Cache**: `CacheInterface` with implementations `CacheFile`, `CacheMemory`, `CacheRedis` (optional cache for analyses).

## Requirements

PHP 8.3+, Composer

## Installation

```bash
composer install
```

## Usage

Full example and scenarios see `example.php`.

## Structure

```
src/DI/
├── Core/
│   ├── CacheInterface.php
│   ├── ClassAnalyzer.php
│   └── Container.php
└── Infrastructure/
    ├── CacheFile.php
    ├── CacheMemory.php
    └── CacheRedis.php
```

## Tests

[![Tests](https://github.com/tivins/poc-di-inferred/actions/workflows/tests.yml/badge.svg)](https://github.com/tivins/poc-di-inferred/actions/workflows/tests.yml)

```bash
composer test
composer test:coverage
```

## License

MIT
