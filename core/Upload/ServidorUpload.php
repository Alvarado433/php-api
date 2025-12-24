<?php

namespace Core\Upload;

use Core\Logs\Logs;

class ServidorUpload extends Logs
{
    // Diretório central dentro de public
    protected static string $diretoriocentral = 'upload';

    /**
     * Cria a pasta central se não existir
     */
    public static function criarPastaCentral(): bool
    {
        return self::criarPasta(self::$diretoriocentral);
    }

    /**
     * Cria uma pasta dentro da pasta central
     * @param string $subpasta Nome da subpasta
     */
    public static function criarPasta(string $subpasta): bool
    {
        try {
            $path = __DIR__ . '/../../public/' . self::$diretoriocentral . '/' . $subpasta;

            if (!is_dir($path)) {
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
     * @param string $subpasta Subpasta dentro de upload
     * @return string|false Caminho do arquivo ou false se falhar
     */
    public static function upload(array $arquivo, string $subpasta = 'geral')
    {
        try {
            // Cria pasta central + subpasta
            self::criarPasta($subpasta);

            $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
            $nomeUnico = uniqid('img_', true) . '.' . $extensao;

            $caminhoDestino = __DIR__ . '/../../public/' . self::$diretoriocentral . '/' . $subpasta . '/' . $nomeUnico;

            if (!move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
                throw new \Exception("Falha ao mover arquivo para: $caminhoDestino");
            }

            // Retorna caminho relativo à pasta public para salvar no banco
            return self::$diretoriocentral . '/' . $subpasta . '/' . $nomeUnico;
        } catch (\Throwable $th) {
            self::error("Erro ao fazer upload: " . $th->getMessage());
            return false;
        }
    }

    /**
     * Retorna o caminho completo de uma subpasta
     */
    public static function getPath(string $subpasta = ''): string
    {
        return __DIR__ . '/../../public/' . self::$diretoriocentral . ($subpasta ? '/' . $subpasta : '');
    }
}
