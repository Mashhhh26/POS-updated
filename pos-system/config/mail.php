<?php
/**
 * HRMS SMTP mail service.
 *
 * Credentials are intentionally NOT stored in the database. Create
 * config/mail.local.php from mail.local.php.example and fill it in locally.
 * PHPMailer is expected at vendor/phpmailer/src/.
 */
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/database.php';
}

$localMailConfig = __DIR__ . '/mail.local.php';
if (is_file($localMailConfig)) {
    require_once $localMailConfig;
}

if (!defined('MAIL_HOST')) define('MAIL_HOST', 'smtp.gmail.com');
if (!defined('MAIL_PORT')) define('MAIL_PORT', 587);
if (!defined('MAIL_ENCRYPTION')) define('MAIL_ENCRYPTION', 'tls');
if (!defined('MAIL_USERNAME')) define('MAIL_USERNAME', '');
if (!defined('MAIL_PASSWORD')) define('MAIL_PASSWORD', '');
if (!defined('MAIL_FROM_ADDRESS')) define('MAIL_FROM_ADDRESS', MAIL_USERNAME);
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', 'POS System HRMS');
if (!defined('MAIL_DEBUG')) define('MAIL_DEBUG', false);

function mailer_available(): bool {
    return is_file(__DIR__ . '/../vendor/phpmailer/src/PHPMailer.php')
        && is_file(__DIR__ . '/../vendor/phpmailer/src/SMTP.php')
        && is_file(__DIR__ . '/../vendor/phpmailer/src/Exception.php');
}

function smtp_is_configured(): bool {
    return mailer_available()
        && MAIL_HOST !== ''
        && MAIL_USERNAME !== ''
        && MAIL_PASSWORD !== ''
        && MAIL_FROM_ADDRESS !== '';
}

function send_hr_email(string $to, string $subject, string $htmlBody, string $altBody = ''): array {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Invalid recipient email address.'];
    }
    if (!mailer_available()) {
        return ['ok' => false, 'message' => 'PHPMailer is not installed. Copy its src folder to vendor/phpmailer/src/.'];
    }
    if (!smtp_is_configured()) {
        return ['ok' => false, 'message' => 'SMTP is not configured. Create config/mail.local.php and fill in the SMTP credentials.'];
    }

    require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->Port = (int) MAIL_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = strtolower(MAIL_ENCRYPTION) === 'ssl'
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = MAIL_DEBUG ? 2 : 0;
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody !== '' ? $altBody : trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));
        $mail->send();
        return ['ok' => true, 'message' => 'Email sent successfully.'];
    } catch (Throwable $e) {
        error_log('POS SMTP error: ' . $e->getMessage());
        return ['ok' => false, 'message' => 'SMTP send failed. Check the SMTP settings and server connection.'];
    }
}
