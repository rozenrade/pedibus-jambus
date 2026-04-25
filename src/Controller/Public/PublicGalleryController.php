<?php

namespace App\Controller\Public;

use App\Entity\Album;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PublicGalleryController extends AbstractController
{
    #[Route('/galerie', name: 'public_album')]
    public function gallery(AlbumRepository $albumRepository): Response
    {
        $albums = $albumRepository->findBy(
            ['isPublic' => true],
            ['eventDate' => 'DESC', 'createdAt' => 'DESC']
        );

        return $this->render('public/gallery/index.html.twig', [
            'albums' => $albums,
        ]);
    }

    #[Route('/galerie/{id}', name: 'public_album_show', methods: ['GET'])]
    public function show(Album $album): Response
    {
        $form = $this->createForm(CommentType::class, new Comment());

        return $this->render('public/gallery/show.html.twig', [
            'album' => $album,
            'commentForm' => $form->createView()
        ]);
    }
}
