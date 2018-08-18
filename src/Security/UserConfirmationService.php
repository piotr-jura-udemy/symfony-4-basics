<?php

namespace App\Security;

use App\Exception\InvalidConfirmationTokenException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserConfirmationService
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function confirmUser(string $confirmationToken)
    {
        $user = $this->userRepository->findOneBy(
            ['confirmationToken' => $confirmationToken]
        );

        // User was NOT found by confirmation token
        if (!$user) {
            throw new InvalidConfirmationTokenException();
        }

        $user->setEnabled(true);
        $user->setConfirmationToken(null);
        $this->entityManager->flush();
    }
}
