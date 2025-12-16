<?php
// src/Controller/Public/HikingProgramController.php

namespace App\Controller\Public;

use App\Repository\HikingProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HikingProgramController extends AbstractController
{
    #[Route('/programs', name: 'public_programs_index', methods: ['GET'])]
    public function index(HikingProgramRepository $repository): Response
    {
        // Récupérer tous les programmes triés par année (décroissant) et trimestre
        $programs = $repository->findBy([], [
            'year' => 'DESC', 
            'quarter' => 'ASC',
            'title' => 'ASC'
        ]);
        
        // Grouper par année
        $groupedByYear = [];
        
        foreach ($programs as $program) {
            $year = $program->getYear();
            if (!isset($groupedByYear[$year])) {
                $groupedByYear[$year] = [];
            }
            $groupedByYear[$year][] = $program;
        }
        
        return $this->render('public/hiking_program/index.html.twig', [
            'groupedPrograms' => $groupedByYear
        ]);
    }
}