<?php

// src/Form/HikingProgramType.php
namespace App\Form;

use App\Entity\HikingProgram;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Vich\UploaderBundle\Form\Type\VichFileType;

class HikingProgramType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentYear = (int) date('Y');
        
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la randonnée',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Programme du 1er trimestre 2024'
                ]
            ])
            ->add('year', IntegerType::class, [
                'label' => 'Année',
                'required' => true,
                'data' => $currentYear,
                'attr' => [
                    'min' => 2020,
                    'max' => 2030
                ]
            ])
            ->add('quarter', ChoiceType::class, [
                'label' => 'Type de programme',
                'required' => false,
                'placeholder' => 'Sélectionnez un trimestre',
                'choices' => [
                    '1er trimestre' => 1,
                    '2ème trimestre' => 2,
                    '3ème trimestre' => 3,
                    '4ème trimestre' => 4,
                    'Sorties Grandes Évasions' => null
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Description optionnelle...'
                ]
            ]);
            
        // Ajoutez le champ pdfFile avec des options conditionnelles
        $pdfFileOptions = [
            'label' => 'Programme PDF',
            'required' => $options['is_new'],
            'allow_delete' => false,
            'download_uri' => false,
            'download_label' => 'Télécharger',
            'delete_label' => 'Supprimer',
        ];
        
        // Ajouter les contraintes seulement pour la création
        if ($options['is_new']) {
            $pdfFileOptions['constraints'] = [
                new \Symfony\Component\Validator\Constraints\File([
                    'maxSize' => '5M',
                    'mimeTypes' => ['application/pdf'],
                    'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide',
                ]),
                new \Symfony\Component\Validator\Constraints\NotNull([
                    'message' => 'Veuillez sélectionner un fichier PDF',
                ])
            ];
        }
        
        $builder->add('pdfFile', VichFileType::class, $pdfFileOptions);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HikingProgram::class,
            'is_new' => true, // Par défaut, c'est pour la création
        ]);
        
        $resolver->setAllowedTypes('is_new', 'bool');
    }
}