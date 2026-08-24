<?php
/**
 * FitLife — Notification helpers
 * --------------------------------
 * Sending an email must NEVER break user registration. Every call in
 * here is wrapped so that an SMTP failure (wrong password, no internet,
 * Gmail rate limit, etc.) is logged quietly and the calling code
 * continues normally.
 */

require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function log_notification_error(string $message): void {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($logDir . '/notifications.log', $line, FILE_APPEND);
}

/**
 * Sends the admin an email when a new user registers.
 * Returns true on success, false on failure (never throws).
 */
function notify_admin_new_registration(string $name, string $email): bool {
    if (!defined('ADMIN_NOTIFICATIONS_ENABLED') || !ADMIN_NOTIFICATIONS_ENABLED) {
        return false;
    }
    if (!defined('ADMIN_NOTIFY_EMAIL') || !defined('SMTP_USERNAME') || !defined('SMTP_APP_PASSWORD')) {
        log_notification_error('Notification skipped: mail_config.php is missing required constants.');
        return false;
    }
    if (SMTP_APP_PASSWORD === 'your16charapppassword' || SMTP_USERNAME === 'your-sending-gmail@gmail.com') {
        log_notification_error('Notification skipped: SMTP credentials in config/mail_config.php are still placeholders.');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_APP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->Timeout    = 10;

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress(ADMIN_NOTIFY_EMAIL);
        $mail->addReplyTo($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'FitLife — New member signed up: ' . $name;
        $mail->Body = '
            <div style="font-family:Arial,sans-serif;max-width:480px;margin:0 auto;">
                <h2 style="color:#e4381c;">New FitLife Registration</h2>
                <p>A new member just created an account on your site.</p>
                <table style="width:100%;border-collapse:collapse;margin:16px 0;">
                    <tr><td style="padding:6px 0;color:#666;">Name</td><td style="padding:6px 0;font-weight:bold;">' . htmlspecialchars($name) . '</td></tr>
                    <tr><td style="padding:6px 0;color:#666;">Email</td><td style="padding:6px 0;font-weight:bold;">' . htmlspecialchars($email) . '</td></tr>
                    <tr><td style="padding:6px 0;color:#666;">Registered at</td><td style="padding:6px 0;">' . date('d M Y, h:i A') . '</td></tr>
                </table>
                <p style="color:#999;font-size:12px;">This is an automated notification from your FitLife admin panel.</p>
            </div>';
        $mail->AltBody = "New FitLife registration:\nName: $name\nEmail: $email\nAt: " . date('d M Y, h:i A');

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        log_notification_error('Failed to send registration email to admin: ' . $mail->ErrorInfo);
        return false;
    } catch (\Throwable $e) {
        log_notification_error('Unexpected error sending registration email: ' . $e->getMessage());
        return false;
    }
}
