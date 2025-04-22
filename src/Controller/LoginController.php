<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST', 'GET'])]
    public function login(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher,
        JwtService $jwtService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
$data = $data ?: [
            'username' => 'root',
            'password' => 'password',
];
        //var_dump('login', $data);
        if (empty($data['username']) || empty($data['password'])) {
            return new JsonResponse(['message' => 'Invalid credentials'], 401);
        }

        // Validate the request data        

//var_dump('login', $data);
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['message' => 'Invalid credentials'], 401);
        }

        $token = $jwtService->generateToken(['id' => $user->getId(), 'username' => $user->getUsername()]);
        //$token = 'fake-jwt-token'; // Replace with actual JWT generation logic

        return new JsonResponse(['token' => $token]);
    }
}
