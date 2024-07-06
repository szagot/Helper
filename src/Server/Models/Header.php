<?php

namespace Szagot\Helper\Server\Models;

class Header
{
    public function __construct(
        private string $name,
        private string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}