<?php

namespace Gatovel\Database\transaction;

use PDO;

class Transaction
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function begin(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        $this->connection->rollBack();
    }
}
