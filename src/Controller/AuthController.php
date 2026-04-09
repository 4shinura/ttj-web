<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Service\ApiClientService;

class AuthController extends AbstractController
{
    private string $baseUrl;
    private readonly ApiClientService $apiClientService;

    public function __construct(string $apiBaseUrl, ApiClientService $apiClientService)
    {
        $this->apiClientService = $apiClientService;
        $this->baseUrl   = $apiBaseUrl;
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        // Si déjà connecté, on redirige directement
        if (AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_recruteur_offres');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            try {
                $data = $this->apiClientService->post($this->baseUrl . '/auth/login', [
                    'email'    => $request->request->get('email'),
                    'password' => $request->request->get('password')
                ]);

                if (isset($data['token'])) {
                    $payload = AuthService::jwt_decode($data['token']);

                    if (!$payload) {
                        $error = 'Token invalide reçu de l\'API';
                    } elseif ($payload['user']['type'] !== 'recruteur') {
                        $error = 'Accès refusé : compte non recruteur';
                    } else {
                        $request->getSession()->set('username', $payload['user']['nom'] . ' ' . $payload['user']['prenom']);
                        $response = $this->redirectToRoute('app_recruteur_offres');
                        $response->headers->setCookie(AuthService::buildCookie($data['token']));
                        return $response;
                    }
                } else {
                    $error = 'Email ou mot de passe incorrect';
                }
            } catch (HttpExceptionInterface $e) {
                $error = $e->getCode() === 401
                    ? 'Email ou mot de passe incorrect'
                    : 'Erreur de connexion au serveur';
            } catch (TransportExceptionInterface) {
                $error = 'Erreur réseau : impossible de contacter l\'API';
            }
        }

        return $this->render('security/login.html.twig', [
            'error'         => $error,
        ]);
    }

    #[Route('/admin/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        $request->getSession()->clear();
        $response = $this->redirectToRoute('app_login');
        $response->headers->clearCookie('access_token', '/');
        return $response;
    }
}