<?php
/**
 * Gmail Email Test
 * Access: http://localhost/skillbox/public/test_mailtrap.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<h2>Gmail Email Test</h2>";
echo "<pre>";

$mail = new PHPMailer(true);

try {
    // Display what we're using
    $username = trim($_ENV['MAIL_USERNAME'] ?? '');
    $password = trim($_ENV['MAIL_PASSWORD'] ?? '');
    $fromEmail = trim($_ENV['MAIL_FROM_ADDRESS'] ?? $username);
    
    echo "Using Gmail Configuration:\n";
    echo "Host: smtp.gmail.com\n";
    echo "Port: 587\n";
    echo "Username: $username\n";
    echo "Password: " . (strlen($password) > 0 ? str_repeat('*', strlen($password) - 4) . substr($password, -4) : 'EMPTY') . " (length: " . strlen($password) . ")\n";
    echo "From: $fromEmail\n\n";
    
    // Configure for Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    // Enable full debug
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        echo htmlspecialchars($str) . "\n";
    };
    
    // Test email
    $testEmail = $_GET['email'] ?? 'test@example.com';
    $mail->setFrom($fromEmail, 'SkillBox Test');
    $mail->addAddress($testEmail);
    $mail->Subject = 'Test Email from SkillBox';
    $mail->Body = '<h1>Test Email</h1><p>This is a test email sent via Gmail SMTP.</p>';
    $mail->AltBody = 'This is a test email sent via Gmail SMTP.';
    
    echo "\n=== Attempting to send to: $testEmail ===\n";
    $mail->send();
    echo "\n✅ SUCCESS! Email sent!\n";
    echo "Check the recipient's inbox (and spam folder).\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR:\n";
    echo "Message: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "ErrorInfo: " . htmlspecialchars($mail->ErrorInfo) . "\n";
    
    echo "\n=== Troubleshooting ===\n";
    echo "1. Make sure you're using a Gmail App Password (not regular password)\n";
    echo "2. Verify 2-Step Verification is enabled on your Google account\n";
    echo "3. Generate a new App Password at: https://myaccount.google.com/apppasswords\n";
    echo "4. Check that MAIL_USERNAME and MAIL_PASSWORD are correct in .env\n";
    echo "5. Verify MAIL_FROM_ADDRESS matches your Gmail address\n";
}

echo "</pre>";
?>

