<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // IMPORTANT
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/', name: 'admin_users_list')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/modifier/{id}', name: 'admin_user_edit')]
    public function edit(
        EntityManagerInterface $em,
        User $user,
        Request $request
    ): Response {

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');

            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/supprimer/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(
        EntityManagerInterface $em,
        User $user,
        Request $request
    ): Response {

        if (!$this->isCsrfTokenValid(
            'delete-user-'.$user->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException();
        }

        if ($user === $this->getUser()) {

            $this->addFlash(
                'error',
                'Vous ne pouvez pas supprimer votre propre compte'
            );

            return $this->redirectToRoute('admin_users_list');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé');

        return $this->redirectToRoute('admin_users_list');
    }
}