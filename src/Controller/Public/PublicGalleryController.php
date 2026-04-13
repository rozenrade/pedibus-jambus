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
        // Récupérer uniquement les albums publics, triés par date d'événement (plus récent d'abord)
        $albums = $albumRepository->findBy(
            ['isPublic' => true],
            ['eventDate' => 'DESC', 'createdAt' => 'DESC']
        );

        return $this->render('public/gallery/index.html.twig', [
            'albums' => $albums,
        ]);
    }

    #[Route('/galerie/{id}', name: 'public_album_show', methods: ['GET', 'POST'])]
    public function show(Album $album, Request $request, EntityManagerInterface $em): Response
    {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($this->getUser() && $form->isSubmitted() && $form->isValid()) {

            $comment->setAuthor($this->getUser());
            $comment->setAlbum($album);

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('public_album_show', [
                'id' => $album->getId()
            ]);
        }

        return $this->render('public/gallery/show.html.twig', [
            'album' => $album,
            'commentForm' => $form->createView()
        ]);
    }
}
