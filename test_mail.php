<?php
/**
 * Test Mailtrap Email Sending
 * Run this file to test if Mailtrap is configured correctly
 * Access: http://localhost/skillbox/public/test_mail.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

echo "<h2>Mailtrap Configuration Test</h2>";
echo "<pre>";

try {
    // Display configuration
    echo "Configuration:\n";
    echo "Host: " . ($_ENV['MAIL_HOST'] ?? 'NOT SET') . "\n";
    echo "Port: " . ($_ENV['MAIL_PORT'] ?? 'NOT SET') . "\n";
    echo "Username: " . ($_ENV['MAIL_USERNAME'] ?? 'NOT SET') . "\n";
    echo "Password: " . (empty($_ENV['MAIL_PASSWORD']) ? 'NOT SET' : '***' . substr($_ENV['MAIL_PASSWORD'], -4)) . "\n";
    echo "\n";
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = trim($_ENV['MAIL_HOST'] ?? 'sandbox.smtp.mailtrap.io');
    $mail->SMTPAuth = true;
    $mail->Username = trim($_ENV['MAIL_USERNAME'] ?? '');
    $mail->Password = trim($_ENV['MAIL_PASSWORD'] ?? '');
    $mail->Port = (int)($_ENV['MAIL_PORT'] ?? 2525);
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet = 'UTF-8';
    
    // SMTP Options for better compatibility
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    
    // Enable verbose debug output
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        echo htmlspecialchars($str) . "\n";
    };
    
    // Recipients
    $testEmail = $_GET['email'] ?? 'test@example.com';
    $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@skillbox.com', $_ENV['MAIL_FROM_NAME'] ?? 'SkillBox');
    $mail->addAddress($testEmail);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from SkillBox';
    $mail->Body = '<h1>Test Email</h1><p>This is a test email to verify Mailtrap configuration.</p>';
    $mail->AltBody = 'This is a test email to verify Mailtrap configuration.';
    
    echo "\nAttempting to send email...\n";
    echo "----------------------------------------\n";
    
    $mail->send();
    
    echo "----------------------------------------\n";
    echo "\n✅ SUCCESS! Email sent successfully!\n";
    echo "Check your Mailtrap inbox at: https://mailtrap.io/inboxes\n";
    
} catch (Exception $e) {
    echo "----------------------------------------\n";
    echo "\n❌ ERROR: Message could not be sent.\n";
    echo "Mailer Error: " . htmlspecialchars($mail->ErrorInfo) . "\n";
    echo "\nException: " . htmlspecialchars($e->getMessage()) . "\n";
    
    echo "\n\nTroubleshooting:\n";
    echo "1. Verify your Mailtrap credentials in .env file\n";
    echo "2. Make sure MAIL_HOST is 'sandbox.smtp.mailtrap.io'\n";
    echo "3. Check that MAIL_USERNAME and MAIL_PASSWORD are correct\n";
    echo "4. Verify port 2525 is not blocked by firewall\n";
}

echo "</pre>";
?>

