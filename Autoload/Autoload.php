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
        "Routers\\"  => __DIR__ . "/../routers/",  // NOVO SUPORTE PARA ROTAS
    ];

    /**
     * Registra o autoloader
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, "loader"]);
    }

    /**
     * Carrega automaticamente a classe solicitada
     */
    private static function loader(string $classe): bool
    {
        // Verifica todos os namespaces mapeados
        foreach (self::$map as $namespace => $baseDir) {

            // Verifica se a classe começa com o namespace atual
            if (strpos($classe, $namespace) === 0) {

                // Remove o prefixo do namespace
                $relativeClass = substr($classe, strlen($namespace));

                // Monta caminho do arquivo final
                $arquivo = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";

                // Se o arquivo existir → inclui
                if (file_exists($arquivo)) {
                    require_once $arquivo;
                    return true;
                }
            }
        }

        return false;
    }
}
