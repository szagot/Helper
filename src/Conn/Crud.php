<?php
/**
 * Classe para fazer um CRUD básico em banco usando as classes Query e Connection
 *
 * Para funcionar, seus Models de Tabela devem ter um Extends de Szagot\Conn\aModel
 *
 * Exemplo de uso:
 *      Crud::getAll(MyModel::class)
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2024
 */

namespace Szagot\Helper\Conn;

use Szagot\Helper\Attributes\ModelHelper;

class Crud
{
    /**
     * Pega um registro específico pelo identificados
     *
     * Exemplo de uso:
     *       Crud::get(MyModel::class, 'id', 2)
     *
     * @param string $class Classe relacionada a pesquisa.
     * @param mixed  $value Valor do identificador
     *
     * @return mixed
     * @throws ConnException
     */
    static public function get(string $class, mixed $value): mixed
    {
        $table = self::getTable($class);
        $idField = self::getPrimaryKey($class);

        return Query::exec(
        /** @lang text */
            "SELECT * FROM $table WHERE $idField = :value",
            [
                'value' => $value,
            ],
            $class
        )[0] ?? null;
    }

    /**
     * Pega todos os registros
     *
     * Exemplo de uso:
     *       Crud::getAll(MyModel::class, 0, 0, 'created_at DESC')
     *
     * @param string $class Classe relacionada a pesquisa.
     * @param int    $limit Deixe 0 para não ter limite. Nesse caso $offset é ignorado
     * @param int    $offset
     * @param string $orderBy
     *
     * @return array
     * @throws ConnException
     */
    static public function getAll(string $class, int $limit = 0, int $offset = 0, string $orderBy = ''): array
    {
        $table = self::getTable($class);
        $filter = '';
        if ($orderBy) {
            $filter .= " ORDER BY $orderBy";
        }
        if ($limit > 0) {
            $filter .= " LIMIT {$offset}, {$limit}";
        }

        return Query::exec(
        /** @lang text */
            "SELECT * FROM $table {$filter}",
            null,
            $class
        ) ?? [];
    }

    /**
     * Pesquisa pelo termo
     *
     * Exemplo de uso:
     *        Crud::search(MyModel::class,'name', '%fulano%')
     *
     * @param string $class       Classe relacionada a pesquisa.
     * @param string $searchField Nome do campo a ser pesquisado
     * @param mixed  $value       Valor da pesquisa. Use % como coringa
     *
     * @return array
     * @throws ConnException
     */
    static public function search(string $class, string $searchField, mixed $value): array
    {
        $table = self::getTable($class);

        return Query::exec(
        /** @lang text */
            "SELECT * FROM $table WHERE $searchField LIKE :value",
            [
                'value' => $value,
            ],
            $class
        ) ?? [];
    }

    /**
     * Insere um registro
     *
     * Exemplo de uso:
     *        Crud::insert(MyModel::class, 'id', $myModelInstance)
     *
     * @param string $class    Classe relacionada a pesquisa.
     * @param aModel $instance Objeto a ser adicionado da mesma instância de $class
     *
     * @return int|null
     * @throws ConnException
     */
    static public function insert(string $class, aModel $instance): ?int
    {
        $table = self::getTable($class, $instance);
        $tableContent = $instance->toArray();

        $idField = self::getPrimaryKey($class, true);
        if (!empty($idField)) {
            unset($tableContent[$idField]);
        }

        $fieldsValues = ':' . implode(', :', array_keys($tableContent));
        $fields = implode(', ', array_keys($tableContent));

        $insert = Query::exec(
        /** @lang text */
            "INSERT INTO $table ($fields) VALUES ($fieldsValues)",
            $tableContent,
            $class
        ) ?? [];
        if (!$insert) {
            throw new ConnException('Não foi possível inserir o registro no momento.');
        }

        return Query::getLastLog()?->getLastId() ?? null;
    }

    /**
     * Atualiza um registro
     *
     * * Exemplo de uso:
     * *        Crud::update(MyModel::class, 'id', $myModelInstance)
     *
     * @param string $class    Classe relacionada a pesquisa.
     * @param aModel $instance Objeto a ser alterado
     *
     * @return void
     * @throws ConnException
     */
    static public function update(string $class, aModel $instance): void
    {
        $table = self::getTable($class, $instance);
        $tableContent = $instance->toArray();
        $idField = self::getPrimaryKey($class);

        $fields = [];
        foreach ($tableContent as $key => $value) {
            if ($key == $idField) {
                continue;
            }
            $fields[] = "$key = :$key";
        }
        $fieldsValues = implode(', ', $fields);

        $update = Query::exec(
        /** @lang text */
            "UPDATE $table SET $fieldsValues WHERE {$idField} = :{$idField}",
            $tableContent,
            $class
        ) ?? [];
        if (!$update) {
            throw new ConnException("Não foi possível atualizar o registro de ID {$instance->$idField} no momento.");
        }
    }

    /**
     * Apaga um registro
     *
     * * Exemplo de uso:
     * *        Crud::delete(MyModel::class, 'id', 2)
     *
     * @param string $class   Classe relacionada a pesquisa.
     * @param mixed  $pkValue Valor do identificador a ser deletado
     *
     * @return void
     * @throws ConnException
     */
    static public function delete(string $class, mixed $pkValue): void
    {
        $table = self::getTable($class);
        $idField = self::getPrimaryKey($class);

        $delete = Query::exec(
        /** @lang text */
            "DELETE FROM $table WHERE {$idField} = :{$idField}",
            [
                $idField => $pkValue,
            ],
            $class
        ) ?? [];

        if (!$delete) {
            throw new ConnException("Não foi possível deletar o registro de ID {$pkValue} no momento.");
        }
    }

    /**
     * Devolve o valor declarado de TABLE
     *
     * @throws ConnException
     */
    private static function getTable(string $class, mixed $instanceValidate = null): string
    {
        if (!$table = ModelHelper::getTableName($class)) {
            throw new ConnException('Nome da tabela não declarada');
        }

        if ($instanceValidate && !$instanceValidate instanceof $class) {
            throw new ConnException('A instância da classe não confere com ela');
        }

        return $table;
    }

    /**
     * Pega a chave primária
     *
     * @param string $class
     * @param bool   $allowEmpty
     *
     * @return string
     * @throws ConnException
     */
    private static function getPrimaryKey(string $class, bool $allowEmpty = false): string
    {
        $pk = ModelHelper::getPrimaryKey($class);
        if (empty($pk) && !$allowEmpty) {
            throw new ConnException('Chave primária não declarada');
        }

        return $pk;
    }
}