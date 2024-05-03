<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieFormType;
use App\Form\UpdateProfilType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sortir', name: 'app_sortir')]
class HomeController extends AbstractController
{

    #[Route('/accueil',name:'_accueil')]
    public function home(Request $request,UserRepository $userRepository, User $user, EntityManagerInterface $entityManager):Response
    {

        $user = $this->getUser();
        $id = $this->getUser()->getId();



        return $this->render('navigation/Accueil.html.twig',[
            'user'=>$user,
            'id'=>$id
        ]);
    }

    #[Route('/monProfil',name:'_monProfil')]
    public function monProfil(User $user):Response
    {
        $user = $this->getUser();
        $id = $this->getUser()->getId();
        return $this->render('navigation/monProfil.html.twig',[
            "user"=>$user
            ]
        );
    }

    #[Route('/creerSorti',name:'_creerSorti')]
    public function creerSorti(User $user, Request $request, EntityManagerInterface $entityManager):Response
    {
        $sortie = new Sortie();
        $sortieFormType = $this->createForm(SortieFormType::class, $sortie);
        $sortieFormType->handleRequest($request);

        if ($sortieFormType->isSubmitted() && $sortieFormType->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Une sortie a été créer');

            return $this->redirectToRoute('app_sortir_accueil');
        }
        return $this->render('navigation/creerSorti.html.twig',[
            "user"=>$user,
            "sortieFormType"=> $sortieFormType]);
    }

    #[Route('/modifierSorti',name:'_modifierSorti')]
    public function modifierSorti(User $user):Response
    {
        return $this->render('navigation/creerSorti.html.twig',[
            "user"=>$user
        ]);
    }

    #[Route('/updateProfil/{id}', name:'_updateProfil')]
    public function updateProfil(Request $request, int $id, EntityManagerInter $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $updateProfilForm = $this->createForm(UpdateProfilType::class, $user);
        $updateProfilForm->handleRequest($request);

        if ($updateProfilForm->isSubmitted() && $updateProfilForm->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortir_accueil');
        }

        return $this->render('registration/updateProfil.html.twig', [
            'updateProfilForm'=> $updateProfilForm->createView(),
            "user"=> $user
        ]);
    }


}

