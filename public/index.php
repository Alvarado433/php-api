<?php

// ================================
// Mostra erros (DEV) - pode desligar depois
// ================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ================================
// Composer autoload (CORRIGIDO)
// ================================
require_once __DIR__ . "/../vendor/autoload.php";

use Core\Cors\Cors;
use Core\Env\IndexEnv;
use Database\conexao\conectar;
use Routers\Inicio\roteamento;
use Routers\Agrupamento\Groups;

// ================================
// CORS (ANTES de qualquer coisa)
// ================================
Cors::handle([
    'https://imperio-woad.vercel.app',
    'https://universoimperio.com.br',
    'https://www.universoimperio.com.br',
    'http://localhost:3000'
]);

// ================================
// Carrega .env e conecta no banco
// ================================
IndexEnv::carregar();
conectar::conectar();

// ================================
// Rotas
// ================================
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

roteamento::get("/catalogo", "CatalogoController@listar");

/*
|----------------------------------------------------------------------
| 🛒 CARRINHO
|----------------------------------------------------------------------
*/
roteamento::get("/carrinho/{usuarioId}", "CarrinhoController@listar");            // listar itens + endereço
roteamento::post("/carrinho/adicionar", "CarrinhoController@adicionar");         // adicionar item
roteamento::put("/carrinho/atualizar/{itemId}", "CarrinhoController@atualizar"); // atualizar quantidade
roteamento::delete("/carrinho/remover/{itemId}", "CarrinhoController@remover");  // remover item
roteamento::delete("/carrinho/limpar/{usuarioId}", "CarrinhoController@limpar"); // limpar carrinho
roteamento::post("/carrinho/endereco", "CarrinhoController@salvarEndereco");     // criar/atualizar endereço

/*
|----------------------------------------------------------------------
| 📦 PEDIDO
|----------------------------------------------------------------------
*/
roteamento::get("/pedidos", "PedidoController@listarTodos");                     // listar todos pedidos (admin)
roteamento::post("/pedido/finalizar", "PedidoController@finalizar");                     // criar pedido com itens + endereço do carrinho
roteamento::get("/pedido/{usuarioId}", "PedidoController@listarPorUsuario");             // listar pedidos do usuário
roteamento::get("/pedido/detalhes/{pedidoId}", "PedidoController@detalhes");             // detalhes do pedido
roteamento::put("/pedido/{pedidoId}/status/{statusId}", "PedidoController@alterarStatus"); // alterar status (admin)
roteamento::delete("/pedido/{pedidoId}", "PedidoController@deletar");                    // deletar pedido

/*
|----------------------------------------------------------------------
| 🎟 CUPONS (SITE / API)
|----------------------------------------------------------------------
*/
roteamento::get("/cupons", "CupomController@listar");
roteamento::get("/cupons/ativos", "CupomController@listarAtivos");
roteamento::get("/cupons/inativos", "CupomController@listarInativos");
roteamento::get("/cupom/{codigo}", "CupomController@buscarPorCodigo");
roteamento::post("/catalogo/unificar", "CatalogoController@unificar");

Groups::prefix("/admin", function () {

    // Dashboard
    Groups::get("/dash", "CardsController@listar");
    Groups::get("/dash/configuracoes", "CardsController@listarCardsConfiguracao");
    Groups::get("/configuracoes/login", "DashboardController@listarTodasConfiguracoesLogin");

    Groups::get("/carrinho", "DashboardController@listarCarrinhos");

    // Produtos
    Groups::get("/produtos", "DashboardController@listartudo");
    Groups::post("/produto/criar", "DashboardController@criarProduto");
    Groups::delete("/produto/{id}/remover", "DashboardController@removerProduto");

    // Destaques
    Groups::get("/produtos/destaques", "DashboardController@listarAtivos");
    Groups::post("/produtos/destaques/criar", "DashboardController@criarProdutoDestaque");
    Groups::delete("/produtos/destaques/{id}/remover", "DashboardController@removerProdutoDestaque");

    // Catálogo
    Groups::get("/produtos/catalogo", "DashboardController@listarCatalogo");
    Groups::put("/produtos/{id}/catalogo/sim", "DashboardController@marcarCatalogoSim");
    Groups::put("/produtos/{id}/catalogo/nao", "DashboardController@marcarCatalogoNao");

    // Status
    Groups::get("/status", "DashboardController@listarStatus");
    Groups::get("/categorias/{id}", "DashboardController@buscarCategoria");

    // Unificar produtos em uma categoria
    Groups::post("/produtos/unificar", "DashboardController@unificarProdutosEmCategoria");

    // Banners
    Groups::get("/banner", "DashboardController@listarBanners"); // FRONT USA ESSA
    Groups::get("/banners", "DashboardController@listarBanners"); // opcional
    Groups::post("/banner/criar", "DashboardController@criarBanner");
    Groups::delete("/banner/{id}/remover", "DashboardController@removerBanner");

    // Categorias
    Groups::get("/categorias", "DashboardController@listarCategorias");
    Groups::post("/cat", "DashboardController@criarCategoria");
    Groups::delete("/categoria/{id}/remover", "DashboardController@deletarCategoria");

    // Cupons
    Groups::get("/cupons", "DashboardController@listarCupons");
    Groups::post("/cupom/tipos/criar", "DashboardController@criarCupomTipo");
    Groups::get("/cupons/ativos", "DashboardController@listarCuponsAtivos");
    Groups::get("/cupons/inativos", "DashboardController@listarCuponsInativos");
    Groups::post("/cupom/criar", "DashboardController@criarCupom");
    Groups::put("/cupom/{id}/atualizar", "DashboardController@atualizarCupom");
    Groups::put("/cupom/{id}/status/{statusid}", "DashboardController@alterarStatusCupom");
    Groups::delete("/cupom/{id}/remover", "DashboardController@deletarCupom");

    // Usuários
    Groups::get("/usuarios", "Usuariodashboard@listar");
    Groups::get("/usuarios/niveis", "Usuariodashboard@listarNiveis");
    Groups::post("/usuarios", "Usuariodashboard@criar");
    Groups::delete("/usuarios/{id}", "Usuariodashboard@remover");
    Groups::get("/usuarios/{id}", "Usuariodashboard@buscar");
    Groups::put("/usuarios/{id}", "Usuariodashboard@atualizar");
    Groups::put("/usuarios/{id}/reset-pin", "Usuariodashboard@resetPin");

    // Tipos de cupom
    Groups::get("/cupom/tipos", "DashboardController@listarCupomTipos");

    // Menus
    Groups::get("/menu", "DashboardController@listarMenus");

    Groups::auth();
});

// ================================
// Start
// ================================
try {
    roteamento::start();
} catch (\Exception $e) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "erro" => true,
        "mensagem" => $e->getMessage()
    ]);
    exit;
}
