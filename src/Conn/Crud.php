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

namespace Szagot\Conn;

class Crud
{
    /**
     * Pega um registro específico pelo identificados
     *
     * Exemplo de uso:
     *       Crud::get(MyModel::class, 'id', 2)
     *
     * @param string $class   Classe relacionada a pesquisa. Deve ser uma classe extendida de aModel
     * @param string $idField Nome do campo identificador
     * @param mixed  $value   Valor do identificador
     *
     * @return mixed
     * @throws ConnException
     */
    static public function get(string $class, string $idField, mixed $value): mixed
    {
        $table = self::getTable($class);

        return Query::exec(
            "SELECT * FROM {$table} WHERE $idField = :value",
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
     * @param string $class Classe relacionada a pesquisa. Deve ser uma classe extendida de aModel
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

        return Query::exec("SELECT * FROM $table {$filter}", null, $class) ?? [];
    }

    /**
     * Pesquisa pelo termo
     *
     * Exemplo de uso:
     *        Crud::search(MyModel::class,'name', '%fulano%')
     *
     * @param string $class       Classe relacionada a pesquisa. Deve ser uma classe extendida de aModel
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
     * @param string $class    Classe relacionada a pesquisa. Deve ser uma classe extendida de aModel
     * @param string $idField  Nome do campo identificador
     * @param aModel  $instance Objeto a ser adicionado da mesma instância de $class
     *
     * @return int|null
     * @throws ConnException
     */
    static public function insert(string $class, string $idField, aModel $instance): ?int
    {
        $table = self::getTable($class, $instance);
        $tableContent = $instance->toArray();

        if (!empty($idField)) {
            unset($tableContent[$idField]);
        }

        $fieldsValues = ':' . implode(', :', array_keys($tableContent));
        $fields = implode(', ', array_keys($tableContent));

        $insert = Query::exec("INSERT INTO $table ($fields) VALUES ($fieldsValues)", $tableContent, $class) ?? [];
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
     * @param string $class    Classe relacionada a pesquisa. Deve ser uma classe extendida de aModel
     * @param string $idField  Nome do campo identificador
     * @param aModel  $instance Objeto a ser alterado
     *
     * @return void
     * @throws ConnException
     */
    static public function update(string $class, string $idField, aModel $instance): void
    {
        $table = self::getTable($class, $instance);
        $tableContent = $instance->toArray();

        $fields = [];
        foreach ($tableContent as $key => $value) {
            if ($key == $idField) {
                continue;
            }
            $fields[] = "$key = :$key";
        }
        $fieldsValues = implode(', ', $fields);

        $update = Query::exec(
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
     * @param string $class   Classe relacionada a pesquisa. Deve ser uma classe extendida de aModel
     * @param string $idField Nome do campo identificador
     * @param mixed  $value   Valor do identificador a ser deletado
     *
     * @return void
     * @throws ConnException
     */
    static public function delete(string $class, string $idField, mixed $value): void
    {
        $table = self::getTable($class);

        $delete = Query::exec(
            "DELETE FROM $table WHERE {$idField} = :{$idField}",
            [
                $idField => $value,
            ],
            $class
        ) ?? [];

        if (!$delete) {
            throw new ConnException("Não foi possível deletar o registro de ID {$value} no momento.");
        }
    }

    /**
     * Devolve o valor declarado de TABLE
     *
     * @throws ConnException
     */
    private static function getTable(string $class, mixed $instanceValidate = null): ?string
    {
        if (!is_subclass_of($class, aModel::class)) {
            throw new ConnException('Modelo de tabela inválido');
        }

        if ($instanceValidate && !$instanceValidate instanceof $class) {
            throw new ConnException('A instância da classe não confere com ela');
        }

        return $class::TABLE;
    }
}