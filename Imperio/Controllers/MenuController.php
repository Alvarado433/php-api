<?php

namespace App\Controllers;

use Config\Base\Basecontrolador;
use App\Dao\Menu\MenuItemDao;
use App\Models\Menu\MenuItemModel;

class MenuItemController extends Basecontrolador
{
    /**
     * Listar todos os itens de menu
     */
    public static function listar(): void
    {
        try {
            $itens = MenuItemDao::listar();
            self::Mensagemjson(
                "Itens de menu carregados com sucesso",
                200,
                array_map(fn($item) => $item->toArray(), $itens)
            );
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar itens: " . $th->getMessage(), 500);
        }
    }

    /**
     * Buscar item de menu por ID
     */
    public static function buscar(int $id): void
    {
        try {
            $item = MenuItemDao::buscarPorId($id);

            if (!$item) {
                self::Mensagemjson("Item de menu não encontrado", 404);
                return;
            }

            self::Mensagemjson("Item de menu encontrado", 200, $item->toArray());

        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao buscar item: " . $th->getMessage(), 500);
        }
    }

    /**
     * Criar novo item de menu
     */
    public static function criar(): void
    {
        try {
            $dados = self::receberJson();

            $item = new MenuItemModel(
                $dados['nome'] ?? '',
                $dados['icone'] ?? null,
                $dados['rota'] ?? null,
                $dados['posicao'] ?? 0,
                $dados['menu_id'] ?? null
            );

            $ok = MenuItemDao::criar($item);

            if ($ok) {
                self::Mensagemjson("Item de menu criado com sucesso", 201, $item->toArray());
            } else {
                self::Mensagemjson("Erro ao criar item de menu", 500);
            }

        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao criar item: " . $th->getMessage(), 500);
        }
    }

    /**
     * Atualizar item de menu
     */
    public static function atualizar(int $id): void
    {
        try {
            $dados = self::receberJson();

            $item = new MenuItemModel(
                $dados['nome'] ?? '',
                $dados['icone'] ?? null,
                $dados['rota'] ?? null,
                $dados['posicao'] ?? 0,
                $dados['menu_id'] ?? null
            );

            $ok = MenuItemDao::atualizar($id, $item);

            if ($ok) {
                self::Mensagemjson("Item de menu atualizado com sucesso", 200, $item->toArray());
            } else {
                self::Mensagemjson("Erro ao atualizar item de menu", 500);
            }

        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao atualizar item: " . $th->getMessage(), 500);
        }
    }

    /**
     * Deletar item de menu
     */
    public static function deletar(int $id): void
    {
        try {
            $ok = MenuItemDao::deletar($id);

            if ($ok) {
                self::Mensagemjson("Item de menu deletado com sucesso", 200);
            } else {
                self::Mensagemjson("Erro ao deletar item de menu", 500);
            }

        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao deletar item: " . $th->getMessage(), 500);
        }
    }

    /**
     * Listar itens por menu
     */
    public static function listarPorMenu(int $menuId): void
    {
        try {
            $itens = MenuItemDao::listarPorMenu($menuId);
            self::Mensagemjson(
                "Itens do menu carregados com sucesso",
                200,
                array_map(fn($item) => $item->toArray(), $itens)
            );
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar itens do menu: " . $th->getMessage(), 500);
        }
    }

    
}