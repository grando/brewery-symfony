<?php

namespace App\Controller;

use App\Service\JwtService;
use App\Service\OpenBreweryClient;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'breweries_v1_')]
final class BreweryController extends AbstractController
{
    public function __construct(
        private JwtService $jwtService,
        private OpenBreweryClient $client
    )
    {
        $this->client = $client;
        $this->jwtService = $jwtService;
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(
        Request $request, 
        UserService $userService,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['username']) || empty($data['password'])) {
            return new JsonResponse(['message' => 'Credentials are required'], Response::HTTP_BAD_REQUEST);
        }

        $token = $userService->generateUserToken($data['username'], $data['password']);
        if(null === $token) {
            return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/breweries', name: 'list', methods: ['GET'])]
    public function breweries(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $token = substr($authHeader, 7);
        $user = $this->jwtService->validateToken($token);

        if (!$user) {
            return new JsonResponse(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('per_page', 10);

        $breweries = $this->client->getBreweries($page, $perPage);

        return new JsonResponse($breweries);
    }
}
