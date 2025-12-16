<?php

// src/Controller/Admin/HikingProgramController.php

namespace App\Controller\Admin;

use App\Entity\HikingProgram;
use App\Form\HikingProgramType;
use App\Repository\HikingProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/programs')]
#[IsGranted('ROLE_ADMIN')]
class HikingProgramController extends AbstractController
{
    #[Route('/', name: 'admin_programs_index', methods: ['GET'])]
    public function index(HikingProgramRepository $repository): Response
    {
        // Récupère les programmes groupés par année
        $programs = $repository->findBy([], ['year' => 'DESC', 'quarter' => 'ASC']);
        $groupedByYear = [];

        foreach ($programs as $program) {
            $year = $program->getYear();
            if (!isset($groupedByYear[$year])) {
                $groupedByYear[$year] = [];
            }
            $groupedByYear[$year][] = $program;
        }

        return $this->render('admin/hiking_program/index.html.twig', [
            'groupedPrograms' => $groupedByYear
        ]);
    }

    #[Route('/new', name: 'admin_programs_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $program = new HikingProgram();
        $form = $this->createForm(HikingProgramType::class, $program, [
            'is_new' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($program);
            $entityManager->flush();

            $this->addFlash('success', 'Le programme a été créé avec succès.');
            return $this->redirectToRoute('admin_programs_index');
        }

        return $this->render('admin/hiking_program/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_programs_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HikingProgram $program, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HikingProgramType::class, $program, [
            'is_new' => false // Pour l'édition, le PDF n'est pas obligatoire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le programme a été modifié avec succès.');
            return $this->redirectToRoute('admin_programs_index');
        }

        return $this->render('admin/hiking_program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'admin_programs_delete', methods: ['POST'])]
    public function delete(Request $request, HikingProgram $program, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $program->getId(), $request->request->get('_token'))) {
            $entityManager->remove($program);
            $entityManager->flush();
            $this->addFlash('success', 'Le programme a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_programs_index');
    }
}
