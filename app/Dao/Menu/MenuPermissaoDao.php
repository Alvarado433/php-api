<?php

namespace App\Dao\Menu;

use Config\BaseDao\BaseDao;
use App\Models\Menu\MenuPermissao;

class MenuPermissaoDao extends BaseDao
{
    protected static string $tabela = "menu_permissao";

    /**
     * Listar permissões por nível
     */
    public static function listarPorNivel(int $nivelId): array
    {
        self::info("MenuPermissao: listando permissões do nível $nivelId");

        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            WHERE nivel_id = ?
        ";

        $rows = self::findAll($sql, [$nivelId]);

        self::success("Permissões encontradas: " . count($rows));

        return array_map(
            fn($row) => new MenuPermissao($row),
            $rows
        );
    }

    /**
     * Criar permissão
     */
    public static function criar(
        int $nivelId,
        ?int $menuId = null,
        ?int $menuItemId = null
    ): bool {
        self::info("Criando permissão para nível $nivelId");

        $sql = "
            INSERT INTO " . self::$tabela . "
            (nivel_id, menu_id, menu_item_id)
            VALUES (?, ?, ?)
        ";

        $ok = self::execute($sql, [
            $nivelId,
            $menuId,
            $menuItemId
        ]);

        if ($ok) {
            self::success("Permissão criada com sucesso");
        } else {
            self::error("Erro ao criar permissão");
        }

        return $ok;
    }

    /**
     * Remover permissões por nível
     */
    public static function removerPorNivel(int $nivelId): bool
    {
        self::warning("Removendo permissões do nível $nivelId");

        $sql = "
            DELETE FROM " . self::$tabela . "
            WHERE nivel_id = ?
        ";

        $ok = self::execute($sql, [$nivelId]);

        if ($ok) {
            self::success("Permissões removidas");
        } else {
            self::error("Erro ao remover permissões");
        }

        return $ok;
    }
}
