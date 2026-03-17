<?php

namespace Core\Email;

use Core\Env\IndexEnv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    /**
     * Envia email via SMTP usando PHPMailer + config do .env
     *
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public static function enviar(
        string $para,
        string $assunto,
        string $mensagem,
        bool $isHtml = false
    ): bool {
        $para = trim($para);
        $assunto = trim($assunto);
        $mensagem = trim($mensagem);

        if ($para === "" || $assunto === "" || $mensagem === "") {
            throw new \InvalidArgumentException("Informe destinatário, assunto e mensagem.");
        }

        // ✅ Lê .env pelo seu loader
        $env = IndexEnv::carregar();

        $host = (string)($env["MAIL_HOST"] ?? "");
        $port = (int)($env["MAIL_PORT"] ?? 587);
        $user = (string)($env["MAIL_USER"] ?? "");
        $pass = (string)($env["MAIL_PASS"] ?? "");

        // Gmail: From deve bater com o user (recomendado)
        $from = (string)($env["MAIL_FROM"] ?? $user);
        $fromName = (string)($env["MAIL_FROM_NAME"] ?? "Imperio");

        // tls (587) ou ssl (465)
        $secure = strtolower((string)($env["MAIL_SECURE"] ?? "tls"));

        if ($host === "" || $user === "" || $pass === "") {
            throw new \RuntimeException("Config SMTP incompleta no .env (MAIL_HOST/MAIL_USER/MAIL_PASS).");
        }

        // ✅ Segurança básica: evita From diferente do Gmail (opcional, mas recomendado)
        if (stripos($host, "gmail.com") !== false && $from !== $user) {
            // se quiser forçar, descomenta:
            // $from = $user;
        }

        try {
            $mail = new PHPMailer(true);

            // SMTP
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;

            $mail->CharSet = "UTF-8";
            $mail->Timeout = 20;

            // tls / ssl
            if ($secure === "ssl") {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = $port ?: 465;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $port ?: 587;
            }

            // ✅ Hostinger/shared hosting: evita erro de certificado (usa só se precisar)
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Remetente
            $mail->setFrom($from, $fromName);

            // Destinatário
            $mail->addAddress($para);

            // Conteúdo
            $mail->isHTML($isHtml);
            $mail->Subject = $assunto;
            $mail->Body = $mensagem;

            if ($isHtml) {
                $mail->AltBody = strip_tags($mensagem);
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            // PHPMailer exception
            throw new \RuntimeException("Erro ao enviar email: " . $e->getMessage());
        } catch (\Throwable $e) {
            // qualquer outro erro
            throw new \RuntimeException("Erro inesperado ao enviar email: " . $e->getMessage());
        }
    }
}