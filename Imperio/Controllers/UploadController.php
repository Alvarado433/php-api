<?php

namespace Imperio\Controllers;

use Config\Base\Basecontrolador;
use Core\Upload\ServidorUpload;

class UploadController extends Basecontrolador
{
    /**
     * Mostra qualquer arquivo dentro de public/upload/...
     * URL esperada:
     *   /upload/produtos/img_123.jpg
     *   /upload/produtos/galeria/gal_123.png
     */
    public function mostrar($arquivo = null)
    {
        // ✅ pega o caminho completo da URL, ignorando o parâmetro do router
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        $prefix  = '/upload/';

        $pos = strpos($uriPath, $prefix);
        if ($pos === false) {
            http_response_code(400);
            echo "Rota inválida";
            return;
        }

        // tudo que vem depois de /upload/
        $rel = substr($uriPath, $pos + strlen($prefix));

        // remove barras no começo e decodifica
        $rel = ltrim($rel, '/');
        $rel = urldecode($rel);

        // ✅ sanitiza (bloqueia ../ e caracteres estranhos)
        $rel = str_replace('\\', '/', $rel);
        $rel = preg_replace('#/+#', '/', $rel);
        $rel = str_replace(['../', '..\\', './', '.\\'], '', $rel);
        $rel = ltrim($rel, '/');
        $rel = preg_replace('#[^a-zA-Z0-9/_\.\-]#', '', $rel);

        if ($rel === '') {
            http_response_code(400);
            echo "Arquivo inválido";
            return;
        }

        // ✅ base correta: public/upload
        $base = __DIR__ . '/../../public/upload';
        $path = $base . '/' . $rel;

        // ✅ proteção extra contra path traversal
        $realBase = realpath($base);
        $realPath = realpath($path);

        if (!$realBase || !$realPath || strpos($realPath, $realBase) !== 0 || !is_file($realPath)) {
            http_response_code(404);
            echo "Arquivo não encontrado: " . $rel;
            return;
        }

        $mime = mime_content_type($realPath) ?: 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($realPath));
        header('Cache-Control: public, max-age=604800'); // 7 dias

        readfile($realPath);
        exit;
    }
}