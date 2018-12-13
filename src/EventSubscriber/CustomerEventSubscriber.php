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

namespace Sulu\SyliusProducerPlugin\EventSubscriber;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\User\Security\Generator\GeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CustomerEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'sylius.customer.pre_create' => 'createToken',
        ];
    }

    /**
     * @var GeneratorInterface
     */
    private $tokenGenerator;

    public function __construct(GeneratorInterface $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    public function createToken(GenericEvent $event)
    {
        $customer = $event->getSubject();
        if (!$customer instanceof CustomerInterface) {
            return;
        }

        $user = $customer->getUser();
        if (!$user) {
            return;
        }

        $token = $this->tokenGenerator->generate();
        $user->setEmailVerificationToken($token);
    }
}
