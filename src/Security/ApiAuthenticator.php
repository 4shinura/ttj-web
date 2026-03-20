<?php

namespace App\Security;

use App\Service\ApiClientService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiAuthenticator extends AbstractAuthenticator
{
    private ApiClientService $apiClient;
    private RouterInterface $router;
    private string $baseUrl;

    public function __construct(ApiClientService $apiClient, RouterInterface $router, string $apiBaseUrl)
    {
        $this->apiClient = $apiClient;
        $this->router    = $router;
        $this->baseUrl   = $apiBaseUrl;
    }

    /**
     * Déclenché uniquement sur la route de login en POST
     */
    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('_username');
        $mdp   = $request->request->get('_password');

        // On appelle l'API
        try {
            $reponse = $this->apiClient->post($this->baseUrl . '/auth/login', [
                'identifiant' => $email,
                'mdp'         => $mdp,
            ]);
        } catch (\RuntimeException $e) {
            throw new CustomUserMessageAuthenticationException('Identifiants incorrects.');
        }

        // Si l'API répond que c'est invalide
        if (empty($reponse['isValide']) || $reponse['isValide'] === false) {
            throw new CustomUserMessageAuthenticationException('Identifiants incorrects.');
        }

        // On construit l'objet User depuis la réponse de l'API
        // (adapte les clés selon ce que renvoie réellement ton API)
        $user = new User(
            $reponse['id'],
            $reponse['email'],
            $reponse['nom'],
            $reponse['prenom'],
            ['ROLE_USER']
        );

        // SelfValidatingPassport = pas de vérification de mot de passe côté Symfony
        // (c'est déjà fait par l'API)
        return new SelfValidatingPassport(
            new \Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge(
                $email,
                fn() => $user
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirige vers les offres après connexion
        return new RedirectResponse($this->router->generate('app_offre_index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Redirige vers login avec l'erreur en session
        $request->getSession()->set(
            \Symfony\Component\Security\Http\SecurityRequestAttributes::AUTHENTICATION_ERROR,
            $exception
        );

        return new RedirectResponse($this->router->generate('app_login'));
    }
}