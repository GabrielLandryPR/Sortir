<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieFormType;
use App\Form\UpdateProfilType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortir', name: 'app_sortir')]
class HomeController extends AbstractController
{
    #[Route('/list', name: '_list')]
    public function list(Request $request, SortieRepository $sortieRepository, SiteRepository $siteRepository, UserRepository $userRepository): Response
    {
        $sites = $siteRepository->findAll();

        $sorties = $sortieRepository->findAll();

        foreach ($sorties as $sortie) {
            $organisateur = $userRepository->find($sortie->getOrganisateur());
            $sortie->setIdOrga($organisateur);
        }

        return $this->render('navigation/list.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites
        ]);
    }



    #[Route('/monProfil', name: '_monProfil')]
    public function monProfil(User $user): Response
    {
        $user = $this->getUser();

        return $this->render('navigation/monProfil.html.twig', [
                'user' => $user
            ]
        );
    }

    #[Route('/creerSortie', name: '_creerSortie')]
    public function creerSortie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $sortie = new Sortie();
        $sortie->setOrganisateur($user->getId());
        $sortie->setNoSite($user->getNoSite());
        $sortie->addUser($user);

        $sortieFormType = $this->createForm(SortieFormType::class, $sortie);
        $sortieFormType->handleRequest($request);

        if ($sortieFormType->isSubmitted() && $sortieFormType->isValid()) {
            $file = $sortieFormType->get('urlPhoto')->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('photos_directory'), $fileName);
                $sortie->setUrlPhoto($fileName);
            }

            $nomLieu = $sortieFormType->get('lieuSearch')->getData();
            $latitude = $sortieFormType->get('latitude')->getData();
            $longitude = $sortieFormType->get('longitude')->getData();
            $street = $sortieFormType->get('rue')->getData();
            $postalCode = $sortieFormType->get('codePostal')->getData();
            $nomVille = $sortieFormType->get('ville')->getData();

            $ville = $entityManager->getRepository(Ville::class)->findOneBy([
                'nomVille' => $nomVille,
                'codePostal' => $postalCode
            ]);

            if (!$ville) {
                $ville = new Ville();
                $ville->setNomVille($nomVille);
                $ville->setCodePostal($postalCode);
                $entityManager->persist($ville);
                $entityManager->flush();
            }

            $lieu = new Lieu();
            $lieu->setNomLieu($nomLieu);
            $lieu->setLatitude($latitude);
            $lieu->setLongitude($longitude);
            $lieu->setRue($street);
            $lieu->setNoVille($ville);

            $entityManager->persist($lieu);

            $sortie->setNoLieu($lieu);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Une sortie a été créée');

            return $this->redirectToRoute('app_sortir_list');
        }

        return $this->render('navigation/createSortie.html.twig', [
            "user" => $user,
            "sortieFormType" => $sortieFormType->createView()
        ]);
    }

    #[Route('/modifierSortie', name: '_modifierSortie')]
    public function modifierSorti(User $user): Response
    {
        return $this->render('navigation/createSortie.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/updateProfil/{id}', name: '_updateProfil')]
    public function updateProfil(Request $request, int $id, UserPasswordHasherInterface $userPasswordHasher,EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $updateProfilForm = $this->createForm(UpdateProfilType::class, $user);
        $updateProfilForm->handleRequest($request);

        if ($updateProfilForm->isSubmitted() && $updateProfilForm->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $updateProfilForm->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortir_list');
        }

        return $this->render('registration/updateProfil.html.twig', [
            'updateProfilForm' => $updateProfilForm->createView(),
            'user' => $user
        ]);
    }

    #[Route('/inscriptionSortie/{id}', name: '_inscriptionSortie')]
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

    #[Route('/detailSortie/{id}', name: '_detailSortie')]
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

    #[Route('/desinscriptionSortie/{id}', name: '_desinscriptionSortie')]
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

    #[Route('/filter_sorties', name: 'filter_sorties')]
    public function filterSorties(Request $request, SortieRepository $sortieRepository): Response
    {
        $user = $this->getUser();
        $organizer = $request->query->get('organizer') === '1' ? $user : null;
        $registered = $request->query->get('registered') === '1' ? $user : null;
        $notRegistered = $request->query->get('notRegistered') === '1' ? $user : null;
        $past = $request->query->get('past') === '1' ? true : false;
        $site = $request->query->get('site');
        $startDate = $request->query->get('startDate') ? new \DateTime($request->query->get('startDate')) : null;
        $endDate = $request->query->get('endDate') ? new \DateTime($request->query->get('endDate')) : null;
        $searchName = $request->query->get('searchName');

        // Validate end date
        if ($endDate && $startDate && $endDate < $startDate) {
            return $this->json(['error' => "La date 'Et' ne peut pas être antérieure à la date 'Comprise entre'."], Response::HTTP_BAD_REQUEST);
        }

        $sorties = $sortieRepository->findFilteredSorties(
            $organizer,
            $registered,
            $notRegistered,
            $past,
            $site,
            $startDate ? $startDate->format('Y-m-d H:i:s') : null,
            $endDate ? $endDate->format('Y-m-d H:i:s') : null,
            $searchName
        );

        return $this->json([
            'sorties' => array_map(function ($sortie) {
                return [
                    'nomSortie' => $sortie->getNomSortie(),
                    'dateDebut' => $sortie->getDateDebut()->format('Y-m-d H:i:s'),
                    'etatSortie' => $sortie->getNoEtat()->getLibelle(),
                    'dateFin' => $sortie->getDateFin()->format('Y-m-d H:i:s'),
                    'description' => $sortie->getDescription(),
                    'organisateur' => $sortie->getIdOrga()->getPseudo()
                ];
            }, $sorties)
        ]);
    }


    #[Route('/filter_by_site', name: 'filter_by_site', methods: ['POST'])]
    public function filterBySite(Request $request, SortieRepository $sortieRepository): Response
    {
        $siteId = $request->request->get('siteId');
        if ($siteId) {
            $sorties = $sortieRepository->findBy(['noSite' => $siteId]);
        } else {
            $sorties = $sortieRepository->findAll();
        }

        return $this->json([
            'sorties' => array_map(function ($sortie) {
                return [
                    'nomSortie' => $sortie->getNomSortie(),
                    'dateDebut' => $sortie->getDateDebut()->format('Y-m-d H:i:s'),
                    'etatSortie' => $sortie->getNoEtat()->getLibelle(),
                    'dateFin' => $sortie->getDateFin()->format('Y-m-d H:i:s'),
                    'description' => $sortie->getDescription(),
                    'organisateur' => $sortie->getIdOrga()->getPseudo()
                ];
            }, $sorties)
        ]);
    }
}

