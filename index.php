<?php

// ================================
// Mostra todos os erros (apenas dev)
// ================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ================================
// ⚠️ CORS manual no topo
// ================================
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$originsPermitidos = [
        'http://localhost:3000',
        'https://imperio-woad.vercel.app',
        'http://imperio.com.br',
        'https://imperio.com.br',
        'http://www.imperioloja.com.br',
        'https://www.imperioloja.com.br',
];

if (in_array($origin, $originsPermitidos, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 86400");

// Responder OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
}

// ================================
// Autoload e configuração
// ================================
require_once __DIR__ . "/Autoload/Autoload.php";
Autoload::register();

use Core\Env\IndexEnv;
use Database\conexao\conectar;
use Routers\Inicio\roteamento;
use Routers\Agrupamento\Groups;

// Carrega .env
IndexEnv::carregar();

// Conecta ao banco
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
        Groups::get("/carrinhos", "DashboardController@listarCarrinhos");

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

Groups::prefix("/admin", function () {

        // Dashboard
        Groups::get("/dash", "CardsController@listar");
        Groups::get("/dash/configuracoes", "CardsController@listarCardsConfiguracao");
        Groups::get("/configuracoes/login", "DashboardController@listarTodasConfiguracoesLogin");

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

        // Banners
        Groups::get("/banner", "DashboardController@listarBanners"); // 👈 FRONT USA ESSA
        Groups::get("/banners", "DashboardController@listarBanners"); // opcional
        Groups::post("/banner/criar", "DashboardController@criarBanner");
        Groups::delete("/banner/{id}/remover", "DashboardController@removerBanner");

        // Categorias
        Groups::get("/categorias", "DashboardController@listarCategorias");
        Groups::post("/cat", "DashboardController@criarCategoria");
        Groups::delete("/categoria/{id}/remover", "DashboardController@deletarCategoria");

        // Cupons
        Groups::get("/cupons", "DashboardController@listarCupons");
        Groups::get("/cupons/ativos", "DashboardController@listarCuponsAtivos");
        Groups::get("/cupons/inativos", "DashboardController@listarCuponsInativos");
        Groups::post("/cupom/criar", "DashboardController@criarCupom");
        Groups::put("/cupom/{id}/atualizar", "DashboardController@atualizarCupom");
        Groups::put("/cupom/{id}/status/{statusid}", "DashboardController@alterarStatusCupom");
        Groups::delete("/cupom/{id}/remover", "DashboardController@deletarCupom");

        // ======================================================
        // 👤 USUÁRIOS
        // ======================================================
        Groups::get("/usuarios", "Usuariodashboard@listar");            // LISTAR
        Groups::get("/usuarios/niveis", "Usuariodashboard@listarNiveis"); // LISTAR NÍVEIS
        Groups::post("/usuarios", "Usuariodashboard@criar");            // CRIAR
        Groups::delete("/usuarios/{id}", "Usuariodashboard@remover");   // REMOVER
        Groups::get("/usuarios/{id}", "Usuariodashboard@buscar");       // BUSCAR (editar)
        Groups::put("/usuarios/{id}", "Usuariodashboard@atualizar");    // ATUALIZAR
        Groups::put("/usuarios/{id}/reset-pin", "Usuariodashboard@resetPin"); // RESET PIN
        // Tipos de cupom
        Groups::get("/cupom/tipos", "DashboardController@listarCupomTipos");

        Groups::auth();
});


try {

        roteamento::start();
} catch (\Exception $e) {
        http_response_code(404);
        echo json_encode([
                "erro" => true,
                "mensagem" => $e->getMessage()
        ]);
        exit;
}
