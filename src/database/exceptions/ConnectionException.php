<?php

namespace Gatovel\Database\exceptions;

use Throwable;

class ConnectionException extends DatabaseException
{
    public function __construct(
        string $message = 'Erro ao conectar ao banco de dados.',
        ?Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            0,
            $previous
        );
    }
}
