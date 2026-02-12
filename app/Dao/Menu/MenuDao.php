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

    public static function listarComItens(): array
    {
        self::info("Buscando menus com itens (JOIN)");

        $sql = "
            SELECT 
                m.id_menu,
                m.nome AS menu_nome,
                m.icone AS menu_icone,
                m.rota AS menu_rota,
                m.pesquisa_placeholder,

                mi.id_item,
                mi.nome AS item_nome,
                mi.icone AS item_icone,
                mi.rota AS item_rota,
                mi.posicao
            FROM menu m
            LEFT JOIN menu_item mi ON mi.menu_id = m.id_menu
            ORDER BY m.id_menu, mi.posicao ASC
        ";

        return self::findAll($sql);
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
}
