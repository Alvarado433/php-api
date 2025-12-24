<?php

namespace App\Dao\UsuarioDao;

use App\Models\Usuario\SessaoModel;
use Config\BaseDao\BaseDao;

class UsuarioSessionDao extends BaseDao
{
    protected static string $tabela = "sessao";

    // Criar nova sessão
    public static function criar(SessaoModel $sessao): bool
    {
        $sql = "
            INSERT INTO " . self::$tabela . "
            (usuario_id, token, ip, user_agent, statusid, criado, expira_em)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        return self::execute($sql, [
            $sessao->getUsuarioId(),
            $sessao->getToken(),
            $sessao->getIp(),
            $sessao->getUserAgent(),
            $sessao->getStatusId(),
            $sessao->getCriado(),
            $sessao->getExpiraEm()
        ]);
    }

    /**
     * 🔹 Criar sessão e retornar o ID inserido
     */
    public static function criarRetornandoId(array $dados): ?int
    {
        $sql = "
            INSERT INTO " . self::$tabela . "
            (usuario_id, token, ip, user_agent, statusid, criado, expira_em)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $ok = self::execute($sql, [
            $dados['usuario_id'],
            $dados['token'],
            $dados['ip'],
            $dados['user_agent'],
            $dados['statusid'],
            $dados['criado'],
            $dados['expira_em']
        ]);

        if ($ok) {
            return self::lastInsertId();
        }

        return null;
    }

    // Buscar sessão pelo token
    public static function buscarPorToken(string $token): ?SessaoModel
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE token = ?";
        $row = self::find($sql, [$token]);

        return $row ? self::mapear($row) : null;
    }

    // Buscar todas as sessões de um usuário
    public static function buscarPorUsuario(int $usuarioId): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE usuario_id = ? ORDER BY criado DESC";
        $rows = self::findAll($sql, [$usuarioId]);

        return array_map(fn($row) => self::mapear($row), $rows);
    }

    // Invalida sessão usando o token
    public static function invalidarToken(string $token): bool
    {
        $statusInativo = 2;
        return self::invalidarSessao($token, $statusInativo);
    }

    // Invalida sessão (seta status como inativo)
    public static function invalidarSessao(string $token, int $statusInativo): bool
    {
        $sql = "
            UPDATE " . self::$tabela . "
            SET statusid = ?, expira_em = NOW()
            WHERE token = ?
        ";

        return self::execute($sql, [$statusInativo, $token]);
    }

    // Renovar sessão (atualiza expiração)
    public static function renovarSessao(string $token, string $novaData): bool
    {
        $sql = "
            UPDATE " . self::$tabela . "
            SET expira_em = ?, atualizado = NOW()
            WHERE token = ?
        ";

        return self::execute($sql, [$novaData, $token]);
    }

    // Mapeia array do banco → SessaoModel
    private static function mapear(array $row): SessaoModel
    {
        return new SessaoModel(
            intval($row["id_sessao"]),
            intval($row["usuario_id"]),
            $row["token"],
            $row["ip"],
            $row["user_agent"],
            intval($row["statusid"]),
            $row["criado"],
            $row["expira_em"]
        );
    }
}
