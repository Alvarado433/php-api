<?php
namespace App\Dao\Carrinho;

use Config\BaseDao\BaseDao;
use App\Models\Carrinho\CarrinhoEndereco;

class CarrinhoEnderecoDao extends BaseDao
{
    protected static string $tabela = 'carrinho_endereco';

    public static function criar(CarrinhoEndereco $endereco): bool
    {
        $sql = "INSERT INTO " . self::$tabela . " 
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

    public static function buscarPorCarrinho(int $carrinhoId): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE carrinho_id = ?";
        return self::find($sql, [$carrinhoId]);
    }

    public static function atualizar(CarrinhoEndereco $endereco): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET 
            cep = ?, rua = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?
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

    public static function deletarPorCarrinho(int $carrinhoId): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE carrinho_id = ?";
        return self::execute($sql, [$carrinhoId]);
    }
}
