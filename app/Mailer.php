<?php

declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

final class Mailer
{
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $logOnly = empty(Config::get('MAIL_HOST'));

        if ($logOnly) {
            $dir = dirname(__DIR__) . '/storage/mail';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $entry = date('c') . " | {$toEmail} | {$subject}\n" . strip_tags($htmlBody) . "\n\n";
            file_put_contents($dir . '/mail.log', $entry, FILE_APPEND);
            return true;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = (string) Config::get('MAIL_HOST');
            $mail->Port = (int) Config::get('MAIL_PORT', 587);
            $mail->SMTPAuth = true;
            $mail->Username = (string) Config::get('MAIL_USERNAME');
            $mail->Password = (string) Config::get('MAIL_PASSWORD');
            $enc = Config::get('MAIL_ENCRYPTION', 'tls');
            if ($enc === 'ssl') { $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; }
            else { $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; }

            $mail->setFrom((string) Config::get('MAIL_FROM_ADDRESS', 'noreply@example.com'), (string) Config::get('MAIL_FROM_NAME', 'Appliances Store'));
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            return $mail->send();
        } catch (MailException $e) {
            if (Config::get('APP_DEBUG', 'false') === 'true') {
                error_log('Mail error: ' . $e->getMessage());
            }
            return false;
        }
    }
}

