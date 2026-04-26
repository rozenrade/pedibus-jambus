<?php

namespace App\Controller;

use App\Form\ChangeEmailType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/mon-profil')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('public/profile/index.html.twig', []);
    }

    #[Route('/changer-mot-de-passe', name: 'app_profile_change_password')]
    public function changePassword(HttpFoundationRequest $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }


        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$passwordHasher->isPasswordValid($user, $form->get('oldPassword')->getData())) {

                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');

                return $this->redirectToRoute('app_profile_change_password');
            }

            if ($form->get('newPassword')->getData() !== $form->get('confirmPassword')->getData()) {

                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');

                return $this->redirectToRoute('app_profile_change_password');
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('newPassword')->getData());
            
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Mot de passe modifié avec succès !');

            return $this->redirectToRoute('app_profile_index');
        }

        return $this->render('public/profile/change_password.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/changer-email', name: 'app_profile_change_email')]
    public function changeEmail(HttpFoundationRequest $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ChangeEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$passwordHasher->isPasswordValid($user, $form->get('password')->getData())) {

                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');

                return $this->redirectToRoute('app_profile_change_email');
            }

            $user->setEmail($form->get('email')->getData());
            if($form->get('nickname')) {
                $user->setNickname($form->get('nickname')->getData());
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Données modifiées avec succès !');

            return $this->redirectToRoute('app_profile_index');
        }

        return $this->render('public/profile/change_email.html.twig', ['form' => $form->createView()]);
    }
}
