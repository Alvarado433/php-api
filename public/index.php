<?php

use App\teste\tete;
use Core\Logs\Logs;
use Config\http\Cors;
use Core\Env\IndexEnv;

use Database\conexao\conectar;
use Routers\Inicio\roteamento;


require_once __DIR__ . "/../autoload/autoload.php";

// Inicia o autoload
Autoload::register();

IndexEnv::carregar();
conectar::conectar();
Cors::aplicar();


roteamento::next("/v1", "HomeRouter");

roteamento::start();


