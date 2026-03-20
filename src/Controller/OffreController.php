<?php

namespace App\Controller;

use App\Service\OffreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/offres')]
class OffreController extends AbstractController
{
    private OffreService $offreService;

    // ID recruteur hardcodé temporairement en attendant la connexion
    private const RECRUTEUR_ID = 1;

    public function __construct(OffreService $offreService)
    {
        $this->offreService = $offreService;
    }

    #[Route('', name: 'app_offre_index')]
    public function index(): Response
    {
        $offres = $this->offreService->getOffresRecruteur(self::RECRUTEUR_ID);

        return $this->render('offre/index.html.twig', [
            'offres' => $offres,
        ]);
    }

    #[Route('/new', name: 'app_offre_new', methods: ['GET', 'POST'])]
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
            $this->offreService->creerOffre(self::RECRUTEUR_ID, $donnees);
            $this->addFlash('success', 'Offre créée avec succès !');
            return $this->redirectToRoute('app_offre_index');

        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->render('offre/new.html.twig');
        }
    }

    #[Route('/{id}', name: 'app_offre_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        try {
            $offre        = $this->offreService->getOffre(self::RECRUTEUR_ID, $id);
            $candidatures = $this->offreService->getCandidatures(self::RECRUTEUR_ID, $id);

        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Offre introuvable.');
            return $this->redirectToRoute('app_offre_index');
        }

        return $this->render('offre/show.html.twig', [
            'offre'        => $offre,
            'candidatures' => $candidatures,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offre_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        try {
            $offre = $this->offreService->getOffre(self::RECRUTEUR_ID, $id);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Offre introuvable.');
            return $this->redirectToRoute('app_offre_index');
        }

        if ($request->isMethod('GET')) {
            return $this->render('offre/edit.html.twig', [
                'offre' => $offre,
            ]);
        }

        $donnees = [
            'type'            => $request->request->get('type_offre'),
            'titre'           => $request->request->get('titre_offre'),
            'description'     => $request->request->get('description_offre'),
            'datePublication' => $request->request->get('date_publication_offre'),
            'dateLimite'      => $request->request->get('date_limite_offre'),
            'statut'          => $request->request->get('statut_offre'),
        ];

        try {
            $this->offreService->modifierOffre(self::RECRUTEUR_ID, $id, $donnees);
            $this->addFlash('success', 'Offre modifiée avec succès !');
            return $this->redirectToRoute('app_offre_show', ['id' => $id]);

        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->render('offre/edit.html.twig', ['offre' => $offre]);
        }
    }

    #[Route('/{id}/delete', name: 'app_offre_delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        // Vérification du token CSRF pour sécuriser la suppression
        if (!$this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_offre_index');
        }

        try {
            $this->offreService->supprimerOffre(self::RECRUTEUR_ID, $id);
            $this->addFlash('success', 'Offre supprimée.');

        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_offre_index');
    }
}