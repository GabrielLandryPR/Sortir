<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UpdateProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $editMode = $options['editMode'];

        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'attr' => [
                    'readonly' => !$editMode
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('tel', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'readonly' => !$editMode
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'readonly' => true
                ]
            ])
            ->add('noSite', null, [
                'label' => 'Ville de rattachement',
                'attr' => [
                    'readonly' => true
                ]
            ]);

        if ($editMode) {
            $builder
                ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'mapped' => false,
                    'required' => false,
                    'first_options' => [
                        'label' => 'Nouveau mot de passe',
                        'attr' => ['placeholder' => 'Laisser vide si pas de changement'],
                    ],
                    'second_options' => [
                        'label' => 'Confirmer le nouveau mot de passe',
                        'attr' => ['placeholder' => 'Laisser vide si pas de changement'],
                    ],
                ])
                ->add('urlPhoto', FileType::class, [
                    'label' => 'Photo de profil (fichier image)',
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '2M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid image file (jpeg or png)',
                        ])
                    ],
                ])
                ->add('delete_image', CheckboxType::class, [
                    'label' => 'Supprimer la photo actuelle',
                    'mapped' => false,
                    'required' => false,
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Enregistrer',
                    'attr' => [
                        'class' => 'btn btn-primary',
                    ],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'editMode' => false
        ]);
    }
}
