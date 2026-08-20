<?php
// src/Form/RecipeType.php

namespace App\Form;

use App\Entity\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la recette',
                'attr' => ['placeholder' => 'Ex: Tarte aux pommes de grand-mère'],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => Recipe::CATEGORIES,
            ])
            ->add('servings', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'required' => false,
                'attr' => ['min' => 1, 'placeholder' => 'Ex: 4'],
            ])
            ->add('ingredients', TextareaType::class, [
                'label' => 'Ingrédients (un par ligne)',
                'attr' => [
                    'rows' => 8,
                    'placeholder' => "200g de farine\n3 œufs\n100g de sucre\n...",
                ],
            ])
            ->add('steps', TextareaType::class, [
                'label' => 'Étapes de préparation (une par ligne)',
                'attr' => [
                    'rows' => 10,
                    'placeholder' => "Préchauffer le four à 180°C\nMélanger la farine et le sucre\n...",
                ],
            ])
            ->add('photoFile', VichImageType::class, [
                'label' => 'Photo de la recette',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'imagine_pattern' => 'thumbnail',
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG ou WebP)',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}