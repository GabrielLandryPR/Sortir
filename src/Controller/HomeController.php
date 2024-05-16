<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\SortieFormType;
use App\Form\UpdateProfilType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Form\SortieModificationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/sortir', name: 'app_sortir')]
class HomeController extends AbstractController
{

    #[Route('/annulerSortie/{id}', name: '_annulerSortie')]
    public function annulerSortie(int $id, EntityManagerInterface $entityManager, EtatRepository $etatRepository, SortieRepository $sortieRepository): Response
    {
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);
        $idOrga = $sortie->getIdOrga();

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée');
        }

        $etatAnnule = $etatRepository->find(2);
        if (!$etatAnnule) {
            throw $this->createNotFoundException('Etat non trouvé il faut le répertorié en BDD');
        }


        if ($idOrga != $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas l\'organisateur de cette sortie');
            return $this->redirectToRoute('app_sortir_list');
        }
        if ($etatAnnule->getId() == 2) {
            $this->addFlash('error', 'La sortie est déjà annulée');
            return $this->redirectToRoute('app_sortir_list');
        }
        $sortie->setNoEtat($etatAnnule);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'La sortie a été annulée avec succès');


        return $this->redirectToRoute('app_sortir_list');
    }

    #[Route('/list', name: '_list')]
    public function list(SortieRepository $sortieRepository, SiteRepository $siteRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $sites = $siteRepository->findAll();
        $sorties = $sortieRepository->findAll();

        foreach ($sorties as $sortie) {
            $organisateur = $userRepository->find($sortie->getOrganisateur());
            $sortie->setIdOrga($organisateur);
        }

        return $this->render('navigation/list.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
            'user' => $user
        ]);
    }

    #[Route('/monProfil', name: '_monProfil')]
    public function monProfil(SortieRepository $sortieRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $sorties = $sortieRepository->findBy(['idOrga' => $user]);

        return $this->render('navigation/monProfil.html.twig', [
            'user' => $user,
            'sorties' => $sorties
        ]);
    }

    #[Route('/updateProfil/{id}', name: '_updateProfil')]
    public function updateProfil(Request $request, int $id, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $updateProfilForm = $this->createForm(UpdateProfilType::class, $user, ['editMode' => true]);
        $updateProfilForm->handleRequest($request);

        if ($updateProfilForm->isSubmitted() && $updateProfilForm->isValid()) {
            if ($updateProfilForm->has('delete_image') && $updateProfilForm->get('delete_image')->getData()) {
                $user->deletePhoto();
            }

            $photoFile = $updateProfilForm->get('urlPhoto')->getData();
            if ($photoFile) {
                $newFilename = strtolower($slugger->slug($user->getPseudo() . '-' . uniqid())) . '.' . $photoFile->guessExtension();
                $photoFile->move(
                    $this->getParameter('photos_directory'),
                    $newFilename
                );
                $user->deletePhoto();
                $user->setUrlPhoto($newFilename);
            }

            $plainPassword = $updateProfilForm->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour.');

            return $this->redirectToRoute('app_sortir_monProfil');
        }

        return $this->render('registration/updateProfil.html.twig', [
            'updateProfilForm' => $updateProfilForm->createView(),
            'user' => $user
        ]);
    }

    #[Route('/createSortie', name: '_createSortie')]
    public function creerSortie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $sortie = new Sortie();
        $sortie->setOrganisateur($user->getId());
        $sortie->setIdOrga($user);
        $sortie->setNoSite($user->getNoSite());
        $sortie->setEtatSortie($entityManager->getRepository(Etat::class)->find(1)->getId());
        $sortie->setNoEtat($entityManager->getRepository(Etat::class)->find(1));
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

    #[Route('/modifierSortie/{id}', name: '_modifierSortie')]
    public function modifierSortie(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée');
        }

        $user = $this->getUser();
        if ($sortie->getIdOrga()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette sortie.');
        }

        $sortieFormType = $this->createForm(SortieModificationFormType::class, $sortie);
        $sortieFormType->handleRequest($request);

        if ($sortieFormType->isSubmitted() && $sortieFormType->isValid()) {
            $file = $sortieFormType->get('urlPhoto')->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('photos_directory'), $fileName);
                $sortie->setUrlPhoto($fileName);
            }

            // Set the new location if the search input was used
            $nomLieu = $sortieFormType->get('lieuSearch')->getData();
            if ($nomLieu) {
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
            }

            $entityManager->flush();

            $this->addFlash('success', 'Sortie modifiée avec succès');

            return $this->redirectToRoute('app_sortir_list');
        }

        return $this->render('navigation/updateSortie.html.twig', [
            'sortieFormType' => $sortieFormType->createView(),
            'user' => $user
        ]);
    }



    #[Route('/inscriptionSortie/{id}', name: '_inscriptionSortie')]
    public function inscriptionSortie(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
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

    #[Route('/ajax_desinscriptionSortie/{id}', name: 'ajax_desinscriptionSortie')]
    public function ajaxDesinscriptionSortie(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_UNAUTHORIZED);
        }

        $sortie = $entityManager->getRepository(Sortie::class)->find($id);
        if (!$sortie) {
            return new JsonResponse(['error' => 'Sortie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $sortie->removeUser($user);
        $entityManager->persist($sortie);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Vous êtes désinscrit de la sortie.']);
    }

    #[Route('/ajax_inscriptionSortie/{id}', name: 'ajax_inscriptionSortie')]
    public function ajaxInscriptionSortie(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_UNAUTHORIZED);
        }

        $sortie = $entityManager->getRepository(Sortie::class)->find($id);
        if (!$sortie) {
            return new JsonResponse(['error' => 'Sortie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $sortie->addUser($user);
        $entityManager->persist($sortie);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Vous êtes inscrit à la sortie.']);
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

        $filteredSorties = array_filter($sorties, function ($sortie) use ($user) {
            return $sortie->getNoEtat()->getId() !== 1 || $sortie->getIdOrga()->getId() === $user->getId();
        });

        return $this->json([
            'sorties' => array_map(function ($sortie) use ($user) {
                $actions = '';
                if ($sortie->getIdOrga()->getId() == $user->getId()) {
                    $actions .= ' <a href="' . $this->generateUrl('app_sortir_modifierSortie', ['id' => $sortie->getId()]) . '" class="btn btn-primary btn-sm">Modifier</a>';
                    if ($sortie->getNoEtat()->getId() != 1) {
                        $actions .= ' <button class="btn btn-warning btn-sm annuler-sortie" data-sortie-id="' . $sortie->getId() . '">Annuler</button>';
                    }
                } elseif ($sortie->getUsers()->contains($user)) {
                    $actions .= ' <a href="#" class="btn btn-danger btn-sm desinscription-link" data-sortie-id="' . $sortie->getId() . '">Se désister</a>';
                } elseif ($sortie->getUsers()->count() < $sortie->getNbInscriptionMax()) {
                    $actions .= ' <a href="#" class="btn btn-success btn-sm inscription-link" data-sortie-id="' . $sortie->getId() . '">S\'inscrire</a>';
                }

                return [
                    'id' => $sortie->getId(),
                    'nomSortie' => $sortie->getNomSortie(),
                    'dateDebut' => $sortie->getDateDebut()->format('Y-m-d H:i'),
                    'dateClotureInscription' => $sortie->getDateFin()->format('Y-m-d H:i'),
                    'nbInscrits' => $sortie->getUsers()->count(),
                    'nbInscriptionMax' => $sortie->getNbInscriptionMax(),
                    'etatSortie' => $sortie->getNoEtat()->getLibelle(),
                    'organisateur' => $sortie->getIdOrga()->getPseudo() ?: $sortie->getIdOrga()->getPrenom() . ' ' . $sortie->getIdOrga()->getNom(),
                    'actions' => $actions
                ];
            }, $filteredSorties)
        ]);
    }



}


