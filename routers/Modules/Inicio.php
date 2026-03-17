<?php

namespace Routers\Modules;

use Routers\Inicio\roteamento;

class Inicio
{
    public static function rotas()
    {
        /* ============================================================
         * HOME & NAVEGAÇÃO
         * ============================================================ */
        // GET
        roteamento::get("/", "InicioController@index");
        roteamento::get("/navbar", "InicioController@navbar");

        /* ============================================================
         * MENUS
         * ============================================================ */
        // GET
        roteamento::get("/menu", "InicioController@listar");
        roteamento::get("/menu/ativos", "InicioController@listar");
        roteamento::get("/menu/com-itens", "MenuController@listarComItens");
        roteamento::get("/menu/nivel/{nivelId}/itens", "MenuController@listarItensPorNivel");
        roteamento::get("/menu/{id}/itens", "MenuController@listarItens");
        roteamento::get("/menu/{id}", "MenuController@buscar");

        // POST
        roteamento::post("/menu", "MenuController@create");
        roteamento::post("/menu/{id}/itens", "MenuController@criarItem");

        // PUT
        roteamento::put("/menu/item/{id}", "MenuController@atualizarItem");
        roteamento::put("/menu/{id}", "MenuController@update");

        // DELETE
        roteamento::delete("/menu/item/{id}", "MenuController@deletarItem");
        roteamento::delete("/menu/{id}", "MenuController@delete");

        /* ============================================================
         * UPLOAD
         * ============================================================ */
        // GET
        roteamento::get("/upload/{arquivo}", "UploadController@mostrar");

        /* ============================================================
         * PRODUTOS & CATÁLOGO
         * ============================================================ */
        // GET
        roteamento::get("/catalogo", "CatalogoController@listar");
        roteamento::get("/catalogo/destaques", "CatalogoController@listardestaques");
        roteamento::get("/produtos/destaques", "ProdutoController@listarDestaques");
        roteamento::get("/produtos/destaques/status", "ProdutoController@listarProdutosStatusDestaque");
        roteamento::get("/produtos", "ProdutoController@listar");
        roteamento::get("/produtos/catalogo", "ProdutoController@listarCatalogo");
        roteamento::get("/produtos/pesquisa", "ProdutoController@pesquisar");
        roteamento::get("/produtos/categoria/{id}", "ProdutoController@listarPorCategoria");
        roteamento::get("/produto/slug/{slug}", "ProdutoController@buscarPorSlug");
        roteamento::get("/produtos/{id}", "ProdutoController@buscar");

        // POST
        roteamento::post("/produtos/destaques", "ProdutoController@criarDestaque");
        roteamento::post("/produtos", "ProdutoController@criar");

        // PUT
        roteamento::put("/produtos/destaques/{id}", "ProdutoController@atualizarDestaque");
        roteamento::put("/produtos/{id}", "ProdutoController@atualizar");

        // DELETE
        roteamento::delete("/produtos/destaques/{id}", "ProdutoController@deletarDestaque");
        roteamento::delete("/produtos/{id}", "ProdutoController@deletar");

        /* ============================================================
         * CATEGORIAS
         * ============================================================ */
        // GET
        roteamento::get("/categorias", "InicioController@listarCategorias");
        roteamento::get("/categorias/ativas", "InicioController@listarCategoriasAtivas");
        roteamento::get("/categorias/ordenadas", "InicioController@listarCategoriasOrdenadas");
        roteamento::get("/categorias/{id}", "InicioController@buscarCategoria");

        // POST
        roteamento::post("/categorias", "InicioController@criarCategoria");

        // PUT
        roteamento::put("/categorias/{id}", "InicioController@atualizarCategoria");

        // DELETE
        roteamento::delete("/categorias/{id}", "InicioController@deletarCategoria");

        /* ============================================================
         * AUTH & USUÁRIOS DO SISTEMA
         * ============================================================ */
        // GET
        roteamento::get("/me", "LoginController@me");
        roteamento::get("/configuracoes/login", "LoginController@loginAtiva");
        roteamento::get("/usuarios-sistema", "UsuarioSistemaController@listar");
        roteamento::get("/usuarios-sistema/{id}", "UsuarioSistemaController@buscar");

        // POST
        roteamento::post("/login", "LoginController@login");
        roteamento::post("/login/etapa1", "LoginController@etapa1");
        roteamento::post("/login/etapa2", "LoginController@etapa2");
        roteamento::post("/logout", "LoginController@logout");
        roteamento::post("/usuarios-sistema", "UsuarioSistemaController@criar");

        // PUT
        roteamento::put("/usuarios-sistema/{id}", "UsuarioSistemaController@atualizar");

        // DELETE
        roteamento::delete("/usuarios-sistema/{id}", "UsuarioSistemaController@deletar");

        /* ============================================================
         * CAMPANHAS & BANNERS
         * ============================================================ */
        // GET
        roteamento::get("/campanha/ativa/{slug}", "InicioController@campanhaAtiva");
        roteamento::get("/banners", "InicioController@listarBanners");
        roteamento::get("/banners/ativos", "InicioController@bannersAtivos");
        roteamento::get("/banners/{id}", "InicioController@buscarBanner");

        // POST
        roteamento::post("/banners", "InicioController@criarBanner");

        // PUT
        roteamento::put("/banners/{id}/view", "InicioController@incrementarBannerView");
        roteamento::put("/banners/{id}/click", "InicioController@incrementarBannerClick");
        roteamento::put("/banners/{id}", "InicioController@atualizarBanner");

        // DELETE
        roteamento::delete("/banners/{id}", "InicioController@deletarBanner");

        /* ============================================================
         * CUPONS
         * ============================================================ */
        // GET
        roteamento::get("/cupons", "InicioController@listarCupons");
        roteamento::get("/cupons/ativos", "InicioController@listarCuponsAtivos");
        roteamento::get("/cupons/inativos", "InicioController@listarCuponsInativos");
        roteamento::get("/cupom/{codigo}", "InicioController@buscarCupomPorCodigo");

        /* ============================================================
         * CARRINHO E ENDEREÇO
         * ============================================================ */
        // GET
        roteamento::get("/carrinho/endereco", "InicioController@buscarEnderecoCarrinho");
        roteamento::get("/carrinho/enderecos", "InicioController@listarEnderecosCarrinho");
        roteamento::get("/carrinho", "InicioController@buscarCarrinho");
        roteamento::get("/carrinho/itens", "InicioController@listarItensCarrinho");

        // POST
        roteamento::post("/carrinho/endereco", "InicioController@salvarEnderecoCarrinho");
        roteamento::post("/carrinho/adicionar", "InicioController@adicionarItemCarrinho");

        // PUT
        roteamento::put("/carrinho/endereco", "InicioController@atualizarEnderecoCarrinho");
        roteamento::put("/carrinho/item/{id}", "InicioController@atualizarQuantidadeItemCarrinho");

        // DELETE
        roteamento::delete("/carrinho/endereco", "InicioController@removerEnderecoCarrinho");
        roteamento::delete("/carrinho/item/{id}", "InicioController@removerItemCarrinho");
        roteamento::delete("/carrinho/limpar", "InicioController@limparCarrinho");

        /* ============================================================
         * PAGAMENTO & PEDIDOS
         * ============================================================ */
        // GET
        roteamento::get("/pedidos", "InicioController@listarPedidos");
        roteamento::get("/pedido/{id}/itens", "InicioController@listarItensPedido");
        roteamento::get("/pedido/{id}", "InicioController@buscarPedido");

        // POST
        roteamento::post("/carrinho/pix", "InicioController@gerarPixCarrinho");
        roteamento::post("/pagamento/webhook", "InicioController@webhookMercadoPago");
        roteamento::post("/pedido/finalizar", "InicioController@finalizarPedido");

        // PUT
        roteamento::put("/pedido/{id}/status", "InicioController@alterarStatusPedido");

        // DELETE
        roteamento::delete("/pedido/{id}", "InicioController@deletarPedido");er@deletarPedido");
    }
}
