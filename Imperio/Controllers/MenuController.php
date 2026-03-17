<?php

namespace Imperio\Controllers;

use App\Dao\Menu\MenuDao;
use App\Dao\Menu\MenuItemDao;
use App\Models\Menu\MenuModel;
use App\Models\Menu\MenuItemModel;
use Config\Base\Basecontrolador;

class MenuController extends Basecontrolador
{
    /**
     * Criar menu
     * POST /menu/criar
     */
    public function create()
    {
        try {
            $dados = self::receberJson();

            if (empty($dados["nome"]) || empty($dados["icone"]) || empty($dados["rota"])) {
                self::warning("Campos obrigatórios ausentes para criação do menu.");
                return self::Mensagemjson("Campos obrigatórios: nome, icone, rota.", 400);
            }

            $pesquisaPlaceholder = $dados["pesquisa_placeholder"] ?? null;

            $menu = new MenuModel(
                null,
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
        } catch (\Throwable $th) {
            self::error("Erro ao criar menu: " . $th->getMessage());
            return self::Mensagemjson("Erro ao criar menu", 500);
        }
    }

    /**
     * Buscar menu por ID
     * GET /menu/{id}
     */
    public function buscar(int $id)
    {
        try {
            self::info("Buscando menu por ID: {$id}");

            $menu = MenuDao::buscarPorId($id);
            if (!$menu) {
                self::warning("Menu não encontrado: {$id}");
                return self::Mensagemjson("Menu não encontrado.", 404);
            }

            return self::Mensagemjson("Menu encontrado.", 200, $menu->toArray());
        } catch (\Throwable $th) {
            self::error("Erro ao buscar menu: " . $th->getMessage());
            return self::Mensagemjson("Erro ao buscar menu", 500);
        }
    }

    /**
     * Atualizar menu
     * PUT /menu/{id}
     */
    public function update(int $id)
    {
        try {
            $dados = self::receberJson();

            if (empty($dados["nome"]) || empty($dados["icone"]) || empty($dados["rota"])) {
                self::warning("Campos obrigatórios ausentes para atualizar menu.");
                return self::Mensagemjson("Campos obrigatórios: nome, icone, rota.", 400);
            }

            $pesquisaPlaceholder = $dados["pesquisa_placeholder"] ?? null;

            $menu = new MenuModel(
                $id,
                $dados["nome"],
                $dados["icone"],
                $dados["rota"],
                $pesquisaPlaceholder
            );

            self::info("Atualizando menu ID: {$id}");
            $ok = MenuDao::atualizar($id, $menu);

            if (!$ok) {
                self::error("Erro ao atualizar menu ID: {$id}");
                return self::Mensagemjson("Erro ao atualizar menu.", 500);
            }

            self::success("Menu atualizado com sucesso ID: {$id}");
            return self::Mensagemjson("Menu atualizado com sucesso!", 200, $menu->toArray());
        } catch (\Throwable $th) {
            self::error("Erro ao atualizar menu: " . $th->getMessage());
            return self::Mensagemjson("Erro ao atualizar menu", 500);
        }
    }

    /**
     * Deletar menu
     * DELETE /menu/{id}
     */
    public function delete(int $id)
    {
        try {
            self::warning("Tentando deletar menu ID: {$id}");

            $ok = MenuDao::deletar($id);
            if (!$ok) {
                self::error("Erro ao deletar menu ID: {$id}");
                return self::Mensagemjson("Erro ao deletar menu.", 500);
            }

            self::success("Menu deletado com sucesso ID: {$id}");
            return self::Mensagemjson("Menu deletado com sucesso!", 200, ["id_menu" => $id]);
        } catch (\Throwable $th) {
            self::error("Erro ao deletar menu: " . $th->getMessage());
            return self::Mensagemjson("Erro ao deletar menu", 500);
        }
    }

    /**
     * Menus ativos em formato "cards" (sem mudar o Model)
     * GET /menu/ativos
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

            $cards = array_map(function ($m) {
                $data = $m->toArray();
                return [
                    "id" => $data["id_menu"],
                    "titulo" => $data["nome"],
                    "icone" => $data["icone"],
                    "rota" => $data["rota"],
                    "pesquisa_placeholder" => $data["pesquisa_placeholder"]
                ];
            }, $menus);

            return self::Mensagemjson("Menus ativos", 200, ["cards" => $cards]);
        } catch (\Throwable $th) {
            self::error("Erro ao listar menus ativos: " . $th->getMessage());
            return self::Mensagemjson("Erro ao listar menus ativos", 500);
        }
    }

    /**
     * Menus com itens (JOIN cru)
     * GET /menu/com-itens
     */
    public function listarComItens()
    {
        try {
            self::info("Listando menus com itens (JOIN) ...");

            $rows = MenuDao::listarComItens();
            if (empty($rows)) {
                return self::Mensagemjson("Nenhum menu/item encontrado.", 404);
            }

            return self::Mensagemjson("Menus com itens", 200, $rows);
        } catch (\Throwable $th) {
            self::error("Erro ao listar menus com itens: " . $th->getMessage());
            return self::Mensagemjson("Erro ao listar menus com itens", 500);
        }
    }

    /**
     * Listar itens de um menu
     * GET /menu/{id}/itens
     */
    public function listarItens(int $id)
    {
        try {
            self::info("Listando itens do menu ID: {$id}");

            $itens = MenuItemDao::listarPorMenu($id);
            if (empty($itens)) {
                return self::Mensagemjson("Nenhum item encontrado para este menu.", 404);
            }

            $lista = array_map(fn($i) => $i->toArray(), $itens);
            return self::Mensagemjson("Itens do menu", 200, $lista);
        } catch (\Throwable $th) {
            self::error("Erro ao listar itens do menu: " . $th->getMessage());
            return self::Mensagemjson("Erro ao listar itens", 500);
        }
    }

    /**
     * Criar item de menu
     * POST /menu/{id}/itens
     */
    public function criarItem(int $id)
    {
        try {
            $dados = self::receberJson();

            if (empty($dados["nome"]) || empty($dados["rota"])) {
                return self::Mensagemjson("Campos obrigatórios: nome e rota.", 400);
            }

            $item = new MenuItemModel(
                null,
                $id,
                $dados["nome"],
                $dados["rota"],
                $dados["icone"] ?? null,
                $dados["posicao"] ?? 0
            );

            self::info("Criando item para menu {$id}: " . $dados["nome"]);
            $ok = MenuItemDao::criar($item);

            if (!$ok) {
                return self::Mensagemjson("Erro ao criar item.", 500);
            }

            return self::Mensagemjson("Item criado com sucesso!", 201, $item->toArray());
        } catch (\Throwable $th) {
            self::error("Erro ao criar item de menu: " . $th->getMessage());
            return self::Mensagemjson("Erro ao criar item", 500);
        }
    }

    /**
     * Atualizar item de menu
     * PUT /menu/item/{id}
     */
    public function atualizarItem(int $id)
    {
        try {
            $dados = self::receberJson();

            if (empty($dados["menu_id"]) || empty($dados["nome"]) || empty($dados["rota"])) {
                return self::Mensagemjson("Campos obrigatórios: menu_id, nome e rota.", 400);
            }

            $item = new MenuItemModel(
                $id,
                (int)$dados["menu_id"],
                $dados["nome"],
                $dados["rota"],
                $dados["icone"] ?? null,
                $dados["posicao"] ?? 0
            );

            self::info("Atualizando item de menu ID: {$id}");
            $ok = MenuItemDao::atualizar($id, $item);

            if (!$ok) {
                return self::Mensagemjson("Erro ao atualizar item.", 500);
            }

            return self::Mensagemjson("Item atualizado com sucesso!", 200, $item->toArray());
        } catch (\Throwable $th) {
            self::error("Erro ao atualizar item de menu: " . $th->getMessage());
            return self::Mensagemjson("Erro ao atualizar item", 500);
        }
    }

    /**
     * Deletar item de menu
     * DELETE /menu/item/{id}
     */
    public function deletarItem(int $id)
    {
        try {
            self::warning("Deletando item de menu ID: {$id}");

            $ok = MenuItemDao::deletar($id);
            if (!$ok) {
                return self::Mensagemjson("Erro ao deletar item.", 500);
            }

            return self::Mensagemjson("Item deletado com sucesso!", 200, ["id_item" => $id]);
        } catch (\Throwable $th) {
            self::error("Erro ao deletar item de menu: " . $th->getMessage());
            return self::Mensagemjson("Erro ao deletar item", 500);
        }
    }
}