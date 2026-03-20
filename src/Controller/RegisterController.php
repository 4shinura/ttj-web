<?php

namespace App\Controller;

use App\Service\ApiClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private ApiClientService $apiClient;
    private string $baseUrl;

    public function __construct(ApiClientService $apiClient, string $apiBaseUrl)
    {
        $this->apiClient = $apiClient;
        $this->baseUrl   = $apiBaseUrl;
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        // Si déjà connecté, on redirige
        if ($this->getUser()) {
            return $this->redirectToRoute('app_offre_index');
        }

        // Affichage du formulaire
        if ($request->isMethod('GET')) {
            return $this->render('security/register.html.twig');
        }

        // Traitement du formulaire
        try {
            $this->apiClient->post($this->baseUrl . '/register', [
                'nom'    => $request->request->get('nom_utilisateur'),
                'prenom' => $request->request->get('prenom_utilisateur'),
                'email'  => $request->request->get('email_utilisateur'),
                'mdp'    => $request->request->get('mdp_utilisateur'),
            ]);

            $this->addFlash('success', 'Inscription envoyée ! En attente de validation.');
            return $this->redirectToRoute('app_login');

        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur lors de l\'inscription : ' . $e->getMessage());
            return $this->render('security/register.html.twig');
        }
    }
}