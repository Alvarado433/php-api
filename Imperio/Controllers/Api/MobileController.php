<?php

namespace Imperio\Controllers\Api;

use App\Dao\Banner\BannerDao;
use App\Dao\Campanha\CampanhaDao;
use App\Dao\Carrinho\CarrinhoDao;
use App\Dao\Categoria\CategoriaDao;
use App\Dao\Cupom\CupomDao;
use App\Dao\Notificacao\NotificacaoDao;
use App\Dao\Produto\ProdutoDao;
use App\Dao\Produto\ProdutoDestaqueDao;
use App\Dao\Status\StatusDao;
use App\Dao\UsuarioDao\UsuarioDao;
use App\Models\BannerModel;
use Config\Base\Basecontrolador;
use Core\Upload\ServidorUpload;

class MobileController extends Basecontrolador
{
    // =========================
    // DASHBOARD
    // =========================
    public function index(): void
    {
        self::info("Dashboard: carregando cards");

        $cards = [
            [
                "titulo" => "Produtos",
                "quantidade" => count(ProdutoDao::Todos()),
                "rota" => "/admin/produtos/produto",
                "metodo" => "GET",
            ],
            [
                "titulo" => "Categorias",
                "quantidade" => count(CategoriaDao::listarAtivas()),
                "rota" => "/admin/categorias/categoria",
                "metodo" => "GET",
            ],
            [
                "titulo" => "Banners",
                "quantidade" => count(BannerDao::Todos()),
                "rota" => "/admin/banners/banner",
                "metodo" => "GET",
            ],
            [
                "titulo" => "Usuários",
                "quantidade" => UsuarioDao::contar(),
                "rota" => "/admin/usuarios",
                "metodo" => "GET",
            ],
            [
                "titulo" => "Cupons Ativos",
                "quantidade" => count(CupomDao::listarAtivos()),
                "rota" => "/admin/cupons",
                "metodo" => "GET",
            ],
            [
                "titulo" => "Produto em destaque",
                "quantidade" => count(ProdutoDestaqueDao::listar()),
                "rota" => "/admin/produtos-em-destaque/produtos",
                "metodo" => "GET",
            ],
            [
                "titulo" => "Produto em catálogo",
                "quantidade" => count(ProdutoDao::listarCatalogo()),
                "rota" => "/admin/produtos-em-catalogo/produtos",
                "metodo" => "GET",
            ],
            [
                "titulo" => "Campanhas",
                "quantidade" => count(CampanhaDao::listar()),
                "rota" => "/admin/campanhas",
                "metodo" => "GET",
            ],
        ];

        self::Mensagemjson("Dashboard carregado com sucesso", 200, ["dados" => $cards]);
    }

    // =========================
    // PRODUTOS
    // =========================
    public function listartudo(): void
    {
        self::info("Produto: listando todos os produtos");

        $produtos = ProdutoDao::listarTodos();

        self::success(count($produtos) . " produtos carregados");
        self::Mensagemjson("Produtos carregados com sucesso", 200, $produtos);
    }

    public static function criarProdutoInterno(array $dados, ?array $arquivo = null): void
    {
        try {
            self::info("Produto: iniciando criação");

            if ($arquivo && !empty($arquivo["tmp_name"])) {
                $caminhoImagem = ServidorUpload::upload($arquivo, "produtos");

                if ($caminhoImagem) {
                    $dados["imagem"] = $caminhoImagem;
                    self::info("Produto: imagem salva em {$caminhoImagem}");
                }
            }

            if (empty($dados["slug"]) && !empty($dados["nome"])) {
                $dados["slug"] = self::gerarSlug($dados["nome"]);
                self::info("Produto: slug gerado {$dados['slug']}");
            }

            $ok = ProdutoDao::criar($dados);

            self::Mensagemjson(
                $ok ? "Produto criado com sucesso" : "Erro ao criar produto",
                $ok ? 201 : 500
            );
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao criar produto", 500);
        }
    }

