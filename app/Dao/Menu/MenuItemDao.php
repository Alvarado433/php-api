<?php

namespace App\Dao\Menu;

use Config\BaseDao\BaseDao;
use App\Models\Menu\MenuItemModel;

class MenuItemDao extends BaseDao
{
    protected static string $tabela = "menu_item";

    /**
     * Listar todos os itens de menu
     */
    public static function listar(): array
    {
        self::info("Buscando todos os itens de menu...");

        $sql = "SELECT * FROM " . self::$tabela . " ORDER BY posicao ASC";
        $rows = self::findAll($sql);

        return array_map(
            fn($row) => new MenuItemModel(
                $row["nome"],
                $row["icone"],
                $row["rota"],
                $row["posicao"],
                $row["menu_id"]
            ),
            $rows
        );
    }

    /**
     * Buscar item de menu por ID
     */
    public static function buscarPorId(int $id): ?MenuItemModel
    {
        self::info("Buscando item de menu ID: $id");

        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_item = ?";
        $row = self::find($sql, [$id]);

        if (!$row) {
            self::warning("Item de menu ID $id não encontrado.");
            return null;
        }

        return new MenuItemModel(
            $row["nome"],
            $row["icone"],
            $row["rota"],
            $row["posicao"],
            $row["menu_id"]
        );
    }

    /**
     * Criar novo item de menu
     */
    public static function criar(MenuItemModel $item): bool
    {
        self::info("Criando novo item de menu: " . $item->getNome());

        $sql = "
            INSERT INTO " . self::$tabela . "
            (menu_id, nome, rota, icone, posicao)
            VALUES (?, ?, ?, ?, ?)
        ";

        return self::execute($sql, [
            $item->getMenuId(),
            $item->getNome(),
            $item->getRota(),
            $item->getIcone(),
            $item->getPosicao()
        ]);
    }

    /**
     * Atualizar item de menu
     */
    public static function atualizar(int $id, MenuItemModel $item): bool
    {
        self::info("Atualizando item de menu ID: $id");

        $sql = "
            UPDATE " . self::$tabela . "
            SET menu_id = ?, nome = ?, rota = ?, icone = ?, posicao = ?
            WHERE id_item = ?
        ";

        return self::execute($sql, [
            $item->getMenuId(),
            $item->getNome(),
            $item->getRota(),
            $item->getIcone(),
            $item->getPosicao(),
            $id
        ]);
    }

    /**
     * Deletar item de menu
     */
    public static function deletar(int $id): bool
    {
        self::warning("Deletando item de menu ID: $id");

        $sql = "DELETE FROM " . self::$tabela . " WHERE id_item = ?";
        return self::execute($sql, [$id]);
    }

    /**
     * Listar itens por menu (ex: dropdown)
     */
    public static function listarPorMenu(int $menuId): array
    {
        self::info("Buscando itens do menu ID: $menuId");

        $sql = "SELECT * FROM " . self::$tabela . " WHERE menu_id = ? ORDER BY posicao ASC";
        $rows = self::findAll($sql, [$menuId]);

        return array_map(
            fn($row) => new MenuItemModel(
                $row["nome"],
                $row["icone"],
                $row["rota"],
                $row["posicao"],
                $row["menu_id"]
            ),
            $rows
        );
    }
     public static function listarPorNivel(int $nivelId): array
    {
        self::info("Buscando itens de menu permitidos para nivel_id: $nivelId");

        $sql = "
            SELECT mi.*
            FROM menu_item mi
            INNER JOIN menu_permissao mp ON mp.menu_item_id = mi.id_item
            WHERE mp.nivel_id = ?
            ORDER BY mi.posicao ASC
        ";

        $rows = self::findAll($sql, [$nivelId]);

        return array_map(
            fn($row) => new MenuItemModel(
                $row["nome"],
                $row["icone"],
                $row["rota"],
                $row["posicao"],
                $row["menu_id"]
            ),
            $rows
        );
    }
}
