<?php

namespace App\Controller\Public;

use App\Entity\Album;
use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Turbo\TurboStreamResponse;

class CommentController extends AbstractController
{
    #[Route('commentaire/supprimer/{id}', name: 'public_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, EntityManagerInterface $em): Response
    {
        if ($comment->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        
        $currentAlbum = $comment->getAlbum();
        $commentId = $comment->getId();
        
        $em->remove($comment);
        $em->flush();
        
        $commentCount = $currentAlbum->getComments()->count();

        return new TurboStreamResponse(
            $this->renderView('comment/delete_stream.html.twig', [
                'commentId' => $commentId,
                'count' => $commentCount,
                'album' => $currentAlbum,
            ])
        );

    }

    #[Route('/commentaire/envoyer/{id}', name: 'public_comment_submit', methods: ['POST'])]
    public function post(Request $request, Album $album, EntityManagerInterface $em): Response
    {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);


        if ($this->getUser() && $form->isSubmitted() && $form->isValid()) {

            $comment->setAuthor($this->getUser());
            $comment->setAlbum($album);

            $em->persist($comment);
            $em->flush();

            $currentAlbum = $comment->getAlbum();
            $commentCount = $currentAlbum->getComments()->count();

            $emptyForm = $this->createForm(CommentType::class, new Comment());

            return new TurboStreamResponse(
                $this->renderView('comment/add_stream.html.twig', [
                    'comment' => $comment,
                    'album' => $album,
                    'count' => $commentCount,
                    'emptyForm' => $emptyForm->createView()
                ])
            );
        }

        return $this->redirectToRoute('public_album_show', ['id' => $album->getId()]);
    }
}
