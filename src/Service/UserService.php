<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JwtService $jwtService
    ) {
    }

    public function generateUserToken(string $username, string $hashedPassword): ?string
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $hashedPassword)) {
            return null;
        }

        $token = $this->jwtService->generateToken(['id' => $user->getId(), 'username' => $user->getUsername()]);
        return $token;
    }
    
}

 