<?php

namespace Imperio\Controllers;

use App\Dao\UsuarioDao\UsuarioSessionDao;
use Config\Base\Basecontrolador;

class LoginController extends Basecontrolador
{
    /**
     * =======================================================
     * 🔓 LOGOUT (SEGURO + TOKEN INVÁLIDO)
     * =======================================================
     */
    public function logout()
    {
        $token = $_COOKIE['imperio_session'] ?? null;

        if ($token) {
            /**
             * 🔐 Invalida token independente do estado:
             * - válido
             * - inválido
             * - expirado
             * - já deslogado
             */
            UsuarioSessionDao::invalidarToken($token);

            /**
             * 👉 OPCIONAL (MAIS SEGURO)
             * Se quiser logout global (todas as sessões do usuário),
             * você pode buscar o usuário pelo token e invalidar todas:
             *
             * UsuarioSessionDao::invalidarTodasPorUsuario($usuarioId);
             */
        }

        // 🔍 Detecta HTTPS real
        $isHttps =
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? null) == 443;

        /**
         * 🍪 Limpa cookie SEMPRE
         */
        $cookieOptions = [
            'expires'  => time() - 3600, // expira no passado
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => $isHttps ? 'None' : 'Lax',
        ];

        // ⚠️ mesmo domínio do login
        if ($isHttps) {
            $cookieOptions['domain'] = '.imperioloja.com.br';
        }

        setcookie('imperio_session', '', $cookieOptions);

        /**
         * 🔒 Nunca informa se o token existia ou não
         * (boa prática de segurança)
         */
        return self::Mensagemjson(
            "Logout realizado com sucesso.",
            200
        );
    }
}
