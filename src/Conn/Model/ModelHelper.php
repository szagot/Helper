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
    private static array $tableCache = [];
    private static array $primaryKeyCache = [];
    private static array $ignoreFieldCache = [];


    /**
     * Pega o nome da tabela do model, conforme atributo #[Table(name='nome_da_tabela')]
     *
     * @param string $class
     *
     * @return string|null
     */
    public static function getTableName(string $class): ?string
    {
        if (isset(self::$tableCache[$class])) {
            return self::$tableCache[$class];
        }

        try {
            $reflection = new ReflectionClass($class);
            // Pega os atributos da Classe
            $attributes = $reflection->getAttributes(Table::class);
            if (!empty($attributes)) {
                /** @var Table $tableAttribute Se foi declarado o atributo da tabela, devolve */
                $tableAttribute = $attributes[0]->newInstance();
                return self::$tableCache[$class] = $tableAttribute->getTableName();
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
        if ($attribute) {
            /** @var PrimaryKey $instance */
            $instance = $attribute->getAttributes(PrimaryKey::class)[0]->newInstance();
            return $instance->isAutoIncrement();
        }
        return false;
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
        $key = $class . '::' . $field;
        if (isset(self::$ignoreFieldCache[$key])) {
            return self::$ignoreFieldCache[$key];
        }

        try {
            $reflection = new ReflectionClass($class);

            foreach ($reflection->getProperties() as $property) {
                // A propriedade tem o atributo IgnoreField?
                if ($property->getName() === $field && !empty($property->getAttributes(IgnoreField::class))) {
                    return self::$ignoreFieldCache[$key] = true;
                }
            }
        } catch (Exception) {
            return false;
        }

        return false;
    }

    private static function getPKAttribute(string $class): ?ReflectionProperty
    {
        if (isset(self::$primaryKeyCache[$class])) {
            return self::$primaryKeyCache[$class];
        }

        try {
            $reflection = new ReflectionClass($class);

            // Percorre as propriedades em busca
            foreach ($reflection->getProperties() as $property) {
                // Se a propriedade tem o atributo PrimaryKey, retorna ela.
                if (!empty($property->getAttributes(PrimaryKey::class))) {
                    return self::$primaryKeyCache[$class] = $property;
                }
            }
        } catch (Exception) {
            return null;
        }

        return null;
    }
}