<?php
declare(strict_types=1);

// Simple mail utility with development-friendly fallback
// Configure from-address for your environment
const MAIL_FROM = 'no-reply@appliancepro.local';

function send_email(string $to, string $subject, string $html, string $text = ''): bool {
    // Try PHP mail() if available; also log to file for debugging
    $headers   = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=utf-8';
    $headers[] = 'From: AppliancePro <' . MAIL_FROM . '>';
    $headers[] = 'X-Mailer: PHP/' . phpversion();

    $ok = false;
    try {
        $ok = @mail($to, $subject, $html, implode("\r\n", $headers));
    } catch (Throwable $e) {
        $ok = false;
    }

    // Always log
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) { @mkdir($logDir, 0777, true); }
    $log = date('c') . " | TO: $to | SUBJECT: $subject\n$html\n\n";
    @file_put_contents($logDir . '/mail.log', $log, FILE_APPEND);

    return $ok;
}

