# SuluSyliusProducerPlugin

[![Build Status](https://travis-ci.org/sulu/SuluSyliusProducerPlugin.svg)](https://travis-ci.org/sulu/SuluSyliusProducerPlugin)

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

### Add routes

```bash
// config/routes/sulu_sylius_producer.yaml

sylius_sulu_admin_api:
    resource: "@SuluSyliusProducerPlugin/Resources/config/routing.yml"
    prefix: /api
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
