<?php

declare(strict_types=1);

namespace App\Shared\UrlResolver;

/**
 * AppUrlResolver
 *
 * Resolves the absolute base URL for the current HTTP application runtime.
 * This class isolates all logic related to HTTP environment inspection for 
 * URL composition, maintaining separation from domain or configuration concerns.
 *
 * Usage:
 *   $resolver = new AppUrlResolver($mountPoint);
 *   $baseUrl = $resolver->baseUrl(); // e.g., "https://example.com/my-app/"
 */
final class AppUrlResolver implements UrlResolverInterface
{
    /**
     * @var string Relative path under which the application is mounted (from document root)
     */
    private string $appDirectory;

    /**
     * Constructor
     *
     * @param string $appDirectory Path where the application is mounted relative to the host (e.g., "/public")
     */
    public function __construct(string $appDirectory)
    {
        $this->appDirectory = $this->normalizePath($appDirectory);
    }

    /**
     * Returns the fully qualified base URL, including scheme, host and mount point.
     *
     * @return string Fully qualified base URL, ending with a slash
     */
    public function baseUrl(): string
    {
        $scheme = $this->detectScheme();
        $host = $this->detectHost();

        return $scheme . $host . $this->appDirectory;
    }

    /**
     * Detects the request scheme (http or https).
     *
     * @return string
     */
    private function detectScheme(): string
    {
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return $isSecure ? 'https://' : 'http://';
    }

    /**
     * Detects the HTTP host.
     *
     * @return string
     */
    private function detectHost(): string
    {
        return $_SERVER['HTTP_HOST'] ?? 'localhost';
    }

    /**
     * Ensures path starts with '/' and ends with '/' (or is just '/').
     *
     * @param string $path
     * @return string
     */
    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return rtrim($normalized, '/') . '/';
    }
}
