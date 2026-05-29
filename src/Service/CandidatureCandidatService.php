<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class CandidatureCandidatService
{
    private ApiClientService $apiClient;
    private string $baseUrl;

    public function __construct(ApiClientService $apiClient, string $apiBaseUrl)
    {
        $this->apiClient = $apiClient;
        $this->baseUrl   = $apiBaseUrl;
    }

    /**
     * Récupère toutes les candidatures candidat
     * GET /api/candidats/candidature-candidats
     */
    public function getAllCandidatures(Request $request): array
    {
        return $this->apiClient->get($this->baseUrl . '/recruteurs/candidature-candidats', [], AuthService::bearerHeaders($request));
    }

    /**
     * Récupère une candidature candidat précise pour un recruteur connecté
     * GET /api/recruteurs/candidature-candidats/{id}
     */
    public function getCandidature(Request $request, int $id): array
    {
        return $this->apiClient->get($this->baseUrl . '/recruteurs/candidature-candidats/' . $id, [], AuthService::bearerHeaders($request));
    }
}
