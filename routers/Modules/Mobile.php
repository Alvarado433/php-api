<?php

namespace Routers\Modules;

use Routers\Inicio\roteamento;

class Mobile
{
    public static function rotas()
    {

        // =========================
        // DASHBOARD
        // =========================

        roteamento::get("/", "Api\\MobileController@index");


        // =========================
        // PRODUTOS
        // =========================

        roteamento::get("/produto", "Api\\MobileController@listartudo");

        roteamento::post("/produto/criar", "Api\\MobileController@criarProduto");

        roteamento::delete("/produto/{id}", "Api\\MobileController@removerProduto");

        roteamento::post("/produtos/unificar", "Api\\MobileController@unificarProdutosEmCategoria");

        roteamento::get("/produtos/status", "Api\\MobileController@listarStatus");

        roteamento::get("/produtos-destaques", "Api\\MobileController@listarProdutosEmDestaque");

        roteamento::get("/produtos/catalogo", "Api\\MobileController@listarProdutosCatalogo");


        // =========================
        // CATEGORIAS
        // =========================

        roteamento::get("/categorias", "Api\\MobileController@listarCategorias");

        roteamento::get("/categorias/status", "Api\\MobileController@status");

        roteamento::post("/categorias", "Api\\MobileController@criarCategoria");


        // =========================
        // BANNERS
        // =========================

        roteamento::get("/banners-listar", "Api\\MobileController@mostrarBanners");

        // edição via JSON
        roteamento::put("/banners-editar/{id}", "Api\\MobileController@atualizarBanner");

        // edição via multipart (upload)
        roteamento::post("/banners-editar/{id}", "Api\\MobileController@atualizarBanner");

        roteamento::get("/banner-status", "Api\\MobileController@BannerStatus");


        // =========================
        // CAMPANHAS (NOVO)
        // =========================

        roteamento::get("/campanhas", "Api\\MobileController@listarCampanhas");

        roteamento::get("/campanhas/ativas", "Api\\MobileController@campanhasAtivas");

        roteamento::get("/campanha/{slug}/produtos", "Api\\MobileController@produtosDaCampanha");

        // campanha destaque automática
        roteamento::get("/campanha/destaque/nivel9", "Api\\MobileController@campanhaAtivaNivel9");


        // =========================
        // STATUS GLOBAL
        // =========================

        roteamento::get("/status", "Api\\MobileController@listarStatus");


        // =========================
        // NOTIFICAÇÕES
        // =========================

        roteamento::get("/notificacoes", "Api\\MobileController@listarNotificacoes");

        roteamento::get("/notificacoes/nao-lidas", "Api\\MobileController@totalNaoLidas");

        roteamento::put("/notificacoes/{id}/lida", "Api\\MobileController@marcarComoLida");

    }
}