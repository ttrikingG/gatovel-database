<?php

namespace Gatovel\Database\exceptions;

class UnsupportedDriverException extends DatabaseException
{
    public function __construct(
        string $driver
    ) {
        parent::__construct(
            "Driver de banco não suportado: {$driver}"
        );
    }
}
