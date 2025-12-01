<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestController extends AbstractController
{
    #[Route('/test-mail', name: 'test_mail')]
    public function testMail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('test@pedibus.com')
            ->to('test@example.com')
            ->subject('Test MailHog')
            ->text('Ceci est un test de MailHog');
        
        try {
            $mailer->send($email);
            return new Response('Email envoyé avec succès ! Vérifiez <a href="http://localhost:8025">MailHog</a>');
        } catch (\Exception $e) {
            return new Response('Erreur: ' . $e->getMessage());
        }
    }
}