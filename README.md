# AnonymizerBundle

[![CI](https://github.com/PHPSharkTank/AnonymizerBundle/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/PHPSharkTank/AnonymizerBundle/actions/workflows/ci.yml)
[![Coverage](https://codecov.io/gh/PHPSharkTank/AnonymizerBundle/branch/main/graph/badge.svg)](https://codecov.io/gh/PHPSharkTank/AnonymizerBundle)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg)](https://phpstan.org/)
[![semantic-release](https://img.shields.io/badge/semantic--release-angular-e10079?logo=semantic-release)](https://github.com/semantic-release/semantic-release)

[![Latest Stable Version](https://img.shields.io/packagist/v/php-shark-tank/anonymizer-bundle)](https://packagist.org/packages/php-shark-tank/anonymizer-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/php-shark-tank/anonymizer-bundle)](https://packagist.org/packages/php-shark-tank/anonymizer-bundle)
[![PHP Version](https://img.shields.io/packagist/dependency-v/php-shark-tank/anonymizer-bundle/php)](https://packagist.org/packages/php-shark-tank/anonymizer-bundle)
[![Symfony Version](https://img.shields.io/packagist/dependency-v/php-shark-tank/anonymizer-bundle/symfony/http-kernel?label=symfony)](https://packagist.org/packages/php-shark-tank/anonymizer-bundle)
[![License](https://img.shields.io/packagist/l/php-shark-tank/anonymizer-bundle)](LICENSE)

Symfony bundle to integrate the [Anonymizer](https://github.com/PHPSharkTank/Anonymizer) library into a Symfony application.

# Installation

```bash
composer require php-shark-tank/anonymizer-bundle
```

Register the bundle in `config/bundles.php` (Symfony Flex does this automatically):

```php
return [
    // ...
    PHPSharkTank\AnonymizerBundle\AnonymizerBundle::class => ['all' => true],
];
```

# Usage

Inject `PHPSharkTank\Anonymizer\AnonymizerInterface` (or the `anonymizer` service) and pass the objects to anonymize:

```php
use PHPSharkTank\Anonymizer\AnonymizerInterface;

final class AnonymizeUserHandler
{
    public function __construct(
        private readonly AnonymizerInterface $anonymizer,
    ) {}

    public function __invoke(User $user): void
    {
        $this->anonymizer->process($user);
    }
}
```

How to mark classes and properties for anonymization is described in the [library documentation](https://github.com/PHPSharkTank/Anonymizer#documentation).

Services implementing `PHPSharkTank\Anonymizer\Handler\HandlerInterface` or `PHPSharkTank\Anonymizer\Registry\HandlerRegistryInterface` are registered automatically when autoconfiguration is enabled.

# Configuration

```yaml
# config/packages/sharktank_anonymizer.yaml
sharktank_anonymizer:
    # register the public "anonymizer" service alias
    enable_alias: true

    exclusion_strategy:
        # skip objects/properties via #[Skip] expressions, requires symfony/expression-language
        expression:
            enabled: false

    # cache the mapping metadata in the cache.<pool> service
    cache:
        enabled: false
        pool: system

    # enable the faker handlers, requires fakerphp/faker
    faker:
        enabled: false
        # service id of a Faker\Generator
        id: faker

    # YAML mapping files, in addition to the PHP attributes
    yaml_config: []
```
