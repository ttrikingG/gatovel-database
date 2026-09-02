<?php

namespace Gatovel\Database;

use PDO;
use Gatovel\Database\connection\Connection;
use Gatovel\Database\query\QueryBuilder;
use Gatovel\Database\query\Grammar;
use Gatovel\Database\query\grammars\MySQLGrammar;
use Gatovel\Database\query\grammars\PostgresGrammar;
use Gatovel\Database\query\grammars\SQLiteGrammar;
use Gatovel\Database\transaction\Transaction;
use Gatovel\Database\migration\Schema;

class Database
{
    private static ?Connection $connection = null;

    private static ?Grammar $grammar = null;

    public static function connect(array $config): void
    {
        self::$connection = new Connection($config);

        $driver = $config['connection'] ?? 'mysql';

        self::$grammar = match ($driver) {
            'mysql' => new MySQLGrammar(),
            'pgsql' => new PostgresGrammar(),
            'sqlite' => new SQLiteGrammar(),

            default => throw new \Exception(
                "Grammar não suportada: {$driver}"
            ),
        };
    }

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            throw new \Exception(
                'Banco de dados não conectado.'
            );
        }

        return self::$connection->getConnection();
    }

    public static function transaction(): Transaction
    {
        return new Transaction(
            self::connection()
        );
    }

    public static function schema(): Schema
    {
        return new Schema(
            self::connection()
        );
    }

    public static function table(string $table): QueryBuilder
    {
        if (self::$grammar === null) {
            throw new \Exception(
                'Banco de dados não conectado.'
            );
        }

        return new QueryBuilder(
            self::connection(),
            $table,
            self::$grammar
        );
    }
}