<?php

namespace Gatovel\Database\migration;

use Closure;
use Gatovel\Database\migration\schema\Blueprint;
use Gatovel\Database\migration\schema\SchemaGrammar;
use PDO;

class Schema
{
    public function __construct(
        private PDO $connection,
        private SchemaGrammar $grammar
    ) {
    }

    public function create(
        string $table,
        Closure $callback
    ): void {
        $blueprint = new Blueprint(
            $table
        );

        $callback(
            $blueprint
        );

        $sql = $this->grammar->compileCreate(
            $blueprint
        );

        $this->connection->exec(
            $sql
        );
    }

    public function drop(
        string $table
    ): void {
        $sql = $this->grammar->compileDrop(
            $table
        );

        $this->connection->exec(
            $sql
        );
    }

    public function addColumn(
        string $table,
        Closure $callback
    ): void {
        $blueprint = new Blueprint(
            $table
        );

        $callback(
            $blueprint
        );

        foreach ($blueprint->columns() as $column) {
            $sql = $this->grammar->compileAddColumn(
                $table,
                $column
            );

            $this->connection->exec(
                $sql
            );
        }
    }
}
