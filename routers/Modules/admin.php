<?php

namespace Routers\Modules;

use Routers\Inicio\roteamento;

class admin
{
    public static function rotas()
    {
        // Exemplo de rotas (modifique como quiser)
        roteamento::get("/", "Home@index");
       
    }
}