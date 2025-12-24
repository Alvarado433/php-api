<?php

namespace App\Dao\Seguraca;

use Config\BaseDao\BaseDao;
use App\Models\SegurancaConfig;

class SegurancaConfigDAO extends BaseDao
{
    protected static string $tabela = "seguranca_config";

    /**
     * =========================================================
     * 🔍 VERIFICAR SE JÁ EXISTE CONFIGURAÇÃO
     * =========================================================
     */
    public static function existeConfig(): bool
    {
        $sql = "SELECT id FROM " . self::$tabela . " LIMIT 1";
        $linha = parent::find($sql);
        return $linha ? true : false;
    }

    /**
     * =========================================================
     * 🆕 CRIAR CONFIGURAÇÃO INICIAL
     * =========================================================
     */
    public static function criarConfig(string $pinHash, int $tentativasMax, int $statusid): bool
    {
        $sql = "INSERT INTO " . self::$tabela . "
                (id, pin_sistema, tentativas_max, statusid)
                VALUES (1, ?, ?, ?)";

        return parent::execute($sql, [
            $pinHash,
            $tentativasMax,
            $statusid
        ]);
    }

    /**
     * =========================================================
     * 🔍 BUSCAR CONFIGURAÇÃO COMPLETA
     * =========================================================
     */
    public static function getConfig(): ?SegurancaConfig
    {
        $sql = "SELECT id, pin_sistema, tentativas_max, statusid, criado, atualizado
                FROM " . self::$tabela . "
                WHERE id = 1
                LIMIT 1";

        $linha = parent::find($sql);

        if (!$linha) {
            return null;
        }

        return new SegurancaConfig(
            (int)$linha["id"],
            $linha["pin_sistema"],
            (int)$linha["tentativas_max"],
            (int)$linha["statusid"],
            $linha["criado"],
            $linha["atualizado"]
        );
    }

    /**
     * =========================================================
     * 🔄 ATUALIZAR STATUS DO SISTEMA
     * =========================================================
     */
    public static function atualizarStatus(int $statusid): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET statusid = ? WHERE id = 1";
        return parent::execute($sql, [$statusid]);
    }

    /**
     * =========================================================
     * 🔄 ATUALIZAR LIMITE DE TENTATIVAS (Manual)
     * =========================================================
     */
    public static function atualizarTentativasMax(int $tentativas): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET tentativas_max = ? WHERE id = 1";
        return parent::execute($sql, [$tentativas]);
    }

    /**
     * =========================================================
     * 🔐 ATUALIZAR PIN
     * =========================================================
     */
    public static function atualizarPin(string $novoPinCriptografado): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET pin_sistema = ? WHERE id = 1";
        return parent::execute($sql, [$novoPinCriptografado]);
    }

    /**
     * =========================================================
     * ➕ INCREMENTAR TENTATIVAS (cada erro de PIN)
     * =========================================================
     */
    public static function incrementarTentativas(): bool
    {
        $sql = "UPDATE " . self::$tabela . "
                SET tentativas_max = tentativas_max + 1
                WHERE id = 1";
        return parent::execute($sql);
    }

    /**
     * =========================================================
     * 🔢 OBTER QUANTIDADE DE TENTATIVAS ATUAL
     * =========================================================
     */
    public static function getTentativas(): int
    {
        $sql = "SELECT tentativas_max FROM " . self::$tabela . " WHERE id = 1";
        $linha = parent::find($sql);
        return $linha ? (int)$linha["tentativas_max"] : 0;
    }

    /**
     * =========================================================
     * 🔄 RESETAR CONTADOR DE TENTATIVAS
     * =========================================================
     */
    public static function resetarTentativas(): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET tentativas_max = 0 WHERE id = 1";
        return parent::execute($sql);
    }
}
