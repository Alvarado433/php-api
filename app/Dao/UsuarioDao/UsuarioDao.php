<?php

namespace App\Dao\UsuarioDao;

use Config\BaseDao\BaseDao;
use App\Models\Usuario\UsuarioModel;

class UsuarioDao extends BaseDao
{
    protected static string $tabela = "usuario";

    public static function listar(): array
    {
        $sql = "SELECT * FROM " . self::$tabela;
        $rows = self::findAll($sql);
        return array_map(fn($row) => self::mapear($row), $rows);
    }

    public static function buscarPorId(int $id): ?UsuarioModel
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_usuario = ?";
        $row = self::find($sql, [$id]);
        return $row ? self::mapear($row) : null;
    }

    public static function criar(UsuarioModel $usuario): ?int
    {
        $sql = "
            INSERT INTO " . self::$tabela . "
            (nome, email, senha, pin, nivel_id, statusid, telefone, cpf)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $ok = self::execute($sql, [
            $usuario->getNome(),
            $usuario->getEmail(),
            $usuario->getSenha(),
            $usuario->getPin(),
            $usuario->getNivelId(),
            $usuario->getStatusId(),
            $usuario->getTelefone(),
            $usuario->getCpf()
        ]);

        if ($ok) {
            self::info("Usuário '{$usuario->getEmail()}' criado com sucesso.");
            return self::lastInsertId();
        }

        self::error("Falha ao criar usuário '{$usuario->getEmail()}'.");
        return null;
    }

    public static function atualizar(int $id, UsuarioModel $usuario): bool
    {
        $sql = "
            UPDATE " . self::$tabela . "
            SET nome = ?, email = ?, senha = ?, pin = ?, nivel_id = ?, statusid = ?, telefone = ?, cpf = ?
            WHERE id_usuario = ?
        ";

        $ok = self::execute($sql, [
            $usuario->getNome(),
            $usuario->getEmail(),
            $usuario->getSenha(),
            $usuario->getPin(),
            $usuario->getNivelId(),
            $usuario->getStatusId(),
            $usuario->getTelefone(),
            $usuario->getCpf(),
            $id
        ]);

        if ($ok) self::info("Usuário ID {$id} atualizado com sucesso.");
        else self::error("Falha ao atualizar usuário ID {$id}.");

        return $ok;
    }

    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_usuario = ?";
        $ok = self::execute($sql, [$id]);
        if ($ok) self::info("Usuário ID {$id} deletado com sucesso.");
        else self::error("Falha ao deletar usuário ID {$id}.");
        return $ok;
    }

    public static function contar(): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . self::$tabela;
        $row = self::find($sql);
        return $row ? intval($row['total']) : 0;
    }

    private static function mapear(array $row): UsuarioModel
    {
        return new UsuarioModel(
            intval($row["id_usuario"]),
            $row["nome"],
            $row["email"],
            $row["senha"],
            $row["pin"],
            intval($row["nivel_id"]),
            intval($row["statusid"]),
            $row["telefone"] ?? null,
            $row["cpf"] ?? null,
            $row["criado"],
            $row["atualizado"]
        );
    }

    public static function buscarPorEmailOuNome(string $valor): ?UsuarioModel
    {
        $sql = "
        SELECT * FROM usuario 
        WHERE email = ? OR nome = ?
        LIMIT 1
    ";

        $row = self::find($sql, [$valor, $valor]);
        return $row ? self::mapear($row) : null;
    }
}
