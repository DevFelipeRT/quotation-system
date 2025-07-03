<?php

declare(strict_types=1);

namespace Routing\Infrastructure\Definition;

use Routing\Infrastructure\Contract\Definition\RouteLoaderInterface;
use Routing\Infrastructure\Definition\RouteCollector;

/**
 * Loads route definitions from a PHP file that returns an array.
 */
final class PhpArrayRouteLoader implements RouteLoaderInterface
{
    /**
     * @param string $filePath The absolute path to the PHP route file.
     * @throws \InvalidArgumentException if the file does not exist or is not readable.
     */
    public function __construct(
        private readonly string $filePath
    ) {
        if (!is_file($this->filePath) || !is_readable($this->filePath)) {
            throw new \InvalidArgumentException("Route file not found or not readable: {$this->filePath}");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(RouteCollector $collector): void
    {
        $routes = require $this->filePath;

        if (!is_array($routes)) {
            throw new \RuntimeException("Route file must return an array: {$this->filePath}");
        }

        foreach ($routes as $route) {
            $this->validateRouteArray($route);

            $verb = $route[0];
            $path = $route[1];
            $handler = $route[2];

            $collector->add($verb, $path, $handler);
        }
    }

    /**
     * Validates the structure of a single route definition array.
     *
     * @param mixed $route
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateRouteArray(mixed $route): void
    {
        if (!is_array($route) || count($route) < 3) {
            throw new \InvalidArgumentException('Invalid route definition format. Expected [verb, path, handler].');
        }
    }
}