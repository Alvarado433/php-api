<?php
/* REGRA 

    [1)nao muda o que esta feito]
    [2] implementa as novasmudanças solicitadas
    [3] mantenha essa mensagem ok
*/

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use Core\Upload\ServidorUpload;

// MODELS
use App\Models\BannerModel;
use App\Models\Cupom\CupomModel;

// DAOs
use App\Dao\Banner\BannerDao;
use App\Dao\Status\StatusDao;
use App\Dao\Produto\ProdutoDao;
use App\Dao\Categoria\CategoriaDao;
use App\Dao\Produto\ProdutoDestaqueDao;
use App\Dao\UsuarioDao\UsuarioDao;
use App\Dao\Cupom\CupomDao;

class DashboardController extends Basecontrolador
{
    // ======================================================
    // 📊 DASHBOARD CARDS
    // ======================================================
    public function listar(): void
    {
        self::info("Dashboard: carregando cards");

        $cards = [
            ["titulo" => "Produtos", "quantidade" => count(ProdutoDao::Todos())],
            ["titulo" => "Categorias", "quantidade" => count(CategoriaDao::listarAtivas())],
            ["titulo" => "Banners", "quantidade" => count(BannerDao::Todos())],
            ["titulo" => "usuarios", "quantidade" => UsuarioDao::contar()],
        ];

        self::Mensagemjson("Dashboard carregado com sucesso", 200, $cards);
    }
    // ======================================================
    // ⚙️ CARDS DE CONFIGURAÇÃO
    // ======================================================
    public function listarCardsConfiguracao(): void
    {
        self::info("Dashboard: carregando cards de configuração");

        $cards = [
            [
                "titulo" => "Configuração de Login",
                "quantidade" => count(\App\Dao\ConfiguracaoDao\ConfiguracaoLoginDao::listar()),
                "rota" => "/admin/configuracoes/gerenciar/login" // cada card terá uma rota própria
            ],

            // Adicione outras configurações se necessário
        ];

        self::Mensagemjson("Cards de configuração carregados com sucesso", 200, $cards);
    }

    public function listarTodasConfiguracoesLogin(): void
    {
        self::info("Dashboard: carregando todas as configurações de login");

        $configs = \App\Dao\ConfiguracaoDao\ConfiguracaoLoginDao::listar();

        // Transforma objetos em arrays para JSON
        $dados = array_map(fn($cfg) => [
            "id" => $cfg->getId(),
            "titulo" => $cfg->getTitulo(),
            "logo" => $cfg->getLogo(),
            "fundo" => $cfg->getFundo(),
            "mensagem_personalizada" => $cfg->getMensagemPersonalizada(),
            "tipo_login_id" => $cfg->getTipoLoginId(),
            "statusid" => $cfg->getStatusId(),
            "criado" => $cfg->getCriado(),
            "atualizado" => $cfg->getAtualizado()
        ], $configs);

        self::Mensagemjson("Configurações de login carregadas com sucesso", 200, $dados);
    }

    // ======================================================
    // 📦 PRODUTOS
    // ======================================================
    public function listartudo(): void
    {
        $produtos = ProdutoDao::listarTodos();
        self::Mensagemjson("Produtos carregados com sucesso", 200, $produtos);
    }

    public static function criarProdutoInterno(array $dados, ?array $arquivo = null): void
    {
        try {
            self::info("Produto: iniciando criação");

            if ($arquivo) {
                $caminhoImagem = ServidorUpload::upload($arquivo, 'produtos');
                if ($caminhoImagem) {
                    $dados['imagem'] = $caminhoImagem;
                    self::info("Produto: imagem salva {$caminhoImagem}");
                }
            }

            if (empty($dados['slug']) && !empty($dados['nome'])) {
                $dados['slug'] = self::gerarSlug($dados['nome']);
            }

            $ok = ProdutoDao::criar($dados);

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

            if (!empty($produto['imagem'])) {
                $caminho = __DIR__ . '/../../public/' . $produto['imagem'];
                if (file_exists($caminho)) unlink($caminho);
            }

            $destaque = ProdutoDestaqueDao::buscar($id);
            if ($destaque) ProdutoDestaqueDao::deletar($destaque['id_destaque']);

            ProdutoDao::deletar($id);
            self::Mensagemjson("Produto removido com sucesso", 200);
        } catch (\Throwable $th) {
            self::Mensagemjson("Erro ao remover produto", 500);
        }
    }

