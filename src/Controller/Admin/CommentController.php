<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/commentaires')]
class CommentController extends AbstractController
{
    #[Route('/', name: 'admin_comments')]
    public function index(CommentRepository $repo): Response
    {
        return $this->render('admin/comments/index.html.twig', ['comments' => $repo->findBy([], ['createdAt' => 'DESC'])]);
    }

    #[Route('/supprimer/{id}', name: 'admin_comment_delete')]
    public function delete(Comment $comment, EntityManagerInterface $em): Response
    {
        $em->remove($comment);
        $em->flush();

        $this->addFlash('success', 'Commentaire supprimé avec succès !');
        return $this->redirectToRoute('admin_comments');
    }
}
