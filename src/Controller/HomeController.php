<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
//#[Route('/sorir', name: 'app_sortir')]
class HomeController extends AbstractController
{

    #[Route('/Accueil',name:'_accueil')]
    public function home():Response
    {
        return $this->render('Accueil.html.twig');
    }
}
