<?php
// src/Controller/Admin/RecipeController.php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/recipes')]
#[IsGranted('ROLE_ADMIN')]
class RecipeController extends AbstractController
{
    #[Route('/', name: 'admin_recipes_index', methods: ['GET'])]
    public function index(RecipeRepository $repository): Response
    {
        return $this->render('admin/recipe/index.html.twig', [
            'recipes' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'admin_recipes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($recipe);
            $em->flush();

            $this->addFlash('success', 'La recette a bien été créée.');

            return $this->redirectToRoute('admin_recipes_index');
        }

        return $this->render('admin/recipe/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_recipes_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Recipe $recipe, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'La recette a bien été modifiée.');

            return $this->redirectToRoute('admin_recipes_index');
        }

        return $this->render('admin/recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_recipes_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Recipe $recipe, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete-recipe-' . $recipe->getId(), $request->request->get('_token'))) {
            $em->remove($recipe);
            $em->flush();

            $this->addFlash('success', 'La recette a bien été supprimée.');
        }

        return $this->redirectToRoute('admin_recipes_index');
    }
}