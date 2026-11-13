<?php
class Mailer {
    static function enviar(string $para, string $asunto, string $html): void {
        if (env('MAIL_DRIVER', 'log') === 'log') {   // modo desarrollo: no sale ningún correo real
            file_put_contents(__DIR__ . '/../../storage/mail.log', date('c') . " PARA:$para ASUNTO:$asunto\n$html\n----\n", FILE_APPEND);
            return;
        }
        $m = new PHPMailer\PHPMailer\PHPMailer(true);
        $m->isSMTP();
        $m->Host = env('SMTP_HOST'); $m->SMTPAuth = true;
        $m->Username = env('SMTP_USER'); $m->Password = env('SMTP_PASS');
        $m->SMTPSecure = env('SMTP_SECURE', 'ssl') === 'tls' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $m->Port = (int)env('SMTP_PORT', 465);
        $m->CharSet = 'UTF-8';
        $m->setFrom(env('MAIL_FROM', env('SMTP_USER')), 'Talento Humano - TALENTIA');
        $m->addAddress($para);
        $m->isHTML(true); $m->Subject = $asunto; $m->Body = $html; $m->AltBody = strip_tags($html);
        $m->send();
    }
}
