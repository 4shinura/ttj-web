<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class CandidatureService
{
    private ApiClientService $apiClient;
    private string $baseUrl;

    public function __construct(ApiClientService $apiClient, string $apiBaseUrl)
    {
        $this->apiClient = $apiClient;
        $this->baseUrl   = $apiBaseUrl;
    }

    /**
     * Récupère les candidatures pour une offre
     * GET /api/recruteurs/offres/{offreId}/candidatures
     */
    public function getCandidaturesForOffre(Request $request, int $offreId): array
    {
        return $this->apiClient->get($this->baseUrl . '/recruteurs/offres/' . $offreId . '/candidatures', [], AuthService::bearerHeaders($request));
    }

    /**
     * Met à jour le statut d'une candidature
     * PUT /api/recruteurs/candidatures/{id}/statut
     */
    public function updateStatutCandidature(Request $request, int $candidatureId, string $statut): array
    {
        return $this->apiClient->put($this->baseUrl . '/recruteurs/candidatures/' . $candidatureId . '/statut', ['statut' => $statut], AuthService::bearerHeaders($request));
    }
}