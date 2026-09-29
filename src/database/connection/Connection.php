<?php

namespace Gatovel\Database\connection;

use Gatovel\Database\exceptions\ConfigurationException;
use Gatovel\Database\exceptions\ConnectionException;
use Gatovel\Database\exceptions\UnsupportedDriverException;
use PDO;
use PDOException;

class Connection
{
    private PDO $connection;

    public function __construct(
        array $config
    ) {
        $this->validateConfig(
            $config
        );

        $dsn = $this->buildDsn(
            $config
        );

        $driver = $config['connection'];

        $username = $driver === 'sqlite'
            ? null
            : $config['username'];

        $password = $driver === 'sqlite'
            ? null
            : $config['password'];

        try {
            $this->connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE
                        => PDO::ERRMODE_EXCEPTION,

                    PDO::ATTR_DEFAULT_FETCH_MODE
                        => PDO::FETCH_ASSOC,

                    PDO::ATTR_EMULATE_PREPARES
                        => false,
                ]
            );
        } catch (PDOException $exception) {
            throw new ConnectionException(
                'Erro ao conectar ao banco de dados.',
                $exception
            );
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function validateConfig(
        array $config
    ): void {
        if (
            !array_key_exists(
                'connection',
                $config
            )
            || !is_string(
                $config['connection']
            )
            || $config['connection'] === ''
        ) {
            throw new ConfigurationException(
                'A configuração "connection" é obrigatória.'
            );
        }

        $driver = $config['connection'];

        $required = match ($driver) {
            'mysql' => [
                'host',
                'port',
                'database',
                'username',
                'password',
                'charset',
            ],

            'pgsql' => [
                'host',
                'port',
                'database',
                'username',
                'password',
            ],

            'sqlite' => [
                'database',
            ],

            default => throw new UnsupportedDriverException(
                $driver
            ),
        };

        foreach ($required as $key) {
            if (!array_key_exists($key, $config)) {
                throw new ConfigurationException(
                    "A configuração \"{$key}\" é obrigatória "
                    . "para o driver {$driver}."
                );
            }

            if (
                $key !== 'password'
                && (
                    !is_string($config[$key])
                    && !is_int($config[$key])
                )
            ) {
                throw new ConfigurationException(
                    "A configuração \"{$key}\" possui valor inválido."
                );
            }

            if (
                $key !== 'password'
                && (string) $config[$key] === ''
            ) {
                throw new ConfigurationException(
                    "A configuração \"{$key}\" não pode ser vazia."
                );
            }

            if (
                $key === 'password'
                && !is_string($config[$key])
            ) {
                throw new ConfigurationException(
                    'A configuração "password" deve ser uma string.'
                );
            }
        }
    }

    private function buildDsn(
        array $config
    ): string {
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

            'sqlite' => 'sqlite:'
                . $config['database'],

            default => throw new UnsupportedDriverException(
                $config['connection']
            ),
        };
    }
}
