<?php

namespace App\Controller;
use App\Repository\EtatRepository;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\SortieFormType;
use App\Form\UpdateProfilType;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/sortir', name: 'app_sortir')]
class HomeController extends AbstractController
{
    private function updateSortiesEtat(SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): void
    {
        $sorties = $sortieRepository->findAll();
        foreach ($sorties as $sortie) {
            $sortie->updateEtat($etatRepository);
            $entityManager->persist($sortie);
        }
        $entityManager->flush();
    }

    #[Route('/annulerSortie/{id}', name: '_annulerSortie')]
    public function annulerSortie(int $id, EntityManagerInterface $entityManager, EtatRepository $etatRepository, SortieRepository $sortieRepository): Response
    {

        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

        $sortie = $entityManager->getRepository(Sortie::class)->find($id);
        $idOrga = $sortie->getIdOrga();

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée');
        }

        $etatAnnule = $etatRepository->find(6);
        if (!$etatAnnule) {
            throw $this->createNotFoundException('Etat non trouvé il faut le répertorié en BDD');
        }


        if ($idOrga != $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas l\'organisateur de cette sortie');
            if ($sortie->getNoEtat()->getId() == 4){
                $this->addFlash('error', 'La sortie est en cour');
                return $this->redirectToRoute('app_sortir_list');
            }
            if ($sortie->getNoEtat()->getId() == 5){
                $this->addFlash('error', 'La sortie est en passée');
                return $this->redirectToRoute('app_sortir_list');
            }
            if ($sortie->getNoEtat()->getId() == 6){
                $this->addFlash('error', 'La sortie est annulée');
                return $this->redirectToRoute('app_sortir_list');
            }
            if ($etatAnnule->getId() == 2) {
                $this->addFlash('error', 'La sortie est déjà annulée');
                return $this->redirectToRoute('app_sortir_list');
            }
            return $this->redirectToRoute('app_sortir_list');
        }

        $sortie->setNoEtat($etatAnnule);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'La sortie a été annulée avec succès');

        return $this->redirectToRoute('app_sortir_list');
    }

    #[Route('/list', name: '_list')]
    public function list(SortieRepository $sortieRepository, SiteRepository $siteRepository, UserRepository $userRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

        $this->updateSortiesEtat($sortieRepository, $etatRepository, $entityManager);
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
        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

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
        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

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
        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

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
        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

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
        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

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
        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

        $sortie = $entityManager->getRepository(Sortie::class)->find($id);
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée');
        }

        $user = $this->getUser();
        $userIsOrganizer = $sortie->getIdOrga()->getId() === $user->getId();
        $etatSortie = $sortie->getNoEtat()->getLibelle();

        return $this->render('navigation/detailSortie.html.twig', [
            'sortie' => $sortie,
            'user' => $user,
            'userIsOrganizer' => $userIsOrganizer,
            'etatSortie' => $etatSortie,
        ]);
    }


    #[Route('/desinscriptionSortie/{id}', name: '_desinscriptionSortie')]
    public function desinscriptionSortie(int $id, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

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
        if (!$this->isGranted('ROLE_USER')) {
            // Ajout d'un message flash
            $this->addFlash('error', "Connecter vous pour accéder à cette page.");
            // Redirection vers une autre page (par exemple, la liste des séries)
            return $this->redirectToRoute('app_login');
        }

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

        return $this->json([
            'sorties' => array_map(function ($sortie) use ($user) {
                $actions = '';

                if ($sortie->getIdOrga()->getId() === $user->getId()) {
                    if ($sortie->getNoEtat()->getId() !== 6) {
                        $actions .= ' <a href="' . $this->generateUrl('app_sortir_modifierSortie', ['id' => $sortie->getId()]) . '" class="btn btn-primary btn-sm">Modifier</a>';
                    }
                    if ($sortie->getNoEtat()->getId() === 1) {
                        $actions .= ' <button class="btn btn-success btn-sm publier-sortie" data-sortie-id="' . $sortie->getId() . '">Publier</button>';
                    } elseif ($sortie->getNoEtat()->getId() !== 6) {
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
                    'dateDebut' => $sortie->getDateDebut()->format('Y-m-d H:i:s'),
                    'dateClotureInscription' => $sortie->getDateFin()->format('Y-m-d'),
                    'nbInscrits' => $sortie->getUsers()->count(),
                    'nbInscriptionMax' => $sortie->getNbInscriptionMax(),
                    'etatSortie' => $sortie->getNoEtat()->getLibelle(),
                    'description' => $sortie->getDescription(),
                    'organisateur' => $sortie->getIdOrga()->getPseudo(),
                    'actions' => $actions
                ];
            }, $sorties)
        ]);
    }




    #[Route('/ajax_annulerSortie/{id}', name: 'ajax_annulerSortie', methods: ['POST'])]
    public function ajaxAnnulerSortie(int $id, Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): JsonResponse
    {
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            return new JsonResponse(['error' => 'Sortie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        if ($sortie->getIdOrga()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Vous n\'êtes pas l\'organisateur de cette sortie'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $motif = $data['motif'] ?? '';

        $etatAnnule = $etatRepository->find(6);
        if (!$etatAnnule) {
            return new JsonResponse(['error' => 'État "Annulée" non trouvé'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $sortie->setNoEtat($etatAnnule);
        $sortie->setMotifAnnulation($motif);
        $entityManager->persist($sortie);
        $entityManager->flush();

        return new JsonResponse(['success' => 'La sortie a été annulée avec succès']);
    }



    #[Route('/ajax_publierSortie/{id}', name: 'ajax_publierSortie')]
    public function ajaxPublierSortie(int $id, EntityManagerInterface $entityManager, EtatRepository $etatRepository): JsonResponse
    {
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            return new JsonResponse(['error' => 'Sortie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        if ($sortie->getIdOrga()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Vous n\'êtes pas l\'organisateur de cette sortie'], Response::HTTP_FORBIDDEN);
        }

        if ($sortie->getDateDebut() < new \DateTime()) {
            return new JsonResponse(['error' => 'La date de la sortie est antérieure à la date actuelle, vous ne pouvez pas la publier.'], Response::HTTP_BAD_REQUEST);
        }

        $etatCree = $etatRepository->find(1);
        if ($sortie->getNoEtat()->getId() !== $etatCree->getId()) {
            return new JsonResponse(['error' => 'La sortie ne peut pas être publiée car elle n\'est pas à l\'état "Créée"'], Response::HTTP_BAD_REQUEST);
        }

        $etatOuvert = $etatRepository->find(2);
        if (!$etatOuvert) {
            return new JsonResponse(['error' => 'État "Ouvert" non trouvé'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $sortie->setNoEtat($etatOuvert);
        $entityManager->persist($sortie);
        $entityManager->flush();

        return new JsonResponse(['success' => 'La sortie a été publiée avec succès']);
    }



}


