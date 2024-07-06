<?php
/**
 * Controle de chamadas recebidas (GET, POST, PUT, DELETE, etc...)
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2024
 */

namespace Szagot\Helper\Server;

use Szagot\Helper\Server\Models\File;
use Szagot\Helper\Server\Models\Header;
use Szagot\Helper\Server\Models\Parameter;

class Uri
{
    private static ?Uri $uri = null;

    private string $method;
    private array  $parameters;
    private mixed  $body;
    private array  $headers;
    private array  $files;
    private string $url;
    private string $requestIp;
    private string $root;

    /**
     * Seta uma nova instância de URI apenas se está não tiver sido instanciada anteriormente
     *
     * @param string $root
     *
     * @return Uri
     */
    public static function newInstance(string $root = ''): Uri
    {
        if (is_null(self::$uri)) {
            self::$uri = new Uri($root);
        } elseif (!empty($root)) {
            self::$uri->setRoot($root);
        }

        return self::$uri;
    }

    /**
     * Configura a raiz da URL, isto é, a parte da URL que deve ser ignorada
     *
     * @param string $root
     *
     * @return $this
     */
    public function setRoot(string $root = ''): Uri
    {
        $this->root = preg_replace('/(^\/|\/$)/', '', $root);
        return $this;
    }

    /**
     * Pega a raiz da URL (parte a ser ignorada)
     *
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * A requisição é local?
     *
     * @return bool
     */
    public function isLocal(): bool
    {
        return preg_match('/localhost|127\.0\.0\.1/i', $this->getUrl());
    }

    /**
     * Pega todos os parâmetros da requisição
     *
     * Obs: Parâmetros informados no body tem prioridade sobre os parâmetros da querystring em caso de mesma chave
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Pega um parâmetro específico
     *
     * @param string      $key
     * @param string|null $filter Se o tipo da variável não bater com o solicitado, devolve null
     *
     * @return Parameter|null
     */
    public function getParameter(string $key, ?string $filter = Parameter::FILTER_DEFAULT): ?Parameter
    {
        if (!$this->parameterExists($key)) {
            return null;
        }

        /** @var Parameter $parameter */
        $parameter = $this->parameters[$key];

        if ($filter && !$parameter->isTypeValid($filter)) {
            return null;
        }

        return $this->parameters[$key];
    }

    public function getBody(bool $jsonFormat = true): mixed
    {
        if ($jsonFormat && is_string($this->body) && is_array(json_decode($this->body, true))) {
            return json_decode($this->body);
        }

        return ($jsonFormat && empty($this->body)) ? [] : $this->body;
    }

    /**
     * O parâmetro foi informado?
     *
     * @param string $key
     *
     * @return bool
     */
    public function parameterExists(string $key): bool
    {
        return isset($this->parameters[$key]) && $this->parameters[$key] instanceof Parameter;
    }

    /**
     * Pega os arquivos da requisição
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Foi informado o arquivo específico?
     *
     * @param string $key
     *
     * @return bool
     */
    public function fileExists(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key] instanceof File;
    }

    /**
     * Pega um arquivo específico
     *
     * @param string $key
     *
     * @return File|null
     */
    public function getFile(string $key): ?File
    {
        if (!$this->fileExists($key)) {
            return null;
        }

        return $this->files[$key];
    }

    /**
     * Devolve a Url completa
     *
     * @param bool $withServer Completo, com o servidor?
     *
     * @return string
     */
    public function getUrl(bool $withServer = true): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        $server = $_SERVER['HTTP_HOST'] . '/';

        return $withServer ? "{$protocol}{$server}{$this->url}" : $this->url;
    }

    /**
     * Devolve apenas a URI
     *
     * Exemplo:
     *      para a URL https://meuservidor.com/minha/raiz/pagina/detalhe/
     *      caso "minha/raiz" tenha sido informada como root do projeto, a URI fica assim:
     *      "/pagina/detalhe/"
     *
     * @return string
     */
    public function getTextUri(): string
    {
        if (empty($this->url)) {
            return '';
        }

        $uri = preg_replace('/(^\/|\/$)/', '', $this->url);
        if (!empty($this->root)) {
            $uri = preg_replace('/^' . preg_quote($this->root, '/') . '/i', '', $uri);
            $uri = preg_replace('/(^\/|\/$)/', '', $uri);
        }

        return $uri;
    }

    /**
     * Devolve todas as posições da URI em um array.
     *
     * @param int|null $index Se informado, devolve uma posição específica
     *
     * @return string|array|null
     */
    public function getUri(?int $index = null): string|array|null
    {
        $uri = explode('/', $this->getTextUri());
        if (!is_null($index)) {
            return $uri[$index] ?? null;
        }
        return $uri;
    }

    /**
     * Pega o IP de quem fez a requisição
     *
     * @return string
     */
    public function getRequestIp(): string
    {
        return $this->requestIp;
    }

    /**
     * Pega os cabeçalhos da requisição
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function headerExists(string $key): bool
    {
        return isset($this->headers[$key]) && $this->headers[$key] instanceof Header;
    }

    /**
     * Pega um cabeçalho específico
     *
     * @param string $key
     *
     * @return Header|null
     */
    public function getHeader(string $key): ?Header
    {
        if (!$this->headerExists($key)) {
            return null;
        }

        return $this->headers[$key];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Não pode ser instanciado diretamente. Utilize newInstance()
     */
    private function __construct(string $root = '')
    {
        $this->setRoot($root);

        // Seta o método
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Seta a URL
        $fullUrl = urldecode($_SERVER['REQUEST_URI'] ?? '');
        $this->url = preg_replace('/(^\/|\/$)/', '', explode('?', $fullUrl)[0] ?? $fullUrl);

        // Seta o body
        $this->body = file_get_contents('php://input') ?? '';

        // Seta os parâmetros
        $this->parameters = [];
        if ($_REQUEST) {
            foreach ($_REQUEST as $key => $value) {
                $this->parameters[$key] = new Parameter($key, $value);
            }
        }
        $postVars = [];
        $this->parseRawHTTPRequest($postVars);
        if ($postVars) {
            foreach ($postVars as $key => $value) {
                $this->parameters[$key] = new Parameter($key, $value);
            }
        }
        if ($this->body) {
            foreach ($this->getBody() as $key => $value) {
                $this->parameters[$key] = new Parameter($key, $value);
            }
        }

        // Seta os arquivos
        $this->files = [];
        if ($_FILES) {
            foreach ($_FILES as $key => $value) {
                $this->files[$key] = new File($key, $value);
            }
        }

        // Seta os headers
        $this->headers = [];
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $key => $value) {
                $this->headers[$key] = new Header($key, $value);
            }
        }
        if (empty($this->headers)) {
            foreach ($_SERVER as $name => $value) {
                if (str_starts_with($name, 'HTTP_')) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $this->headers[$key] = new Header($key, $value);
                }
            }
        }

        // IP de quem fez a requisição
        $this->requestIp = $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     *  Fonte:
     *  https://stackoverflow.com/questions/5483851/manually-parse-raw-multipart-form-data-data-with-php/5488449#5488449
     */
    private function parseRawHTTPRequest(array &$a_data): void
    {
        // read incoming data
        $input = $this->getBody(false);

        if (!isset($_SERVER['CONTENT_TYPE'])) {
            return;
        }

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);

        if (!isset($matches[1])) {
            return;
        }

        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block) {
            if (empty($block)) {
                continue;
            }

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== false) {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
            } // parse all other fields
            else {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
            }
            $a_data[$matches[1]] = $matches[2];
        }
    }
}