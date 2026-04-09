<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class OffreService
{
    private ApiClientService $apiClient;
    private string $baseUrl;

    public function __construct(ApiClientService $apiClient, string $apiBaseUrl)
    {
        $this->apiClient = $apiClient;
        $this->baseUrl   = $apiBaseUrl;
    }

    /**
     * Récupère toutes les offres du recruteur connecté
     * GET /api/recruteurs/offres
     */
    public function getOffresRecruteur(Request $request): array
    {
        return $this->apiClient->get($this->baseUrl . '/recruteurs/offres', [], AuthService::bearerHeaders($request));
    }

    

    /**
     * Récupère une offre précise
     * GET /api/offres/{id}
     */
    public function getRecruteurOffre(Request $request, int $offreId): array
    {
        return $this->apiClient->get($this->baseUrl . '/recruteurs/offres/' . $offreId, [], AuthService::bearerHeaders($request));
    }

    /**
     * Récupère toutes les offres publiques
     * GET /api/offres
     */
    public function getAllOffres(): array
    {
        return $this->apiClient->get($this->baseUrl . '/offres');
    }

    /**
     * Récupère une offre publiée
     * GET /api/offres/{id}
     */
    public function getPublishedOffre(int $offreId): array
    {
        return $this->apiClient->get($this->baseUrl . '/offres/' . $offreId);
    }

    /**
     * Crée une nouvelle offre
     * POST /api/offres
     */
    public function creerOffre(Request $request, array $donnees): array
    {
        return $this->apiClient->post($this->baseUrl . '/recruteurs/offres', $donnees, AuthService::bearerHeaders($request));
    }

    /**
     * Modifie une offre
     * PUT /api/offres/{id}
     */
    public function modifierOffre(Request $request, int $offreId, array $donnees): array
    {
        return $this->apiClient->put($this->baseUrl . '/recruteurs/offres/' . $offreId, $donnees, AuthService::bearerHeaders($request));
    }

    /**
     * Supprime une offre
     * DELETE /api/offres/{id}
     */
    public function supprimerOffre(Request $request, int $offreId): array
    {
        return $this->apiClient->delete($this->baseUrl . '/recruteurs/offres/' . $offreId, [], AuthService::bearerHeaders($request));
    }
}