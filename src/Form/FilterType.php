<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomSortie')
            ->add('dateDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('duree')
            ->add('dateFin', null, [
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionMax')
            ->add('description')
            ->add('etatSortie')
            ->add('urlPhoto')
            ->add('organisateur')
            ->add('Users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('idOrga', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('noEtat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'id',
            ])
            ->add('noLieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'id',
            ])
            ->add('noSite', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'id',
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
