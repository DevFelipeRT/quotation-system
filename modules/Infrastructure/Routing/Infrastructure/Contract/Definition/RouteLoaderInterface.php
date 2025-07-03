<?php

declare(strict_types=1);

namespace Routing\Infrastructure\Contract\Definition;

use Routing\Infrastructure\Definition\RouteCollector;

/**
 * Defines the contract for a route loader.
 *
 * A route loader is responsible for loading route definitions from a specific
 * source (e.g., a file, an array) and registering them into a RouteCollector.
 */
interface RouteLoaderInterface
{
    /**
     * Loads route definitions into the provided collector.
     *
     * @param RouteCollector $collector The route collector instance to be populated.
     * @return void
     */
    public function load(RouteCollector $collector): void;
}