<?php

namespace Gatovel\Database\connection;

use PDO;
use PDOException;

class Connection
{
    private PDO $connection;

    public function __construct(array $config)
    {
        $dsn = $this->buildDsn($config);

        try {

            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

        } catch (PDOException $exception) {

            throw new \Exception(
                'Erro ao conectar ao banco de dados.',
                0,
                $exception
            );
        }
    }

    private function buildDsn(array $config): string
    {
        return match ($config['connection']) {

            'mysql' => sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            ),

            'pgsql' => sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $config['host'],
                $config['port'],
                $config['database']
            ),

            'sqlite' => 'sqlite:' . $config['database'],

            default => throw new \Exception(
                "Driver de banco não suportado: {$config['connection']}"
            ),
        };
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}