    public function criarProduto(): void
    {
        $dados = $_POST;
        $arquivo = $_FILES["imagem"] ?? null;

        self::criarProdutoInterno($dados, $arquivo);
    }

    public function removerProduto(int $id): void
    {
        try {
            $produto = ProdutoDao::buscar($id);

            if (!$produto) {
                self::Mensagemjson("Produto não encontrado", 404);
                return;
            }

            if (!empty($produto["imagem"])) {
                $caminho = __DIR__ . "/../../public/" . ltrim($produto["imagem"], "/");
                if (file_exists($caminho)) {
                    @unlink($caminho);
                }
            }

            $destaque = ProdutoDestaqueDao::buscar($id);
            if ($destaque) {
                ProdutoDestaqueDao::deletar($destaque["id_destaque"]);
            }

            ProdutoDao::deletar($id);

            self::Mensagemjson("Produto removido com sucesso", 200);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao remover produto", 500);
        }
    }

    public function listarProdutosEmDestaque(): void
    {
        self::info("ProdutoDestaque: listando destaques");

        $destaques = ProdutoDestaqueDao::listarAtivos();

        self::success(count($destaques) . " destaques carregados");
        self::Mensagemjson("Destaques carregados", 200, $destaques);
    }

    public function listarProdutosCatalogo(): void
    {
        try {
            self::info("Produto: listando produtos do catálogo");

            $produtos = ProdutoDao::listarCatalogo();

            self::success(count($produtos) . " produtos do catálogo carregados");

            self::Mensagemjson("Produtos do catálogo carregados", 200, [
                "total" => count($produtos),
                "itens" => $produtos
            ]);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar produtos do catálogo", 500);
        }
    }

    public function unificarProdutosEmCategoria(): void
    {
        try {
            $dados = self::receberJson();

            $categoriaId = (int)($dados["categoria_id"] ?? 0);
            $produtos = $dados["produtos"] ?? [];

            if ($categoriaId <= 0) {
                self::Mensagemjson("categoria_id inválido", 422);
                return;
            }

            if (!is_array($produtos) || count($produtos) === 0) {
                self::Mensagemjson("Selecione pelo menos um produto", 422);
                return;
            }

            $produtos = array_values(array_unique(array_map("intval", $produtos)));
            $produtos = array_filter($produtos, fn($id) => $id > 0);

            if (count($produtos) === 0) {
                self::Mensagemjson("Lista de produtos inválida", 422);
                return;
            }

            $cat = CategoriaDao::buscar($categoriaId);
            if (!$cat) {
                self::Mensagemjson("Categoria não encontrada", 404);
                return;
            }

            $todos = ProdutoDao::Todos();
            $permitidos = [];

            foreach ($todos as $p) {
                if (
                    isset($p["id_produto"]) &&
                    in_array((int)$p["id_produto"], $produtos, true) &&
                    (empty($p["categoria_id"]) || $p["categoria_id"] === null)
                ) {
                    $permitidos[] = (int)$p["id_produto"];
                }
            }

            if (count($permitidos) === 0) {
                self::Mensagemjson("Nenhum produto elegível para unificação", 422);
                return;
            }

            $ok = ProdutoDao::unificarEmCategoria($categoriaId, $permitidos);

            self::Mensagemjson(
                $ok ? "Produtos unificados com sucesso" : "Erro ao unificar produtos",
                $ok ? 200 : 500,
                $ok ? [
                    "categoria_id" => $categoriaId,
                    "total_unificados" => count($permitidos),
                    "produtos" => $permitidos
                ] : null
            );
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao unificar produtos", 500);
        }
    }

    // =========================
    // STATUS
    // =========================
    public function listarStatus(): void
    {
        $status = StatusDao::listar();
        self::Mensagemjson("Status carregados", 200, $status);
    }

    public function Status(): void
    {
        $status = StatusDao::listar();
        self::Mensagemjson("Status carregados", 200, $status);
    }

