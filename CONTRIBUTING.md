# Documentation

To get started with FactoryBot read [GETTING STARTED](FactoryBot/README.md)

## Deployment to Packagist

[Packagist](https://packagist.org/) is the main registry for [Composer](https://getcomposer.org/) packages.

This package is already registered on Packagist, to release a new version just create a new tag.

### Tagging convention

Packagist uses [semantic versioning](https://semver.org/) as their standard.

tag => `x.y.z` => e.g. `1.2.13`

- x => 1  => MAJOR version upgrade (breaking changes in the API)
- y => 2  => MINOR version upgrade (added new features in a backwards compatible manner)
- z => 13 => PATCH upgrade         (backwards compatible bug fixes)

## Contribution

### Setup

Install dependencies with the following command:

```
composer install
```

## Coding Convention

We enforce PSR-12 coding conventions

all public properties and functions must have documentation

all private properties and functions should have documentation

new features must be documented in the README

all test assertions must have messages

## Testing

To run the test suite run the following command:

```
composer test
```
