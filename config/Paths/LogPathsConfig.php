<?php

namespace Config\Paths;

use InvalidArgumentException;

/**
 * Class LogPathsConfig
 *
 * Resolves log file paths based on logical context identifiers.
 * All paths are relative to a standard storage directory.
 *
 * @package Config\Paths
 */
final class LogPathsConfig
{
    private const PATH_MAP = [
        'database'      => 'database_log.txt',
        'execute_query' => 'execute_query_log.txt',
        'controllers'   => 'controllers_log.txt',
        'models'        => 'models_log.txt',
        'item_model'    => 'item_model_log.txt',
    ];

    private string $basePath;

    /**
     * LogPathsConfig constructor.
     *
     * @param PathsConfig $pathsConfig Source for base log directory.
     */
    public function __construct(PathsConfig $pathsConfig)
    {
        $this->basePath = $pathsConfig->getLogsDirPath();
    }

    /**
     * Returns the full path to a log file for a given identifier.
     *
     * @param string $pathName Logical name of the log context.
     * @return string
     * @throws InvalidArgumentException if the identifier is unknown.
     */
    public function getLogPath(string $pathName): string
    {
        if (!isset(self::PATH_MAP[$pathName])) {
            throw new InvalidArgumentException("Unknown log path identifier: '{$pathName}'");
        }

        return $this->basePath . '/' . self::PATH_MAP[$pathName];
    }

    /**
     * Returns all resolved log paths mapped by their identifiers.
     *
     * @return array<string, string>
     */
    public function getAllLogPaths(): array
    {
        $paths = [];
        foreach (self::PATH_MAP as $key => $file) {
            $paths[$key] = $this->basePath . '/' . $file;
        }
        return $paths;
    }
}
