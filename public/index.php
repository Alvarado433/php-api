<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * ===============================
 * 🔹 CARREGA AUTOLOAD DO COMPOSER
 * ===============================
 */
$vendor = __DIR__ . "/../vendor/autoload.php";

if (file_exists($vendor)) {
    require_once $vendor;
} else {
    http_response_code(500);
    echo json_encode([
        "erro" => true,
        "mensagem" => "vendor/autoload.php não encontrado."
    ]);
    exit;
}

/**
 * ===============================
 * 🔹 CARREGA AUTOLOAD DO FRAMEWORK
 * ===============================
 */
$autoload = __DIR__ . "/../Autoload/Autoload.php";

if (file_exists($autoload)) {
    require_once $autoload;

    if (class_exists("Autoload") && method_exists("Autoload", "register")) {
        Autoload::register();
    }
} else {
    http_response_code(500);
    echo json_encode([
        "erro" => true,
        "mensagem" => "Autoload do framework não encontrado."
    ]);
    exit;
}

/**
 * ===============================
 * 🔹 IMPORTA CLASSES DO SISTEMA
 * ===============================
 */
use Core\Cors\Cors;
use Core\Env\IndexEnv;
use Database\conexao\conectar;
use Routers\Inicio\roteamento;

/**
 * ===============================
 * 🔹 CONFIGURA CORS
 * ===============================
 */
Cors::handle([
    'https://imperio-woad.vercel.app',
    'https://universoimperio.com.br',
    'https://www.universoimperio.com.br',
    'http://localhost:3000'
]);

/**
 * ===============================
 * 🔹 CARREGA ENV
 * ===============================
 */
IndexEnv::carregar();

/**
 * ===============================
 * 🔹 CONECTA NO BANCO
 * ===============================
 */
conectar::conectar();

/**
 * ===============================
 * 🔹 DEFINE ROTAS
 * ===============================
 */

// Site público
roteamento::next("", "Inicio");

// Admin
roteamento::next("", "Admin");

// Mobile
roteamento::next("", "Mobile");

/**
 * ===============================
 * 🔹 EXECUTA ROTEADOR
 * ===============================
 */
try {
    roteamento::start();
} catch (\Throwable $e) {

    http_response_code(404);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        "erro" => true,
        "mensagem" => $e->getMessage()
    ]);

    exit;
}