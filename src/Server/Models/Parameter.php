<?php

namespace Szagot\Helper\Server\Models;

class Parameter
{
    const FILTER_BOOL    = 'boolean';
    const FILTER_INT     = 'integer';
    const FILTER_DOUBLE  = 'double';
    const FILTER_STRING  = 'string';
    const FILTER_ARRAY   = 'array';
    const FILTER_OBJ     = 'object';
    const FILTER_NULL    = 'NULL';
    const FILTER_DEFAULT = null;

    private string $type;

    public function __construct(
        private string $name,
        private string $value,
    ) {
        $this->type = gettype($this->amendValue());
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(bool $original = false): string
    {
        return $original ? $this->value : $this->amendValue();
    }

    public function isTypeValid(?string $type = self::FILTER_DEFAULT): bool
    {
        if (!$type) {
            return true;
        }

        // Se o tipo for boolean, valida como tal mesmo que o tipo seja outro
        if ($type == self::FILTER_BOOL) {
            return
                $this->type == $type ||
                $this->amendValue() == 1 ||
                $this->amendValue() == 0 ||
                empty($this->value);
        }

        return $this->type == $type;
    }

    private function amendValue(): mixed
    {
        $value = $this->value;

        if (empty($value)) {
            return $value;
        }

        if ((int)$value != 0) {
            return $value * 1;
        }

        if (strtolower($value) == 'true') {
            return true;
        }

        if (strtolower($value) == 'false') {
            return false;
        }

        return $value;
    }
}