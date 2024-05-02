<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Entity\User;
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

    #[Route('/monProfil',name:'_monProfil')]
    public function monProfil(User $user):Response
    {
        return $this->render('navigation/monProfil.html.twig',
        );
    }

    #[Route('/creerSortie', name:'_creerSortie')]
    public function creerSortie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $sortie->setOrganisateur($user->getId());

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortir_accueil');
        }

        $user = $this->getUser();

        return $this->render('navigation/creerSortie.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
            'user' => $user,
        ]);
    }

    #[Route('/modifierSortie',name:'_modifierSortie')]
    public function modifierSorti(User $user):Response
    {
        return $this->render('navigation/creerSortie.html.twig',[
            "user"=>$user
        ]);
    }

    #[Route('/updateProfil/{id}', name:'_updateProfil')]
    public function updateProfil(Request $request, int $id, EntityManagerInterface $entityManager): Response
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

