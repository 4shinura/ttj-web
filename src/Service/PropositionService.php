<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class PropositionService
{
    private ApiClientService $apiClient;
    private string $baseUrl;

    public function __construct(ApiClientService $apiClient, string $apiBaseUrl)
    {
        $this->apiClient = $apiClient;
        $this->baseUrl   = $apiBaseUrl;
    }

    /**
     * Récupère les propositions du recruteur connecté
     * GET /api/recruteurs/propositions
     */
    public function getMesPropositions(Request $request): array
    {
        return $this->apiClient->get($this->baseUrl . '/recruteurs/propositions', [], AuthService::bearerHeaders($request));
    }

    /**
     * Crée une proposition
     * POST /api/recruteurs/propositions
     */
    public function createProposition(Request $request, array $payload): array
    {
        return $this->apiClient->post($this->baseUrl . '/recruteurs/propositions', $payload, AuthService::bearerHeaders($request));
    }
}
