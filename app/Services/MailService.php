<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $mailer;
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        // Ensure .env is loaded
        if (!isset($_ENV['MAIL_USERNAME'])) {
            try {
                $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
                $dotenv->load();
            } catch (\Exception $e) {
                error_log("Failed to load .env: " . $e->getMessage());
            }
        }
        
        $this->mailer = new PHPMailer(true);
        $this->fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? $_ENV['MAIL_USERNAME'] ?? 'noreply@skillbox.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'SkillBox';

        // Configure for Gmail SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = trim($_ENV['MAIL_USERNAME'] ?? '');
        $this->mailer->Password = trim($_ENV['MAIL_PASSWORD'] ?? ''); // Use App Password, not regular password
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        $this->mailer->CharSet = 'UTF-8';
        
        // Enable debug output for troubleshooting (set to 0 in production, 2 for verbose)
        $this->mailer->SMTPDebug = (int)($_ENV['MAIL_DEBUG'] ?? 0);
        $this->mailer->Debugoutput = function($str, $level) {
            error_log("PHPMailer: $str");
        };
        
        // Log configuration (without password) for debugging
        if (($_ENV['APP_DEBUG'] ?? false)) {
            error_log("MailService Config - Host: {$this->mailer->Host}, Port: {$this->mailer->Port}, Username: {$this->mailer->Username}");
        }
    }

    /**
     * Send password reset code email
     */
    public function sendPasswordResetCode($toEmail, $toName, $code)
    {
        try {
            // Clear any previous recipients and reset
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearCustomHeaders();
            
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Code - SkillBox';
            
            $htmlBody = $this->getPasswordResetEmailTemplate($toName, $code);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Your password reset code is: {$code}. This code will expire in 15 minutes.";

            $this->mailer->send();
            return ['success' => true];
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log("MailService Error: {$this->mailer->ErrorInfo}");
            return ['success' => false, 'error' => $this->mailer->ErrorInfo];
        } catch (\Exception $e) {
            error_log("MailService Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get HTML email template for password reset
     */
    private function getPasswordResetEmailTemplate($name, $code)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1F3440 0%, #25BDB0 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { background: white; border: 2px dashed #25BDB0; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .code { font-size: 32px; font-weight: bold; color: #1F3440; letter-spacing: 5px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Reset Request</h1>
                </div>
                <div class='content'>
                    <p>Hello {$name},</p>
                    <p>You have requested to reset your password. Use the code below to verify your identity:</p>
                    <div class='code-box'>
                        <div class='code'>{$code}</div>
                    </div>
                    <p><strong>This code will expire in 15 minutes.</strong></p>
                    <p>If you didn't request this, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " SkillBox. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}

