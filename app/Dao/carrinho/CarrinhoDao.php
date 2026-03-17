<?php

namespace App\Dao\Carrinho;

use Config\BaseDao\BaseDao;
use App\Models\Carrinho\Carrinho;
use App\Models\Carrinho\CarrinhoEndereco;

class CarrinhoDao extends BaseDao
{
    protected static string $tabela = 'carrinho';

    // =========================================
    // CARRINHO
    // =========================================

    public static function listarTodos(): array
    {
        $sql = "SELECT * FROM " . self::$tabela;
        return self::findAll($sql);
    }

    public static function criar(Carrinho $carrinho): bool
    {
        $sql = "INSERT INTO " . self::$tabela . " (usuario_id) VALUES (?)";
        return self::execute($sql, [$carrinho->getUsuarioId()]);
    }

    public static function buscarPorUsuario(int $usuarioId): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE usuario_id = ? LIMIT 1";
        return self::find($sql, [$usuarioId]);
    }

    public static function buscarPorId(int $carrinhoId): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_carrinho = ? LIMIT 1";
        return self::find($sql, [$carrinhoId]);
    }

    public static function criarOuObter(int $usuarioId): int
    {
        $carrinho = self::buscarPorUsuario($usuarioId);

        if ($carrinho) {
            return (int)$carrinho['id_carrinho'];
        }

        $novoCarrinho = new Carrinho($usuarioId);
        self::criar($novoCarrinho);

        return (int)self::lastInsertId();
    }

    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_carrinho = ?";
        return self::execute($sql, [$id]);
    }

    // =========================================
    // ENDEREÇO DO CARRINHO
    // =========================================

    public static function criarEndereco(CarrinhoEndereco $endereco): bool
    {
        $sql = "INSERT INTO carrinho_endereco
            (carrinho_id, cep, rua, numero, complemento, bairro, cidade, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        return self::execute($sql, [
            $endereco->getCarrinhoId(),
            $endereco->getCep(),
            $endereco->getRua(),
            $endereco->getNumero(),
            $endereco->getComplemento(),
            $endereco->getBairro(),
            $endereco->getCidade(),
            $endereco->getEstado()
        ]);
    }

    public static function buscarEnderecoPorCarrinho(int $carrinhoId): ?array
    {
        $sql = "SELECT * FROM carrinho_endereco WHERE carrinho_id = ? LIMIT 1";
        return self::find($sql, [$carrinhoId]);
    }

    public static function buscarEnderecoPorId(int $enderecoId): ?array
    {
        $sql = "SELECT * FROM carrinho_endereco WHERE id_endereco = ? LIMIT 1";
        return self::find($sql, [$enderecoId]);
    }

    public static function listarEnderecosPorUsuario(int $usuarioId): array
    {
        $sql = "SELECT ce.*
                FROM carrinho_endereco ce
                INNER JOIN carrinho c ON c.id_carrinho = ce.carrinho_id
                WHERE c.usuario_id = ?
                ORDER BY ce.id_endereco DESC";

        return self::findAll($sql, [$usuarioId]);
    }

    public static function atualizarEndereco(CarrinhoEndereco $endereco): bool
    {
        $sql = "UPDATE carrinho_endereco SET
                    cep = ?,
                    rua = ?,
                    numero = ?,
                    complemento = ?,
                    bairro = ?,
                    cidade = ?,
                    estado = ?
                WHERE carrinho_id = ?";

        return self::execute($sql, [
            $endereco->getCep(),
            $endereco->getRua(),
            $endereco->getNumero(),
            $endereco->getComplemento(),
            $endereco->getBairro(),
            $endereco->getCidade(),
            $endereco->getEstado(),
            $endereco->getCarrinhoId()
        ]);
    }

    public static function deletarEnderecoPorCarrinho(int $carrinhoId): bool
    {
        $sql = "DELETE FROM carrinho_endereco WHERE carrinho_id = ?";
        return self::execute($sql, [$carrinhoId]);
    }

    // =========================================
    // ITENS DO CARRINHO
    // =========================================

    public static function listarItensPorCarrinho(int $carrinhoId): array
    {
        $sql = "
            SELECT
                ci.id_item,
                ci.carrinho_id,
                ci.produto_id,
                ci.quantidade,
                ci.preco_unitario,
                p.nome AS nome_produto,
                p.imagem
            FROM carrinho_item ci
            INNER JOIN produto p ON p.id_produto = ci.produto_id
            WHERE ci.carrinho_id = ?
        ";

        return self::findAll($sql, [$carrinhoId]);
    }

    public static function buscarItemPorCarrinhoEProduto(int $carrinhoId, int $produtoId): ?array
    {
        $sql = "
            SELECT *
            FROM carrinho_item
            WHERE carrinho_id = ? AND produto_id = ?
            LIMIT 1
        ";

        return self::find($sql, [$carrinhoId, $produtoId]);
    }

    public static function adicionarItem(
        int $carrinhoId,
        int $produtoId,
        int $quantidade,
        float $precoUnitario
    ): bool {
        $sql = "
            INSERT INTO carrinho_item
            (carrinho_id, produto_id, quantidade, preco_unitario)
            VALUES (?, ?, ?, ?)
        ";

        return self::execute($sql, [
            $carrinhoId,
            $produtoId,
            $quantidade,
            $precoUnitario
        ]);
    }

    public static function atualizarQuantidadeItem(int $idItem, int $quantidade): bool
    {
        $sql = "UPDATE carrinho_item SET quantidade = ? WHERE id_item = ?";
        return self::execute($sql, [$quantidade, $idItem]);
    }

    public static function removerItem(int $idItem): bool
    {
        $sql = "DELETE FROM carrinho_item WHERE id_item = ?";
        return self::execute($sql, [$idItem]);
    }

    public static function limparItens(int $carrinhoId): bool
    {
        $sql = "DELETE FROM carrinho_item WHERE carrinho_id = ?";
        return self::execute($sql, [$carrinhoId]);
    }

    // =========================================
    // RESUMO / TOTAL
    // =========================================

    public static function contarItens(int $carrinhoId): int
    {
        $sql = "SELECT COALESCE(SUM(quantidade), 0) AS total FROM carrinho_item WHERE carrinho_id = ?";
        $resultado = self::find($sql, [$carrinhoId]);

        return (int)($resultado['total'] ?? 0);
    }

    public static function subtotal(int $carrinhoId): float
    {
        $sql = "SELECT COALESCE(SUM(quantidade * preco_unitario), 0) AS subtotal
                FROM carrinho_item
                WHERE carrinho_id = ?";

        $resultado = self::find($sql, [$carrinhoId]);

        return (float)($resultado['subtotal'] ?? 0);
    }

    public static function obterCompletoPorUsuario(int $usuarioId): array
    {
        $carrinho = self::buscarPorUsuario($usuarioId);

        if (!$carrinho) {
            return [
                "carrinho" => null,
                "endereco" => null,
                "itens" => [],
                "quantidade_itens" => 0,
                "subtotal" => 0
            ];
        }

        $carrinhoId = (int)$carrinho["id_carrinho"];
        $itens = self::listarItensPorCarrinho($carrinhoId);

        return [
            "carrinho" => $carrinho,
            "endereco" => self::buscarEnderecoPorCarrinho($carrinhoId),
            "itens" => $itens,
            "quantidade_itens" => self::contarItens($carrinhoId),
            "subtotal" => self::subtotal($carrinhoId)
        ];
    }
}