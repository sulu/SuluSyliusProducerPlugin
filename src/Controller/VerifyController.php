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
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

    public function verifyAction(int $version, string $token): Response
    {
        /** @var ShopUserInterface|null $user */
        $user = $this->userRepository->findOneBy(['emailVerificationToken' => $token]);
        if (null === $user) {
            return new JsonResponse(null, 404);
        }

        $this->verifyUser($user);

        $this->entityManager->flush();

        $customer = $user->getCustomer();
        if (!$customer) {
            return new JsonResponse(null, 401);
        }

        $serializationContext = new SerializationContext();
        $serializationContext->setGroups(['Default', 'Detailed', 'CustomData']);
        $data = $this->serializer->serialize($customer, 'json', $serializationContext);

        return new JsonResponse($data, 200, [], true);
    }

    protected function verifyUser(ShopUserInterface $user): void
    {
        $user->setVerifiedAt(new \DateTime());
        $user->setEmailVerificationToken(null);
        $user->enable();
    }
}
