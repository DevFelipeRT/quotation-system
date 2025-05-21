<?php

declare(strict_types=1);

namespace App\Shared\UrlResolver;

/**
 * UrlResolverInterface
 *
 * Defines a contract for resolving the absolute base URL of the application
 * based on the HTTP execution environment. This is typically used by
 * presentation-layer components to generate consistent links regardless
 * of deployment context (local, staging, production).
 *
 * Implementations may extract protocol, host, and mount path dynamically
 * from server variables or configuration sources.
 */
interface UrlResolverInterface
{
    /**
     * Returns the fully qualified base URL under which the application is mounted.
     *
     * Examples:
     *  - "https://example.com/"
     *  - "http://localhost/my-app/"
     *
     * The returned URL must:
     *  - Include protocol (http/https)
     *  - Include host
     *  - Include path to application root (if not at "/")
     *  - Always end with a single trailing slash
     *
     * @return string
     */
    public function baseUrl(): string;
}
