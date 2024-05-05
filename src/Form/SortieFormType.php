<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomSortie', TextType::class)
            ->add('dateDebut', DateType::class)
            ->add('dateFin', DateType::class)
            ->add('nolieu', TextType::class)
            ->add('description', TextareaType::class)
            ->add('organisateur', TextType::class)
            ->add('duree', IntegerType::class, [
                'label' => 'Durée de la sortie (en minutes)'
            ])
            ->add('nbInscriptionMax', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}