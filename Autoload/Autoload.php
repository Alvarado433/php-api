<?php
/**
 * ============================================
 * 🔥 AUTOLOAD MANUAL — PADRÃO PSR-4 COMPLETO
 * RhaianAlvarado Framework
 * ============================================
 */

class Autoload
{
    /** @var array Mapeamento de namespaces → diretórios */
    private static array $map = [
        "Imperio\\"  => __DIR__ . "/../src/",
        "Core\\"     => __DIR__ . "/../core/",
        "App\\"      => __DIR__ . "/../app/",
        "Config\\"   => __DIR__ . "/../config/",
        "Database\\" => __DIR__ . "/../database/",
        "Routers\\"  => __DIR__ . "/../routers/",
    ];

    /**
     * Registra o autoloader
     */
    public static function register(): void
    {
        // ✅ 1️⃣ Primeiro carrega o autoload do Composer (vendor)
        $vendor = __DIR__ . "/../vendor/autoload.php";

        if (file_exists($vendor)) {
            require_once $vendor;
        }

        // ✅ 2️⃣ Depois registra o autoload do framework
        spl_autoload_register([self::class, "loader"]);
    }

    /**
     * Carrega automaticamente a classe solicitada
     */
    private static function loader(string $classe): bool
    {
        foreach (self::$map as $namespace => $baseDir) {

            if (strpos($classe, $namespace) === 0) {
                $relativeClass = substr($classe, strlen($namespace));

                $arquivo = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";

                if (file_exists($arquivo)) {
                    require_once $arquivo;
                    return true;
                }
            }
        }

        return false;
    }
}