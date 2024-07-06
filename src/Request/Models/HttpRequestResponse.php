<?php

namespace Szagot\Helper\Request\Models;

class HttpRequestResponse
{
    private ?string $body;
    private ?int    $status;

    /**
     * @return mixed
     */
    public function getBody(): mixed
    {
        return @json_decode($this->body) ?? [];
    }

    /**
     * @return string
     */
    public function getTextBody(): string
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     *
     * @return HttpRequestResponse
     */
    public function setBody(?string $body): HttpRequestResponse
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return HttpRequestResponse
     */
    public function setStatus(?int $status): HttpRequestResponse
    {
        $this->status = $status;

        return $this;
    }
}