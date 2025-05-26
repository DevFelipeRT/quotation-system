<?php

declare(strict_types=1);

namespace Config;

use Config\ConfigProvider;
use Config\App\AppConfig;
use Config\Paths\PathsConfig;

/**
 * SystemConstants
 *
 * Defines and exposes system-wide constants based on runtime configuration.
 * After initialization, constants are made globally accessible via `define()`.
 */
final class SystemConstants
{
    private static bool $initialized = false;

    private function __construct() {}

    public static function initialize(ConfigProvider $config): void
    {
        if (self::$initialized) {
            return;
        }

        $appConfig = $config->getAppConfig();
        $pathsConfig = $config->getPathsConfig();

        $constants = self::gatherConstants($appConfig, $pathsConfig);
        self::defineConstants($constants);

        self::$initialized = true;
    }

    /**
     * Collects constant key-value pairs from configuration sources.
     *
     * @param AppConfig $appConfig
     * @param PathsConfig $pathsConfig
     * @return array<string, mixed>
     */
    private static function gatherConstants(AppConfig $appConfig, PathsConfig $pathsConfig): array
    {
        $basePath = $pathsConfig->getBasePath();

        return [
            'SYS_APP_NAME'  => $appConfig->getName(),
            'VERSION'       => $appConfig->getVersion(),
            'BASE_PATH'     => $basePath,
            'SRC_DIR'      => $pathsConfig->getSrcDir(),
            'IS_CLI'        => PHP_SAPI === 'cli',
            'PSR4_PREFIX'   => $appConfig->getPsr4Prefix(),
        ];
    }

    /**
     * Defines each constant globally if not already defined.
     *
     * @param array<string, mixed> $constants
     */
    private static function defineConstants(array $constants): void
    {
        foreach ($constants as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}
