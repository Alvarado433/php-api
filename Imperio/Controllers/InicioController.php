<?php

namespace Imperio\Controllers;

use App\Dao\Banner\BannerDao;
use App\Dao\Carrinho\CarrinhoDao;
use App\Dao\Cupom\CupomDao;
use App\Dao\Menu\MenuDao;
use App\Dao\pedido\PedidoDao;
use App\Dao\Produto\ProdutoDao;
use App\Dao\UsuarioDao\UsuarioDao;
use App\Dao\UsuarioDao\UsuarioSessionDao;
use App\Models\BannerModel;
use App\Models\Carrinho\CarrinhoEndereco;
use App\Models\Carrinho\Pedido;
use App\Models\Carrinho\PedidoItem;
use Config\Base\Basecontrolador;
use Core\Pagamento\MercadoPagoService;



class InicioController extends Basecontrolador
{
    /* ============================================================
     * HOME
     * ============================================================ */
    public function index()
    {
        return self::Mensagemjson("API funcionando com sucesso", 200, [
            "version" => "1.0.0",
            "status"  => "online"
        ]);
    }

    /* ============================================================
     * HELPERS
     * ============================================================ */
    private function normalizarPreco($valor): float
    {
        if ($valor === null) {
            return 0.0;
        }

        if (is_int($valor) || is_float($valor)) {
            return (float)$valor;
        }

        $valor = trim((string)$valor);

        if ($valor === "") {
            return 0.0;
        }

        $valor = preg_replace('/[^\d,.\-]/', '', $valor);

        if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (strpos($valor, ',') !== false) {
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float)$valor : 0.0;
    }

    private function getUsuarioAutenticado(): array
    {
        $token = $_COOKIE["imperio_session"] ?? null;

        if (!$token) {
            throw new \Exception("Usuário não autenticado.");
        }

        $sessao = UsuarioSessionDao::buscarPorToken($token);

        if (!$sessao || (int)$sessao->getStatusId() !== 1) {
            throw new \Exception("Sessão inválida.");
        }

        if (new \DateTime() > new \DateTime($sessao->getExpiraEm())) {
            UsuarioSessionDao::invalidarToken($token);
            throw new \Exception("Sessão expirada.");
        }

        $usuario = UsuarioDao::buscarPorId((int)$sessao->getUsuarioId());

        if (!$usuario) {
            throw new \Exception("Usuário não encontrado.");
        }

        return [
            "id" => (int)$usuario->getId(),
            "nome" => $usuario->getNome(),
            "email" => $usuario->getEmail(),
            "nivel_id" => (int)$usuario->getNivelId(),
        ];
    }

