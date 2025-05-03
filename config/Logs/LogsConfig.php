<?php

namespace Config\Logs;

/**
 * LogsConfig
 *
 * Provides centralized, immutable access to log file paths used throughout
 * the system. Each log path corresponds to a specific operational context
 * (e.g., database operations, controller execution, model behavior).
 *
 * All paths are resolved relative to the /storage/logs directory. If the
 * base directory does not exist, fallback resolution is used.
 */
class LogsConfig
{
    /**
     * Path to the database activity log file.
     */
    private string $databaseLog;

    /**
     * Path to the SQL execution log file.
     */
    private string $executeQueryLog;

    /**
     * Path to the controllers' execution log file.
     */
    private string $controllersLog;

    /**
     * Path to the general model logic log file.
     */
    private string $modelsLog;

    /**
     * Path to the item-specific model log file.
     */
    private string $itemModelLog;

    /**
     * Initializes paths for all log files, using relative resolution.
     */
    public function __construct()
    {
        $base = realpath(__DIR__ . '/../storage/logs') ?: __DIR__ . '/../storage/logs';

        $this->databaseLog      = $base . '/database_log.txt';
        $this->executeQueryLog  = $base . '/execute_query_log.txt';
        $this->controllersLog   = $base . '/controllers_log.txt';
        $this->modelsLog        = $base . '/models_log.txt';
        $this->itemModelLog     = $base . '/item_model_log.txt';
    }

    /**
     * Get the path to the database log file.
     */
    public function database(): string
    {
        return $this->databaseLog;
    }

    /**
     * Get the path to the query execution log file.
     */
    public function executeQuery(): string
    {
        return $this->executeQueryLog;
    }

    /**
     * Get the path to the controllers' log file.
     */
    public function controllers(): string
    {
        return $this->controllersLog;
    }

    /**
     * Get the path to the general models' log file.
     */
    public function models(): string
    {
        return $this->modelsLog;
    }

    /**
     * Get the path to the item model-specific log file.
     */
    public function itemModel(): string
    {
        return $this->itemModelLog;
    }
}
