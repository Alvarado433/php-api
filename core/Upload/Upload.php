<?php
namespace Core\Upload;

use Core\Logs\Logs;

class Upload extends Logs
{
    protected static string $pastaBase = __DIR__ . "./../../public/uploads"; // pasta base para uploads

    /**
     * Salvar arquivo
     * @param array $arquivo $_FILES['file']
     * @param string $subpasta Subpasta dentro de uploads
     * @param array $tiposPermitidos Tipos permitidos (ex: ['png','jpg','jpeg','gif'])
     * @return string|null Caminho relativo ou null se falhar
     */
    public static function salvar(array $arquivo, string $subpasta = "geral", array $tiposPermitidos = ['png','jpg','jpeg','gif']): ?string
    {
        try {
            if (!isset($arquivo['tmp_name'])) {
                static::warning("Arquivo inválido para upload");
                return null;
            }

            $caminhoPasta = static::$pastaBase . "/" . $subpasta;

            // Cria pasta se não existir
            if (!is_dir($caminhoPasta)) {
                mkdir($caminhoPasta, 0755, true);
                static::info("Pasta criada: {$caminhoPasta}");
            }

            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

            if (!in_array($extensao, $tiposPermitidos)) {
                static::warning("Tipo de arquivo não permitido: {$extensao}");
                return null;
            }

            $nomeArquivo = uniqid('up_') . ".{$extensao}";
            $caminhoCompleto = $caminhoPasta . "/" . $nomeArquivo;

            move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto);
            static::success("Arquivo salvo: {$caminhoCompleto}");

            return "/uploads/{$subpasta}/{$nomeArquivo}";

        } catch (\Throwable $th) {
            static::error("Erro ao salvar arquivo: " . $th->getMessage());
            return null;
        }
    }
}
