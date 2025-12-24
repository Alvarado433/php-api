<?php

namespace Imperio\Controllers;

use Core\View\templates;
use App\Dao\Menu\MenuDao;
use Config\Base\Basecontrolador;
use App\Dao\UsuarioDao\UsuarioSessionDao;

class Home extends Basecontrolador
{
    public function index()
    {
        self::Mensagemjson("API funcionando com sucesso", 200, [
            "version" => "1.0.0",
            "status"  => "online"
        ]);
    }

   
}