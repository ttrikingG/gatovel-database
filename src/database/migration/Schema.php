<?php

namespace Gatovel\Database\migration;

use PDO;

class Schema
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function create(
        string $table,
        array $columns
    ): void {
        $definitions = [];

        foreach ($columns as $name => $definition) {
            $definitions[] = "{$name} {$definition}";
        }

        $sql = sprintf(
            'CREATE TABLE %s (%s)',
            $table,
            implode(', ', $definitions)
        );

        $this->connection->exec($sql);
    }

    public function drop(string $table): void
    {
        $this->connection->exec(
            "DROP TABLE {$table}"
        );
    }
}
