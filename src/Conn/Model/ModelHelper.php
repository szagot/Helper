<?php
/**
 * Helper para pegar os atributos
 */

namespace Szagot\Helper\Attributes;

use Exception;
use ReflectionClass;

class ModelHelper
{
    /**
     * Pega o nome da tabela do model, conforme atributo #[Table(name='nome_da_tabela')]
     *
     * @param string $class
     *
     * @return string|null
     */
    public static function getTableName(string $class): ?string
    {
        try {
            $reflection = new ReflectionClass($class);
            // Pega os atributos da Classe
            $attributes = $reflection->getAttributes(Table::class);
            if (isset($attributes[0])) {
                /** @var Table $tableAttribute Se foi declarado o atributo da tabela, devolve */
                $tableAttribute = $attributes[0]->newInstance();
                return $tableAttribute->getTableName();
            }
        } catch (Exception) {
            return null;
        }

        return null;
    }

    /**
     * Devolve a chave primária do Model.
     * O Atributo #[PrimaryKey] deve ter somente uma vez
     *
     * @param string $class
     *
     * @return string
     */
    public static function getPrimaryKey(string $class): string
    {
        try {
            $reflection = new ReflectionClass($class);

            // Percorre as propriedades em busca
            foreach ($reflection->getProperties() as $property) {
                // Se a propriedade tem o atributo PrimaryKey, retorna o nome dela.
                if (!empty($property->getAttributes(PrimaryKey::class))) {
                    return $property->getName();
                }
            }
        } catch (Exception) {
            return '';
        }

        return '';
    }
}