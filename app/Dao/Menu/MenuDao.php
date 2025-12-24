<?php

namespace App\Dao\Menu;

use Config\BaseDao\BaseDao;
use App\Models\Menu\MenuModel;

class MenuDao extends BaseDao
{
    protected static string $tabela = "menu";

    /**
     * Retorna todos os menus
     */
    public static function listar(): array
    {
        self::info("Buscando todos os menus...");

        $sql = "SELECT * FROM " . self::$tabela;
        $rows = self::findAll($sql);

        self::success("Menus carregados: " . count($rows));

        return array_map(
            fn($row) =>
            new MenuModel(
                $row["nome"],
                $row["icone"],
                $row["rota"],
                $row["pesquisa_placeholder"]
            ),
            $rows
        );
    }

    /**
     * Buscar menu por ID
     */
    public static function buscarPorId(int $id): ?MenuModel
    {
        self::info("Buscando menu ID: $id");

        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_menu = ?";
        $row = self::find($sql, [$id]);

        if (!$row) {
            self::warning("Menu ID $id não encontrado.");
            return null;
        }

        self::success("Menu encontrado.");

        return new MenuModel(
            $row["nome"],
            $row["icone"],
            $row["rota"],
            $row["pesquisa_placeholder"]
        );
    }

    /**
     * Criar menu
     */
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

        if ($ok) {
            self::success("Menu criado com sucesso!");
        } else {
            self::error("Erro ao criar menu.");
        }

        return $ok;
    }

    /**
     * Atualizar menu
     */
    public static function atualizar(int $id, MenuModel $menu): bool
    {
        self::info("Atualizando menu ID: $id");

        $sql = "
            UPDATE " . self::$tabela . "
            SET nome = ?, icone = ?, rota = ?, pesquisa_placeholder = ?
            WHERE id_menu = ?
        ";

        $ok = self::execute($sql, [
            $menu->getNome(),
            $menu->getIcone(),
            $menu->getRota(),
            $menu->getPesquisaPlaceholder(),
            $id
        ]);

        if ($ok) {
            self::success("Menu atualizado com sucesso!");
        } else {
            self::error("Erro ao atualizar menu.");
        }

        return $ok;
    }

    /**
     * Deletar menu
     */
    public static function deletar(int $id): bool
    {
        self::warning("Deletando menu ID: $id");

        $sql = "DELETE FROM " . self::$tabela . " WHERE id_menu = ?";
        $ok = self::execute($sql, [$id]);

        if ($ok) self::success("Menu deletado.");
        else self::error("Erro ao deletar menu.");

        return $ok;
    }

    /**
     * Listar menus ativos (vinculados ao INICIO)
     */
    public static function listarAtivos(): array
    {
        self::info("Buscando menus ATIVOS...");

        $sql = "SELECT * FROM " . self::$tabela; // Retorna todos
        $rows = self::findAll($sql);

        self::success("Menus ativos retornados: " . count($rows));

        return array_map(
            fn($row) =>
            new MenuModel(
                $row["nome"],
                $row["icone"],
                $row["rota"],
                $row["pesquisa_placeholder"]
            ),
            $rows
        );
    }
}
