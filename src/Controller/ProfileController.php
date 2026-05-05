<?php

namespace App\Controller;

use App\Form\ChangeDataType;
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

    #[Route('/mes-informations', name: 'app_profile_change_data')]
    public function changeEmail(HttpFoundationRequest $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        $passwordForm = $this->createForm(ChangePasswordType::class);
        $passwordForm->handleRequest($request);

        # Password Logic
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {

            if (!$passwordHasher->isPasswordValid($user, $passwordForm->get('oldPassword')->getData())) {

                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');

                return $this->redirectToRoute('app_profile_change_password');
            }

            if ($passwordForm->get('newPassword')->getData() !== $passwordForm->get('confirmPassword')->getData()) {

                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');

                return $this->redirectToRoute('app_profile_change_password');
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $passwordForm->get('newPassword')->getData());

            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Mot de passe modifié avec succès !');

            return $this->redirectToRoute('app_profile_index');
        }

        $userForm = $this->createForm(ChangeDataType::class, $user);
        $userForm->handleRequest($request);

        # User data Logic
        if ($userForm->isSubmitted() && $userForm->isValid()) {

            if (!$passwordHasher->isPasswordValid($user, $userForm->get('password')->getData())) {

                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');

                return $this->redirectToRoute('app_profile_change_data');
            }

            // If user decides to change email
            if ($userForm->get('email')->getData()) {
                $user->setEmail($userForm->get('email')->getData());
            }

            // If user decides to add a nickname
            if ($userForm->get('nickname')->getData()) {
                $user->setNickname($userForm->get('nickname')->getData());
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Données modifiées avec succès !');

            return $this->redirectToRoute('app_profile_index');
        }

        return $this->render('public/profile/change_infos.html.twig', ['userForm' => $userForm, 'passwordForm' => $passwordForm->createView()]);
    }
}
