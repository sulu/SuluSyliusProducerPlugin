<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace spec\Sulu\SyliusProducerPlugin\Producer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\RemoveProductVariantMessage;
use Sulu\Bundle\SyliusConsumerBundle\Model\Product\Message\SynchronizeProductVariantMessage;
use Sulu\SyliusProducerPlugin\Producer\ProductVariantMessageProducer;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductVariantMessageProducerSpec extends ObjectBehavior
{
    public function let(
        SerializerInterface $serializer,
        MessageBusInterface $messageBus
    ): void {
        $this->beConstructedWith($serializer, $messageBus);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductVariantMessageProducer::class);
    }

    public function it_should_dispatch_synchronize_message(
        SerializerInterface $serializer,
        MessageBusInterface $messageBus,
        ProductVariantInterface $productVariant,
        ProductInterface $product
    ): void {
        $productVariant->getCode()->willReturn('product-1-variant-0');
        $productVariant->getProduct()->willReturn($product);
        $product->getCode()->willReturn('product-1');
        $serializer->serialize($productVariant, 'json', Argument::type(SerializationContext::class))
            ->shouldBeCalled()->willReturn('{"code": "product-1"}');

        $this->synchronize($productVariant);

        $messageBus->dispatch(
            Argument::that(
                function (SynchronizeProductVariantMessage $message) {
                    return 'product-1-variant-0' === $message->getCode()
                        && 'product-1' === $message->getProductCode()
                        && ['code' => 'product-1'] === $message->getPayload();
                }
            )
        )->shouldBeCalled();
    }

    public function it_should_dispatch_remove_message(
        MessageBusInterface $messageBus,
        ProductVariantInterface $productVariant
    ): void {
        $productVariant->getCode()->willReturn('product-1-variant-0');

        $this->remove($productVariant);

        $messageBus->dispatch(
            Argument::that(
                function (RemoveProductVariantMessage $message) {
                    return 'product-1-variant-0' === $message->getCode();
                }
            )
        )->shouldBeCalled();
    }
}
