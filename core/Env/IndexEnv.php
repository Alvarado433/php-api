<?php

namespace Core\Env;

use Config\nucleo\nucleoenv\nucleo;
use Config\Base\BaseDiretorios;

class IndexEnv extends nucleo
{
    protected static string $arquivo = ".env";

    /**
     * Carrega variáveis de ambiente
     */
    public static function carregar(): array
    {
        $caminho = BaseDiretorios::path(static::$arquivo);

        // Criar se não existir
        if (!file_exists($caminho)) {
            static::criarArquivo();
        }

        // Carregar valores
        return static::lerEnv();
    }
}
