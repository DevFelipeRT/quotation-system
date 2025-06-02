<?php

declare(strict_types=1);

namespace Tests\ClassDiscovery\Classes;

use Tests\ClassDiscovery\Interfaces\TestInterface;

final class ImplB implements TestInterface
{
    public function doSomething(): string
    {
        return 'ImplB';
    }
}
