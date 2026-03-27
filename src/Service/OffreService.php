<?php

namespace App\Service;

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
    public function getOffresRecruteur(): array
    {
        return $this->apiClient->get($this->baseUrl . '/recruteurs/offres');
    }

    /**
     * Récupère une offre précise
     * GET /api/offres/{id}
     */
    public function getOffre(int $offreId): array
    {
        return $this->apiClient->get($this->baseUrl . '/offres/' . $offreId);
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
     * Crée une nouvelle offre
     * POST /api/offres
     */
    public function creerOffre(array $donnees): array
    {
        return $this->apiClient->post($this->baseUrl . '/offres', $donnees);
    }

    /**
     * Modifie une offre
     * PUT /api/offres/{id}
     */
    public function modifierOffre(int $offreId, array $donnees): array
    {
        return $this->apiClient->put($this->baseUrl . '/offres/' . $offreId, $donnees);
    }

    /**
     * Supprime une offre
     * DELETE /api/offres/{id}
     */
    public function supprimerOffre(int $offreId): array
    {
        return $this->apiClient->delete($this->baseUrl . '/offres/' . $offreId);
    }
}
