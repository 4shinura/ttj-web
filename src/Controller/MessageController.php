<?php

namespace App\Controller;

use App\Service\ApiClientService;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/messages')]
class MessageController extends AbstractController
{
    public function __construct(
        private ApiClientService $apiClientService
    ) {}

    // ── Liste des conversations ──────────────────────────────────────────────

    #[Route('', name: 'message_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        $headers = AuthService::bearerHeaders($request);
        $url = $this->getParameter('api_base_url') . '/users/messages/correspondants';
        
        $result = $this->apiClientService->get($url, [], $headers);

        return $this->render('message/index.html.twig', [
            'correspondants' => $result['data'] ?? [],
        ]);
    }

    // ── Conversation avec un correspondant ──────────────────────────────────

    #[Route('/{correspondantId}', name: 'message_conversation', methods: ['GET'], requirements: ['correspondantId' => '\d+'])]
    public function conversation(int $correspondantId, Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        $headers = AuthService::bearerHeaders($request);
        $url = $this->getParameter('api_base_url') . '/users/messages/' . $correspondantId;
        
        $result = $this->apiClientService->get($url, [], $headers);

        // if ($result['status'] === 400) {
        //     $this->addFlash('warning', $result['data']['error'] ?? 'Requête invalide.');
        //     return $this->redirectToRoute('message_index');
        // }

        // if ($result['status'] === 404) {
        //     $this->addFlash('error', 'Utilisateur introuvable.');
        //     return $this->redirectToRoute('message_index');
        // }

        return $this->render('message/conversation.html.twig', [
            'conversation' => $result,
        ]);
    }

    // ── Détail d'un message envoyé ───────────────────────────────────────────

    #[Route('/sent/{id}', name: 'message_sent_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function sentShow(int $id, Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        $headers = AuthService::bearerHeaders($request);
        $url = $this->getParameter('api_base_url') . '/users/messages/sent/' . $id;
        
        $result = $this->apiClientService->get($url, [], $headers);

        // if ($result['status'] === 403) {
        //     $this->addFlash('error', 'Accès refusé : ce message ne vous appartient pas.');
        //     return $this->redirectToRoute('message_index');
        // }

        // if ($result['status'] === 404) {
        //     $this->addFlash('error', 'Message introuvable.');
        //     return $this->redirectToRoute('message_index');
        // }

        return $this->render('message/show.html.twig', [
            'message' => $result,
            'isSent'  => true,
        ]);
    }

    // ── Détail d'un message reçu ─────────────────────────────────────────────

    #[Route('/received/{id}', name: 'message_received_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function receivedShow(int $id, Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        $headers = AuthService::bearerHeaders($request);
        $url = $this->getParameter('api_base_url') . '/users/messages/received/' . $id;
        
        $result = $this->apiClientService->get($url, [], $headers);

        // if ($result['status'] === 403) {
        //     $this->addFlash('error', 'Accès refusé : ce message ne vous est pas destiné.');
        //     return $this->redirectToRoute('message_index');
        // }

        // if ($result['status'] === 404) {
        //     $this->addFlash('error', 'Message introuvable.');
        //     return $this->redirectToRoute('message_index');
        // }

        return $this->render('message/show.html.twig', [
            'message' => $result,
            'isSent'  => false,
        ]);
    }

    // ── Formulaire nouveau message ────────────────────────────────────────────

    #[Route('/new', name: 'message_new', methods: ['GET'])]
    public function new(Request $request): Response
    {
        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        $headers = AuthService::bearerHeaders($request);
        $url = $this->getParameter('api_base_url') . '/admin/utilisateurs';
        
        $users = $this->apiClientService->get($url, [], $headers);

        return $this->render('message/new.html.twig', [
            'utilisateurs' => $users ?? [],
        ]);
    }

    // ── Envoi d'un message ────────────────────────────────────────────────────

    #[Route('/send/{destinataireId}', name: 'message_send', methods: ['POST'], requirements: ['destinataireId' => '\d+'])]
    public function send(int $destinataireId, Request $request): Response
    {
        $contenu = trim($request->request->get('contenu', ''));

        if (empty($contenu)) {
            $this->addFlash('error', 'Le message ne peut pas être vide.');
            return $this->redirectToRoute('message_new');
        }

        if (!AuthService::isLoggedIn($request)) {
            return $this->redirectToRoute('app_login');
        }

        $headers = AuthService::bearerHeaders($request);
        $url = $this->getParameter('api_base_url') . '/users/messages/send/' . $destinataireId;
        
        $result = $this->apiClientService->post($url, ['contenu' => $contenu], $headers);

        // if (isset($result['error']) || $result['status'] === 401) {
        //     $this->addFlash('error', 'Vous devez être connecté pour envoyer un message.');
        //     return $this->redirectToRoute('app_login');
        // }

        // if ($result['status'] === 400) {
        //     $this->addFlash('warning', $result['data']['error'] ?? 'Requête invalide.');
        //     return $this->redirectToRoute('message_new');
        // }

        // if ($result['status'] === 404) {
        //     $this->addFlash('error', 'Destinataire introuvable.');
        //     return $this->redirectToRoute('message_new');
        // }

        // if ($result['status'] !== 201) {
        //     $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message.');
        //     return $this->redirectToRoute('message_new');
        // }

        $this->addFlash('success', 'Message envoyé avec succès !');
        return $this->redirectToRoute('message_conversation', ['correspondantId' => $destinataireId]);
    }
}

