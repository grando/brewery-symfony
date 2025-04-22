<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenBreweryClient
{
    public function __construct(
        private HttpClientInterface $client,
        public string $baseUrl,
    )
    {
    }

    public function getBreweries($page = 1, $perPage = 10): array
    {
        $response = $this->client->request('GET', $this->baseUrl. '/v1/breweries', [
            'query' => [
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);

        return $response->toArray();
    }
}