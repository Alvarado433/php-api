<?php

namespace App\Dao\Menu;

use App\Models\Menu\MenuItemConfigModel;
use Config\BaseDao\BaseDao;

class MenuItemConfigDao extends BaseDao
{
    protected static string $tabela = "menu_item_config";

    /**
     * Listar todas as configurações de menu
     */
    public static function listar(): array
    {
        self::info("Buscando todas as configurações de menu...");

        $sql = "SELECT * FROM " . self::$tabela . " ORDER BY posicao ASC";
        $rows = self::findAll($sql);

        self::success("Configurações carregadas: " . count($rows));

        return array_map(fn($row) =>
            new MenuItemConfigModel(
                $row["menu_id"],
                $row["tipo"],
                $row["posicao"]
            ),
        $rows);
    }

    /**
     * Buscar configuração por ID
     */
    public static function buscarPorId(int $id): ?MenuItemConfigModel
    {
        self::info("Buscando configuração menu ID: $id");

        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_config = ?";
        $row = self::find($sql, [$id]);

        if (!$row) {
            self::warning("Configuração ID $id não encontrada.");
            return null;
        }

        self::success("Configuração encontrada.");

        return new MenuItemConfigModel(
            $row["menu_id"],
            $row["tipo"],
            $row["posicao"]
        );
    }

    /**
     * Criar nova configuração de menu
     */
    public static function criar(MenuItemConfigModel $config): bool
    {
        self::info("Criando nova configuração de menu para menu ID: " . $config->getMenuId());

        $sql = "
            INSERT INTO " . self::$tabela . "
            (menu_id, tipo, posicao)
            VALUES (?, ?, ?)
        ";

        $ok = self::execute($sql, [
            $config->getMenuId(),
            $config->getTipo(),
            $config->getPosicao()
        ]);

        if ($ok) self::success("Configuração criada com sucesso!");
        else self::error("Erro ao criar configuração.");

        return $ok;
    }

    /**
     * Atualizar configuração de menu
     */
    public static function atualizar(int $id, MenuItemConfigModel $config): bool
    {
        self::info("Atualizando configuração ID: $id");

        $sql = "
            UPDATE " . self::$tabela . "
            SET menu_id = ?, tipo = ?, posicao = ?
            WHERE id_config = ?
        ";

        $ok = self::execute($sql, [
            $config->getMenuId(),
            $config->getTipo(),
            $config->getPosicao(),
            $id
        ]);

        if ($ok) self::success("Configuração atualizada com sucesso!");
        else self::error("Erro ao atualizar configuração.");

        return $ok;
    }

    /**
     * Deletar configuração de menu
     */
    public static function deletar(int $id): bool
    {
        self::warning("Deletando configuração ID: $id");

        $sql = "DELETE FROM " . self::$tabela . " WHERE id_config = ?";
        $ok = self::execute($sql, [$id]);

        if ($ok) self::success("Configuração deletada.");
        else self::error("Erro ao deletar configuração.");

        return $ok;
    }

    /**
     * Listar configurações de um menu específico
     */
    public static function listarPorMenu(int $menuId): array
    {
        self::info("Buscando configurações para menu ID: $menuId");

        $sql = "SELECT * FROM " . self::$tabela . " WHERE menu_id = ? ORDER BY posicao ASC";
        $rows = self::findAll($sql, [$menuId]);

        self::success("Configurações retornadas: " . count($rows));

        return array_map(fn($row) =>
            new MenuItemConfigModel(
                $row["menu_id"],
                $row["tipo"],
                $row["posicao"]
            ),
        $rows);
    }
}
