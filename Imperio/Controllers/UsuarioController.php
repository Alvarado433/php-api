<?php

namespace Imperio\Controllers;

use Config\BaseDao\BaseDao;
use Config\Base\Basecontrolador;
use App\Dao\UsuarioDao\UsuarioDao;
use App\Dao\UsuarioDao\UsuarioSessionDao;
use App\Models\Usuario\UsuarioModel;
use App\Models\Usuario\SessaoModel;

class UsuarioController extends Basecontrolador
{
    /**
     * Listar todos os usuários
     */
    public function listar()
    {
        $usuarios = UsuarioDao::listar();
        self::info("Listando todos os usuários.");

        return self::Mensagemjson("Lista de usuários", 200, [
            "total" => count($usuarios),
            "usuarios" => array_map(fn($u) => $u->toArray(), $usuarios)
        ]);
    }

    /**
     * Buscar usuário por ID
     */
    public function buscar($id)
    {
        $usuario = UsuarioDao::buscarPorId((int)$id);
        if (!$usuario) {
            self::warning("Usuário ID {$id} não encontrado.");
            return self::Mensagemjson("Usuário não encontrado", 404);
        }

        self::info("Usuário ID {$id} encontrado.");
        return self::Mensagemjson("Usuário encontrado", 200, $usuario->toArray());
    }

    /**
     * Criar novo usuário com PIN automático e sessão
     */
    public function criar()
    {
        $dados = self::receberJson();

        if (empty($dados["nome"]) || empty($dados["email"]) || empty($dados["senha"])) {
            self::warning("Campos obrigatórios ausentes.");
            return self::Mensagemjson("Nome, email e senha são obrigatórios.", 400);
        }

        $pin = str_pad(random_int(0, 999999), 6, "0", STR_PAD_LEFT);
        $nivelid = $dados["nivelid"] ?? 1; // nível sistema por padrão

        $usuario = new UsuarioModel(
            id_usuario: 0,
            nome: $dados["nome"],
            email: $dados["email"],
            senha: password_hash($dados["senha"], PASSWORD_DEFAULT),
            pin: $pin,
            nivel_id: $nivelid,
            statusid: 1,
            telefone: $dados["telefone"] ?? null,
            cpf: $dados["cpf"] ?? null,
            criado: date("Y-m-d H:i:s"),
            atualizado: date("Y-m-d H:i:s")
        );

        $idCriado = UsuarioDao::criar($usuario);

        if (!$idCriado) {
            return self::Mensagemjson("Erro ao criar usuário", 500);
        }

        // Criar sessão automática
        $token = bin2hex(random_bytes(16));
        $sessao = new SessaoModel(
            id_sessao: 0,
            usuario_id: $idCriado,
            token: $token,
            ip: $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            user_agent: $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
            statusid: 1,
            criado: date("Y-m-d H:i:s"),
            expira_em: date("Y-m-d H:i:s", strtotime("+1 day"))
        );

        UsuarioSessionDao::criar($sessao);
        self::success("Usuário '{$dados['email']}' criado com sucesso. ID: {$idCriado}");

        return self::Mensagemjson("Usuário criado com sucesso!", 201, [
            "id_usuario" => $idCriado,
            "email" => $dados["email"],
            "pin" => $pin,
            "nivelid" => $nivelid,
            "telefone" => $dados["telefone"] ?? null,
            "cpf" => $dados["cpf"] ?? null,
            "token_sessao" => $token
        ]);
    }

    /**
     * Atualizar usuário existente
     */
    public function atualizar($id)
    {
        $dados = self::receberJson();
        $usuarioAtual = UsuarioDao::buscarPorId((int)$id);

        if (!$usuarioAtual) {
            self::warning("Usuário ID {$id} não encontrado para atualizar.");
            return self::Mensagemjson("Usuário não encontrado", 404);
        }

        $senha = $dados["senha"] ?? $usuarioAtual->getSenha();

        $novoUsuario = new UsuarioModel(
            id_usuario: $id,
            nome: $dados["nome"] ?? $usuarioAtual->getNome(),
            email: $dados["email"] ?? $usuarioAtual->getEmail(),
            senha: $senha,
            pin: $dados["pin"] ?? $usuarioAtual->getPin(),
            nivel_id: $dados["nivelid"] ?? $usuarioAtual->getNivelId(),
            statusid: $dados["statusid"] ?? $usuarioAtual->getStatusId(),
            telefone: $dados["telefone"] ?? $usuarioAtual->getTelefone(),
            cpf: $dados["cpf"] ?? $usuarioAtual->getCpf(),
            criado: $usuarioAtual->getCriado(),
            atualizado: date("Y-m-d H:i:s")
        );

        $ok = UsuarioDao::atualizar((int)$id, $novoUsuario);
        if (!$ok) {
            return self::Mensagemjson("Erro ao atualizar usuário", 500);
        }

        self::success("Usuário ID {$id} atualizado com sucesso.");
        return self::Mensagemjson("Usuário atualizado com sucesso", 200);
    }

    /**
     * Deletar usuário
     */
    public function deletar($id)
    {
        $usuario = UsuarioDao::buscarPorId((int)$id);
        if (!$usuario) {
            self::warning("Usuário ID {$id} não encontrado para deletar.");
            return self::Mensagemjson("Usuário não encontrado", 404);
        }

        $ok = UsuarioDao::deletar((int)$id);
        if (!$ok) {
            return self::Mensagemjson("Erro ao deletar usuário.", 500);
        }

        self::success("Usuário ID {$id} deletado com sucesso.");
        return self::Mensagemjson("Usuário deletado com sucesso!", 200);
    }

    public function listarNiveis()
    {
        echo "UsuarioController::listarNiveis executado (gerado automaticamente)!";
    }
}