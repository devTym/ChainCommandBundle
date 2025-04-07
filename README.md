# ChainCommandBundle

This Symfony bundle allows you to define chains of console commands.

## Installation

```bash
composer require devtym/chain-command-bundle
```

## Configuration
Enable the bundle in config/bundles.php
```bash
return [
    // ...
    DevTym\ChainCommandBundle\ChainCommandBundle::class => ['all' => true],
];
```

Then, define your command chains in a YAML config (e.g. config/packages/chain_command.yaml):
```bash
chain_command:
  options:
    logging: true
  chains:
    foo:hello:
      members:
        - command: bar:hi
```

## Demonstration

```bash
composer require devtym/foo-bundle
composer require devtym/bar-bundle
```

### Example Usage
```bash
php bin/console master:hello
```
