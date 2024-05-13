<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SortieFormType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event) use ($builder) {
            $sortie = $event->getData();
            $form = $event->getForm();


            $user = $this->security->getUser();

            if (!$sortie->getId() && $user instanceof User) {
                // Si la sortie est nouvellement créée et qu'un utilisateur est authentifié
                // Définir l'utilisateur comme l'organisateur
                $form->get('organisateur')->setData(true);
            }
        })
            ->add('nomSortie')
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'date et heure de début'
            ])
            ->add('duree')
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => "Date limite d'inscription"
            ])
            ->add('nbInscriptionMax')
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('etatSortie', ChoiceType::class, [
                'label' => 'État de la sortie',
                'choices' => [
                    'En cours' => '1',
                    'Termine' => '2',
                    'Annule' => '3',
                ],
                'placeholder' => '-- Choisissez un état --',
                'required' => true,
            ])
            ->add('urlPhoto')
            ->add('organisateur', EntityType::class,[
                'class' => User::class,
                'label' => 'Organisateur',
            ])
            ->add('Users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'pseudo',
                'multiple' => true,
            ])
            ->add('idOrga', EntityType::class, [
                'class' => User::class,
                'label' => 'idOrga',
            ])
            ->add('noEtat', EntityType::class, [
                'class' => Etat::class,
                'label'=> "libelle"
            ])
            ->add('noLieu', EntityType::class, [
                'class' => Lieu::class,
                'label' => 'id',
            ])
            ->add('noSite', EntityType::class, [
                'class' => Site::class,
                'label' => 'nomSite',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}