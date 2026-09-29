<?php

namespace Gatovel\Database\migration;

use Gatovel\Database\Database;
use Gatovel\Database\exceptions\DatabaseException;

class MigrationRunner
{
    private MigrationRepository $repository;

    private MigrationLoader $loader;

    public function __construct()
    {
        $this->repository = new MigrationRepository(
            Database::connection(),
            Database::schema()
        );

        $this->loader = new MigrationLoader();

        $this->repository->createTable();
    }

    /**
     * @param LoadedMigration[] $migrations
     */
    public function run(
        array $migrations
    ): void {
        $batch = $this->repository->getLastBatch() + 1;

        foreach ($migrations as $loadedMigration) {
            $name = $loadedMigration->name();

            if ($this->repository->hasRun($name)) {
                continue;
            }

            $loadedMigration
                ->migration()
                ->up();

            $this->repository->log(
                $name,
                $batch
            );
        }
    }

    /**
     * @param LoadedMigration[] $migrations
     */
    public function rollback(
        array $migrations
    ): void {
        $executed = $this->repository
            ->getLastBatchMigrations();

        foreach ($executed as $record) {
            $loadedMigration = $this->findMigration(
                $migrations,
                $record['migration']
            );

            if ($loadedMigration === null) {
                throw new DatabaseException(
                    'Migration executada não encontrada para rollback: '
                    . $record['migration']
                );
            }

            $loadedMigration
                ->migration()
                ->down();

            $this->repository->delete(
                $record['migration']
            );
        }
    }

    public function migrate(
        string $directory
    ): void {
        $migrations = $this->loader->load(
            $directory
        );

        $this->run(
            $migrations
        );
    }

    public function rollbackLastBatch(
        string $directory
    ): void {
        $migrations = $this->loader->load(
            $directory
        );

        $this->rollback(
            $migrations
        );
    }

    /**
     * @param LoadedMigration[] $migrations
     */
    private function findMigration(
        array $migrations,
        string $name
    ): ?LoadedMigration {
        foreach ($migrations as $loadedMigration) {
            if ($loadedMigration->name() === $name) {
                return $loadedMigration;
            }
        }

        return null;
    }
}
