<?php
/**
 * Classe de base para Modelos de Tabelas de banco para serem usadas pela classe Crud
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2024
 */

namespace Szagot\Helper\Conn\Model;

/**
 * Utilizar o atributo:
 *      #[Table(name: 'nome_da_tabela')]
 *
 * E na propriedade que referencia a chave primaria, usar o atributo
 *      #[PrimaryKey]
 */
abstract class aModel
{
    /**
     * Cria uma versão em array associativa do seu model
     *
     * @param bool $notIgnoreFields TRUE se não é para ignorar os campos
     *
     * @return array|null
     */
    public function toArray(bool $notIgnoreFields = false): ?array
    {
        $modelArray = [];

        foreach ($this as $key => $value) {
            // O campo deve ser ignorado?
            if (ModelHelper::ignoreField($this::class, $key) && !$notIgnoreFields) {
                continue;
            }

            // Se for array, trata cada item dele
            if (is_array($value)) {
                foreach ($value as $arrayKey => $arrayValue) {
                    $modelArray[$key][$arrayKey] = ($arrayValue instanceof aModel) ? $arrayValue->toArray() : $arrayValue;
                }

                continue;
            }

            $modelArray[$key] = ($value instanceof aModel) ? $value->toArray() : $value;
        }

        return $modelArray;
    }
}