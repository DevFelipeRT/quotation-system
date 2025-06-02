<?php

declare(strict_types=1);

namespace Session\Infrastructure\Resolvers;

use Session\Domain\Contracts\SessionHandlerInterface;
use Session\Domain\Contracts\SessionHandlerResolverInterface;
use Session\Exceptions\UnsupportedSessionDriverException;
use Session\Infrastructure\Support\SessionHandlerDriverValidator;
use Session\Infrastructure\Support\SessionHandlerClassMap;
use Config\Session\SessionConfig;

/**
 * Resolves the handler class name for the session driver configured in SessionConfig.
 *
 * Ensures the configured driver is supported and mapped to a handler implementation.
 */
final class DefaultSessionHandlerResolver implements SessionHandlerResolverInterface
{
    private SessionConfig $config;

    public function __construct(SessionConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Returns the class name of the session handler implementation for the configured driver.
     *
     * @return class-string<SessionHandlerInterface>
     *
     * @throws UnsupportedSessionDriverException
     */
    public function resolve(): string
    {
        $driver = $this->getConfiguredDriver();
        $this->validateDriverSupport($driver);
        return $this->resolveHandlerClass($driver);
    }

    /**
     * Retrieves the driver configured as default in SessionConfig.
     *
     * @return string
     */
    private function getConfiguredDriver(): string
    {
        return $this->config->defaultDriver();
    }

    /**
     * Validates that the provided driver is supported by the system.
     *
     * @param string $driver
     *
     * @throws UnsupportedSessionDriverException
     */
    private function validateDriverSupport(string $driver): void
    {
        SessionHandlerDriverValidator::ensureSupported($driver);
    }

    /**
     * Resolves the handler class mapped to the given driver.
     *
     * @param string $driver
     * @return class-string<SessionHandlerInterface>
     *
     * @throws UnsupportedSessionDriverException
     */
    private function resolveHandlerClass(string $driver): string
    {
        $handlerClass = SessionHandlerClassMap::handlerClassFor($driver);

        if ($handlerClass === null) {
            throw new UnsupportedSessionDriverException(
                "No handler class mapped for session driver '{$driver}'."
            );
        }

        return $handlerClass;
    }
}
