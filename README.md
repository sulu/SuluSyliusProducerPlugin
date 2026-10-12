# SuluSyliusProducerPlugin

<a href="https://github.com/sulu/SuluSyliusProducerPlugin/actions" target="_blank">
    <img src="https://img.shields.io/github/actions/workflow/status/sulu/SuluSyliusProducerPlugin/test-application.yaml" alt="Test workflow status">
</a>

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

### Synchronize on every change

By default the plugin synchronizes on the Sylius resource events. Only the resource controller of the admin
dispatches them, so changes through the API, imports or custom code are missed. The Doctrine listener
catches every change that reaches the database:

```yaml
// config/packages/sulu_sylius_producer.yaml

sulu_sylius_producer:
    doctrine_listener:
        enabled: true # default: false
    resource_events:
        enabled: false # default: true, turn it off to not send each admin change twice
```

The listener watches products, variants, taxons and what belongs to them: translations, images, product taxons
and attribute values. It sends one message per entity and flush, after the flush is committed.
