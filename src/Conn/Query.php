<?php
/**
 * Executa uma consulta em banco de dados com segurança
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2024
 */

namespace Szagot\Helper\Conn;

use \PDOException;
use \PDO;

class Query
{
    private static ?Connection $conn = null;
    private static array      $log = [];

    /**
     * Executa uma consulta ao Banco de Dados.
     * Em caso de consulta segura, segue-se o mesmo padrão do PDO, informando-se o valor das chaves em $params
     * Exemplo: $consulta = Query::exec('SELECT * FROM tabela WHERE id = :idValue', ['idValue' => 25]);
     *
     * NOTA: Por padrão, os dados dos parâmetros, quando string, sofrerão a remoção de quaisquer tags html, a menos que
     * a chave do parâmetro venha acompanhado de asterisco(*). Exemplo: $params = ['desc*' => '<p>...</p>'];
     *
     * ATENÇÃO! É necessário que a conexão ao BD tenha sido informado em algum momento antes com setConn()
     *
     * @param string      $sql   Comando SQL
     * @param array|null  $params
     * @param string|null $class Classe associada
     *
     * @return array|boolean Em caso de sucesso retorna TRUE ou um array associativo em caso de SELECT
     */
    public static function exec(string $sql, ?array $params = [], ?string $class = null): array|bool
    {
        if (!self::$conn) {
            die('Efetue uma conexão primeiro');
        }

        $erro =
        $query =
        $lastId =
        $rowsAffected = null;
        try {
            // Prepara a query
            $query = self::$conn->getConn()->prepare($sql);

            if (!empty($params) && count($params) > 0) {
                foreach ($params as $campo => $valor) {
                    $campoTratado = str_replace('*', '', $campo);
                    // É nulo ou está vazio (menos para números e booleans)?
                    if (empty($valor) && $valor !== 0 && $valor !== false) {
                        $query->bindValue(':' . $campoTratado, null, PDO::PARAM_NULL);
                    } // O valor é boolean?
                    elseif (is_bool($valor) && $valor !== 0) {
                        $query->bindValue(':' . $campoTratado, $valor, PDO::PARAM_BOOL);
                    } // O valor é inteiro?
                    elseif (is_int($valor)) {
                        $query->bindValue(':' . $campoTratado, $valor, PDO::PARAM_INT);
                    } // É string, mas permite HTML? (Ou seja, tem * no campo)
                    elseif (preg_match('/\*$/', $campo)) {
                        $query->bindValue(':' . $campoTratado, $valor, PDO::PARAM_STR);
                    } // É apenas string?
                    else {
                        $query->bindValue(':' . $campoTratado, strip_tags(trim($valor)), PDO::PARAM_STR);
                    }
                }
            }

            // Executa a query
            $query->execute();

            // Número de Linhas Afetadas pela Query
            $rowsAffected = $query->rowCount();

            // Sendo um INSERT ou REPLACE, retorna o último ID inserido
            if (preg_match('/^[\n\r\s\t]*(insert|replace)/is', $sql)) {
                $lastId = self::$conn->getConn()->lastInsertId();
            }

        } catch (PDOException $e) {
            $erro = $e->getMessage();
        }

        // Cria o log da execução
        self::makeLog($sql, $params, $lastId, $rowsAffected, $erro);

        if ($erro) {
            return false;
        }

        // Retorno em um array associativo quando a Query for um SELECT ou um SHOW ou um SELECT iniciado por WITH
        if (preg_match('/^[\n\r\s\t]*(select|show|with)/is', $sql)) {
            return empty($class)
                ? $query->fetchAll(PDO::FETCH_ASSOC)
                : $query->fetchAll(PDO::FETCH_CLASS, $class);
        }

        // Query executada
        return true;
    }

    /**
     * Seta a Conexão ao BD desejado. Só é necessário uma vez, a menos que deseje mudar o BD.
     *
     * @param Connection $conn
     */
    public static function setConn(Connection $conn): void
    {
        self::$conn = $conn;
    }

    /**
     * Pega o último log
     *
     * @return Log
     */
    public static function getLastLog(): Log
    {
        return end(self::$log);
    }

    public static function getLogs(): array
    {
        return self::$log;
    }

    /**
     * Cria log de execução
     *
     * @param string      $sql          Query executada
     * @param array|null  $params
     * @param mixed       $lastId       Último id inserido
     * @param int|null    $rowsAffected Quantidade de linhas afetadas
     * @param string|null $error        Erros
     */
    private static function makeLog(
        string $sql,
        ?array $params = [],
        mixed $lastId = null,
        ?int $rowsAffected = 0,
        ?string $error = ''
    ): void {
        $sqlOriginal = $sql;

        // Tem parâmetros?
        if (!empty($params) && count($params) > 0) {
            foreach ($params as $campo => $valor) // É nulo?
            {
                if (is_null($valor)) {
                    $sql = str_replace(':' . $campo, 'NULL', $sql);
                } // É vazio?
                elseif (empty($valor)) {
                    $sql = str_replace(':' . $campo, '""', $sql);
                } // O valor é booleano ou numerico?
                elseif (is_bool($valor) || is_int($valor)) {
                    $sql = str_replace(':' . $campo, $valor, $sql);
                } // É string, mas permite HTML? (Ou seja, tem * no campo)
                elseif (preg_match('/\*$/', $campo)) {
                    $sql = str_replace(':' . str_replace('*', '', $campo), '"' . $valor . '"', $sql);
                } // É apenas string
                else {
                    $sql = str_replace(':' . $campo, '"' . strip_tags(trim($valor)) . '"', $sql);
                }
            }
        }

        // Monta o Log
        self::$log[] = new Log(
            self::$conn,
            $sql,
            $lastId,
            $rowsAffected,
            !empty($error),
            $error,
            $sqlOriginal,
            $params
        );
    }

    /**
     * Exec constructor.
     */
    private function __construct()
    {
        // Impede que a classe seja instanciada
    }

    public function __toString(): string
    {
        return self::getLastLog() ?? '';
    }

}