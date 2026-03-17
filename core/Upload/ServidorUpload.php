<?php

namespace Core\Upload;

use Core\Logs\Logs;

class ServidorUpload extends Logs
{
    // Diretório central dentro de public
    protected static string $diretoriocentral = 'upload';

    /**
     * Sanitiza subpasta para criar diretório (permite vazio!)
     * - permite "produtos/galeria"
     * - remove ../
     */
    private static function sanitizeSubpastaAllowEmpty(string $subpasta): string
    {
        $subpasta = trim($subpasta);

        // ✅ aqui é a diferença: vazio continua vazio (base)
        if ($subpasta === '') return '';

        $subpasta = str_replace('\\', '/', $subpasta);
        $subpasta = str_replace(['../', '..\\', './', '.\\'], '', $subpasta);
        $subpasta = preg_replace('#/+#', '/', $subpasta);
        $subpasta = preg_replace('#[^a-zA-Z0-9/_-]#', '', $subpasta);
        $subpasta = trim($subpasta, '/');

        return $subpasta;
    }

    /**
     * Sanitiza caminho relativo de arquivo vindo pela URL.
     * Ex: "produtos/img_123.jpg"
     */
    public static function sanitizeArquivoRelativo(string $rel): string
    {
        $rel = trim($rel);
        $rel = str_replace('\\', '/', $rel);
        $rel = preg_replace('#/+#', '/', $rel);
        $rel = str_replace(['../', '..\\', './', '.\\'], '', $rel);
        $rel = ltrim($rel, '/');

        // permite letras, números, /, _, -, ., (para extensão)
        $rel = preg_replace('#[^a-zA-Z0-9/_\.\-]#', '', $rel);

        return $rel;
    }

    /**
     * Retorna o caminho base do upload (sem /geral)
     */
    public static function getBasePath(): string
    {
        return __DIR__ . '/../../public/' . self::$diretoriocentral;
    }

    /**
     * Cria a pasta central se não existir
     */
    public static function criarPastaCentral(): bool
    {
        return self::criarPasta('');
    }

    /**
     * Cria uma pasta dentro da pasta central
     * @param string $subpasta Nome da subpasta (pode ter /, ex: produtos/galeria)
     */
    public static function criarPasta(string $subpasta): bool
    {
        try {
            $subpasta = self::sanitizeSubpastaAllowEmpty($subpasta);

            $base = self::getBasePath();
            $path = $base . ($subpasta !== '' ? '/' . $subpasta : '');

            // garante central
            if (!is_dir($base)) {
                if (!mkdir($base, 0755, true)) {
                    throw new \Exception("Não foi possível criar o diretório central: $base");
                }
            }

            // garante subpasta (pode ser aninhada)
            if ($subpasta !== '' && !is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    throw new \Exception("Não foi possível criar o diretório: $path");
                }
            }

            return true;
        } catch (\Throwable $th) {
            self::error("Erro ao criar pasta: " . $th->getMessage());
            return false;
        }
    }

    /**
     * Faz upload do arquivo para uma subpasta
     * @param array $arquivo $_FILES['campo']
     * @param string $subpasta Subpasta dentro de upload (pode ter /)
     * @param string $prefix Prefixo do nome do arquivo (ex: img_, gal_)
     * @return string|false Caminho do arquivo ou false se falhar
     */
    public static function upload(array $arquivo, string $subpasta = 'geral', string $prefix = 'img_')
    {
        try {
            // ✅ aqui "geral" continua sendo default
            $subpasta = self::sanitizeSubpastaAllowEmpty($subpasta);
            if ($subpasta === '') $subpasta = 'geral';

            self::criarPasta($subpasta);

            if (empty($arquivo['tmp_name']) || !is_uploaded_file($arquivo['tmp_name'])) {
                throw new \Exception("Arquivo inválido (tmp_name).");
            }

            $extensao = strtolower(pathinfo($arquivo['name'] ?? '', PATHINFO_EXTENSION));
            if ($extensao === '') $extensao = 'jpg';

            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($extensao, $permitidas, true)) {
                throw new \Exception("Extensão não permitida: {$extensao}");
            }

            $nomeUnico = uniqid($prefix, true) . '.' . $extensao;

            $caminhoDestino =
                __DIR__ . '/../../public/' .
                self::$diretoriocentral .
                '/' . $subpasta .
                '/' . $nomeUnico;

            if (!move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
                throw new \Exception("Falha ao mover arquivo para: $caminhoDestino");
            }

            // ✅ salva no banco SEM "public/"
            return self::$diretoriocentral . '/' . $subpasta . '/' . $nomeUnico;
        } catch (\Throwable $th) {
            self::error("Erro ao fazer upload: " . $th->getMessage());
            return false;
        }
    }

    /**
     * Retorna o caminho completo de uma subpasta (agora aceita vazio corretamente)
     */
    public static function getPath(string $subpasta = ''): string
    {
        $subpasta = self::sanitizeSubpastaAllowEmpty($subpasta);
        return self::getBasePath() . ($subpasta !== '' ? '/' . $subpasta : '');
    }
}