    // ======================================================
    // 🔹 CATEGORIAS
    // ======================================================
    public static function listarCategorias(): void
    {
        $categorias = CategoriaDao::listar();
        self::Mensagemjson("Categorias carregadas", 200, $categorias);
    }

    public function criarCategoria(): void
    {
        $dados = $_POST;
        $nome = trim($dados['nome'] ?? '');

        if (empty($nome)) {
            self::Mensagemjson("Nome obrigatório", 422);
            return;
        }

        CategoriaDao::criar(
            $nome,
            $dados['icone'] ?? '',
            (int)($dados['statusid'] ?? ProdutoDao::status('ativo'))
        );

        self::Mensagemjson("Categoria criada com sucesso", 201);
    }

    public function deletarCategoria(int $id): void
    {
        ProdutoDao::removerCategoriaDosProdutos($id);
        CategoriaDao::deletar($id);
        self::Mensagemjson("Categoria excluída com sucesso", 200);
    }

    // ======================================================
    // ⭐ PRODUTOS EM DESTAQUE
    // ======================================================
    public function listarAtivos(): void
    {
        $destaques = ProdutoDestaqueDao::listarAtivos();
        self::Mensagemjson("Destaques carregados", 200, $destaques);
    }

    public function criarProdutoDestaque(): void
    {
        $dados = json_decode(file_get_contents("php://input"), true);
        $produtoId = (int)($dados['produto_id'] ?? 0);

        $idDestaque = ProdutoDestaqueDao::criar([
            'produto_id' => $produtoId,
            'statusid' => ProdutoDao::status('destaque'),
            'ordem' => 1
        ]);

        ProdutoDao::atualizar($produtoId, ['destaque' => $idDestaque]);
        self::Mensagemjson("Produto adicionado ao destaque", 201);
    }

    public function removerProdutoDestaque(int $id): void
    {
        ProdutoDestaqueDao::deletar($id);
        self::Mensagemjson("Produto removido do destaque", 200);
    }

    // ======================================================
    // 🖼 BANNERS
    // ======================================================
    public function listarBanners(): void
    {
        $banners = BannerDao::listar();
        $lista = array_map(fn($b) => $b->toArray(), $banners);
        self::Mensagemjson("Banners carregados", 200, $lista);
    }

    public function criarBanner(): void
    {
        $arquivo = $_FILES['imagem'] ?? null;
        $dados = $_POST;

        $imagem = ServidorUpload::upload($arquivo, 'banners');

        BannerDao::criar([
            'titulo' => $dados['titulo'],
            'descricao' => $dados['descricao'] ?? '',
            'imagem' => $imagem,
            'link' => $dados['link'] ?? null,
            'statusid' => ProdutoDao::status('ativo')
        ]);

        self::Mensagemjson("Banner criado com sucesso", 201);
    }

    public function removerBanner(int $id): void
    {
        BannerDao::deletar($id);
        self::Mensagemjson("Banner removido com sucesso", 200);
    }

    // ======================================================
    // 🔵 STATUS
    // ======================================================
    public function listarStatus(): void
    {
        $status = StatusDao::listar();
        self::Mensagemjson("Status carregados", 200, $status);
    }

    // ======================================================
    // 🛒 CATÁLOGO
    // ======================================================
    public function marcarCatalogoSim(int $id): void
    {
        ProdutoDao::atualizar($id, ['catalogo' => ProdutoDao::status('catalogo_sim')]);
        self::Mensagemjson("Produto marcado como catálogo", 200);
    }

    public function marcarCatalogoNao(int $id): void
    {
        ProdutoDao::atualizar($id, ['catalogo' => ProdutoDao::status('catalogo_nao')]);
        self::Mensagemjson("Produto removido do catálogo", 200);
    }

    public function listarCatalogo(): void
    {
        $produtos = ProdutoDao::listarCatalogo();
        self::Mensagemjson("Catálogo carregado", 200, $produtos);
    }

