<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieFormType;
use App\Form\FilterType;
use App\Form\UpdateProfilType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

#[Route('/sortir', name: 'app_sortir')]
class HomeController extends AbstractController
{

    #[Route('/accueil',name:'_accueil')]
public function home(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $id = $user->getId();

    return $this->render('navigation/Accueil.html.twig',[
        'user' => $user,
        'id' => $id
    ]);
}

#[Route('/list',name:'_list')]
public function list(Request $request,EntityManagerInterface $em,SortieRepository $sortieRepository, SiteRepository $siteRepository, UserRepository $userRepository):Response
{
   $user = $this->getUser();
    $sites = $siteRepository->findAll();
    $choices = [];
        foreach ($sites as $site) {
            $choices[$site->getNomSite()] = $site->getId();
}
        $choices = ["Tous les sites" => null] + $choices;

        $form = $this->createFormBuilder(['site' => null])
        ->add('site', ChoiceType::class, [
            'choices'  => $choices,
            'required' => false,
            ])
    ->add('submit', SubmitType::class, ['label' => 'Filtrer'])
    ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['site'] !== null) {
                $sorties = $sortieRepository->findBy(['noSite' => $data['site']]);
        } else {
                $sorties = $sortieRepository->findAll();
        }
    } else {
        $sorties = $sortieRepository->findAll();
}

    return $this->render('navigation/list.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
            'form' => $form->createView(),
            'user'=> $user
        ]
    );
}



    #[Route('/monProfil', name: '_monProfil')]
    public function monProfil(User $user, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        return $this->render('navigation/monProfil.html.twig',[
            'user'=>$user]
        );
    }

    #[Route('/creerSortie',name:'_creerSortie')]
    public function creerSortie(User $user, Request $request, EntityManagerInterface $entityManager):Response
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
        return $this->render('navigation/creerSortie.html.twig',[
            "user"=>$user,
            "sortieFormType"=> $sortieFormType]);
    }

    #[Route('/modifierSortie',name:'_modifierSortie')]
    public function modifierSorti(User $user):Response
    {
        return $this->render('navigation/creerSortie.html.twig',[
            "user"=>$user
        ]);
    }

    #[Route('/updateProfil/{id}', name: '_updateProfil')]
    public function updateProfil(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
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
            'updateProfilForm' => $updateProfilForm->createView(),
            "user" => $user
        ]);
    }

#[Route('/inscriptionSortie/{id}', name:'_inscriptionSortie')]
public function inscriptionSortie(int $id, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    {
        {#$sortie = $entityManager->getRepository(Sortie::class)->find($id);#}}

            if (!$user) {
                throw $this->createNotFoundException('utilisateur non trouvée');
            }

            $sortie = $entityManager->getRepository(Sortie::class)->find($id);

            if (!$sortie) {
                throw $this->createNotFoundException('Sortie non trouvée');
            }

            $sortie->addUser($user);

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortir_detailSortie', ['id' => $id]);
        }

    }
}
#[Route('/detailSortie/{id}', name:'_detailSortie')]
public function detailSortie(int $id, EntityManagerInterface $entityManager): Response
{
    $sortie = $entityManager->getRepository(Sortie::class)->find($id);

    if (!$sortie) {
        throw $this->createNotFoundException('Sortie non trouvée');
    }

    $user = $this->getUser();

    return $this->render('navigation/detailSortie.html.twig', [
        'sortie' => $sortie,
        'user' => $user,
    ]);
}

#[Route('/desinscriptionSortie/{id}', name:'_desinscriptionSortie')]
public function desinscriptionSortie(int $id, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    $sortie = $entityManager->getRepository(Sortie::class)->find($id);

    if (!$sortie) {
        throw $this->createNotFoundException('Sortie non trouvée');
    }

    $sortie->removeUser($user);

    $entityManager->persist($sortie);
    $entityManager->flush();

    return $this->redirectToRoute('app_sortir_detailSortie', ['id' => $id]);
}


}

