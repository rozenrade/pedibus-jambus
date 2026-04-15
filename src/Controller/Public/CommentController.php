<?php

namespace App\Controller\Public;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $commentId = $comment->getId();

        $em->remove($comment);
        $em->flush();

        return new TurboStreamResponse(
            $this->renderView('comment/delete_stream.html.twig', [
                'commentId' => $commentId
            ])
        );
    }
}
