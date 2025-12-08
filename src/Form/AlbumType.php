<?php
// src/Form/AlbumType.php

namespace App\Form;

use App\Entity\Album;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;

class AlbumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'album *',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Sortie scolaire 2024'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez le contenu de cet album...',
                    'rows' => 4
                ]
            ])
            ->add('eventDate', DateType::class, [
                'label' => 'Date de l\'événement *',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control datepicker'
                ]
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Album public',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ]
            ])
            ->add('coverImageFile', FileType::class, [
                'label' => 'Image de couverture',
                'required' => false,
                'mapped' => true,
                'attr' => [
                    'class' => 'form-control-file',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG ou WebP)',
                    ])
                ],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Album::class,
        ]);
    }
}