    public function BannerStatus(): void
    {
        self::info("Status: listando status");

        $status = StatusDao::listar();

        self::success(count($status) . " status carregados");
        self::Mensagemjson("Status carregados", 200, $status);
    }

    // =========================
    // CATEGORIAS
    // =========================
    public function listarCategorias(): void
    {
        try {
            $categorias = CategoriaDao::listar();
            self::Mensagemjson("Categorias carregadas", 200, $categorias);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar categorias", 500);
        }
    }

    public function criarCategoria(): void
    {
        try {
            $dados = json_decode(file_get_contents("php://input"), true) ?? [];

            $nome = trim((string)($dados["nome"] ?? ""));
            $icone = trim((string)($dados["icone"] ?? ""));
            $statusid = (int)($dados["statusid"] ?? 1);

            if ($nome === "") {
                self::Mensagemjson("Informe o nome da categoria", 422);
                return;
            }

            if ($icone === "") {
                self::Mensagemjson("Informe o ícone da categoria", 422);
                return;
            }

            $ok = CategoriaDao::criar($nome, $icone, $statusid);

            if ($ok) {
                NotificacaoDao::criar([
                    "titulo"   => "Nova Categoria Criada",
                    "mensagem" => "A categoria '{$nome}' foi criada com sucesso.",
                    "statusid" => NotificacaoDao::status("ativa")
                ]);

                self::Mensagemjson("Categoria criada com sucesso", 201);
                return;
            }

            self::Mensagemjson("Erro ao criar categoria", 500);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao criar categoria", 500);
        }
    }

    // =========================
    // BANNERS
    // =========================
    public function mostrarBanners(): void
    {
        self::info("Banner: listando banners");

        $banners = BannerDao::listar();
        $lista = array_map(fn(BannerModel $b) => $b->toArray(), $banners);

        self::Mensagemjson("Banners carregados", 200, $lista);
    }

    public function atualizarBanner($id): void
    {
        try {
            $id = (int)$id;

            if ($id <= 0) {
                self::Mensagemjson("ID inválido", 422);
                return;
            }

            $bannerAtual = BannerDao::buscarPorId($id);
            if (!$bannerAtual) {
                self::Mensagemjson("Banner não encontrado", 404);
                return;
            }

            $isMultipart = !empty($_POST) || !empty($_FILES);
            $dados = $isMultipart ? ($_POST ?? []) : (self::receberJson() ?? []);

            $atualArr = $bannerAtual->toArray();

            $titulo = trim((string)($dados["titulo"] ?? $atualArr["titulo"] ?? ""));
            $descricao = (string)($dados["descricao"] ?? $atualArr["descricao"] ?? "");
            $link = isset($dados["link"])
                ? (trim((string)$dados["link"]) ?: null)
                : ($atualArr["link"] ?? null);
            $statusid = (int)($dados["statusid"] ?? ($atualArr["statusid"] ?? 1));

            $imagemFinal = (string)($atualArr["imagem"] ?? "");

            $arquivo = $_FILES["imagem"] ?? null;
            if ($arquivo && !empty($arquivo["tmp_name"])) {
                $caminhoImagem = ServidorUpload::upload($arquivo, "banner");

                if ($caminhoImagem) {
                    $imagemAntiga = (string)($atualArr["imagem"] ?? "");

                    if ($imagemAntiga && $imagemAntiga !== $caminhoImagem) {
                        $caminhoAntigo = __DIR__ . "/../../public/" . ltrim($imagemAntiga, "/");
                        if (file_exists($caminhoAntigo)) {
                            @unlink($caminhoAntigo);
                        }
                    }

                    $imagemFinal = $caminhoImagem;
                }
            } elseif (!empty($dados["imagem"])) {
                $imagemFinal = trim((string)$dados["imagem"]);
            }

            if ($titulo === "") {
                self::Mensagemjson("Título é obrigatório", 422);
                return;
            }

            if ($imagemFinal === "") {
                self::Mensagemjson("Imagem é obrigatória", 422);
                return;
            }

            $banner = new BannerModel(
                $id,
                $titulo,
                $descricao,
                $imagemFinal,
                $link,
                $statusid,
                (int)($atualArr["visualizacoes"] ?? 0),
                (int)($atualArr["cliques"] ?? 0),
            );

            $ok = BannerDao::atualizar($id, $banner->toArray());

            self::Mensagemjson(
                $ok ? "Banner atualizado com sucesso" : "Erro ao atualizar banner",
                $ok ? 200 : 500,
                $ok ? $banner->toArray() : null
            );
        } catch (\Throwable $e) {
            self::Mensagemjson("Erro ao atualizar banner: " . $e->getMessage(), 500);
        }
    }

