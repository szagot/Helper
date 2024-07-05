<?php

namespace Szagot\Helper\Conn;

use DateTime;

class Log
{
    private DateTime $timestamp;

    /**
     * @param Connection  $conn
     * @param string|null $sql
     * @param mixed|null  $lastId
     * @param int|null    $rowsAffected
     * @param bool        $hasError
     * @param string|null $errorMessage
     * @param string|null $pdoSQL
     * @param array|null  $sqlParams
     */
    public function __construct(
        private Connection $conn,
        private ?string $sql,
        private mixed $lastId,
        private ?int $rowsAffected,
        private ?bool $hasError,
        private ?string $errorMessage,
        private ?string $pdoSQL,
        private ?array $sqlParams
    ) {
        $this->timestamp = new DateTime();
    }

    public function __toString(): string
    {
        return $this->hasError
            ? "{$this->timestamp->format('Y-m-d H:i:s')}: {$this->errorMessage} | {$this->sql}"
            : "{$this->timestamp->format('Y-m-d H:i:s')}: {$this->rowsAffected} linha(s) afetada(s) | {$this->sql}";
    }

    public function getConn(): Connection
    {
        return $this->conn;
    }

    public function getTimestamp(): ?DateTime
    {
        return $this->timestamp;
    }

    public function getSql(): ?string
    {
        return $this->sql;
    }

    public function getLastId(): mixed
    {
        return $this->lastId;
    }

    public function getRowsAffected(): ?int
    {
        return $this->rowsAffected;
    }

    public function getHasError(): ?bool
    {
        return $this->hasError;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getPdoSQL(): ?string
    {
        return $this->pdoSQL;
    }

    public function getSqlParams(): ?array
    {
        return $this->sqlParams;
    }
}