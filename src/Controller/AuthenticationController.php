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

namespace Sulu\SyliusProducerPlugin\Controller;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationController extends Controller
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var AuthenticationProviderInterface
     */
    protected $authenticationProvider;

    public function __construct(
        SerializerInterface $serializer,
        AuthenticationProviderInterface $authenticationProvider
    ) {
        $this->authenticationProvider = $authenticationProvider;
        $this->serializer = $serializer;
    }

    public function authenticateAction(Request $request, int $version): Response
    {
        $email = $request->get('email');
        $plainPassword = $request->get('password');

        $token = new UsernamePasswordToken($email, $plainPassword, 'shop');

        try {
            $token = $this->authenticationProvider->authenticate($token);

            /** @var ShopUserInterface $user */
            $user = $token->getUser();
            if (!$user instanceof ShopUserInterface) {
                throw new \RuntimeException('Invalid instance given');
            }

            $serializationContext = new SerializationContext();
            $serializationContext->setGroups(['Default', 'Detailed']);
            $data = $this->serializer->serialize($user->getCustomer(), 'json', $serializationContext);

            return new JsonResponse($data, 200, [], true);
        } catch (AuthenticationException $exception) {
            return new JsonResponse(get_class($exception), 400);
        }
    }
}
