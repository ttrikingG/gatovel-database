<?php

namespace Gatovel\Database\migration;

use Gatovel\Database\migration\schema\Blueprint;
use PDO;

class MigrationRepository
{
    public function __construct(
        private PDO $connection,
        private Schema $schema
    ) {
    }

    public function createTable(): void
    {
        if ($this->tableExists()) {
            return;
        }

        $this->schema->create(
            'migrations',
            function (Blueprint $table): void {
                $table->id();
                $table->string(
                    'migration',
                    255
                );
                $table->integer(
                    'batch'
                );
            }
        );
    }

    public function hasRun(
        string $migration
    ): bool {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*) FROM migrations WHERE migration = ?'
        );

        $statement->execute([
            $migration,
        ]);

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
            $batch,
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

        $statement->execute([
            $batch,
        ]);

        return $statement->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    public function delete(
        string $migration
    ): void {
        $statement = $this->connection->prepare(
            'DELETE FROM migrations WHERE migration = ?'
        );

        $statement->execute([
            $migration,
        ]);
    }

    private function tableExists(): bool
    {
        try {
            $this->connection->query(
                'SELECT 1 FROM migrations WHERE 1 = 0'
            );

            return true;
        } catch (\PDOException) {
            return false;
        }
    }
}
