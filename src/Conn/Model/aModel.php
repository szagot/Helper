<?php
/**
 * Classe de base para Modelos de Tabelas de banco para serem usadas pela classe Crud
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2024
 */

namespace Szagot\Helper\Conn;

/**
 * Utilizar o atributo:
 *      #[Table(name='nome_da_tabela')]
 *
 * E na propriedade que referencia a chave primaria, usar o atributo
 *      #[PrimaryKey]
 */
abstract class aModel
{
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