<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class MailSender
{
    protected PHPMailer $mail;

    public function __construct()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->mail = new PHPMailer(true);

        // Mail server settings
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['SMTP_USER'];
        $this->mail->Password   = $_ENV['SMTP_PASS'];
        $this->mail->SMTPSecure = $_ENV['SMTP_SECURE'];
        $this->mail->Port       = $_ENV['SMTP_PORT'];

        // Sender info
        $this->mail->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']);
        $this->mail->isHTML(true);
    }

    public function send(
        string $to,
        string $subject,
        string $body,
        array $cc = [],
        array $bcc = []
    ): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearCCs();
            $this->mail->clearBCCs();

            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;

            // Add CC recipients
            foreach ($cc as $ccEmail) {
                $this->mail->addCC($ccEmail);
            }

            // Add BCC recipients
            foreach ($bcc as $bccEmail) {
                $this->mail->addBCC($bccEmail);
            }

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mail Error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }
}
