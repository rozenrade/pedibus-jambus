<?php
// src/Form/PhotoMultipleType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;

class PhotoMultipleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('images', FileType::class, [
                'label' => 'Photos *',
                'required' => true,
                'multiple' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control-file',
                    'accept' => 'image/*',
                    'multiple' => 'multiple'
                ],
                'constraints' => [
                    new All([
                        new Image([
                            'maxSize' => '10M',
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                            'mimeTypesMessage' => 'Veuillez télécharger des images valides (JPG, PNG, WebP ou GIF)',
                        ])
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

        ]);
    }
}