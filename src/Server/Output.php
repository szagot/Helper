<?php
/**
 * Executa saídas no formato json
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2024
 */

namespace Szagot\Helper\Server;

class Output
{
    const GET_SUCCESS             = 200;
    const POST_SUCCESS            = 201;
    const PUT_SUCCESS             = 204;
    const DELETE_SUCCESS          = 204;
    const ERROR_UNAUTHORIZED      = 401;
    const ERROR_MALFORMED_REQUEST = 400;
    const ERROR_NOT_FOUND         = 404;
    const ERROR_INTERNAL          = 500;

    /**
     * Envia uma resposta JSON de sucesso.
     *
     * @param mixed $output   Os dados a serem enviados na resposta.
     * @param int   $httpCode O código HTTP da resposta.
     * @param array $headers  Cabeçalhos adicionais para a resposta.
     */
    static public function success(mixed $output = [], int $httpCode = self::GET_SUCCESS, array $headers = []): void
    {
        self::sendResponse($output, $httpCode, $headers);
    }

    /**
     * Envia uma resposta JSON de erro.
     *
     * @param mixed $message  A mensagem de erro ou os dados a serem enviados na resposta.
     * @param int   $httpCode O código HTTP da resposta.
     * @param array $headers  Cabeçalhos adicionais para a resposta.
     */
    static public function error(
        mixed $message,
        int $httpCode = self::ERROR_MALFORMED_REQUEST,
        array $headers = []
    ): void {
        $output = [
            'status'  => $httpCode,
            'message' => $message,
        ];
        self::sendResponse($output, $httpCode, $headers);
    }

    /**
     * Envia uma resposta JSON.
     *
     * @param mixed $output   Os dados a serem enviados na resposta.
     * @param int   $httpCode O código HTTP da resposta.
     * @param array $headers  Cabeçalhos adicionais para a resposta.
     */
    private static function sendResponse(mixed $output, int $httpCode, array $headers): void
    {
        // Define o cabeçalho de tipo de conteúdo
        header('Content-type: application/json');

        // Define o código de resposta HTTP
        http_response_code($httpCode);

        // Define cabeçalhos adicionais, se houver
        foreach ($headers as $key => $value) {
            header("$key: $value");
        }

        // Verifica se a saída é uma string ou um array/objeto
        if (!is_string($output)) {
            $output = empty($output) ? null : @json_encode($output);
        }

        // Envia a resposta e encerra o script
        die($output ?? '[]');
    }
}
