<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Infrastructure\Support;

use App\Infrastructure\Session\Domain\Contracts\SessionHandlerInterface;
use App\Infrastructure\Session\Infrastructure\Drivers\NativeSessionHandler;

/**
 * SessionHandlerClassMap
 *
 * Maps session driver identifiers to their corresponding handler class names.
 * This class does not perform validation or throw exceptions; it is purely a data mapping utility.
 */
final class SessionHandlerClassMap
{
    /**
     * Returns the map of supported drivers to their handler class names.
     *
     * @return array<string, class-string<SessionHandlerInterface>>
     */
    public static function map(): array
    {
        return [
            'native' => NativeSessionHandler::class,
            // 'redis' => RedisSessionHandler::class,
            // 'array' => ArraySessionHandler::class,
        ];
    }

    /**
     * Returns the handler class name for the given driver key,
     * or null if there is no mapping.
     *
     * @param string $driver The driver key (e.g. 'native', 'redis').
     * @return class-string<SessionHandlerInterface>|null
     */
    public static function handlerClassFor(string $driver): ?string
    {
        $map = self::map();
        return $map[$driver] ?? null;
    }
}
