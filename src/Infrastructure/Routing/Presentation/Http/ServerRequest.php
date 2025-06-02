<?php

declare(strict_types=1);

namespace App\Infrastructure\Routing\Presentation\Http;

use App\Infrastructure\Routing\Domain\ValueObjects\HttpMethod;
use App\Infrastructure\Routing\Domain\ValueObjects\RoutePath;
use App\Infrastructure\Routing\Presentation\Http\Contracts\ServerRequestInterface;
use InvalidArgumentException;

/**
 * Class ServerRequest
 *
 * Default implementation of ServerRequestInterface.
 * Represents an immutable and validated HTTP server request.
 */
final class ServerRequest implements ServerRequestInterface
{
    private readonly HttpMethod $method;
    private readonly RoutePath $path;
    private readonly string $host;
    private readonly string $scheme;

    /**
     * ServerRequest constructor.
     *
     * @param HttpMethod $method
     * @param RoutePath $path
     * @param string $host
     * @param string $scheme
     * @throws InvalidArgumentException If host or scheme are invalid.
     */
    public function __construct(
        HttpMethod $method,
        RoutePath $path,
        string $host,
        string $scheme
    ) {
        $host = $this->normalizeHost($host);
        $scheme = $this->normalizeScheme($scheme);

        $this->validateHost($host);
        $this->validateScheme($scheme);

        $this->method = $method;
        $this->path = $path;
        $this->host = $host;
        $this->scheme = $scheme;
    }

    public function method(): HttpMethod
    {
        return $this->method;
    }

    public function path(): RoutePath
    {
        return $this->path;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function scheme(): string
    {
        return $this->scheme;
    }

    public function equals(ServerRequestInterface $other): bool
    {
        return $this->method->equals($other->method())
            && $this->path->equals($other->path())
            && $this->host === $other->host()
            && $this->scheme === $other->scheme();
    }

    /**
     * Normalizes the host string (trims and lowercases).
     */
    private function normalizeHost(string $host): string
    {
        return strtolower(trim($host));
    }

    /**
     * Normalizes the scheme string (trims and lowercases).
     */
    private function normalizeScheme(string $scheme): string
    {
        return strtolower(trim($scheme));
    }

    /**
     * Validates the host value.
     *
     * @throws InvalidArgumentException
     */
    private function validateHost(string $host): void
    {
        if ($host === '') {
            throw new InvalidArgumentException('Host must not be empty.');
        }
        // Basic validation: RFC 3986 host (letters, digits, '-', '.', ':')
        if (!preg_match('/^[a-z0-9\.\-:]+$/', $host)) {
            throw new InvalidArgumentException("Invalid host: {$host}");
        }
    }

    /**
     * Validates the scheme value.
     *
     * @throws InvalidArgumentException
     */
    private function validateScheme(string $scheme): void
    {
        if ($scheme === '') {
            throw new InvalidArgumentException('Scheme must not be empty.');
        }
        $allowed = ['http', 'https'];
        if (!in_array($scheme, $allowed, true)) {
            throw new InvalidArgumentException("Invalid scheme: {$scheme}");
        }
    }
}
