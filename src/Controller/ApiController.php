<?php

namespace App\Controller;

use App\Service\ApiClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private ApiClientService $apiClient;

    public function __construct(ApiClientService $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    #[Route('/test-api', name: 'test_api')]
    public function test(): JsonResponse
    {
        $url = 'https://api.example.com/data';
        
        // GET avec query parameters
        $data = $this->apiClient->get($url, ['limit' => 5, 'type' => 'offre']);

        return $this->json($data);
    }
}