    /* ============================================================
     * NAVBAR
     * ============================================================ */
    public function navbar()
    {
        try {
            $menus = MenuDao::listar();

            if (empty($menus)) {
                return self::Mensagemjson("Nenhum menu encontrado", 200, [
                    "titulo" => "Universo Império",
                    "subtitulo" => "Decorações & Eventos",
                    "menus" => []
                ]);
            }

            $menusFormatados = array_map(function ($menu) {
                return [
                    "id" => $menu["id"] ?? null,
                    "titulo" => $menu["titulo"] ?? $menu["nome"] ?? null,
                    "icone" => $menu["icone"] ?? null,
                    "rota" => $menu["rota"] ?? null,
                    "pesquisa_placeholder" => $menu["pesquisa_placeholder"] ?? null,
                    "permissoes" => $menu["permissoes"] ?? [],
                    "itens" => isset($menu["itens"]) ? array_map(function ($item) {
                        return [
                            "id" => $item["id"] ?? null,
                            "titulo" => $item["titulo"] ?? $item["nome"] ?? null,
                            "rota" => $item["rota"] ?? null,
                            "icone" => $item["icone"] ?? null,
                            "posicao" => $item["posicao"] ?? 0,
                            "permissoes" => $item["permissoes"] ?? []
                        ];
                    }, $menu["itens"]) : []
                ];
            }, $menus);

            return self::Mensagemjson(
                "Navbar carregada com sucesso",
                200,
                [
                    "titulo" => "Universo Império",
                    "subtitulo" => "Decorações & Eventos",
                    "menus" => $menusFormatados
                ]
            );
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao carregar navbar", 500, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    /* ============================================================
     * MENUS
     * ============================================================ */
    public function listar()
    {
        try {
            $menus = MenuDao::listar();

            if (empty($menus)) {
                return self::Mensagemjson("Nenhum menu encontrado", 200, [
                    "cards" => []
                ]);
            }

            $cards = array_map(function ($m) {
                $data = is_object($m) && method_exists($m, "toArray")
                    ? $m->toArray()
                    : (array)$m;

                return [
                    "id" => (int)($data["id"] ?? 0),
                    "titulo" => $data["nome"] ?? $data["titulo"] ?? null,
                    "icone" => $data["icone"] ?? null,
                    "rota" => $data["rota"] ?? null,
                    "pesquisa_placeholder" => $data["pesquisa_placeholder"] ?? null,
                ];
            }, $menus);

            return self::Mensagemjson("Menus retornados", 200, [
                "cards" => $cards
            ]);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao listar menus", 500, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    /* ============================================================
     * BANNERS
     * ============================================================ */
    public function listarBanners()
    {
        try {
            $banners = BannerDao::listar();

            if (empty($banners)) {
                return self::Mensagemjson("Nenhum banner encontrado", 200, []);
            }

            $lista = array_map(fn($b) => $b->toArray(), $banners);

            return self::Mensagemjson("Lista de banners", 200, $lista);
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao listar banners: " . $e->getMessage(), 500);
        }
    }

    public function ativos()
    {
        try {
            $banners = BannerDao::listarAtivos();

            if (empty($banners)) {
                return self::Mensagemjson("Nenhum banner ativo encontrado", 200, []);
            }

            $lista = array_map(fn($b) => $b->toArray(), $banners);

            return self::Mensagemjson("Banners ativos", 200, $lista);
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao listar banners ativos: " . $e->getMessage(), 500);
        }
    }

    public function bannersAtivos()
    {
        return $this->ativos();
    }

    public function ativosEDestaque()
    {
        try {
            $banners = BannerDao::listarAtivosEDestaque();

            if (empty($banners)) {
                return self::Mensagemjson("Nenhum banner ativo ou destaque encontrado", 200, []);
            }

            $lista = array_map(fn($b) => $b->toArray(), $banners);

            return self::Mensagemjson("Banners ativos e destaque", 200, $lista);
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao listar banners ativos e destaque: " . $e->getMessage(), 500);
        }
    }

    public function buscarBanner($id)
    {
        return $this->buscar($id);
    }

    public function buscar($id)
    {
        try {
            $banner = BannerDao::buscarPorId((int)$id);

            if (!$banner) {
                return self::Mensagemjson("Banner não encontrado", 404);
            }

            return self::Mensagemjson("Banner encontrado", 200, $banner->toArray());
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao buscar banner: " . $e->getMessage(), 500);
        }
    }

    public function atualizarBanner($id)
    {
        return $this->atualizar($id);
    }

    public function atualizar($id)
    {
        try {
            $dados = self::receberJson();

            if (empty($dados["titulo"]) || empty($dados["imagem"])) {
                return self::Mensagemjson("Título e imagem são obrigatórios", 422);
            }

            $banner = new BannerModel(
                $dados["titulo"],
                $dados["descricao"] ?? "",
                $dados["imagem"],
                $dados["link"] ?? null
            );

            $ok = BannerDao::atualizar((int)$id, $banner->toArray());

            if (!$ok) {
                return self::Mensagemjson("Erro ao atualizar banner", 500);
            }

            return self::Mensagemjson("Banner atualizado com sucesso", 200, $banner->toArray());
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao atualizar banner: " . $e->getMessage(), 500);
        }
    }

    public function deletarBanner($id)
    {
        return $this->deletar($id);
    }

    public function deletar($id)
    {
        try {
            $ok = BannerDao::deletar((int)$id);

            if (!$ok) {
                return self::Mensagemjson("Erro ao deletar banner", 500);
            }

            return self::Mensagemjson("Banner deletado com sucesso", 200);
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao deletar banner: " . $e->getMessage(), 500);
        }
    }

    public function criarBanner()
    {
        return $this->criar();
    }

    public function criar()
    {
        try {
            $dados = self::receberJson();

            if (empty($dados["titulo"]) || empty($dados["imagem"])) {
                return self::Mensagemjson("Título e imagem são obrigatórios", 422);
            }

            $banner = new BannerModel(
                $dados["titulo"],
                $dados["descricao"] ?? "",
                $dados["imagem"],
                $dados["link"] ?? null
            );

            $ok = BannerDao::criar($banner->toArray());

            if (!$ok) {
                return self::Mensagemjson("Erro ao criar banner", 500);
            }

            return self::Mensagemjson("Banner criado com sucesso", 201, $banner->toArray());
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao criar banner: " . $e->getMessage(), 500);
        }
    }

    public function incrementarBannerView($id)
    {
        return $this->incrementarVisualizacao($id);
    }

    public function incrementarVisualizacao($id)
    {
        try {
            $ok = BannerDao::incrementarVisualizacao((int)$id);

            if (!$ok) {
                return self::Mensagemjson("Erro ao incrementar visualização", 500);
            }

            return self::Mensagemjson("Visualização incrementada", 200);
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao incrementar visualização: " . $e->getMessage(), 500);
        }
    }

    public function incrementarBannerClick($id)
    {
        return $this->incrementarClique($id);
    }

    public function incrementarClique($id)
    {
        try {
            $ok = BannerDao::incrementarClique((int)$id);

            if (!$ok) {
                return self::Mensagemjson("Erro ao incrementar clique", 500);
            }

            return self::Mensagemjson("Clique incrementado", 200);
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao incrementar clique: " . $e->getMessage(), 500);
        }
    }

    /* ============================================================
     * CUPONS
     * ============================================================ */
    public function listarCupons(): void
    {
        try {
            $cupons = CupomDao::listar();
            self::Mensagemjson("Cupons listados com sucesso", 200, $cupons);
        } catch (\Throwable $th) {
            self::error("Erro ao listar cupons: " . $th->getMessage());
            self::Mensagemjson("Erro ao listar cupons", 500);
        }
    }

    public function listarCuponsAtivos(): void
    {
        try {
            $cupons = CupomDao::listarAtivos();
            self::Mensagemjson("Cupons ativos listados com sucesso", 200, $cupons);
        } catch (\Throwable $th) {
            self::error("Erro ao listar cupons ativos: " . $th->getMessage());
            self::Mensagemjson("Erro ao listar cupons ativos", 500);
        }
    }

    public function listarCuponsInativos(): void
    {
        try {
            $cupons = CupomDao::listarInativos();
            self::Mensagemjson("Cupons inativos listados com sucesso", 200, $cupons);
        } catch (\Throwable $th) {
            self::error("Erro ao listar cupons inativos: " . $th->getMessage());
            self::Mensagemjson("Erro ao listar cupons inativos", 500);
        }
    }

    public function buscarCupomPorCodigo(string $codigo): void
    {
        try {
            $cupom = CupomDao::buscarPorCodigo($codigo);

            if (!$cupom) {
                self::Mensagemjson("Cupom não encontrado ou inválido", 404);
                return;
            }

            self::Mensagemjson("Cupom encontrado", 200, $cupom);
        } catch (\Throwable $th) {
            self::error("Erro ao buscar cupom: " . $th->getMessage());
            self::Mensagemjson("Erro ao buscar cupom", 500);
        }
    }

    /* ============================================================
     * CAMPANHAS
     * ============================================================ */
    public function campanhaAtiva(string $slug)
    {
        try {
            $statusCodigo = isset($_GET["status"]) ? (string)$_GET["status"] : null;

            if ($statusCodigo) {
                $campanha = \App\Dao\Campanha\CampanhaDao::buscarAtivaPorSlug($slug, $statusCodigo);
            } else {
                $campanha = \App\Dao\Campanha\CampanhaDao::buscarAtivaPorSlugSemStatus($slug);
            }

            if (!$campanha) {
                return self::Mensagemjson("Campanha não encontrada", 404);
            }

            $produtos = \App\Dao\Campanha\CampanhaDao::listarProdutosDaCampanha(
                (int)$campanha["id_campanha"]
            );

            return self::Mensagemjson("Campanha carregada com sucesso", 200, [
                "campanha" => $campanha,
                "produtos" => $produtos
            ]);
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao carregar campanha", 500, [
                "erro" => $e->getMessage()
            ]);
        }
    }

    /* ============================================================
     * CARRINHO
     * ============================================================ */
    public function buscarCarrinho()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $resumo = CarrinhoDao::obterCompletoPorUsuario($usuario["id"]);

            return self::Mensagemjson("Carrinho carregado com sucesso", 200, $resumo);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao buscar carrinho", 401, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function listarItensCarrinho()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $carrinhoId = CarrinhoDao::criarOuObter($usuario["id"]);
            $itens = CarrinhoDao::listarItensPorCarrinho($carrinhoId);

            return self::Mensagemjson("Itens do carrinho carregados com sucesso", 200, [
                "carrinho_id" => $carrinhoId,
                "total" => count($itens),
                "itens" => $itens,
                "subtotal" => CarrinhoDao::subtotal($carrinhoId)
            ]);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao listar itens do carrinho", 401, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function adicionarItemCarrinho()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $dados = self::receberJson();

            $produtoId = (int)($dados["produto_id"] ?? 0);
            $quantidade = max(1, (int)($dados["quantidade"] ?? 1));

            if ($produtoId <= 0) {
                return self::Mensagemjson("produto_id inválido", 422);
            }

            $produto = ProdutoDao::buscar($produtoId);

            if (!$produto) {
                return self::Mensagemjson("Produto não encontrado", 404);
            }

            $precoPromocional = $this->normalizarPreco($produto["preco_promocional"] ?? null);
            $precoNormal = $this->normalizarPreco($produto["preco"] ?? null);
            $preco = $precoPromocional > 0 ? $precoPromocional : $precoNormal;

            if ($preco <= 0) {
                return self::Mensagemjson("Preço do produto inválido", 422, [
                    "produto_id" => $produtoId,
                    "preco_recebido" => $produto["preco"] ?? null,
                    "preco_promocional_recebido" => $produto["preco_promocional"] ?? null,
                ]);
            }

            $carrinhoId = CarrinhoDao::criarOuObter($usuario["id"]);
            $itemExistente = CarrinhoDao::buscarItemPorCarrinhoEProduto($carrinhoId, $produtoId);

            if ($itemExistente) {
                $novaQuantidade = (int)$itemExistente["quantidade"] + $quantidade;
                $ok = CarrinhoDao::atualizarQuantidadeItem((int)$itemExistente["id_item"], $novaQuantidade);
            } else {
                $ok = CarrinhoDao::adicionarItem($carrinhoId, $produtoId, $quantidade, $preco);
            }

            if (!$ok) {
                return self::Mensagemjson("Erro ao salvar item no carrinho", 500);
            }

            return self::Mensagemjson("Item adicionado ao carrinho com sucesso", 200, [
                "carrinho_id" => $carrinhoId,
                "produto_id" => $produtoId,
                "preco_usado" => $preco,
                "quantidade_itens" => CarrinhoDao::contarItens($carrinhoId),
                "subtotal" => CarrinhoDao::subtotal($carrinhoId)
            ]);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao adicionar item ao carrinho", 500, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function atualizarQuantidadeItemCarrinho(int $id)
    {
        try {
            $this->getUsuarioAutenticado();
            $dados = self::receberJson();

            $quantidade = (int)($dados["quantidade"] ?? 0);

            if ($quantidade <= 0) {
                return self::Mensagemjson("Quantidade inválida", 422);
            }

            $ok = CarrinhoDao::atualizarQuantidadeItem($id, $quantidade);

            return self::Mensagemjson(
                $ok ? "Quantidade atualizada com sucesso" : "Erro ao atualizar quantidade",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao atualizar item do carrinho", 401, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function removerItemCarrinho(int $id)
    {
        try {
            $this->getUsuarioAutenticado();

            $ok = CarrinhoDao::removerItem($id);

            return self::Mensagemjson(
                $ok ? "Item removido do carrinho com sucesso" : "Erro ao remover item do carrinho",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao remover item do carrinho", 401, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function limparCarrinho()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $carrinhoId = CarrinhoDao::criarOuObter($usuario["id"]);

            $ok = CarrinhoDao::limparItens($carrinhoId);

            return self::Mensagemjson(
                $ok ? "Carrinho limpo com sucesso" : "Erro ao limpar carrinho",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao limpar carrinho", 401, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function buscarEnderecoCarrinho()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $carrinhoId = CarrinhoDao::criarOuObter($usuario["id"]);
            $endereco = CarrinhoDao::buscarEnderecoPorCarrinho($carrinhoId);

            if (!$endereco) {
                return self::Mensagemjson("Nenhum endereço encontrado para o carrinho", 200, []);
            }

            return self::Mensagemjson("Endereço do carrinho carregado com sucesso", 200, $endereco);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao buscar endereço do carrinho", 401, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function listarEnderecosCarrinho()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $enderecos = CarrinhoDao::listarEnderecosPorUsuario($usuario["id"]);

            return self::Mensagemjson("Endereços carregados com sucesso", 200, [
                "total" => count($enderecos),
                "enderecos" => $enderecos
            ]);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao listar endereços", 401, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function salvarEnderecoCarrinho()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $carrinhoId = CarrinhoDao::criarOuObter($usuario["id"]);
            $dados = self::receberJson();

            $cep = trim((string)($dados["cep"] ?? ""));
            $rua = trim((string)($dados["rua"] ?? ""));
            $numero = trim((string)($dados["numero"] ?? ""));
            $complemento = trim((string)($dados["complemento"] ?? ""));
            $bairro = trim((string)($dados["bairro"] ?? ""));
            $cidade = trim((string)($dados["cidade"] ?? ""));
            $estado = trim((string)($dados["estado"] ?? ""));

            if ($cep === "" || $rua === "" || $numero === "" || $bairro === "" || $cidade === "" || $estado === "") {
                return self::Mensagemjson("Preencha os campos obrigatórios do endereço", 422);
            }

            $existente = CarrinhoDao::buscarEnderecoPorCarrinho($carrinhoId);

            $endereco = new CarrinhoEndereco(
                $carrinhoId,
                $cep,
                $rua,
                $numero,
                $complemento,
                $bairro,
                $cidade,
                $estado
            );

            $ok = $existente
                ? CarrinhoDao::atualizarEndereco($endereco)
                : CarrinhoDao::criarEndereco($endereco);

            return self::Mensagemjson(
                $ok ? "Endereço salvo com sucesso" : "Erro ao salvar endereço",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao salvar endereço do carrinho", 401, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function atualizarEnderecoCarrinho()
    {
        return $this->salvarEnderecoCarrinho();
    }

    public function removerEnderecoCarrinho()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $carrinhoId = CarrinhoDao::criarOuObter($usuario["id"]);

            $ok = CarrinhoDao::deletarEnderecoPorCarrinho($carrinhoId);

            return self::Mensagemjson(
                $ok ? "Endereço removido com sucesso" : "Erro ao remover endereço",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao remover endereço do carrinho", 401, [
                "erro" => $th->getMessage()
            ]);
        }
    }
    public function gerarPixCarrinho()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $resumo = CarrinhoDao::obterCompletoPorUsuario($usuario["id"]);

            if (empty($resumo["itens"])) {
                return self::Mensagemjson("Carrinho vazio", 422);
            }

            $subtotal = (float)($resumo["subtotal"] ?? 0);

            if ($subtotal <= 0) {
                return self::Mensagemjson("Subtotal do carrinho inválido", 422);
            }

            $descricao = "Pagamento do carrinho - Universo Império";
            $externalReference = "carrinho_usuario_" . $usuario["id"] . "_" . time();

            $mp = new MercadoPagoService();

            $pix = $mp->criarPagamentoPix(
                $subtotal,
                $descricao,
                [
                    "nome" => $usuario["nome"],
                    "email" => $usuario["email"]
                ],
                $externalReference
            );

            return self::Mensagemjson("PIX gerado com sucesso", 200, [
                "carrinho" => $resumo["carrinho"] ?? null,
                "quantidade_itens" => $resumo["quantidade_itens"] ?? 0,
                "subtotal" => $subtotal,
                "pagamento" => $pix
            ]);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao gerar PIX do carrinho", 500, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function webhookMercadoPago()
    {
        try {
            $body = file_get_contents("php://input");
            $headers = function_exists("getallheaders") ? getallheaders() : [];

            file_put_contents(
                __DIR__ . "/../../webhook_mercadopago.log",
                date("Y-m-d H:i:s") . PHP_EOL .
                    "HEADERS: " . json_encode($headers, JSON_UNESCAPED_UNICODE) . PHP_EOL .
                    "BODY: " . $body . PHP_EOL .
                    "GET: " . json_encode($_GET, JSON_UNESCAPED_UNICODE) . PHP_EOL .
                    "POST: " . json_encode($_POST, JSON_UNESCAPED_UNICODE) . PHP_EOL .
                    "----------------------------------------" . PHP_EOL,
                FILE_APPEND
            );

            http_response_code(200);

            echo json_encode([
                "status" => "ok"
            ]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);

            echo json_encode([
                "erro" => $e->getMessage()
            ]);
            exit;
        }
    }

    /* ============================================================
 * PEDIDOS
 * ============================================================ */

    public function listarPedidos()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $pedidos = PedidoDao::listarPorUsuario($usuario["id"]);

            return self::Mensagemjson("Pedidos carregados com sucesso", 200, [
                "total" => count($pedidos),
                "pedidos" => $pedidos
            ]);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao listar pedidos", 500, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function buscarPedido($id)
    {
        if (!is_numeric($id)) {
            return self::Mensagemjson("ID do pedido inválido", 422);
        }

        $id = (int)$id;

        try {
            $usuario = $this->getUsuarioAutenticado();
            $pedido = PedidoDao::buscar($id);

            if (!$pedido) {
                return self::Mensagemjson("Pedido não encontrado", 404);
            }

            if ((int)$pedido["usuario_id"] !== (int)$usuario["id"] && (int)$usuario["nivel_id"] !== 1) {
                return self::Mensagemjson("Você não tem permissão para acessar este pedido", 403);
            }

            return self::Mensagemjson("Pedido carregado com sucesso", 200, $pedido);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao buscar pedido", 500, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function listarItensPedido($id)
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $pedido = PedidoDao::buscar($id);

            if (!$pedido) {
                return self::Mensagemjson("Pedido não encontrado", 404);
            }

            if ((int)$pedido["usuario_id"] !== (int)$usuario["id"] && (int)$usuario["nivel_id"] !== 1) {
                return self::Mensagemjson("Você não tem permissão para acessar este pedido", 403);
            }

            $itens = PedidoDao::listarItens($id);

            return self::Mensagemjson("Itens do pedido carregados com sucesso", 200, [
                "pedido" => $pedido,
                "total_itens" => count($itens),
                "itens" => $itens
            ]);
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao listar itens do pedido", 500, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function finalizarPedido()
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $dados = self::receberJson();

            $metodoPagamento = trim((string)($dados["metodo_pagamento"] ?? "pix"));
            $pagamentoInfo = $dados["pagamento_info"] ?? null;

            if (!in_array($metodoPagamento, ["pix", "cartao"], true)) {
                return self::Mensagemjson("Método de pagamento inválido", 422);
            }

            $resumo = CarrinhoDao::obterCompletoPorUsuario($usuario["id"]);

            if (empty($resumo["carrinho"])) {
                return self::Mensagemjson("Carrinho não encontrado", 404);
            }

            if (empty($resumo["itens"])) {
                return self::Mensagemjson("Carrinho vazio", 422);
            }

            $endereco = $resumo["endereco"] ?? null;

            if (!$endereco || empty($endereco["id_endereco"])) {
                return self::Mensagemjson("Endereço não encontrado", 422);
            }

            $subtotal = (float)($resumo["subtotal"] ?? 0);
            $frete = 0.0;
            $total = $subtotal + $frete;

            if ($total <= 0) {
                return self::Mensagemjson("Total do pedido inválido", 422);
            }

            $statusId = 11; // pagamento_pendente

            $pedido = new Pedido(
                (int)$usuario["id"],
                (int)$statusId,
                (float)$total,
                (float)$frete,
                (int)$endereco["id_endereco"],
                $metodoPagamento,
                ""
            );

            $ok = PedidoDao::criar($pedido);

            if (!$ok) {
                return self::Mensagemjson("Erro ao criar pedido", 500);
            }

            $pedidoId = (int)PedidoDao::lastInsertId();

            if ($pedidoId <= 0) {
                return self::Mensagemjson("Erro ao obter ID do pedido", 500);
            }

            foreach ($resumo["itens"] as $item) {
                $pedidoItem = new \App\Models\Carrinho\PedidoItem(
                    $pedidoId,
                    (int)$item["produto_id"],
                    (int)$item["quantidade"],
                    (float)$item["preco_unitario"]
                );

                $okItem = PedidoDao::adicionarItem($pedidoItem);

                if (!$okItem) {
                    return self::Mensagemjson("Erro ao salvar itens do pedido", 500, [
                        "pedido_id" => $pedidoId
                    ]);
                }
            }

            // =========================================================
            // PIX
            // =========================================================
            if ($metodoPagamento === "pix") {
                $mp = new MercadoPagoService();
                $externalReference = "pedido_" . $pedidoId;

                $pix = $mp->criarPagamentoPix(
                    $total,
                    "Pedido #" . $pedidoId . " - Universo Império",
                    [
                        "nome" => $usuario["nome"],
                        "email" => $usuario["email"]
                    ],
                    $externalReference
                );

                if (!$pix || !is_array($pix)) {
                    return self::Mensagemjson("Erro ao gerar PIX", 500);
                }

                $salvarPagamento = [
                    "pix_id" => $pix["id"] ?? null,
                    "qr_code" => $pix["qr_code"] ?? null,
                    "qr_code_base64" => $pix["qr_code_base64"] ?? null,
                    "ticket_url" => $pix["ticket_url"] ?? null,
                    "external_reference" => $externalReference
                ];

                PedidoDao::salvarPagamentoInfo($pedidoId, $salvarPagamento);

                CarrinhoDao::limparItens((int)$resumo["carrinho"]["id_carrinho"]);

                return self::Mensagemjson("Pedido criado e PIX gerado", 200, [
                    "pedido_id" => $pedidoId,
                    "metodo_pagamento" => "pix",
                    "pagamento" => [
                        "id" => $pix["id"] ?? null,
                        "qr_code" => $pix["qr_code"] ?? null,
                        "qr_code_base64" => $pix["qr_code_base64"] ?? null,
                        "ticket_url" => $pix["ticket_url"] ?? null,
                        "external_reference" => $externalReference
                    ]
                ]);
            }

            // =========================================================
            // CARTÃO
            // =========================================================
            if ($metodoPagamento === "cartao") {
                PedidoDao::salvarPagamentoInfo($pedidoId, [
                    "cartao" => $pagamentoInfo,
                    "external_reference" => "pedido_" . $pedidoId
                ]);

                CarrinhoDao::limparItens((int)$resumo["carrinho"]["id_carrinho"]);

                return self::Mensagemjson("Pedido criado com sucesso", 200, [
                    "pedido_id" => $pedidoId,
                    "metodo_pagamento" => "cartao"
                ]);
            }

            return self::Mensagemjson("Método de pagamento inválido", 422);
        } catch (\Throwable $e) {
            return self::Mensagemjson("Erro ao finalizar pedido", 500, [
                "erro" => $e->getMessage()
            ]);
        }
    }

    public function alterarStatusPedido(int $id)
    {
        try {
            $usuario = $this->getUsuarioAutenticado();

            if ((int)$usuario["nivel_id"] !== 1) {
                return self::Mensagemjson("Somente administrador pode alterar status do pedido", 403);
            }

            $pedido = PedidoDao::buscar($id);

            if (!$pedido) {
                return self::Mensagemjson("Pedido não encontrado", 404);
            }

            $dados = self::receberJson();
            $statusId = (int)($dados["statusid"] ?? 0);

            if ($statusId <= 0) {
                return self::Mensagemjson("statusid inválido", 422);
            }

            $ok = PedidoDao::alterarStatus($id, $statusId);

            return self::Mensagemjson(
                $ok ? "Status do pedido atualizado com sucesso" : "Erro ao atualizar status do pedido",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao alterar status do pedido", 500, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    public function deletarPedido(int $id)
    {
        try {
            $usuario = $this->getUsuarioAutenticado();
            $pedido = PedidoDao::buscar($id);

            if (!$pedido) {
                return self::Mensagemjson("Pedido não encontrado", 404);
            }

            if ((int)$usuario["nivel_id"] !== 1 && (int)$pedido["usuario_id"] !== (int)$usuario["id"]) {
                return self::Mensagemjson("Você não tem permissão para remover este pedido", 403);
            }

            $ok = PedidoDao::deletar($id);

            return self::Mensagemjson(
                $ok ? "Pedido removido com sucesso" : "Erro ao remover pedido",
                $ok ? 200 : 500
            );
        } catch (\Throwable $th) {
            return self::Mensagemjson("Erro ao deletar pedido", 500, [
                "erro" => $th->getMessage()
            ]);
        }
    }

    
}
