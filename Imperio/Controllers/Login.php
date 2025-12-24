<?php

namespace Imperio\Controllers;

use App\Dao\UsuarioDao\UsuarioDao;
use App\Dao\UsuarioDao\UsuarioSessionDao;
use Config\Base\Basecontrolador;

class Login extends Basecontrolador
{
    /**
     * =======================================================
     * 🔥 ETAPA 1 — LOGIN (USUÁRIO + SENHA)
     * =======================================================
     */
    public function etapa1()
    {
        $dados = self::receberJson();

        if (empty($dados['usuario']) || empty($dados['senha'])) {
            return self::Mensagemjson(
                "Informe usuário/email e senha.",
                400
            );
        }

        $usuarioInput = trim($dados['usuario']);
        $senha = $dados['senha'];

        $usuario = UsuarioDao::buscarPorEmailOuNome($usuarioInput);

        if (!$usuario) {
            return self::Mensagemjson(
                "Usuário não encontrado.",
                404
            );
        }

        if (!password_verify($senha, $usuario->getSenha())) {
            return self::Mensagemjson(
                "Senha incorreta.",
                401
            );
        }

        // 🔐 Superadmin exige PIN
        if ($usuario->getNivelId() === 1) {
            return self::Mensagemjson(
                "Login validado. Confirme o PIN.",
                200,
                [
                    "acao"       => "pedir_pin",
                    "id_usuario" => $usuario->getId(),
                    "email"      => $usuario->getEmail()
                ]
            );
        }

        return $this->criarSessao($usuario);
    }

    /**
     * =======================================================
     * 🔥 ETAPA 2 — VALIDAR PIN
     * =======================================================
     */
    public function etapa2()
    {
        $dados = self::receberJson();

        if (empty($dados['id_usuario']) || empty($dados['pin'])) {
            return self::Mensagemjson(
                "ID do usuário e PIN são obrigatórios.",
                400
            );
        }

        $usuario = UsuarioDao::buscarPorId((int)$dados['id_usuario']);

        if (!$usuario) {
            return self::Mensagemjson(
                "Usuário não encontrado.",
                404
            );
        }

        if ($usuario->getPin() !== trim($dados['pin'])) {
            return self::Mensagemjson(
                "PIN incorreto.",
                401
            );
        }

        return $this->criarSessao($usuario);
    }

    /**
     * =======================================================
     * 🔐 CRIAR SESSÃO + COOKIE (CORRETO)
     * =======================================================
     */
    private function criarSessao($usuario)
    {
        $token = bin2hex(random_bytes(32));

        $agora    = date('Y-m-d H:i:s');
        $expiraEm = date('Y-m-d H:i:s', strtotime('+7 days'));

        $sessaoId = UsuarioSessionDao::criarRetornandoId([
            'usuario_id' => $usuario->getId(),
            'token'      => $token,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'statusid'   => 1,
            'criado'     => $agora,
            'expira_em'  => $expiraEm
        ]);

        if (!$sessaoId) {
            return self::Mensagemjson(
                "Erro ao criar sessão.",
                500
            );
        }

        // 🔐 DETECÇÃO REAL DE HTTPS
        $isHttps =
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? null) == 443;

        $cookieOptions = [
            'expires'  => time() + (7 * 24 * 60 * 60),
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => $isHttps ? 'None' : 'Lax',
        ];

        // ⚠️ domínio só em HTTPS real
        if ($isHttps) {
            $cookieOptions['domain'] = '.imperioloja.com.br';
        }

        setcookie('imperio_session', $token, $cookieOptions);

        return self::Mensagemjson(
            "Login realizado com sucesso.",
            200,
            [
                'usuario' => [
                    'id'       => $usuario->getId(),
                    'nome'     => $usuario->getNome(),
                    'email'    => $usuario->getEmail(),
                    'nivel_id' => $usuario->getNivelId(),
                ]
            ]
        );
    }

    /**
     * =======================================================
     * 🔎 USUÁRIO AUTENTICADO (/me)
     * =======================================================
     */
    public function me()
    {
        $token = $_COOKIE['imperio_session'] ?? null;

        if (!$token) {
            return self::Mensagemjson(
                "Usuário não autenticado.",
                401
            );
        }

        $sessao = UsuarioSessionDao::buscarPorToken($token);

        if (!$sessao || $sessao->getStatusId() !== 1) {
            return self::Mensagemjson(
                "Sessão inválida.",
                401
            );
        }

        if (new \DateTime() > new \DateTime($sessao->getExpiraEm())) {
            UsuarioSessionDao::invalidarToken($token);
            return self::Mensagemjson(
                "Sessão expirada.",
                401
            );
        }

        $usuario = UsuarioDao::buscarPorId($sessao->getUsuarioId());

        if (!$usuario) {
            return self::Mensagemjson(
                "Usuário não encontrado.",
                404
            );
        }

        return self::Mensagemjson(
            "Usuário autenticado.",
            200,
            [
                'usuario' => [
                    'id'       => $usuario->getId(),
                    'nome'     => $usuario->getNome(),
                    'email'    => $usuario->getEmail(),
                    'nivel_id' => $usuario->getNivelId(),
                ]
            ]
        );
    }
}
