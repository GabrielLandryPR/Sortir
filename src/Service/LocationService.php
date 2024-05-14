<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LocationService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function searchLocation(string $query): array
    {
        $url = 'https://nominatim.openstreetmap.org/search';
        $response = $this->client->request('GET', $url, [
            'query' => [
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'countrycodes' => 'fr',
                'limit' => 5
            ],
        ]);

        return $response->toArray();
    }
}
