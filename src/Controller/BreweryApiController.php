<?php

namespace App\Controller;

use App\Service\DataTableParser;
use App\Service\JwtService;
use App\Service\OpenBreweryClient;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BreweryApiController extends AbstractController
{
    public function __construct(
        private JwtService $jwtService,
        private OpenBreweryClient $client
    )
    {
        $this->client = $client;
        $this->jwtService = $jwtService;
    }

    #[Route('/api/login', name: 'breweries_v1_login', methods: ['POST'])]
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

    #[Route('/api/breweries', name: 'breweries_v1_list', methods: ['GET'])]
    public function breweries(Request $request, DataTableParser $dataTableParser): JsonResponse
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

        $params = $dataTableParser->parseDataTableRequest($request->query->all());

        // get the data
        if(!empty($params['search_term'])) {
            $breweries = $this->client->getBreweriesSearch($params['page'], $params['per_page'], $params['search_term'], $params['sort']);
        }else {
            $breweries = $this->client->getBreweries($params['page'], $params['per_page'], $params['sort']);
        }
        
        $total = $this->client->getBreweriesCount();

        $data = [
            'data' => $breweries,
            "meta" => [
                "current_page" => $params['page'],
                "per_page" => $params['per_page'],
                "total" => $total,
                "last_page" => ceil($total / $params['per_page']),
            ],
            "links" => [
                "first" => "/api/breweries?page=1&per_page=".$params['per_page'],
                "last" => "/api/breweries?page=" . ceil($total / $params['per_page']) . "&per_page=".$params['per_page'],
                "next" => $params['page'] < ceil($total / $params['per_page']) ? "/api/breweries?page=" . ($params['page'] + 1) . "&per_page=".$params['per_page'] : null,
                "prev" => $params['page'] > 1 ? "/api/breweries?page=" . ($params['page'] - 1) . "&per_page=".$params['per_page'] : null,
            ],
            "recordsTotal" => $total,    // Total number of breweries in the database
            "recordsFiltered" => $total, // Total number of breweries after filtering                    
        ];
        return new JsonResponse($data);
    }

}
