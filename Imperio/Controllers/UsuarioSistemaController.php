<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\UsuarioDao\UsuarioDao;
use App\Dao\UsuarioDao\UsuarioSessionDao;
use App\Models\Usuario\UsuarioModel;
use App\Models\Usuario\SessaoModel;

class UsuarioSistemaController extends Basecontrolador
{
    /**
     * GET /usuarios-sistema
     * Listar todos os usuários do sistema
     */
    public function listar()
    {
        try {
            self::info("Listando todos os usuários do sistema...");

            $usuarios = UsuarioDao::listar();

            return self::Mensagemjson("Lista de usuários", 200, [
                "total" => count($usuarios),
                "usuarios" => array_map(fn($u) => $u->toArray(), $usuarios),
            ]);
        } catch (\Throwable $th) {
            self::error("Erro ao listar usuários: " . $th->getMessage());
            return self::Mensagemjson("Erro ao listar usuários", 500);
        }
    }

    /**
     * GET /usuarios-sistema/{id}
     * Buscar usuário por ID
     */
    public function buscar($id)
    {
        try {
            if (!is_numeric($id)) {
                return self::Mensagemjson("ID inválido.", 400);
            }

            $id = (int)$id;

            $usuario = UsuarioDao::buscarPorId($id);
            if (!$usuario) {
                self::warning("Usuário ID {$id} não encontrado.");
                return self::Mensagemjson("Usuário não encontrado", 404);
            }

            self::info("Usuário ID {$id} encontrado.");
            return self::Mensagemjson("Usuário encontrado", 200, $usuario->toArray());
        } catch (\Throwable $th) {
            self::error("Erro ao buscar usuário: " . $th->getMessage());
            return self::Mensagemjson("Erro ao buscar usuário", 500);
        }
    }

    /**
     * POST /usuarios-sistema
     * Criar novo usuário do sistema
     */
    public function criar()
    {
        try {
            $dados = self::receberJson();

            if (empty($dados["nome"]) || empty($dados["email"]) || empty($dados["senha"])) {
                self::warning("Campos obrigatórios ausentes (nome/email/senha).");
                return self::Mensagemjson("Nome, email e senha são obrigatórios.", 400);
            }

            // (Opcional) normalizar email
            $email = strtolower(trim($dados["email"]));
            $nome = trim($dados["nome"]);

            $pin = str_pad((string)random_int(0, 999999), 6, "0", STR_PAD_LEFT);
            $nivelid = isset($dados["nivelid"]) ? (int)$dados["nivelid"] : 1;

            $telefone = $dados["telefone"] ?? null;
            $cpf = $dados["cpf"] ?? null;

            $usuario = new UsuarioModel(
                id_usuario: 0,
                nome: $nome,
                email: $email,
                senha: password_hash($dados["senha"], PASSWORD_DEFAULT),
                pin: $pin,
                nivel_id: $nivelid,
                statusid: 1,
                telefone: $telefone,
                cpf: $cpf,
                criado: date("Y-m-d H:i:s"),
                atualizado: date("Y-m-d H:i:s")
            );

            $idCriado = UsuarioDao::criar($usuario);

            if (!$idCriado) {
                self::error("Erro ao criar usuário no banco.");
                return self::Mensagemjson("Erro ao criar usuário", 500);
            }

            /**
             * ⚠️ ATENÇÃO:
             * Se isso for CADASTRO PÚBLICO, NÃO recomendo criar sessão automática.
             * Mas como seu código antigo fazia, vou manter igual.
             * Se quiser, eu te mando a versão sem sessão automática.
             */
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

            self::success("Usuário '{$email}' criado com sucesso. ID: {$idCriado}");

            return self::Mensagemjson("Usuário criado com sucesso!", 201, [
                "id_usuario" => $idCriado,
                "nome" => $nome,
                "email" => $email,
                "pin" => $pin,
                "nivelid" => $nivelid,
                "telefone" => $telefone,
                "cpf" => $cpf,
                "token_sessao" => $token,
            ]);
        } catch (\Throwable $th) {
            self::error("Erro ao criar usuário: " . $th->getMessage());
            return self::Mensagemjson("Erro ao criar usuário", 500);
        }
    }

    /**
     * PUT /usuarios-sistema/{id}
     * Atualizar usuário
     */
    public function atualizar($id)
    {
        try {
            if (!is_numeric($id)) {
                return self::Mensagemjson("ID inválido.", 400);
            }

            $id = (int)$id;

            $dados = self::receberJson();
            $usuarioAtual = UsuarioDao::buscarPorId($id);

            if (!$usuarioAtual) {
                self::warning("Usuário ID {$id} não encontrado para atualizar.");
                return self::Mensagemjson("Usuário não encontrado", 404);
            }

            // ✅ se vier senha, hash; se não, mantém a atual
            $senhaFinal = $usuarioAtual->getSenha();
            if (!empty($dados["senha"])) {
                $senhaFinal = password_hash($dados["senha"], PASSWORD_DEFAULT);
            }

            $novoUsuario = new UsuarioModel(
                id_usuario: $id,
                nome: isset($dados["nome"]) ? trim($dados["nome"]) : $usuarioAtual->getNome(),
                email: isset($dados["email"]) ? strtolower(trim($dados["email"])) : $usuarioAtual->getEmail(),
                senha: $senhaFinal,
                pin: $dados["pin"] ?? $usuarioAtual->getPin(),
                nivel_id: isset($dados["nivelid"]) ? (int)$dados["nivelid"] : $usuarioAtual->getNivelId(),
                statusid: isset($dados["statusid"]) ? (int)$dados["statusid"] : $usuarioAtual->getStatusId(),
                telefone: $dados["telefone"] ?? $usuarioAtual->getTelefone(),
                cpf: $dados["cpf"] ?? $usuarioAtual->getCpf(),
                criado: $usuarioAtual->getCriado(),
                atualizado: date("Y-m-d H:i:s")
            );

            $ok = UsuarioDao::atualizar($id, $novoUsuario);
            if (!$ok) {
                self::error("Falha ao atualizar usuário ID {$id}.");
                return self::Mensagemjson("Erro ao atualizar usuário", 500);
            }

            self::success("Usuário ID {$id} atualizado com sucesso.");
            return self::Mensagemjson("Usuário atualizado com sucesso", 200, $novoUsuario->toArray());
        } catch (\Throwable $th) {
            self::error("Erro ao atualizar usuário: " . $th->getMessage());
            return self::Mensagemjson("Erro ao atualizar usuário", 500);
        }
    }

    /**
     * DELETE /usuarios-sistema/{id}
     * Deletar usuário
     */
    public function deletar($id)
    {
        try {
            if (!is_numeric($id)) {
                return self::Mensagemjson("ID inválido.", 400);
            }

            $id = (int)$id;

            $usuario = UsuarioDao::buscarPorId($id);
            if (!$usuario) {
                self::warning("Usuário ID {$id} não encontrado para deletar.");
                return self::Mensagemjson("Usuário não encontrado", 404);
            }

            $ok = UsuarioDao::deletar($id);
            if (!$ok) {
                self::error("Erro ao deletar usuário ID {$id}.");
                return self::Mensagemjson("Erro ao deletar usuário.", 500);
            }

            self::success("Usuário ID {$id} deletado com sucesso.");
            return self::Mensagemjson("Usuário deletado com sucesso!", 200, ["id_usuario" => $id]);
        } catch (\Throwable $th) {
            self::error("Erro ao deletar usuário: " . $th->getMessage());
            return self::Mensagemjson("Erro ao deletar usuário", 500);
        }
    }
}