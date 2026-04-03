<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use App\Service\ApiClientService;

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

    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('_username');
        $mdp   = $request->request->get('_password');

        try {
            $reponse = $this->apiClient->post($this->baseUrl . '/auth/login', [
                'email'    => $email,
                'password' => $mdp,
            ]);
        } catch (\RuntimeException $e) {
            throw new CustomUserMessageAuthenticationException('Identifiants incorrects.');
        }

        if (empty($reponse['user'])) {
            throw new CustomUserMessageAuthenticationException('Identifiants incorrects.');
        }

        $userData = $reponse['user'];

        $user = new User(
            $userData['id'],
            $userData['email'],
            $userData['nom'],
            $userData['prenom'],
            ['ROLE_USER']
        );

        return new SelfValidatingPassport(
            new UserBadge($email, fn() => $user)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app_offre_index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(
            \Symfony\Component\Security\Http\SecurityRequestAttributes::AUTHENTICATION_ERROR,
            $exception
        );
        return new RedirectResponse($this->router->generate('app_login'));
    }
}