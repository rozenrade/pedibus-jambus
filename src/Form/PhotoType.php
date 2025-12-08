<?php
// src/Form/PhotoType.php

namespace App\Form;

use App\Entity\Photo;
use App\Entity\Album;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vich\UploaderBundle\Form\Type\VichImageType; // <-- IMPORTANT
use Vich\UploaderBundle\Form\Type\VichFileType;   // Alternative

class PhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [ // <-- REMPLACER FileType par VichImageType
                'label' => 'Photo *',
                'required' => true,
                'allow_delete' => false,   // Cacher le bouton de suppression
                'download_uri' => false,   // Cacher le lien de téléchargement
                'image_uri' => false,      // Cacher la prévisualisation
                'attr' => [
                    'class' => 'form-control-file',
                    'accept' => 'image/*'
                ],
                // Optionnel : ajouter une contrainte de validation
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Image([
                        'maxSize' => '10M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, WebP ou GIF)'
                    ])
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Titre de la photo'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description de la photo...',
                    'rows' => 3
                ]
            ])
            ->add('album', EntityType::class, [
                'label' => 'Album (optionnel)',
                'class' => Album::class,
                'choice_label' => 'title',
                'required' => false,
                'placeholder' => 'Choisir un album...',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags (séparés par des virgules)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ex: école, sortie, enfants, nature'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Photo::class,
        ]);
    }
}