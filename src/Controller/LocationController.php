<?php

namespace App\Controller;

use App\Service\LocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    private LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    #[Route('/location/search', name: 'location_search')]
    public function search(Request $request): Response
    {
        $query = $request->query->get('query');
        $results = $this->locationService->searchLocation($query);

        return $this->json($results);
    }
}

