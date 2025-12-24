<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use Core\Upload\ServidorUpload;

class UploadController extends Basecontrolador
{
    /**
     * Mostra uma imagem da pasta public/upload
     * @param string $arquivo Caminho relativo da URL: produtos/img_123.png
     */
    public function mostrar($arquivo)
    {
        // Caminho completo do arquivo
        $path = ServidorUpload::getPath() . '/' . $arquivo;

        if (file_exists($path)) {
            // Define o tipo de conteúdo da imagem
            header('Content-Type: ' . mime_content_type($path));
            // Evita cache se desejar
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            // Exibe a imagem
            readfile($path);
            exit;
        } else {
            http_response_code(404);
            echo "Arquivo não encontrado: $arquivo";
        }
    }
}
