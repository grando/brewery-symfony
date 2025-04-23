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

    public function getBreweries(
        int $page = 1, 
        int $perPage = 10, 
        ?string $sortParams = null
    ): array
    {
        $url = '/v1/breweries';
        $query = [
            'page' => $page,
            'per_page' => $perPage,
        ];

        if($sortParams) {
            $query['sort'] = $sortParams; 
        }

        $response = $this->client->request('GET', $this->baseUrl . $url, [
            'query' => $query,
        ]);

        return $response->toArray();
    }

    public function getBreweriesSearch(
        int $page = 1, 
        int $perPage = 10, 
        ?string $searchTerm = null, 
        ?string $sortParams = null
    ): array
    {
        $url = '/v1/breweries/search'; 
        $query = [
            'page' => $page,
            'per_page' => $perPage,
        ];

        // Add search parameter if provided
        if ($searchTerm) {
            $query['query'] = $searchTerm; // The API uses 'query' for general search
        }

        if($sortParams) {
            $query['sort'] = $sortParams; 
        }

        $response = $this->client->request('GET', $this->baseUrl . $url, [
            'query' => $query,
        ]);

        return $response->toArray();
    }

    public function getBreweriesMeta(): ?array
    {
        try {
            $response = $this->client->request('GET', $this->baseUrl . '/v1/breweries/meta');
            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getBreweriesCount(): int
    {
        $meta = $this->getBreweriesMeta(); // Call to get meta data first
        return $meta['total'] ?? 0; // Return total count from meta data
    }

}