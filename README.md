# SuluSyliusProducerPlugin

[![Test workflow status](https://img.shields.io/github/workflow/status/sulu/SuluSyliusProducerPlugin/Build.svg?label=build)](https://github.com/sulu/SuluSyliusProducerPlugin/actions)

Producer for synchronization products with sulu.

## Installation

```bash
composer require sulu/sylius-producer-plugin
```

### Register the plugin

```bash
// config/bundles.php

    Sulu\SyliusProducerPlugin\SuluSyliusProducerPlugin::class => ['all' => true],
```

### Add configuration

```bash
// config/packages/sulu_sylius_producer.yaml

imports:
    - { resource: "@SuluSyliusProducerPlugin/Resources/config/app/config.yaml" }
    
framework:
    messenger:
        transports:
            sulu_sylius_transport: 'redis://localhost:6379/sulu_sylius_products'
```
