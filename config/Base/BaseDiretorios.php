<?php

namespace Config\Base;

class BaseDiretorios
{
    /**
     * Retorna o caminho raiz do projeto.
     * Equivalente ao dirname(__DIR__, x)
     */
    public static function raiz(): string
    {
        // Ajuste a quantidade de níveis conforme a sua estrutura real
        return dirname(__DIR__, 2);
        // Exemplo: Config/nucleo/nucleoenv → sobe 4 níveis até /projeto
    }

    /**
     * Atalho para montar caminhos
     */
    public static function path(string $subcaminho): string
    {
        return self::raiz() . DIRECTORY_SEPARATOR . $subcaminho;
    }
    public static function timezone()
    {
        date_default_timezone_set('America/Sao_Paulo');
    }
}
