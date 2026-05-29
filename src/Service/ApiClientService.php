<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class ApiClientService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Effectuer une requête GET
     */
    public function get(string $url, array $query = [], array $headers = []): array
    {
        try {
            $response = $this->client->request('GET', $url, [
                'query' => $query,
                'headers' => $headers,
            ]);

            return $response->toArray(); // convertit JSON en tableau PHP
        } catch (TransportExceptionInterface|ClientExceptionInterface|ServerExceptionInterface $e) {
            // Gérer l’erreur (log, exception, fallback…)
            throw new \RuntimeException('Erreur API : ' . $e->getMessage());
        }
    }

    /**
     * Effectuer une requête POST
     */
    public function post(string $url, array $data = [], array $headers = []): array
    {
        try {
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
                'json' => $data, // envoie les données au format JSON
            ]);

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                return [
                    'error' => 'Erreur API : ' . $response->getStatusCode()
                ];
            }

            return $response->toArray();
        } catch (TransportExceptionInterface|ClientExceptionInterface|ServerExceptionInterface $e) {
            throw new \RuntimeException('Erreur API : ' . $e->getMessage());
        }
    }

    /**
     * Effectuer une requête PUT
     */
    public function put(string $url, array $data = [], array $headers = []): array
    {
        try {
            $response = $this->client->request('PUT', $url, [
                'json' => $data,
                'headers' => $headers,
            ]);

            return $response->toArray();
        } catch (TransportExceptionInterface|ClientExceptionInterface|ServerExceptionInterface $e) {
            throw new \RuntimeException('Erreur API : ' . $e->getMessage());
        }
    }

    /**
     * Effectuer une requête DELETE
     */
    public function delete(string $url, array $data = [], array $headers = []): array
    {
        try {
            $response = $this->client->request('DELETE', $url, [
                'json' => $data,
                'headers' => $headers,
            ]);

            return $response->toArray();
        } catch (TransportExceptionInterface|ClientExceptionInterface|ServerExceptionInterface $e) {
            throw new \RuntimeException('Erreur API : ' . $e->getMessage());
        }
    }

}