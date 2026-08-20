<?php
// src/Controller/Public/RecipeController.php

namespace App\Controller\Public;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    #[Route('/recettes', name: 'public_recipes_index', methods: ['GET'])]
    public function index(RecipeRepository $repository): Response
    {
        return $this->render('public/recipe/index.html.twig', [
            'recipes' => $repository->findAllOrdered(),
            'categories' => Recipe::CATEGORIES,
        ]);
    }

    #[Route('/recettes/{slug}', name: 'public_recipe_show', methods: ['GET'], requirements: ['slug' => '[a-z0-9\-]+'])]
    public function show(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Recipe $recipe
    ): Response {
        return $this->render('public/recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
