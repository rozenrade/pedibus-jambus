<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur est déjà connecté, redirigez-le
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encode le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );

            // L'utilisateur n'est PAS vérifié par défaut
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            // Générer et envoyer l'email de confirmation
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email', 
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@pedibus-jambus.com', 'Pedibus Jambus'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Message de succès
            $this->addFlash('success', 'Un email de confirmation a été envoyé à votre adresse. Veuillez vérifier votre boîte mail pour activer votre compte.');

            // NE PAS connecter l'utilisateur automatiquement
            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        // On vérifie si l'utilisateur est déjà connecté
        // Si oui, on le déconnecte temporairement pour la vérification
        if ($this->getUser()) {
            // On peut soit:
            // 1. Passer directement à la vérification (solution simple)
            // 2. Déconnecter puis reconnecter après vérification (plus propre)
            
            // Pour cette solution, on laisse l'utilisateur connecté
        }

        // Vérifier le lien de confirmation d'email
        $id = $request->query->get('id');
        
        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $this->getDoctrine()->getManager()->getRepository(User::class)->find($id);
        
        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        // Marquer l'utilisateur comme vérifié
        $user->setIsVerified(true);
        $this->getDoctrine()->getManager()->flush();

        // Message de succès
        $this->addFlash('success', 'Votre adresse email a été vérifiée avec succès ! Vous pouvez maintenant vous connecter.');

        // Rediriger vers la page de connexion
        return $this->redirectToRoute('app_login');
    }
}