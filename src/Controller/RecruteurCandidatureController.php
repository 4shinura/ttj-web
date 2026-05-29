<?php

namespace App\Controller;

use App\Service\AuthService;
use App\Service\CandidatureCandidatService;
use App\Service\OffreService;
use App\Service\PropositionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('')]
final class RecruteurCandidatureController extends AbstractController
{
    public function __construct(
        private CandidatureCandidatService $candidatureCandidatService,
        private OffreService $offreService,
        private PropositionService $propositionService
    ) {}

    #[Route('/recruteurs/candidatures', name: 'app_recruteur_candidatures', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $candidatures = $this->candidatureCandidatService->getAllCandidatures($request);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Impossible de récupérer les candidatures.');
            $candidatures = [];
        }

        $query = trim((string) $request->query->get('q', ''));
        $filtered = $candidatures;

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $filtered = array_values(array_filter($candidatures, function (array $candidature) use ($needle): bool {
                foreach (['titre', 'competence', 'typePoste'] as $field) {
                    if (isset($candidature[$field]) && mb_stripos((string) $candidature[$field], $needle) !== false) {
                        return true;
                    }
                }

                return false;
            }));
        }

        return $this->render('recruteur/candidature/index.html.twig', [
            'candidatures' => $filtered,
            'searchQuery' => $query,
        ]);
    }

    #[Route('/recruteurs/candidatures/{id}', name: 'app_recruteur_candidature_show', methods: ['GET'])]
    public function show(int $id, Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $candidature = $this->candidatureCandidatService->getCandidature($request, $id);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Candidature introuvable.');
            return $this->redirectToRoute('app_recruteur_candidatures');
        }

        try {
            $offres = $this->offreService->getOffresRecruteur($request);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Impossible de récupérer vos offres.');
            $offres = [];
        }

        return $this->render('recruteur/candidature/show.html.twig', [
            'candidature' => $candidature,
            'offres' => $offres,
        ]);
    }

    #[Route('/recruteurs/candidatures/{id}/proposer', name: 'app_recruteur_candidature_proposer', methods: ['POST'])]
    public function proposer(int $id, Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        $offreId = (int) $request->request->get('offre_id');
        if ($offreId <= 0) {
            $this->addFlash('error', 'Veuillez sélectionner une offre.');
            return $this->redirectToRoute('app_recruteur_candidature_show', ['id' => $id]);
        }

        $payload = [
            'offre_id' => $offreId,
            'candidature_candidat_id' => $id,
            'date' => (new \DateTimeImmutable('today'))->format('Y-m-d'),
            'statut' => 'send',
        ];

        try {
            $result = $this->propositionService->createProposition($request, $payload);
            if (isset($result['error'])) {
                $this->addFlash('error', $result['error']);
                return $this->redirectToRoute('app_recruteur_candidature_show', ['id' => $id]);
            }

            $this->addFlash('success', 'Proposition créée avec succès.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur lors de la création de la proposition.');
        }

        return $this->redirectToRoute('app_recruteur_candidature_show', ['id' => $id]);
    }

    #[Route('/recruteurs/propositions', name: 'app_recruteur_mes_propositions', methods: ['GET'])]
    public function mesPropositions(Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $propositions = $this->propositionService->getMesPropositions($request);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Impossible de récupérer vos propositions.');
            $propositions = [];
        }

        return $this->render('recruteur/candidature/propositions.html.twig', [
            'propositions' => $propositions,
        ]);
    }
}
