<?php
namespace App\Dao\Carrinho;

use Config\BaseDao\BaseDao;
use App\Models\Carrinho\Carrinho;

class CarrinhoDao extends BaseDao
{
    protected static string $tabela = 'carrinho';

    public static function criar(Carrinho $carrinho): bool
    {
        $sql = "INSERT INTO " . self::$tabela . " (usuario_id) VALUES (?)";
        return self::execute($sql, [$carrinho->getUsuarioId()]);
    }

    public static function buscarPorUsuario(int $usuarioId): ?array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE usuario_id = ?";
        return self::find($sql, [$usuarioId]);
    }

    // Cria o carrinho se não existir, ou retorna existente
    public static function criarOuObter(int $usuarioId): int
    {
        $carrinho = self::buscarPorUsuario($usuarioId);
        if ($carrinho) {
            return $carrinho['id_carrinho'];
        }
        $novoCarrinho = new Carrinho($usuarioId);
        self::criar($novoCarrinho);
        return self::lastInsertId();
    }

    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_carrinho = ?";
        return self::execute($sql, [$id]);
    }
}
