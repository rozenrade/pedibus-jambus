<?php
// src/Form/MemberType.php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;               // ← CHANGÉ (au lieu de File)

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom complet',
            ])
            ->add('role', TextType::class, [
                'label' => 'Rôle / Fonction',
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => Member::CATEGORIES,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('quote', TextareaType::class, [
                'label' => 'Citation (Président d\'honneur uniquement)',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('photoFile', FileType::class, [                 // ← CHANGÉ : mapped retiré (true par défaut désormais)
                'label' => 'Photo',
                'required' => false,
                'attr' => ['accept' => 'image/*'],                // ← AJOUT
                'constraints' => [
                    new Image([                                    // ← CHANGÉ (Image au lieu de File)
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Merci de déposer une image valide (JPEG, PNG, WEBP).',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Member::class]);
    }
}