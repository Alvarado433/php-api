<?php

namespace Imperio\Controllers;


use App\Dao\Cupom\CupomDao;
use App\Dao\Banner\BannerDao;
use Config\Base\Basecontrolador;
use App\Dao\Carrinho\CarrinhoDao;
use App\Dao\UsuarioDao\UsuarioDao;
use App\Dao\Categoria\CategoriaDao;
use App\Dao\ConfiguracaoDao\ConfiguracaoLoginDao;
use App\Dao\Menu\MenuDao;
use App\Dao\Produto\ProdutoDao;
use App\Dao\Produto\ProdutoDestaqueDao; // ✅ IMPORT ADICIONADO
use App\Dao\Status\StatusDao; // ✅ IMPORT ADICIONADO
use Core\Upload\ServidorUpload;

class PainelAdministrativo extends Basecontrolador
{
    public function index(): void
    {
        self::info("Dashboard: carregando sidebar (com grupos)");

        $sidebar = [
            [
                "type"  => "link",
                "label" => "Dashboard",
                "href"  => "/admin/dashboard",
                "icon"  => "fa-solid fa-chart-line",
                "match" => "/admin/dashboard",
            ],

            [
                "type"  => "group",
                "label" => "Gestão",
                "icon"  => "fa-solid fa-grid-2",
                "children" => [
                    [
                        "label" => "Usuários",
                        "href"  => "/admin/usuarios",
                        "icon"  => "fa-solid fa-users",
                        "match" => "/usuarios",
                    ],
                ],
            ],

            [
                "type"  => "group",
                "label" => "Catálogo",
                "icon"  => "fa-solid fa-boxes-stacked",
                "children" => [
                    [
                        "label" => "Produtos",
                        "href"  => "/admin/produtos",
                        "icon"  => "fa-solid fa-box",
                        "match" => "/produtos",
                    ],
                    [
                        "label" => "Categorias",
                        "href"  => "/admin/categorias",
                        "icon"  => "fa-solid fa-tags",
                        "match" => "/categorias",
                    ],
                ],
            ],
        ];

        self::Mensagemjson(
            "Dashboard carregado com sucesso",
            200,
            ["dados" => $sidebar]
        );
    }

    public function listar(): void
    {
        self::info("Dashboard: carregando cards");

        // Contagem de cada item
        $cards = [
            ["titulo" => "Produtos",   "quantidade" => count(ProdutoDao::Todos())],
            ["titulo" => "Categorias", "quantidade" => count(CategoriaDao::listarAtivas())],
            ["titulo" => "Banners",    "quantidade" => count(BannerDao::Todos())],
            ["titulo" => "Usuários",   "quantidade" => UsuarioDao::contar()],
            ["titulo" => "Cupons Ativos", "quantidade" => count(CupomDao::listarAtivos())],
            ["titulo" => "Carrinhos",  "quantidade" => count(CarrinhoDao::listarTodos())] // vamos criar esse método
        ];

        self::Mensagemjson("Dashboard carregado com sucesso", 200, ["dados" => $cards]);
    }

