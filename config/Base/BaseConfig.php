<?php

namespace Config\Base;

class BaseConfig
{
    protected static string $diretorio;
    protected static array $subpasta = [];

    protected static function caminho()
    {
        return dirname(__FILE__, 3) 
            . DIRECTORY_SEPARATOR 
            . static::$diretorio 
            . DIRECTORY_SEPARATOR;
    }

    protected static function criarDiretorio($caminho)
    {
        if (!is_dir($caminho)) {
            mkdir($caminho, 0777, true);
        }
    }

    protected static function criarSubpasta($caminho, string $sub)
    {
        $subCaminho = $caminho . DIRECTORY_SEPARATOR . $sub;

        if (!is_dir($subCaminho)) {
            mkdir($subCaminho, 0777, true);
        }

        return $subCaminho;
    }
}
