<?php

namespace Imperio\Controllers;

use App\Dao\Menu\MenuDao;
use App\Models\Menu\MenuModel;
use Config\Base\Basecontrolador;

class Menu extends Basecontrolador
{
    /**
     * Retorna menus ativos
     */
    public function ativos()
    {
        try {
            self::info("Iniciando listagem de menus ativos...");

            $menus = MenuDao::listarAtivos();

            if (empty($menus)) {
                self::warning("Nenhum menu ativo encontrado.");
                return self::Mensagemjson("Nenhum menu ativo encontrado", 404);
            }

            self::success("Menus ativos carregados: " . count($menus));
            $lista = array_map(fn($m) => $m->toArray(), $menus);

            return self::Mensagemjson("Menus ativos", 200, $lista);

        } catch (\Throwable $th) {
            self::error("Erro ao listar menus ativos: " . $th->getMessage());
            return self::Mensagemjson(
                "Erro ao listar menus ativos",
                500
            );
        }
    }

    /**
     * Criar menu
     */
    public function criar()
    {
        $dados = self::receberJson();

        // VALIDAÇÃO
        if (empty($dados["nome"]) || empty($dados["icone"]) || empty($dados["rota"])) {
            self::warning("Campos obrigatórios ausentes para criação do menu.");
            return self::Mensagemjson("Campos obrigatórios: nome, icone, rota.", 400);
        }

        $pesquisaPlaceholder = $dados["pesquisa_placeholder"] ?? null;

        $menu = new MenuModel(
            $dados["nome"],
            $dados["icone"],
            $dados["rota"],
            $pesquisaPlaceholder
        );

        self::info("Tentando criar menu: " . $menu->getNome());
        $ok = MenuDao::criar($menu);

        if (!$ok) {
            self::error("Erro ao criar menu: " . $menu->getNome());
            return self::Mensagemjson("Erro ao salvar menu.", 500);
        }

        self::success("Menu criado com sucesso: " . $menu->getNome());
        return self::Mensagemjson("Menu criado com sucesso!", 201, $menu->toArray());
    }

    /**
     * Listar todos os menus
     */
    public function listar()
    {
        try {
            self::info("Iniciando listagem de todos os menus...");

            $menus = MenuDao::listar();

            if (empty($menus)) {
                self::warning("Nenhum menu encontrado.");
                return self::Mensagemjson("Nenhum menu encontrado", 404);
            }

            self::success("Menus carregados: " . count($menus));
            $lista = array_map(fn($m) => $m->toArray(), $menus);

            return self::Mensagemjson("Menus retornados", 200, $lista);

        } catch (\Throwable $th) {
            self::error("Erro ao listar menus: " . $th->getMessage());
            return self::Mensagemjson(
                "Erro ao listar menus",
                500
            );
        }
    }
}
