<?php

namespace App\Dao\ConfiguracaoDao;

use Config\BaseDao\BaseDao;
use App\Models\Login\TipoLogin;

class LoginTipoDao extends BaseDao
{
    protected static string $tabela = "tipo_login";

    private static function mapear(array $row): TipoLogin
    {
        return new TipoLogin(
            intval($row["id_tipo"]),
            $row["nome"],
            $row["descricao"] ?? null,
            $row["criado"] ?? ""
        );
    }

    public static function listar(): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " ORDER BY id_tipo ASC";
        $rows = self::findAll($sql);
        return array_map(fn($row) => self::mapear($row), $rows);
    }

    public static function buscarPorId(int $id): ?TipoLogin
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_tipo = ?";
        $row = self::find($sql, [$id]);
        return $row ? self::mapear($row) : null;
    }

    public static function criar(TipoLogin $tipo): bool
    {
        $sql = "INSERT INTO tipo_login (nome, descricao, criado) VALUES (?, ?, ?)";
        return self::execute($sql, [
            $tipo->getNome(),
            $tipo->getDescricao(),
            $tipo->getCriado()
        ]);
    }

    public static function atualizar(int $id, TipoLogin $tipo): bool
    {
        $sql = "UPDATE tipo_login SET nome = ?, descricao = ? WHERE id_tipo = ?";
        return self::execute($sql, [
            $tipo->getNome(),
            $tipo->getDescricao(),
            $id
        ]);
    }

    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM tipo_login WHERE id_tipo = ?";
        return self::execute($sql, [$id]);
    }
}
