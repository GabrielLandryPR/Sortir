<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
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

#[Route('/list',name:'_list')]
public function list(Request $request,EntityManagerInterface $em,SortieRepository $sortieRepository, SiteRepository $siteRepository):Response
{
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
        ]
    );
}

        #[
        Route('/monProfil', name: '_monProfil')]
    public function monProfil(User $user): Response
    {
        return $this->render('navigation/monProfil.html.twig',
        );
    }

    #[Route('/creerSorti', name: '_creerSorti')]
    public function creerSorti(User $user): Response
    {
        return $this->render('navigation/creerSorti.html.twig', [
            "user" => $user]);
    }

    #[Route('/modifierSorti', name: '_modifierSorti')]
    public function modifierSorti(User $user): Response
    {
        return $this->render('navigation/creerSorti.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/updateProfil/{id}', name: '_updateProfil')]
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
            'updateProfilForm' => $updateProfilForm->createView(),
            "user" => $user
        ]);
    }

}

