<?php

namespace Gatovel\Database\migration;

use PDO;

class MigrationRepository
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function createTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL
            )
        ";

        $this->connection->exec($sql);
    }

    public function hasRun(string $migration): bool
    {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*) FROM migrations WHERE migration = ?'
        );

        $statement->execute([$migration]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function getLastBatch(): int
    {
        $statement = $this->connection->query(
            'SELECT MAX(batch) FROM migrations'
        );

        return (int) $statement->fetchColumn();
    }

    public function log(
        string $migration,
        int $batch
    ): void {
        $statement = $this->connection->prepare(
            'INSERT INTO migrations (migration, batch) VALUES (?, ?)'
        );

        $statement->execute([
            $migration,
            $batch
        ]);
    }

    public function getLastBatchMigrations(): array
    {
        $batch = $this->getLastBatch();

        if ($batch === 0) {
            return [];
        }

        $statement = $this->connection->prepare(
            'SELECT * FROM migrations WHERE batch = ? ORDER BY id DESC'
        );

        $statement->execute([$batch]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $migration): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM migrations WHERE migration = ?'
        );

        $statement->execute([$migration]);
    }
}