<?php

namespace App\Interfaces\Infrastructure;

use Throwable;

/**
 * Contrato para qualquer manipulador de exceções da aplicação.
 */
interface ExceptionHandlerInterface
{
    /**
     * Método para tratar exceções lançadas no sistema.
     *
     * @param Throwable $exception
     * @return void
     */
    public function handle(Throwable $exception): void;
}
