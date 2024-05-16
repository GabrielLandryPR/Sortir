<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieModificationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sortie = $options['data'];

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
            ->add('dateFin', DateType::class, [
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
            ->add('delete_image', CheckboxType::class, [
                'label' => 'Supprimer la photo actuelle',
                'required' => false,
                'mapped' => false,
            ])
            ->add('lieuSearch', TextType::class, [
                'label' => 'Rechercher un lieu',
                'mapped' => false,
                'attr' => ['class' => 'autocomplete'],
                'data' => $sortie->getNoLieu() ? $sortie->getNoLieu()->getNomLieu() : '',
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'mapped' => false,
                'data' => $sortie->getNoLieu() ? $sortie->getNoLieu()->getRue() : '',
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'mapped' => false,
                'data' => $sortie->getNoLieu() ? $sortie->getNoLieu()->getNoVille()->getCodePostal() : '',
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'mapped' => false,
                'data' => $sortie->getNoLieu() ? $sortie->getNoLieu()->getNoVille()->getNomVille() : '',
            ])
            ->add('latitude', HiddenType::class, [
                'mapped' => false,
                'data' => $sortie->getNoLieu() ? $sortie->getNoLieu()->getLatitude() : '',
            ])
            ->add('longitude', HiddenType::class, [
                'mapped' => false,
                'data' => $sortie->getNoLieu() ? $sortie->getNoLieu()->getLongitude() : '',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
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
