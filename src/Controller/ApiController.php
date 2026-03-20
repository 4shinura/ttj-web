<?php

namespace App\Controller;

use App\Service\ApiClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private ApiClientService $apiClient;

    public function __construct(ApiClientService $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    #[Route('/offres', name: 'test_api')]
    public function test()
    {
        $url = 'http://127.0.0.1:8001/api/offres/list';
        
        // GET avec query parameters
        $data = $this->apiClient->get($url, ['limit' => 5, 'type' => 'offre']);

        return $this->render('offre/index.html.twig', ['offres' => $data]);
    }
}