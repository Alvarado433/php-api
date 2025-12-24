<?php

namespace Routers\Inicio;

use Config\Base\BaseRoteamento;
use Routers\Namespace\Resolver;

class roteamento extends BaseRoteamento
{
    protected static array $routers = ['get', 'post', 'put', 'delete'];

    public static function start()
    {
        try {
            $url = self::capturarweb(); // ex: /v1/footer/1/links
            $metodo = self::metodo();

            self::info("Rota capturada: {$url} | Método: {$metodo}");

            $rotaEncontrada = false;

            foreach (static::$rotas[$metodo] ?? [] as $rota => $acao) {
                // Transformar /footer/{footerId}/links em regex
               $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '(.+)', $rota);
                $pattern = "#^" . $pattern . "$#";

                if (preg_match($pattern, $url, $matches)) {
                    $rotaEncontrada = true;
                    array_shift($matches); // remove a string completa

                    // Agora $matches contém os parâmetros da rota
                    // Ex: /footer/1/links => [1]
                    $_GET['params'] = $matches;

                    // Executa o controller passando os parâmetros
                    [$classe, $metodoController] = explode('@', $acao);
                    [$controller, $action] = Resolver::resolver($classe, $metodoController);

                    // Chamada com parâmetros
                    call_user_func_array([$controller, $action], $matches);
                    return;
                }
            }

            if (!$rotaEncontrada) {
                throw new \Exception("Rota não encontrada: {$metodo} {$url}");
            }

        } catch (\Throwable $th) {
            self::error("Erro no roteador: " . $th->getMessage());
            throw $th;
        }
    }
}
