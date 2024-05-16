<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SortieController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/sorties", name="sorties")
     */
    public function index(): Response
    {
        $user = $this->security->getUser();

        $site = $user->getNoSite();

        $sorties = $site->getSorties();

        return $this->render('sorties/index.html.twig', [
            'sorties' => $sorties,
        ]);
    }
}
