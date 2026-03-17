<?php

namespace App\Dao\Menu;

use Config\BaseDao\BaseDao;
use App\Models\Menu\MenuModel;

class MenuDao extends BaseDao
{
    protected static string $tabela = "menu";

    // ==================================================
    // LISTAR TODOS OS MENUS
    // ==================================================
    public static function listar(): array
    {
        self::info("Buscando todos os menus...");

        $sql = "
        SELECT *
        FROM " . self::$tabela . "
        ORDER BY id_menu ASC
    ";

        $rows = self::findAll($sql);

        self::success("Menus carregados: " . count($rows));

        return array_map(function ($row) {

            $itens = self::listarItensPorMenu($row["id_menu"]);

            return [
                "id" => (int) $row["id_menu"],
                "nome" => $row["nome"],
                "icone" => $row["icone"],
                "rota" => $row["rota"],
                "pesquisa_placeholder" => $row["pesquisa_placeholder"],
                "itens" => array_map(function ($item) {
                    return [
                        "id" => (int) $item["id_item"],
                        "nome" => $item["nome"],
                        "rota" => $item["rota"],
                        "icone" => $item["icone"],
                        "posicao" => (int) $item["posicao"],
                    ];
                }, $itens)
            ];
        }, $rows);
    }
    public static function listarItensPorMenu(int $menuId): array
    {
        $sql = "
        SELECT *
        FROM menu_item
        WHERE menu_id = ?
        ORDER BY posicao ASC
    ";

        return self::findAll($sql, [$menuId]);
    }

    // ==================================================
    // BUSCAR MENU POR ID
    // ==================================================
    public static function buscarPorId(int $id): ?MenuModel
    {
        self::info("Buscando menu ID: {$id}");

        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            WHERE id_menu = ?
        ";

        $row = self::find($sql, [$id]);

        if (!$row) {
            self::warning("Menu não encontrado.");
            return null;
        }

        return new MenuModel(
            $row["id_menu"],
            $row["nome"],
            $row["icone"],
            $row["rota"],
            $row["pesquisa_placeholder"]
        );
    }

    // ==================================================
    // CRIAR MENU
    // ==================================================
    public static function criar(MenuModel $menu): bool
    {
        self::info("Criando novo menu: " . $menu->getNome());

        $sql = "
            INSERT INTO " . self::$tabela . "
            (nome, icone, rota, pesquisa_placeholder)
            VALUES (?, ?, ?, ?)
        ";

        $ok = self::execute($sql, [
            $menu->getNome(),
            $menu->getIcone(),
            $menu->getRota(),
            $menu->getPesquisaPlaceholder()
        ]);

        if ($ok) self::success("Menu criado com sucesso!");
        else self::error("Erro ao criar menu.");

        return $ok;
    }

    // ==================================================
    // ATUALIZAR MENU
    // ==================================================
    public static function atualizar(int $id, MenuModel $menu): bool
    {
        self::info("Atualizando menu ID: {$id}");

        $sql = "
            UPDATE " . self::$tabela . "
            SET
                nome = ?,
                icone = ?,
                rota = ?,
                pesquisa_placeholder = ?
            WHERE id_menu = ?
        ";

        $ok = self::execute($sql, [
            $menu->getNome(),
            $menu->getIcone(),
            $menu->getRota(),
            $menu->getPesquisaPlaceholder(),
            $id
        ]);

        if ($ok) self::success("Menu atualizado com sucesso!");
        else self::error("Erro ao atualizar menu.");

        return $ok;
    }

    // ==================================================
    // DELETAR MENU
    // ==================================================
    public static function deletar(int $id): bool
    {
        self::warning("Deletando menu ID: {$id}");

        $sql = "
            DELETE FROM " . self::$tabela . "
            WHERE id_menu = ?
        ";

        return self::execute($sql, [$id]);
    }

    // ==================================================
    // LISTAR MENUS ATIVOS (NAVBAR)
    // ==================================================
    public static function listarAtivos(): array
    {
        self::info("Buscando menus ativos...");

        // Caso futuramente tenha status, já está preparado
        $sql = "
            SELECT *
            FROM " . self::$tabela . "
            ORDER BY id_menu ASC
        ";

        $rows = self::findAll($sql);

        return array_map(
            fn($row) => new MenuModel(
                $row["id_menu"],
                $row["nome"],
                $row["icone"],
                $row["rota"],
                $row["pesquisa_placeholder"]
            ),
            $rows
        );
    }
    public static function listarPermissoes(?int $menuId = null, ?int $itemId = null): array
    {
        $sql = "SELECT * FROM menu_permissao WHERE 1=1";
        $params = [];

        if ($menuId !== null) {
            $sql .= " AND menu_id = ?";
            $params[] = $menuId;
        }

        if ($itemId !== null) {
            $sql .= " AND menu_item_id = ?";
            $params[] = $itemId;
        }

        $rows = self::findAll($sql, $params);

        return array_map(fn($row) => [
            "nivel_id" => (int) $row["nivel_id"],
            "id_permissao" => (int) $row["id_permissao"]
        ], $rows);
    }
}
