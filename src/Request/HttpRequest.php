<?php

namespace Szagot\Helper\Request;

use CURLFile;
use Szagot\Helper\Request\Models\HttpRequestResponse;

class HttpRequest
{
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_PATCH  = 'PATCH';
    const HTTP_METHOD_DELETE = 'DELETE';

    private ?string             $url;
    private ?string             $method;
    private ?array              $headers;
    private mixed               $bodyContent = [];
    private CURLFile            $file;
    private string              $basicUser;
    private string              $basicPass;
    private HttpRequestResponse $response;
    private string              $error;

    /**
     * Inicializa a classe setando os atributos principais para a conexão Http
     *
     * @param string|null $url    URL da Requisição
     * @param string|null $method Método.
     * @param array|null  $headers
     * @param mixed       $bodyContent
     * @param string|null $authUser
     * @param string|null $authPass
     */
    public function __construct(
        ?string $url = null,
        ?string $method = self::HTTP_METHOD_GET,
        ?array $headers = null,
        ?string $bodyContent = null,
        ?string $authUser = null,
        ?string $authPass = null
    ) {
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setHeaders($headers);
        $this->setBodyContent($bodyContent);
        $this->setBasicUser($authUser);
        $this->setBasicPass($authPass);
        $this->response = new HttpRequestResponse();
    }

    /**
     * Efetua a requisição
     * A resposta pode ser obtida utilizando o método getResponse()
     *
     * @param int $timeout
     *
     * @return HttpRequest
     */
    public function execute(int $timeout = 30): HttpRequest
    {
        // Inicia a requisição setando parâmetros básicos
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $this->url);      #URL
        curl_setopt($connection, CURLOPT_TIMEOUT, $timeout);          #Timeout de 30seg
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true); #Mostra o resultado real da requisição
        curl_setopt($connection, CURLOPT_MAXREDIRS, 5);
        curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($connection, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // Método
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, $this->method);

        // Tem header?
        if (count($this->headers ?? []) > 0) {
            curl_setopt($connection, CURLOPT_HTTPHEADER, $this->headers);
        }

        // Tem senha?
        if (!empty($this->basicUser) && !empty($this->basicPass)) {
            curl_setopt($connection, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($connection, CURLOPT_USERPWD, "{$this->basicUser}:{$this->basicPass}");
        }

        // Tem Conteúdo de Body?
        if (!empty($this->bodyContent)) {
            if (!is_string($this->bodyContent) && !$this->getFile()) {
                $this->bodyContent = http_build_query($this->bodyContent);
            }
            curl_setopt($connection, CURLOPT_POST, true);
            curl_setopt($connection, CURLOPT_POSTFIELDS, $this->bodyContent);
        }

        // Resultado
        $this->response->setBody(curl_exec($connection));

        // Status da resposta
        $this->response->setStatus(curl_getinfo($connection, CURLINFO_HTTP_CODE));

        curl_close($connection);

        // Erro?
        if ($this->response->getStatus() < 200 || $this->response->getStatus() > 299) {
            $this->error = 'A requisição retornou um erro ou aviso';
        }

        return $this;
    }

    /**
     * Pega o erro da requisição
     *
     * @return string
     */
    public function getError(): string
    {
        return $this?->error ?? '';
    }

    /**
     * @param string $url URL/URI da requisição
     *
     * @return HttpRequest
     */
    public function setUrl(?string $url): HttpRequest
    {
        $this->url = trim($url);
        if (empty($this->url)) {
            $this->error = 'Informe uma URL válida';

            return $this;
        }

        $this->error = null;

        return $this;
    }

    /**
     * Seta o método da requisição, podendo ser:
     *      GET    Chamadas
     *      POST   Postagem/Criação
     *      PUT    Atualização
     *      PATCH  Atualização parcial de campos
     *      DELETE Deleção
     *
     * @param string $method Método da requisição
     *
     * @return HttpRequest
     */
    public function setMethod(string $method = self::HTTP_METHOD_GET): HttpRequest
    {
        $this->method = preg_match('/^(GET|POST|PUT|PATCH|DELETE)$/', $method) ? $method : 'GET';

        return $this;
    }

    /**
     * @param array|null $headers Headers da requisição
     *
     * @return HttpRequest
     */
    public function setHeaders(?array $headers = null): HttpRequest
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string|array $bodyContent Conteúdo a ser enviado.
     *                                  Normalmente uma string em JSON, XML ou parâmetros em array
     *
     * @return HttpRequest
     */
    public function setBodyContent(mixed $bodyContent = null): HttpRequest
    {
        if (is_string($bodyContent)) {
            $bodyContent = @json_decode($bodyContent);
        }

        $this->bodyContent = $bodyContent;

        return $this;
    }

    /**
     * Seta o Usuário de uma autenticação do tipo BASIC
     *
     * @param string|null $basicUser
     *
     * @return HttpRequest
     */
    public function setBasicUser(?string $basicUser = null): HttpRequest
    {
        $this->basicUser = $basicUser;

        return $this;
    }

    /**
     * Seta a Senha de uma autenticação do tipo BASIC
     *
     * @param string|null $basicPass
     *
     * @return HttpRequest
     */
    public function setBasicPass(?string $basicPass = null): HttpRequest
    {
        $this->basicPass = $basicPass;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this?->url ?? '';
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this?->method ?? self::HTTP_METHOD_GET;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this?->headers ?? [];
    }

    /**
     * @return mixed
     */
    public function getBodyContent(): mixed
    {
        return $this?->bodyContent ?? [];
    }

    /**
     * @return string
     */
    public function getBasicUser(): string
    {
        return $this?->basicUser ?? '';
    }

    /**
     * @return string
     */
    public function getBasicPass(): string
    {
        return $this?->basicPass ?? '';
    }

    /**
     * Pega a resposta da requisição em caso de sucesso.
     *
     * @return HttpRequestResponse|null
     */
    public function getResponse(): ?HttpRequestResponse
    {
        return $this?->response ?? null;
    }

    /**
     * @return CURLFile|null
     */
    public function getFile(): ?CURLFile
    {
        return $this?->file ?? null;
    }

    /**
     * Salva o arquivo em formato para envio
     *
     * Exemplo de uso:
     *      $this->addFileToRequest($filePath, $fileName, 'file')
     *
     * Obs: Use primeiro $this->setBodyContent()
     *
     * @param string      $filePath
     * @param string|null $fileName
     * @param string|null $fieldName
     *
     * @return HttpRequest
     */
    public function addFileToRequest(string $filePath, string $fileName = null, string $fieldName = null): HttpRequest
    {
        $contentFile = @file_get_contents($filePath);
        if (empty($contentFile)) {
            return $this;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $contentFile);
        finfo_close($finfo);

        $this->file = curl_file_create($filePath, $mime, $fileName);
        $this->bodyContent[] = [
            $fieldName => $this->getFile(),
        ];

        return $this;
    }
}