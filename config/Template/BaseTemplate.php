<?php

namespace Config\Template;

use Core\Logs\Logs;

class BaseTemplate extends Logs
{
    // ==========================================================
    // 🏗️ Cria estrutura base (base, pages, partials) para um módulo
    // ==========================================================
    protected static function criarEstruturaModulo(string $modulo): void
    {
        try {
            $base = dirname(__DIR__, 2) . "/Views/{$modulo}";
            $pastas = ["{$base}/base", "{$base}/pages", "{$base}/partials"];

            foreach ($pastas as $pasta) {
                if (!is_dir($pasta)) {
                    mkdir($pasta, 0777, true);
                    self::success("📁 Pasta criada: {$pasta}");
                }
            }

            $layout = "{$base}/base/layout.php";
            if (!file_exists($layout)) {
                $html = <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= \$titulo ?? 'Sem título' ?></title>
    <style>
        body { font-family: Arial; background: #fafafa; margin: 0; color: #333; }
        main { padding: 40px; text-align: center; }
    </style>
</head>
<body>
    <main><?= \$conteudo ?></main>
</body>
</html>
HTML;
                file_put_contents($layout, $html);
                self::info("🧱 Layout padrão criado: {$layout}");
            }
        } catch (\Throwable $th) {
            self::error("❌ Erro ao criar estrutura → {$th->getMessage()}");
        }
    }

    // ==========================================================
    // 🧩 Cria ou renderiza um partial
    // ==========================================================
    public static function Partial(string $template, array $dados = []): string
    {
        try {
            [$modulo, $nome] = array_pad(explode('/', $template, 2), 2, 'index');
            $basePath = dirname(__DIR__, 2) . "/Views/{$modulo}";

            self::criarEstruturaModulo($modulo);

            $arquivoParcial = "{$basePath}/partials/{$nome}.php";

            if (!file_exists($arquivoParcial)) {
                $conteudo = <<<HTML
<div style="padding:20px; text-align:center; border:2px dashed #bbb; background:#fff;">
    <h3>🚧 Partial criado automaticamente</h3>
    <p>Arquivo: {$arquivoParcial}</p>
    <p>Edite este arquivo para adicionar conteúdo parcial reutilizável.</p>
</div>
HTML;
                file_put_contents($arquivoParcial, $conteudo);
                self::info("🧱 Partial criado automaticamente: {$arquivoParcial}");
            }

            extract($dados);
            ob_start();
            include $arquivoParcial;
            $conteudo = ob_get_clean();

            self::success("✅ Partial renderizado: {$modulo}/{$nome}");
            return $conteudo;
        } catch (\Throwable $th) {
            self::error("❌ Erro ao renderizar partial → {$th->getMessage()}");
            return "<pre>Erro ao renderizar partial: {$th->getMessage()}</pre>";
        }
    }

    // ==========================================================
    // 🖼️ Renderiza view e injeta no layout
    // ==========================================================
    public static function View(string $template, array $dados = [], string $layout = 'layout'): void
    {
        try {
            [$modulo, $pagina] = array_pad(explode('/', $template, 2), 2, 'index');
            $basePath = dirname(__DIR__, 2) . "/Views/{$modulo}";

            self::criarEstruturaModulo($modulo);

            $arquivoView = "{$basePath}/pages/{$pagina}.php";
            $layoutPath  = "{$basePath}/base/{$layout}.php";

            if (!file_exists($arquivoView)) {
                $html = <<<HTML
<section style="padding:40px;text-align:center;background:#fff;border:2px dashed #ccc;">
    <h2>🚧 View criada automaticamente!</h2>
    <p>Arquivo: {$arquivoView}</p>
</section>
HTML;
                file_put_contents($arquivoView, $html);
                self::info("🧩 View criada automaticamente: {$arquivoView}");
            }

            if (!file_exists($layoutPath)) {
                self::warning("⚠️ Layout não encontrado, criando novamente...");
                self::criarEstruturaModulo($modulo);
            }

            extract($dados);
            ob_start();
            include $arquivoView;
            $conteudo = ob_get_clean();

            ob_start();
            include $layoutPath;
            echo ob_get_clean();

            self::success("✅ View renderizada: {$modulo}/{$pagina}");
        } catch (\Throwable $th) {
            self::error("❌ Erro ao renderizar view → {$th->getMessage()}");
            echo "<pre>Erro: {$th->getMessage()}</pre>";
        }
    }
}
