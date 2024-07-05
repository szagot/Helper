<?php
/**
 * Exceptions de CRUD.
 *
 * É possível pegar os erros das execuções com getQueryLogs() ou getLastQueryLog()
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2024
 */

namespace Szagot\Helper\Conn;

use Exception;

class ConnException extends Exception
{
    private array $queryLogs;

    public function __construct(string $message = "")
    {
        $this->queryLogs = Query::getLogs();

        parent::__construct($message);
    }

    public function __toString()
    {
        return $this->getLastQueryLog() ?? $this->getMessage();
    }

    public function getLastQueryLog(): ?Log
    {
        return $this->queryLogs ? end($this->queryLogs) : null;
    }

    public function getQueryLogs(): array
    {
        return $this->queryLogs;
    }
}