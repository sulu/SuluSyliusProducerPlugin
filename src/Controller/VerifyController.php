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

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class VerifyController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UserRepositoryInterface $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->userRepository = $userRepository;
    }

    public function verifyAction(Request $request, int $version, string $token): Response
    {
        /** @var ShopUserInterface $user */
        $user = $this->userRepository->findOneBy(['emailVerificationToken' => $token]);
        Assert::notNull($user, sprintf('User has not been found.'));

        $user->setVerifiedAt(new \DateTime());
        $user->setEmailVerificationToken(null);
        $user->enable();

        $this->entityManager->flush();

        $data = $this->serializer->serialize($user, 'json');

        return new JsonResponse($data, 200, [], true);
    }
}