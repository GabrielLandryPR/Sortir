<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
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
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $sortie = $event->getData();
                $user = $this->security->getUser();

                if ($user && $sortie) {
                    $sortie->setNoSite($user->getNoSite());
                    $sortie->setOrganisateur($user->getId());
                }
            }
        );

        $builder
            ->add('nomSortie', TextType::class, [
                'label' => 'Nom de la sortie',
            ])
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure de début',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => "Date limite d'inscription",
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => "Nombre d'inscriptions max",
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'rows' => 5,
                ],
            ])
            ->add('urlPhoto', FileType::class, [
                'label' => 'Photo',
                'required' => false,
                'mapped' => false,
            ])
            ->add('lieuSearch', TextType::class, [
                'label' => 'Rechercher un lieu',
                'mapped' => false,
                'attr' => ['class' => 'autocomplete']
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'mapped' => false,
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'mapped' => false,
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'mapped' => false,
            ])
            ->add('latitude', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('longitude', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('organisateur', HiddenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
