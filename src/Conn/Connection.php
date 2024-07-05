<?php
/**
 * Efetua a conexão com o Banco de Dados utilizando PDO
 * Configurações da conexão:
 *      MySQL Database
 *      Charset = UTF-8 (sugestão de COLLATE: utf8_general_ci)
 *      Error Mode = PDOException
 *      Persistência na Conexão = Sim
 *
 * NOTA: Se houver erro na conexão, o script será interrompido e o erro será logado no console do PHP
 *
 * @author    Daniel Bispo <szagot@gmail.com>
 * @copyright Copyright (c) 2024
 */

namespace Szagot\Helper\Conn;

use \PDOException;
use \PDO;

class Connection
{
    private ?PDO $conn;

    /**
     * Connection constructor.
     *
     * @param string $db   Define o Banco de Dados
     * @param string $host Define o Host
     * @param string $user Define o usuário
     * @param string $pass Define a senha
     */
    public function __construct(
        private string $db,
        string $host = 'localhost',
        string $user = 'root',
        string $pass = ''
    ) {
        try {
            $this->conn = new PDO("mysql:host={$host};dbname={$db};charset=utf8", $user, $pass,
                                  [
                                      // Garante a conversão par UTF-8
                                      // É necessário que o banco de dados também seja criado com UTF-8 e cada tabela com COLLATE='utf8mb4_general_ci'
                                      // EX.: CREATE DATABASE nome_bd CHARACTER SET UTF8;
                                      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                                      // Recepciona os erros com PDOException
                                      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                                      // Mantém aberta a Conexão com o Banco de Dados, se possível
                                      PDO::ATTR_PERSISTENT         => true,
                                  ]);
        } catch (PDOException $err) {
            error_log("Erro ao conectar com o Banco de Dados: {$err->getMessage()}");
            $this->conn = null;
            exit(-1);
        }
    }

    /**
     * Connection destructor
     */
    function __destruct()
    {
        // Desfaz a conexão com o PDO
        $this->conn = null;
    }


    /**
     * Pega o resultado da conexão
     *
     * @return PDO|null
     */
    public function getConn(): ?PDO
    {
        return $this->conn;
    }

    /**
     * Retorna nome do BD da conexão atual
     *
     * @return string|null
     */
    public function getDb(): ?string
    {
        return $this->db;
    }
}