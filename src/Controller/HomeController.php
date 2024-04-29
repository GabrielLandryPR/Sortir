<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sortir', name: 'app_sortir')]
class HomeController extends AbstractController
{

    #[Route('/accueil',name:'_accueil')]
    public function home():Response
    {
        return $this->render('navigation/Accueil.html.twig');
    }

    #[Route('/monProfil',name:'_monProfil')]
    public function monProfil():Response
    {
        return $this->render('navigation/monProfil.html.twig');
    }

    #[Route('/creerSorti',name:'_creerSorti')]
    public function creerSorti():Response
    {
        return $this->render('navigation/creerSorti.html.twig');
    }

    #[Route('/modifierSorti',name:'_modifierSorti')]
    public function modifierSorti():Response
    {
        return $this->render('navigation/creerSorti.html.twig');
    }


}

