<?php

namespace App\Dao\ConfiguracaoDao;

use Config\BaseDao\BaseDao;
use App\Models\Login\ConfiguracaoLogin;
use App\Models\Login\TipoLogin;

class ConfiguracaoLoginDao extends BaseDao
{
    protected static string $tabela = "configuracao_login";

    /**
     * Mapear linha do banco para Model
     */
    private static function mapear(array $row): ConfiguracaoLogin
    {
        $cfg = new ConfiguracaoLogin(
            intval($row["id_config"]),
            $row["titulo"],
            $row["logo"],
            $row["fundo"],
            $row["mensagem_personalizada"],
            intval($row["tipo_login_id"]),
            intval($row["statusid"]),
            $row["criado"],
            $row["atualizado"]
        );

        // Se houver dados do tipo de login, adiciona o objeto TipoLogin
        if (isset($row["login_tipo_nome"])) {
            $tipoLogin = new TipoLogin(
                intval($row["tipo_login_id"]),
                $row["login_tipo_nome"],
                $row["login_tipo_descricao"] ?? null,
                $row["login_tipo_criado"] ?? ""
            );
            $cfg->setTipoLogin($tipoLogin);
        }

        return $cfg;
    }

    /**
     * Buscar configuração por ID
     */
    public static function buscarPorId(int $id): ?ConfiguracaoLogin
    {
        $sql = "
            SELECT cl.*, 
                   lt.nome AS login_tipo_nome, 
                   lt.descricao AS login_tipo_descricao,
                   lt.criado AS login_tipo_criado
            FROM configuracao_login cl
            LEFT JOIN tipo_login lt ON lt.id_tipo = cl.tipo_login_id
            WHERE cl.id_config = ?
        ";
        $row = self::find($sql, [$id]);
        return $row ? self::mapear($row) : null;
    }

    /**
     * Buscar configuração ativa (status = ativo)
     */
    public static function buscarAtiva(): ?ConfiguracaoLogin
    {
        $sql = "
            SELECT cl.*, 
                   lt.nome AS login_tipo_nome, 
                   lt.descricao AS login_tipo_descricao,
                   lt.criado AS login_tipo_criado
            FROM configuracao_login cl
            LEFT JOIN tipo_login lt ON lt.id_tipo = cl.tipo_login_id
            INNER JOIN status s ON s.id_status = cl.statusid
            WHERE s.codigo = 'ativo'
            ORDER BY cl.id_config DESC
            LIMIT 1
        ";
        $row = self::find($sql);
        return $row ? self::mapear($row) : null;
    }

    /**
     * Listar todas as configurações
     */
    public static function listar(): array
    {
        $sql = "
            SELECT cl.*, 
                   lt.nome AS login_tipo_nome, 
                   lt.descricao AS login_tipo_descricao,
                   lt.criado AS login_tipo_criado
            FROM configuracao_login cl
            LEFT JOIN tipo_login lt ON lt.id_tipo = cl.tipo_login_id
            ORDER BY cl.id_config DESC
        ";
        $rows = self::findAll($sql);
        return array_map(fn($row) => self::mapear($row), $rows);
    }

    /**
     * Criar nova configuração
     */
    public static function criar(ConfiguracaoLogin $cfg): bool
    {
        $sql = "
            INSERT INTO configuracao_login 
            (titulo, logo, fundo, mensagem_personalizada, tipo_login_id, statusid, criado, atualizado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        return self::execute($sql, [
            $cfg->getTitulo(),
            $cfg->getLogo(),
            $cfg->getFundo(),
            $cfg->getMensagemPersonalizada(),
            $cfg->getTipoLoginId(),
            $cfg->getStatusId(),
            $cfg->getCriado(),
            $cfg->getAtualizado()
        ]);
    }

    /**
     * Atualizar configuração existente
     */
    public static function atualizar(int $id, ConfiguracaoLogin $cfg): bool
    {
        $sql = "
            UPDATE configuracao_login
            SET titulo = ?, 
                logo = ?, 
                fundo = ?, 
                mensagem_personalizada = ?, 
                tipo_login_id = ?, 
                statusid = ?, 
                atualizado = ?
            WHERE id_config = ?
        ";
        return self::execute($sql, [
            $cfg->getTitulo(),
            $cfg->getLogo(),
            $cfg->getFundo(),
            $cfg->getMensagemPersonalizada(),
            $cfg->getTipoLoginId(),
            $cfg->getStatusId(),
            $cfg->getAtualizado(),
            $id
        ]);
    }

    /**
     * Deletar configuração
     */
    public static function deletar(int $id): bool
    {
        $sql = "DELETE FROM configuracao_login WHERE id_config = ?";
        return self::execute($sql, [$id]);
    }
}