    // =========================
    // CAMPANHAS
    // =========================
    public function listarCampanhas(): void
    {
        try {
            self::info("Campanha: listando campanhas");

            $campanhas = CampanhaDao::listar();

            self::success(count($campanhas) . " campanhas carregadas");

            self::Mensagemjson("Campanhas carregadas", 200, [
                "total" => count($campanhas),
                "dados" => $campanhas
            ]);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar campanhas", 500);
        }
    }

    public function campanhasAtivas(): void
    {
        try {
            self::info("Campanha: listando campanhas ativas");

            $campanhas = CampanhaDao::listarAtivas();

            self::success(count($campanhas) . " campanhas ativas carregadas");

            self::Mensagemjson("Campanhas ativas carregadas", 200, [
                "total" => count($campanhas),
                "dados" => $campanhas
            ]);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao listar campanhas ativas", 500);
        }
    }

    public function produtosDaCampanha($slug): void
    {
        try {
            $slug = trim((string)$slug);

            if ($slug === "") {
                self::Mensagemjson("Slug da campanha inválido", 422);
                return;
            }

            self::info("Campanha: carregando produtos da campanha {$slug}");

            $produtos = CampanhaDao::listarProdutosDaCampanhaAtiva($slug);

            self::success(count($produtos) . " produtos carregados");

            self::Mensagemjson("Produtos da campanha carregados", 200, [
                "total" => count($produtos),
                "dados" => $produtos
            ]);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao carregar produtos da campanha", 500);
        }
    }

    public function campanhaAtivaNivel9(): void
    {
        try {
            self::info("Campanha: carregando destaque nível 9");

            $dados = CampanhaDao::listarDestaquesNivel9();

            self::Mensagemjson("Campanha destaque carregada", 200, $dados);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao carregar campanha destaque", 500);
        }
    }

    // =========================
    // NOTIFICAÇÕES
    // =========================
    public function listarNotificacoes(): void
    {
        try {
            $dados = NotificacaoDao::listar();

            self::Mensagemjson("Lista de notificações", 200, [
                "dados" => $dados ?? []
            ]);
        } catch (\Throwable $th) {
            self::Mensagemjson("Nenhuma notificação encontrada", 200, [
                "dados" => []
            ]);
        }
    }

    public function totalNaoLidas(): void
    {
        try {
            $total = NotificacaoDao::totalNaoLidas();

            self::Mensagemjson([
                "status" => 200,
                "mensagem" => "Total de não lidas",
                "dados" => [
                    "total" => (int)($total ?? 0)
                ]
            ], 200);
        } catch (\Throwable $th) {
            self::Mensagemjson([
                "status" => 200,
                "mensagem" => "Sem notificações",
                "dados" => [
                    "total" => 0
                ]
            ], 200);
        }
    }

    public function marcarComoLida($id): void
    {
        try {
            $ok = NotificacaoDao::marcarComoLida((int)$id);

            self::Mensagemjson(
                $ok ? "Notificação marcada como lida" : "Erro ao atualizar",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao atualizar notificação", 500);
        }
    }
}