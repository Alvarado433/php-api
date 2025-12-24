<?php

namespace App\Dao\Footer;

use Config\BaseDao\BaseDao;
use App\Models\Footer\FooterLink;

class FooterLinkDao extends BaseDao
{
    protected static string $tabela = "footer_links";

    /**
     * Recupera todos os links de um footer
     * @param int $footerId
     * @return FooterLink[]
     */
    public static function getByFooterId(int $footerId): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE footer_id = ? ORDER BY ordem ASC";
        $dados = parent::findAll($sql, [$footerId]);

        $links = [];
        foreach ($dados as $row) {
            $links[] = new FooterLink(
                $row['id_link'],
                $row['footer_id'],
                $row['titulo'],
                $row['url'],
                $row['icone'] ?? null,
                $row['ordem'],
                $row['statusid'],
                $row['criado'],
                $row['atualizado']
            );
        }

        return $links;
    }

    /**
     * Recupera link por ID
     */
    public static function getById(int $id): ?FooterLink
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_link = ?";
        $row = parent::find($sql, [$id]);
        if (!$row) return null;

        return new FooterLink(
            $row['id_link'],
            $row['footer_id'],
            $row['titulo'],
            $row['url'],
            $row['icone'] ?? null,
            $row['ordem'],
            $row['statusid'],
            $row['criado'],
            $row['atualizado']
        );
    }

    /**
     * Inserir novo link
     */
    public static function insert(FooterLink $link): ?int
    {
        $sql = "INSERT INTO " . self::$tabela . " 
            (footer_id, titulo, url, icone, ordem, statusid, criado, atualizado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $ok = parent::execute($sql, [
            $link->getFooterId(),
            $link->getTitulo(),
            $link->getUrl(),
            $link->getIcone(),
            $link->getOrdem(),
            $link->getStatusId(),
            $link->getCriado(),
            $link->getAtualizado()
        ]);

        return $ok ? parent::lastInsertId() : null;
    }

    /**
     * Atualizar link
     */
    public static function update(FooterLink $link): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET 
            titulo=?, url=?, icone=?, ordem=?, statusid=?, atualizado=? 
            WHERE id_link=?";

        return parent::execute($sql, [
            $link->getTitulo(),
            $link->getUrl(),
            $link->getIcone(),
            $link->getOrdem(),
            $link->getStatusId(),
            $link->getAtualizado(),
            $link->getId()
        ]);
    }

    /**
     * Deletar link
     */
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_link=?";
        return parent::execute($sql, [$id]);
    }
}
