<?php

namespace Routers\Modules;

use Routers\Inicio\roteamento;
use Routers\Agrupamento\Groups;

class HomeRouter
{
    public static function rotas(): void
    {
        /*
        |----------------------------------------------------------------------
        | 🌐 SITE / HOME
        |----------------------------------------------------------------------
        */
        roteamento::get("/api", "Home@index");
        roteamento::get("/home", "Home@home");
        roteamento::get("/teta", "Home@testeCookie");
        roteamento::get("/upload/{arquivo}", "UploadController@mostrar");

        /*
        |----------------------------------------------------------------------
        | 🔐 AUTENTICAÇÃO
        |----------------------------------------------------------------------
        */
        roteamento::post("/login", "LoginController@login");
        roteamento::post("/login/etapa1", "Login@etapa1");
        roteamento::post("/login/etapa2", "Login@etapa2");
        roteamento::post("/logout", "LoginController@logout");
        roteamento::get("/me", "Login@me");

        /*
        |----------------------------------------------------------------------
        | 👤 USUÁRIOS
        |----------------------------------------------------------------------
        */
        roteamento::get("/usuarios", "UsuarioController@listar");
        roteamento::get("/usuarios/{id}", "UsuarioController@buscar");
        roteamento::post("/usuarios", "UsuarioController@criar");
        roteamento::put("/usuarios/{id}", "UsuarioController@atualizar");
        roteamento::delete("/usuarios/{id}", "UsuarioController@deletar");

        /*
        |----------------------------------------------------------------------
        | 🎯 BANNERS
        |----------------------------------------------------------------------
        */
        roteamento::get("/banners", "Banner@listar");
        roteamento::get("/banners/ativos", "Banner@ativos");
        roteamento::get("/banners/{id}", "Banner@buscar");
        roteamento::post("/banners", "Banner@criar");
        roteamento::put("/banners/{id}", "Banner@atualizar");
        roteamento::delete("/banners/{id}", "Banner@deletar");
        roteamento::put("/banners/{id}/view", "Banner@incrementarVisualizacao");
        roteamento::put("/banners/{id}/click", "Banner@incrementarClique");

        /*
        |----------------------------------------------------------------------
        | 📂 MENU
        |----------------------------------------------------------------------
        */
        roteamento::get("/menu", "Menu@listar");
        roteamento::get("/menu/ativos", "Menu@ativos");
        roteamento::get("/menu/{id}/itens", "Menu@listarItens");
        roteamento::post("/menu", "Menu@criar");
        roteamento::put("/menu/{id}", "Menu@atualizar");
        roteamento::delete("/menu/{id}", "Menu@deletar");

        /*
        |----------------------------------------------------------------------
        | 🟢 MENU ITENS
        |----------------------------------------------------------------------
        */
        roteamento::get("/menu/itens/{nivelId}", "MenuItemController@listar");
        roteamento::post("/menu/{id}/itens", "MenuItemController@criar");
        roteamento::put("/menu/item/{itemId}", "MenuItemController@atualizar");
        roteamento::delete("/menu/item/{itemId}", "MenuItemController@deletar");

        /*
        |----------------------------------------------------------------------
        | 🟣 CATEGORIAS
        |----------------------------------------------------------------------
        */
        roteamento::get("/categorias", "CategoriaController@listar");
        roteamento::get("/categorias/ativas", "CategoriaController@listarAtivas");
        roteamento::get("/categorias/ordenadas", "CategoriaController@listarOrdenadas");
        roteamento::get("/categorias/{id}", "CategoriaController@buscar");
        roteamento::post("/categorias", "CategoriaController@criar");
        roteamento::put("/categorias/{id}", "CategoriaController@atualizar");
        roteamento::delete("/categorias/{id}", "CategoriaController@deletar");

        /*
        |----------------------------------------------------------------------
        | 📦 PRODUTOS
        |----------------------------------------------------------------------
        */
        roteamento::get("/produtos", "ProdutoController@listar");
        roteamento::get("/produtos/destaques/status", "ProdutoDestaqueController@listarPorStatusDestaque");
        roteamento::get("/produto/slug/{slug}", "ProdutoController@buscarPorSlug");
        roteamento::get("/produtos/pesquisa", "ProdutoController@pesquisar");
        roteamento::get("/produtos/{id}", "ProdutoController@buscar");
        roteamento::post("/produtos", "ProdutoController@criar");
        roteamento::put("/produtos/{id}", "ProdutoController@atualizar");
        roteamento::delete("/produtos/{id}", "ProdutoController@deletar");

        /*
        |----------------------------------------------------------------------
        | ⭐ PRODUTOS EM DESTAQUE
        |----------------------------------------------------------------------
        */
        roteamento::get("/produtos/destaques", "ProdutoDestaqueController@listar");
        roteamento::get("/produtos/destaques/ativos", "ProdutoDestaqueController@listarAtivos");
        roteamento::get("/produtos/destaques/{id}", "ProdutoDestaqueController@buscar");
        roteamento::post("/produtos/destaques", "ProdutoDestaqueController@criar");
        roteamento::put("/produtos/destaques/{id}", "ProdutoDestaqueController@atualizar");
        roteamento::delete("/produtos/destaques/{id}", "ProdutoDestaqueController@deletar");

        /*
        |----------------------------------------------------------------------
        | 🛒 CARRINHO
        |----------------------------------------------------------------------
        */
        roteamento::get("/carrinho/{usuarioId}", "CarrinhoController@listar");            // listar itens + endereço
        roteamento::post("/carrinho/adicionar", "CarrinhoController@adicionar");         // adicionar item
        roteamento::put("/carrinho/atualizar/{itemId}", "CarrinhoController@atualizar"); // atualizar quantidade
        roteamento::delete("/carrinho/remover/{itemId}", "CarrinhoController@remover"); // remover item
        roteamento::delete("/carrinho/limpar/{usuarioId}", "CarrinhoController@limpar"); // limpar carrinho
        roteamento::post("/carrinho/endereco", "CarrinhoController@salvarEndereco");    // criar/atualizar endereço

        /*
        |----------------------------------------------------------------------
        | 📦 PEDIDO
        |----------------------------------------------------------------------
        */
        roteamento::post("/pedido/finalizar", "PedidoController@finalizar");           // criar pedido com itens + endereço do carrinho
        roteamento::get("/pedido/{usuarioId}", "PedidoController@listarPorUsuario");   // listar pedidos do usuário
        roteamento::get("/pedido/detalhes/{pedidoId}", "PedidoController@detalhes");   // detalhes do pedido
        roteamento::put("/pedido/{pedidoId}/status/{statusId}", "PedidoController@alterarStatus"); // alterar status (admin)
        roteamento::delete("/pedido/{pedidoId}", "PedidoController@deletar");          // deletar pedido

        /*
        |----------------------------------------------------------------------
        | 🎟 CUPONS (SITE / API)
        |----------------------------------------------------------------------
        */
        roteamento::get("/cupons", "CupomController@listar");
        roteamento::get("/cupons/ativos", "CupomController@listarAtivos");
        roteamento::get("/cupons/inativos", "CupomController@listarInativos");
        roteamento::get("/cupom/{codigo}", "CupomController@buscarPorCodigo");

        /*
        |----------------------------------------------------------------------
        | 🔹 ROTAS ADMIN
        |----------------------------------------------------------------------
        */
        Groups::prefix("/admin", function () {

            // 📊 DASHBOARD
            Groups::get("/", "DashboardController@listar");

            // 📦 PRODUTOS
            Groups::get("/produtos", "DashboardController@listartudo");
            Groups::post("/produto/criar", "DashboardController@criarProduto");
            Groups::delete("/produto/{id}/remover", "DashboardController@removerProduto");

            // ⭐ PRODUTOS EM DESTAQUE
            Groups::get("/produtos/destaques", "ProdutoDestaqueController@listar");
            Groups::get("/produtos/destaques/ativos", "ProdutoDestaqueController@listarAtivos");
            Groups::get("/produtos/destaques/{id}", "ProdutoDestaqueController@buscar");
            Groups::post("/produtos/destaques/criar", "DashboardController@criarProdutoDestaque");
            Groups::delete("/produtos/destaques/{id}/remover", "DashboardController@removerProdutoDestaque");

            // 🗂 CATEGORIAS
            Groups::get("/categorias", "DashboardController@listarCategorias");
            Groups::post("/cat", "DashboardController@criarCategoria");

            // 🖼 BANNERS
            Groups::get("/banner", "DashboardController@listarBanners");
            Groups::post("/banner/criar", "DashboardController@criarBanner");

            // ⚙️ STATUS
            Groups::get("/status", "DashboardController@listarStatus");

            // 🎟 CUPONS (ADMIN)
            Groups::get("/cupons", "DashboardController@listarCupons");
            Groups::get("/cupons/ativos", "DashboardController@listarCuponsAtivos");
            Groups::get("/cupons/inativos", "DashboardController@listarCuponsInativos");
            Groups::post("/cupons/criar", "DashboardController@criarCupom");
            Groups::get("/cupons/tipos", "DashboardController@listarCupomTipos");
            Groups::put("/cupons/{id}", "DashboardController@atualizarCupom");
            Groups::put("/cupons/{id}/status/{statusId}", "DashboardController@alterarStatusCupom");
            Groups::delete("/cupons/{id}", "DashboardController@deletarCupom");

            Groups::get("/configu/cards", "DashboardController@listarCardsConfiguracao");
            Groups::get("/configu/login/todas", "DashboardController@listarTodasConfiguracoesLogin");

            // 🔒 AUTENTICAÇÃO OBRIGATÓRIA
            Groups::auth();
        });
    }
}
