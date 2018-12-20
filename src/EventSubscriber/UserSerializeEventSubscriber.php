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

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Sylius\Component\User\Model\UserInterface;

class UserSerializeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'format' => 'json',
                'method' => 'onPostSerialize',
            ],
        ];
    }

    public function onPostSerialize(ObjectEvent $event): void
    {
        $object = $event->getObject();
        if (!$event->getContext()->hasAttribute('groups') || !$object instanceof UserInterface) {
            return;
        }

        $groups = $event->getContext()->getAttribute('groups');
        if (!in_array('Detailed', $groups)) {
            return;
        }

        $event->getVisitor()->setData('token', $object->getEmailVerificationToken());
        $event->getVisitor()->setData('hash', $this->generateHash($object));
    }

    private function generateHash(UserInterface $user): string
    {
        return sha1($user->getPassword() . $user->isAccountNonLocked() . $user->isEnabled() . $user->isAccountNonExpired() . $user->isCredentialsNonExpired() . $user->isVerified());
    }
}
