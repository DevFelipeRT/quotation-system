<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Routing\Presentation\Http\Routing\Contracts\RouteRequestInterface;

/**
 * ControllerInterface
 *
 * Defines the contract for all HTTP controllers within the application.
 * Controllers are responsible for processing a structured HTTP request and producing a response.
 */
interface ControllerInterface
{
    /**
     * Handles an incoming HTTP request and produces a string-based response.
     *
     * @param RouteRequestInterface $request Structured request containing HTTP method, path, host, etc.
     * @return string Rendered response (HTML, JSON, etc.).
     */
    public function handle(RouteRequestInterface $request): string;
}
