<?php

namespace Tests\Controllers;

/**
 * Controlador de teste simples.
 */
class FakeController
{
    public function hello(): void
    {
        echo "Hello from test controller!";
    }

    public function notFound(): void
    {
        echo "This should not be called.";
    }
}
