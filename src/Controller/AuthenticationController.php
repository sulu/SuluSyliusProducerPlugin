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

        $data = null;

        $token = new UsernamePasswordToken($email, $plainPassword, 'shop');
        $result = $this->authenticationProvider->authenticate($token);

        if ($result) {
            $user = $result->getUser();
            $data = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'gender' => $user->getCustomer()->getGender(),
                'firstName' => $user->getCustomer()->getFirstName(),
                'lastName' => $user->getCustomer()->getLastName(),
                'email' => $user->getCustomer()->getEmail(),
            ];
        }

        return new JsonResponse($data);
    }
}