    // ======================================================
    // 🎟 CUPONS
    // ======================================================
    public function listarCupons(): void
    {
        self::info("Cupom: listando cupons");
        self::Mensagemjson("Cupons carregados", 200, CupomDao::listar());
    }

    public function listarCuponsAtivos(): void
    {
        self::info("Cupom: listando cupons ativos");
        self::Mensagemjson("Cupons ativos carregados", 200, CupomDao::listarAtivos());
    }

    public function listarCuponsInativos(): void
    {
        self::info("Cupom: listando cupons inativos");
        self::Mensagemjson("Cupons inativos carregados", 200, CupomDao::listarInativos());
    }

    public function criarCupom(): void
    {
        try {
            $dados = self::receberJson();

            $publico = 0; // valor inicial, será ajustado no DAO

            $cupom = new CupomModel(
                null,
                $dados['codigo'],
                $dados['descricao'] ?? '',
                (int)$dados['tipo_id'],
                $dados['desconto'],
                $dados['valor_minimo'] ?? 0,
                $dados['limite_uso'] ?? null,
                $dados['inicio'] ?? null,
                $dados['expiracao'] ?? null,
                $dados['statusid'] ?? 1,
                $publico
            );

            CupomDao::criar($cupom);
            self::success("Cupom criado: {$dados['codigo']}");
            self::Mensagemjson("Cupom criado com sucesso", 201);
        } catch (\Throwable $th) {
            self::error("Cupom erro: " . $th->getMessage());
            self::Mensagemjson("Erro ao criar cupom", 500);
        }
    }

    public function atualizarCupom(int $id): void
    {
        try {
            $dados = self::receberJson();

            $cupom = CupomDao::buscarPorId($id);
            if (!$cupom) {
                self::Mensagemjson("Cupom não encontrado", 404);
                return;
            }

            $modelo = new CupomModel(
                $cupom['id_cupom'],
                $cupom['codigo'],
                $dados['descricao'] ?? $cupom['descricao'],
                $dados['tipo_id'] ?? $cupom['tipo_id'],
                $dados['desconto'] ?? $cupom['desconto'],
                $dados['valor_minimo'] ?? $cupom['valor_minimo'],
                $dados['limite_uso'] ?? $cupom['limite_uso'],
                $dados['inicio'] ?? $cupom['inicio'],
                $dados['expiracao'] ?? $cupom['expiracao'],
                $dados['statusid'] ?? $cupom['statusid'],
                $dados['publico'] ?? $cupom['publico']
            );

            CupomDao::atualizar($modelo);
            self::Mensagemjson("Cupom atualizado com sucesso", 200);
        } catch (\Throwable $th) {
            self::error("Erro ao atualizar cupom: " . $th->getMessage());
            self::Mensagemjson("Erro ao atualizar cupom", 500);
        }
    }

    public function alterarStatusCupom(int $id, int $statusid): void
    {
        CupomDao::alterarStatus($id, $statusid);
        self::Mensagemjson("Status do cupom alterado", 200);
    }

    public function deletarCupom(int $id): void
    {
        try {
            self::info("Cupom: removendo ID {$id}");

            $cupom = CupomDao::buscarPorId($id);
            if (!$cupom) {
                self::Mensagemjson("Cupom não encontrado", 404);
                return;
            }

            CupomDao::deletar($id);

            self::success("Cupom removido com sucesso");
            self::Mensagemjson("Cupom removido com sucesso", 200);
        } catch (\Throwable $th) {
            self::error("Erro ao remover cupom: " . $th->getMessage());
            self::Mensagemjson("Erro interno ao remover cupom", 500);
        }
    }

    // ======================================================
    // 🎟 CUPOM TIPOS
    // ======================================================
    public function listarCupomTipos(): void
    {
        try {
            self::info("CupomTipo: listando tipos de cupom");

            $tipos = \App\Dao\Cupom\CupomTipoDao::listar();

            self::Mensagemjson(
                "Tipos de cupom carregados com sucesso",
                200,
                $tipos
            );
        } catch (\Throwable $th) {
            self::error("CupomTipo erro: " . $th->getMessage());
            self::Mensagemjson("Erro ao listar tipos de cupom", 500);
        }
    }
}
