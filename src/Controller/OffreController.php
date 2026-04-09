<?php

namespace App\Controller;

use App\Service\OffreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\AuthService;
use App\Service\CandidatureService;

#[Route('')]
class OffreController extends AbstractController
{
    private OffreService $offreService;
    private CandidatureService $candidatureService;

    public function __construct(OffreService $offreService, CandidatureService $candidatureService)
    {
        $this->offreService = $offreService;
        $this->candidatureService = $candidatureService;
    }

    #[Route('/offres', name: 'app_offre_index')]
    public function index(): Response
    {
        $offres = $this->offreService->getAllOffres(); // ← changé
        return $this->render('offre/index.html.twig', [
            'offres' => $offres,
        ]);
    }

    #[Route('/recruteurs/offres', name: 'app_recruteur_offres')]
    public function recruteurOffres(Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        $offres = $this->offreService->getOffresRecruteur($request);
        return $this->render('offre/recruteur_offres.html.twig', [
            'offres' => $offres
        ]);
    }    

    #[Route('/offres/{id}', name: 'app_offre_show_published', methods: ['GET'])]
    public function showPublished(int $id): Response
    {
        try {
            $offre = $this->offreService->getPublishedOffre($id);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Offre introuvable.');
            return $this->redirectToRoute('app_offre_index');
        }

        return $this->render('offre/show.html.twig', [
            'offre' => $offre,
            'isRecruteurView' => false
        ]);
    }

    #[Route('/recruteurs/offres/new', name: 'app_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('offre/new.html.twig');
        }

        $donnees = [
            'type'            => $request->request->get('type_offre'),
            'titre'           => $request->request->get('titre_offre'),
            'description'     => $request->request->get('description_offre'),
            'datePublication' => $request->request->get('date_publication_offre'),
            'dateLimite'      => $request->request->get('date_limite_offre'),
        ];

    
        try {
            $this->offreService->creerOffre($request, $donnees);
            $this->addFlash('success', 'Offre créée avec succès !');
            return $this->redirectToRoute('app_recruteur_offres');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->render('offre/recruteur_offres.html.twig');
        }
    }

    #[Route('/recruteurs/offres/{id}', name: 'app_offre_show', methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        try {
            $offre = $this->offreService->getRecruteurOffre($request, $id);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Offre introuvable.');
            return $this->redirectToRoute('app_recruteur_offres');
        }

        return $this->render('offre/show.html.twig', [
            'offre' => $offre,
            'isRecruteurView' => true
        ]);
    }

    #[Route('/offres/{id}/edit', name: 'app_offre_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        try {
            $offre = $this->offreService->getRecruteurOffre($request, $id);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Offre introuvable.');
            return $this->redirectToRoute('app_recruteur_offres');
        }

        if ($request->isMethod('GET')) {
            return $this->render('offre/edit.html.twig', ['offre' => $offre]);
        }

        $donnees = [
            'type'            => $request->request->get('type_offre'),
            'titre'           => $request->request->get('titre_offre'),
            'description'     => $request->request->get('description_offre'),
            'datePublication' => $request->request->get('date_publication_offre'),
            'dateLimite'      => $request->request->get('date_limite_offre')
        ];

        try {
            $this->offreService->modifierOffre($request, $id, $donnees);
            $this->addFlash('success', 'Offre modifiée avec succès !');
            return $this->redirectToRoute('app_offre_show', ['id' => $id]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->render('offre/edit.html.twig', ['offre' => $offre]);
        }
    }

    #[Route('/offres/{id}/delete', name: 'app_offre_delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_recruteur_offres');
        }

        try {
            $this->offreService->supprimerOffre($request, $id);
            $this->addFlash('success', 'Offre supprimée.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_recruteur_offres');
    }

    #[Route('/recruteurs/offres/{id}/candidatures', name: 'app_offre_candidatures', methods: ['GET'])]
    public function candidatures(int $id, Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $offre = $this->offreService->getRecruteurOffre($request, $id);
            $candidatures = $this->candidatureService->getCandidaturesForOffre($request, $id);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur lors de la récupération des données.');
            return $this->redirectToRoute('app_recruteur_offres');
        }

        return $this->render('offre/candidatures.html.twig', [
            'offre' => $offre,
            'candidatures' => $candidatures,
        ]);
    }

    #[Route('/recruteurs/candidatures/{candidatureId}/statut', name: 'app_candidature_update_statut', methods: ['POST'])]
    public function updateCandidatureStatut(int $candidatureId, Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        $statut = $request->request->get('statut');
        $offreId = $request->request->get('offre_id');

        if (!$statut || !$offreId) {
            $this->addFlash('error', 'Données invalides.');
            return $this->redirectToRoute('app_offre_candidatures', ['id' => $offreId]);
        }

        try {
            $this->candidatureService->updateStatutCandidature($request, $candidatureId, $statut);
            $this->addFlash('success', 'Statut mis à jour.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour.');
        }

        return $this->redirectToRoute('app_offre_candidatures', ['id' => $offreId]);
    }
}