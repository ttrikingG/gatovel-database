<?php

namespace Gatovel\Database\migration;

use Gatovel\Database\Database;

class MigrationRunner
{
    private MigrationRepository $repository;

    public function __construct()
    {
        $this->repository = new MigrationRepository(
            Database::connection()
        );

        $this->repository->createTable();
    }

    /**
     * @param Migration[] $migrations
     */
    public function run(array $migrations): void
    {
        $batch = $this->repository->getLastBatch() + 1;

        foreach ($migrations as $migration) {

            $name = $migration::class;

            if ($this->repository->hasRun($name)) {
                continue;
            }

            $migration->up();

            $this->repository->log(
                $name,
                $batch
            );
        }
    }

    /**
     * @param Migration[] $migrations
     */
    public function rollback(array $migrations): void
    {
        $executed = $this->repository->getLastBatchMigrations();

        foreach ($executed as $record) {

            foreach ($migrations as $migration) {

                if ($migration::class !== $record['migration']) {
                    continue;
                }

                $migration->down();

                $this->repository->delete(
                    $record['migration']
                );

                break;
            }
        }
    }

    public function migrate(string $directory): void
    {
        $loader = new MigrationLoader();

        $migrations = $loader->load($directory);

        $this->run($migrations);
    }

    public function rollbackLastBatch(string $directory): void
    {
        $loader = new MigrationLoader();

        $migrations = $loader->load($directory);

        $this->rollback($migrations);
    }
}