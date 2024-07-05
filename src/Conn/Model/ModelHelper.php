<?php
/**
 * Helper para pegar os atributos
 */

namespace Szagot\Helper\Conn\Model;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use Szagot\Helper\Attributes\IgnoreField;
use Szagot\Helper\Attributes\PrimaryKey;
use Szagot\Helper\Attributes\Table;

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
        return self::getPKAttribute($class)?->getName() ?? '';
    }

    /**
     * A chave primária não é do tipo de auto incremento (padrão)?
     *
     * @param string $class
     *
     * @return bool
     */
    public static function isPKAutoIncrement(string $class): bool
    {
        $attribute = self::getPKAttribute($class);
        /** @var PrimaryKey $instance */
        $instance = $attribute->getAttributes()[0]?->newInstance();
        return $instance?->isAutoIncrement() ?? false;
    }

    /**
     * O campo deve ser ignorado? Isto é, ele não faz parte do Banco
     *
     * @param string $class
     * @param string $field
     *
     * @return bool
     */
    public static function ignoreField(string $class, string $field): bool
    {
        try {
            $reflection = new ReflectionClass($class);

            // Percorre as propriedades em busca
            foreach ($reflection->getProperties() as $property) {
                if ($property->getName() != $field) {
                    continue;
                }

                // A propriedade tem o atributo IgnoreField?
                return !empty($property->getAttributes(IgnoreField::class));
            }
        } catch (Exception) {
            return false;
        }

        return false;
    }

    private static function getPKAttribute(string $class): ?ReflectionProperty
    {
        try {
            $reflection = new ReflectionClass($class);

            // Percorre as propriedades em busca
            foreach ($reflection->getProperties() as $property) {
                // Se a propriedade tem o atributo PrimaryKey, retorna o nome dela.
                if (!empty($property->getAttributes(PrimaryKey::class))) {
                    return $property;
                }
            }
        } catch (Exception) {
            return null;
        }

        return null;
    }
}