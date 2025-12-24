<?php

namespace Routers\Middleware;

use App\Dao\UsuarioDao\UsuarioSessionDao;
use Config\Base\Basecontrolador;

class AuthMiddleware extends Basecontrolador
{
    /**
     * Verifica se o usuário está autenticado por TOKEN (Header ou Cookie)
     */
    public static function verificar()
    {
        // 1) Pega token (header OU cookie)
        $token = self::extrairToken();

        if (!$token) {
            self::Mensagemjson("Token não fornecido.", 401);
            exit;
        }

        // 2) Buscar sessão no banco
        $sessao = UsuarioSessionDao::buscarPorToken($token);

        if (!$sessao) {
            self::Mensagemjson("Sessão inválida ou não encontrada.", 401);
            exit;
        }

        // 3) Verificar status (1 = ativo)
        if ($sessao->getStatusid() !== 1) {
            self::Mensagemjson("Sessão expirada ou bloqueada.", 401);
            exit;
        }

        // 4) Verificar expiração
        if (strtotime($sessao->getExpira_em()) < time()) {
            self::Mensagemjson("Sessão expirada. Faça login novamente.", 401);
            exit;
        }

        // Tudo certo → autenticado
        return true;
    }



    /**
     * Extrai token tanto do HEADER quanto do COOKIE
     */
    private static function extrairToken(): ?string
    {
        // 🔹 1) Primeiro tenta pegar do header Authorization
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $partes = explode(" ", $headers['Authorization']);

            if (count($partes) === 2 && strtolower($partes[0]) === "bearer") {
                return trim($partes[1]);
            }
        }

        // 🔹 2) Se não encontrou → tenta pegar do cookie
        if (isset($_COOKIE['token']) && !empty($_COOKIE['token'])) {
            return $_COOKIE['token'];
        }

        // 🔹 Nenhum token encontrado
        return null;
    }
}
