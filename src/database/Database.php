<?php

namespace Gatovel\Database;

use Gatovel\Database\connection\Connection;
use Gatovel\Database\exceptions\DatabaseException;
use Gatovel\Database\exceptions\UnsupportedDriverException;
use Gatovel\Database\migration\Schema;
use Gatovel\Database\migration\schema\SchemaGrammar;
use Gatovel\Database\migration\schema\grammars\MySQLSchemaGrammar;
use Gatovel\Database\migration\schema\grammars\PostgresSchemaGrammar;
use Gatovel\Database\migration\schema\grammars\SQLiteSchemaGrammar;
use Gatovel\Database\query\Grammar;
use Gatovel\Database\query\QueryBuilder;
use Gatovel\Database\query\grammars\MySQLGrammar;
use Gatovel\Database\query\grammars\PostgresGrammar;
use Gatovel\Database\query\grammars\SQLiteGrammar;
use Gatovel\Database\transaction\Transaction;
use PDO;

class Database
{
    private static ?Connection $connection = null;

    private static ?Grammar $grammar = null;

    private static ?SchemaGrammar $schemaGrammar = null;

    public static function connect(
        array $config
    ): void {
        $connection = new Connection(
            $config
        );

        $driver = $config['connection'];

        $grammar = match ($driver) {
            'mysql' => new MySQLGrammar(),

            'pgsql' => new PostgresGrammar(),

            'sqlite' => new SQLiteGrammar(),

            default => throw new UnsupportedDriverException(
                $driver
            ),
        };

        $schemaGrammar = match ($driver) {
            'mysql' => new MySQLSchemaGrammar(),

            'pgsql' => new PostgresSchemaGrammar(),

            'sqlite' => new SQLiteSchemaGrammar(),

            default => throw new UnsupportedDriverException(
                $driver
            ),
        };

        self::$connection = $connection;
        self::$grammar = $grammar;
        self::$schemaGrammar = $schemaGrammar;
    }

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            throw new DatabaseException(
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
        if (self::$schemaGrammar === null) {
            throw new DatabaseException(
                'Banco de dados não conectado.'
            );
        }

        return new Schema(
            self::connection(),
            self::$schemaGrammar
        );
    }

    public static function table(
        string $table
    ): QueryBuilder {
        if (self::$grammar === null) {
            throw new DatabaseException(
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