    public function listartudo(): void
    {
        self::info("Produto: listando todos os produtos");
        $produtos = ProdutoDao::listarTodos();
        self::success(count($produtos) . " produtos carregados");
        self::Mensagemjson("Produtos carregados com sucesso", 200, $produtos);
    }
    public function listarStatus(): void
    {
        self::info("Status: listando status");
        $status = StatusDao::listar();
        self::success(count($status) . " status carregados");
        self::Mensagemjson("Status carregados", 200, $status);
    }
    public function removerProduto(int $id): void
    {
        try {
            self::info("Produto: removendo produto ID {$id}");
            $produto = ProdutoDao::buscar($id);
            if (!$produto) {
                self::warning("Produto não encontrado ID {$id}");
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            if (!empty($produto['imagem'])) {
                $caminho = __DIR__ . '/../../public/' . $produto['imagem'];
                if (file_exists($caminho)) {
                    unlink($caminho);
                    self::info("Produto: imagem removida {$caminho}");
                }
            }

            $destaque = ProdutoDestaqueDao::buscar($id);
            if ($destaque) {
                ProdutoDestaqueDao::deletar($destaque['id_destaque']);
                self::info("Produto: destaque removido ID {$destaque['id_destaque']}");
            }

            ProdutoDao::deletar($id);
            self::success("Produto removido com sucesso");
            self::Mensagemjson("Produto removido com sucesso", 200);
        } catch (\Throwable $th) {
            self::error("Produto: erro ao remover - " . $th->getMessage());
            self::Mensagemjson("Erro ao remover produto", 500);
        }
    }
    public static function criarProdutoInterno(array $dados, ?array $arquivo = null): void
    {
        try {
            self::info("Produto: iniciando criação");

            if ($arquivo) {
                $caminhoImagem = ServidorUpload::upload($arquivo, 'produtos');
                if ($caminhoImagem) {
                    $dados['imagem'] = $caminhoImagem;
                    self::info("Produto: imagem salva em {$caminhoImagem}");
                }
            }

            if (empty($dados['slug']) && !empty($dados['nome'])) {
                $dados['slug'] = self::gerarSlug($dados['nome']);
                self::info("Produto: slug gerado {$dados['slug']}");
            }

            $ok = ProdutoDao::criar($dados);
            if ($ok) {
                self::success("Produto criado com sucesso");
            } else {
                self::error("Erro ao criar produto");
            }

            self::Mensagemjson(
                $ok ? "Produto criado com sucesso" : "Erro ao criar produto",
                $ok ? 201 : 500
            );
        } catch (\Throwable $th) {
            self::error("Produto: erro ao criar - " . $th->getMessage());
            self::Mensagemjson("Erro ao criar produto", 500);
        }
    }

    public function criarProduto(): void
    {
        $dados = $_POST;
        $arquivo = $_FILES['imagem'] ?? null;
        self::info("Produto: recebendo dados do POST");
        self::criarProdutoInterno($dados, $arquivo);
    }
    public function listarAtivos(): void
    {
        self::info("ProdutoDestaque: listando destaques");
        $destaques = ProdutoDestaqueDao::listarAtivos();
        self::success(count($destaques) . " destaques carregados");
        self::Mensagemjson("Destaques carregados", 200, $destaques);
    }

    public function criarProdutoDestaque(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true);
        $produtoId = (int)($dados['produto_id'] ?? 0);
        self::info("ProdutoDestaque: adicionando destaque ao produto ID {$produtoId}");

        $idDestaque = ProdutoDestaqueDao::criar([
            'produto_id' => $produtoId,
            'statusid' => ProdutoDao::status('destaque'),
            'ordem' => 1
        ]);

        ProdutoDao::atualizar($produtoId, ['destaque' => $idDestaque]);
        self::success("ProdutoDestaque: produto ID {$produtoId} adicionado ao destaque");
        self::Mensagemjson("Produto adicionado ao destaque", 201);
    }

    public function removerProdutoDestaque(int $id): void
    {
        ProdutoDestaqueDao::deletar($id);
        self::info("ProdutoDestaque: destaque ID {$id} removido");
        self::Mensagemjson("Produto removido do destaque", 200);
    }

    public function marcarCatalogoSim(int $id): void
    {
        ProdutoDao::atualizar($id, ['catalogo' => ProdutoDao::status('catalogo_sim')]);
        self::info("Catálogo: produto ID {$id} marcado como catálogo");
        self::Mensagemjson("Produto marcado como catálogo", 200);
    }
    public function marcarCatalogoNao(int $id): void
    {
        ProdutoDao::atualizar($id, ['catalogo' => ProdutoDao::status('catalogo_nao')]);
        self::info("Catálogo: produto ID {$id} removido do catálogo");
        self::Mensagemjson("Produto removido do catálogo", 200);
    }
}
