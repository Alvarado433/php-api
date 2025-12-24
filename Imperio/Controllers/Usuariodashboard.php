<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\UsuarioDao\UsuarioDao;
use App\Dao\NivelDao\NivelDAO;
use App\Models\Usuario\UsuarioModel;

class Usuariodashboard extends Basecontrolador
{
    // 🌐 LISTAR USUÁRIOS
    public function listar(): void
    {
        $usuarios = UsuarioDao::listar();

        $dados = array_map(fn($u) => [
            "id_usuario" => $u->getId(),
            "nome"       => $u->getNome(),
            "email"      => $u->getEmail(),
            "pin"        => $u->getPin(),
            "nivel_id"   => $u->getNivelId(),
            "statusid"   => $u->getStatusId(),
            "telefone"   => $u->getTelefone(),
            "cpf"        => $u->getCpf(),
            "criado"     => $u->getCriado(),
            "atualizado" => $u->getAtualizado(),
        ], $usuarios);

        self::Mensagemjson("Usuários listados com sucesso", 200, $dados);
    }

    // 🌐 LISTAR NÍVEIS
    // Em Usuariodashboard.php
    public function listarNiveis(): void
    {
        // Importa o DAO de níveis
        $niveis = \App\Dao\NivelDao\NivelDAO::listar();

        $dados = array_map(fn($n) => [
            "id_nivel" => $n->getId(),
            "nome" => $n->getNome()
        ], $niveis);

        self::Mensagemjson("Níveis listados com sucesso", 200, $dados);
    }

    // 🌐 CRIAR USUÁRIO
    public function criar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            self::Mensagemjson("Dados inválidos", 400);
            return;
        }

        // gera PIN aleatório de 6 dígitos
        $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $usuario = new UsuarioModel(
            0,
            $input['nome'] ?? '',
            $input['email'] ?? '',
            password_hash($input['senha'] ?? '', PASSWORD_DEFAULT),
            $pin, // PIN automático
            intval($input['nivel_id'] ?? 2),
            intval($input['statusid'] ?? 1),
            $input['telefone'] ?? null,
            $input['cpf'] ?? null,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        );

        $id = UsuarioDao::criar($usuario);

        if ($id) {
            self::Mensagemjson("Usuário criado com sucesso", 201, [
                'id_usuario' => $id,
                'pin' => $pin // retorna também o PIN
            ]);
        } else {
            self::Mensagemjson("Erro ao criar usuário", 500);
        }
    }
}
