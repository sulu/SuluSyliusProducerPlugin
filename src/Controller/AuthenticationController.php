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
     * @var AuthenticationProviderInterface
     */
    private $authenticationProvider;

    public function __construct(
        AuthenticationProviderInterface $authenticationProvider
    ) {
        $this->authenticationProvider = $authenticationProvider;
    }

    public function authenticateAction(Request $request, int $version): Response
    {
        $email = $request->get('email');
        $plainPassword = $request->get('password');

        $data = [
            'user' => null,
            'exception' => null,
        ];

        $token = new UsernamePasswordToken($email, $plainPassword, 'shop');
        try {
            $token = $this->authenticationProvider->authenticate($token);
            $user = $token->getUser();

            $data['user'] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'gender' => $user->getCustomer()->getGender(),
                'firstName' => $user->getCustomer()->getFirstName(),
                'lastName' => $user->getCustomer()->getLastName(),
                'email' => $user->getCustomer()->getEmail(),
            ];
        } catch (AuthenticationException $exception) {
            $data['exception'] = get_class($exception);
        }

        return new JsonResponse($data);
    }
}