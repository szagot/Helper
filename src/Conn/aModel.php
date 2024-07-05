<?php
/**
 * Classe de base para Modelos de Tabelas de banco para serem usadas pela classe Crud
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2024
 */

namespace Szagot\Helper\Conn;

abstract class aModel
{
    /** @var string Altere em seu model */
    const TABLE = 'nome_da_tabela';

    /**
     * Cria uma versão em array associativa do seu model
     *
     * @return array|null
     */
    public function toArray(): ?array
    {
        $modelArray = [];

        foreach ($this as $key => $value) {
            $modelArray[$key] = ($value instanceof aModel) ? $value->toArray() : $value;
        }

        return $modelArray;
    }
}