<?php

namespace App\Dao\Menu;

use Config\BaseDao\BaseDao;
use App\Models\Menu\MenuItemModel;

class MenuItemDao extends BaseDao
{
    protected static string $tabela = "menu_item";

    // ==================================================
    // LISTAR TODOS OS ITENS DE MENU
    // ==================================================
    public static function listar(): array
    {
        self::info("Buscando todos os itens de menu...");

        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            ORDER BY posicao ASC
        ";

        $rows = self::findAll($sql);

        self::success("Itens encontrados: " . count($rows));

        return array_map(
            fn($row) => new MenuItemModel(
                $row["id_item"],
                $row["menu_id"],
                $row["nome"],
                $row["rota"],
                $row["icone"],
                $row["posicao"]
            ),
            $rows
        );
    }

    // ==================================================
    // BUSCAR ITEM DE MENU POR ID
    // ==================================================
    public static function buscarPorId(int $id): ?MenuItemModel
    {
        self::info("Buscando item de menu ID: {$id}");

        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            WHERE id_item = ?
        ";

        $row = self::find($sql, [$id]);

        if (!$row) {
            self::warning("Item de menu não encontrado.");
            return null;
        }

        self::success("Item de menu encontrado.");

        return new MenuItemModel(
            $row["id_item"],
            $row["menu_id"],
            $row["nome"],
            $row["rota"],
            $row["icone"],
            $row["posicao"]
        );
    }

    // ==================================================
    // CRIAR NOVO ITEM DE MENU
    // ==================================================
    public static function criar(MenuItemModel $item): bool
    {
        self::info("Criando novo item de menu: " . $item->getNome());

        $sql = "
            INSERT INTO " . self::$tabela . "
            (menu_id, nome, rota, icone, posicao)
            VALUES (?, ?, ?, ?, ?)
        ";

        $ok = self::execute($sql, [
            $item->getMenuId(),
            $item->getNome(),
            $item->getRota(),
            $item->getIcone(),
            $item->getPosicao()
        ]);

        if ($ok) {
            self::success("Item de menu criado com sucesso!");
        } else {
            self::error("Erro ao criar item de menu.");
        }

        return $ok;
    }

    // ==================================================
    // ATUALIZAR ITEM DE MENU
    // ==================================================
    public static function atualizar(int $id, MenuItemModel $item): bool
    {
        self::info("Atualizando item de menu ID: {$id}");

        $sql = "
            UPDATE " . self::$tabela . "
            SET
                menu_id = ?,
                nome = ?,
                rota = ?,
                icone = ?,
                posicao = ?
            WHERE id_item = ?
        ";

        $ok = self::execute($sql, [
            $item->getMenuId(),
            $item->getNome(),
            $item->getRota(),
            $item->getIcone(),
            $item->getPosicao(),
            $id
        ]);

        if ($ok) {
            self::success("Item de menu atualizado com sucesso!");
        } else {
            self::error("Erro ao atualizar item de menu.");
        }

        return $ok;
    }

    // ==================================================
    // DELETAR ITEM DE MENU
    // ==================================================
    public static function deletar(int $id): bool
    {
        self::warning("Deletando item de menu ID: {$id}");

        $sql = "
            DELETE FROM " . self::$tabela . "
            WHERE id_item = ?
        ";

        $ok = self::execute($sql, [$id]);

        if ($ok) {
            self::success("Item de menu deletado.");
        } else {
            self::error("Erro ao deletar item de menu.");
        }

        return $ok;
    }

    // ==================================================
    // LISTAR ITENS POR MENU (SUBMENUS / DROPDOWN)
    // ==================================================
    public static function listarPorMenu(int $menuId): array
    {
        self::info("Buscando itens do menu ID: {$menuId}");

        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            WHERE menu_id = ?
            ORDER BY posicao ASC
        ";

        $rows = self::findAll($sql, [$menuId]);

        self::success("Itens do menu encontrados: " . count($rows));

        return array_map(
            fn($row) => new MenuItemModel(
                $row["id_item"],
                $row["menu_id"],
                $row["nome"],
                $row["rota"],
                $row["icone"],
                $row["posicao"]
            ),
            $rows
        );
    }

    // ==================================================
    // LISTAR ITENS POR NÍVEL (PERMISSÕES)
    // ==================================================
    public static function listarPorNivel(int $nivelId): array
    {
        self::info("Buscando itens de menu para nível ID: {$nivelId}");

        $sql = "
            SELECT mi.*
            FROM menu_item mi
            INNER JOIN menu_permissao mp
                ON mp.menu_item_id = mi.id_item
            WHERE mp.nivel_id = ?
            ORDER BY mi.posicao ASC
        ";

        $rows = self::findAll($sql, [$nivelId]);

        self::success("Itens permitidos encontrados: " . count($rows));

        return array_map(
            fn($row) => new MenuItemModel(
                $row["id_item"],
                $row["menu_id"],
                $row["nome"],
                $row["rota"],
                $row["icone"],
                $row["posicao"]
            ),
            $rows
        );
    }
}
