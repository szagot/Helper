<?php
/**
 * Atributo para declarar o nome da Tabela
 */

namespace Szagot\Helper\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(
        private string $name
    ) {
    }

    public function getTableName(): string
    {
        return $this->name;
    }
}