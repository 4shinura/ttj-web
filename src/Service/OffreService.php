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
     */
    public function getOffresRecruteur(int $recruteurId): array
    {
        return $this->apiClient->get(
            $this->baseUrl . '/recruteur/' . $recruteurId . '/offres'
        );
    }

    /**
     * Récupère une offre précise du recruteur
     */
    public function getOffre(int $recruteurId, int $offreId): array
    {
        return $this->apiClient->get(
            $this->baseUrl . '/recruteur/' . $recruteurId . '/offres/' . $offreId
        );
    }

    /**
     * Crée une nouvelle offre
     */
    public function creerOffre(int $recruteurId, array $donnees): array
    {
        return $this->apiClient->post(
            $this->baseUrl . '/recruteur/' . $recruteurId . '/offre',
            $donnees
        );
    }

    /**
     * Modifie une offre existante
     */
    public function modifierOffre(int $recruteurId, int $offreId, array $donnees): array
    {
        return $this->apiClient->put(
            $this->baseUrl . '/recruteur/' . $recruteurId . '/offres/' . $offreId,
            $donnees
        );
    }

    /**
     * Supprime une offre
     */
    public function supprimerOffre(int $recruteurId, int $offreId): array
    {
        return $this->apiClient->delete(
            $this->baseUrl . '/recruteur/' . $recruteurId . '/offres/' . $offreId
        );
    }

    /**
     * Récupère les candidatures d'une offre
     */
    public function getCandidatures(int $recruteurId, int $offreId): array
    {
        return $this->apiClient->get(
            $this->baseUrl . '/recruteur/' . $recruteurId . '/offres/' . $offreId . '/postulations'
        );
    }

    /**
     * Change le statut d'une candidature (accepté / refusé)
     */
    public function updateStatutCandidature(int $recruteurId, int $offreId, int $postulationId, string $statut): array
    {
        return $this->apiClient->put(
            $this->baseUrl . '/recruteur/' . $recruteurId . '/offres/' . $offreId . '/postulations/' . $postulationId,
            ['statutPostulation' => $statut]
        );
    }
}