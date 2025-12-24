<?php

namespace App\Dao\Footer;

use Config\BaseDao\BaseDao;
use App\Models\Footer\Footer;

class FooterDao extends BaseDao
{
    protected static string $tabela = "footer";

    // ============================
    // FOOTER
    // ============================

    /**
     * Recupera todos os footers ativos
     * @return Footer[]
     */
    public static function getAll(): array
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE statusid = ?";
        $dados = parent::findAll($sql, [1]);

        $footers = [];
        foreach ($dados as $row) {
            $footers[] = new Footer(
                $row['id_footer'],
                $row['logo'],
                $row['titulo'],
                $row['descricao'],
                $row['endereco'],
                $row['icone'] ?? null,
                $row['statusid'],
                $row['criado'],
                $row['atualizado']
            );
        }

        return $footers;
    }

    /**
     * Recupera um footer pelo ID
     */
    public static function getById(int $id): ?Footer
    {
        $sql = "SELECT * FROM " . self::$tabela . " WHERE id_footer = ?";
        $row = parent::find($sql, [$id]);

        if (!$row) return null;

        return new Footer(
            $row['id_footer'],
            $row['logo'],
            $row['titulo'],
            $row['descricao'],
            $row['endereco'],
            $row['icone'] ?? null,
            $row['statusid'],
            $row['criado'],
            $row['atualizado']
        );
    }

    /**
     * Inserir novo Footer
     */
    public static function insert(Footer $footer): ?int
    {
        $sql = "INSERT INTO " . self::$tabela . " 
            (logo, titulo, descricao, endereco, icone, statusid, criado, atualizado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $ok = parent::execute($sql, [
            $footer->getLogo(),
            $footer->getTitulo(),
            $footer->getDescricao(),
            $footer->getEndereco(),
            $footer->getIcone(),
            $footer->getStatusId(),
            $footer->getCriado(),
            $footer->getAtualizado()
        ]);

        return $ok ? parent::lastInsertId() : null;
    }

    /**
     * Atualizar Footer
     */
    public static function update(Footer $footer): bool
    {
        $sql = "UPDATE " . self::$tabela . " SET 
            logo=?, titulo=?, descricao=?, endereco=?, icone=?, statusid=?, atualizado=? 
            WHERE id_footer=?";

        return parent::execute($sql, [
            $footer->getLogo(),
            $footer->getTitulo(),
            $footer->getDescricao(),
            $footer->getEndereco(),
            $footer->getIcone(),
            $footer->getStatusId(),
            $footer->getAtualizado(),
            $footer->getId()
        ]);
    }

    /**
     * Deletar Footer
     */
    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM " . self::$tabela . " WHERE id_footer=?";
        return parent::execute($sql, [$id]);
    }
}
