<?php
// test_smtp_direct.php
require 'vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

echo "Test d'envoi d'email direct vers MailHog...\n";

// Essayez avec localhost
$dsn = 'smtp://localhost:1025';
echo "DSN utilisé: $dsn\n";

try {
    // Test 1: Avec SMTP stream
    $transport = Transport::fromDsn($dsn);
    $mailer = new Mailer($transport);
    
    $email = (new Email())
        ->from('test@pedibus.com')
        ->to('test@example.com')
        ->subject('Test SMTP Direct')
        ->text('Ceci est un test direct');
    
    $mailer->send($email);
    echo "✅ Email envoyé avec succès !\n";
    echo "Vérifiez dans MailHog: http://localhost:8025\n";
    
} catch (\Exception $e) {
    echo "❌ Erreur avec localhost: " . $e->getMessage() . "\n";
    
    // Essayez avec 127.0.0.1
    echo "\nEssai avec 127.0.0.1...\n";
    $dsn2 = 'smtp://127.0.0.1:1025';
    
    try {
        $transport = Transport::fromDsn($dsn2);
        $mailer = new Mailer($transport);
        $mailer->send($email);
        echo "✅ Email envoyé avec succès avec 127.0.0.1 !\n";
        echo "Mettez à jour votre .env avec: MAILER_DSN=smtp://127.0.0.1:1025\n";
    } catch (\Exception $e2) {
        echo "❌ Erreur avec 127.0.0.1: " . $e2->getMessage() . "\n";
    }
}