<?php

namespace Szagot\Helper\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey
{
    public function __construct(
        private bool $autoIncrement = true
    ) {
    }

    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }
}