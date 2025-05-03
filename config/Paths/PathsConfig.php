<?php

namespace Config\Paths;

/**
 * PathsConfig
 *
 * Centralizes absolute filesystem paths used by the application runtime.
 * Ensures stable, predictable access to directories and files across components.
 *
 * All paths are resolved from BASE_PATH (defined globally) and exposed
 * via strongly typed, read-only accessors.
 */
class PathsConfig
{
    private string $logsDir;
    private string $templatesDir;
    private string $viewsDir;
    private string $envFile;
    private string $indexFile;
    private string $manageItemsFile;
    private string $appDirectory;

    /**
     * Initializes all filesystem path references from BASE_PATH.
     */
    public function __construct()
    {
        $this->logsDir         = BASE_PATH . '/storage/logs';
        $this->templatesDir    = BASE_PATH . '/public/assets/templates';
        $this->viewsDir        = BASE_PATH . '/src/Presentation/Http/Templates';
        $this->envFile         = BASE_PATH . '/.env';
        $this->indexFile       = BASE_PATH . '/public/index.php';
        $this->manageItemsFile = BASE_PATH . '/public/manage_items.php';

        $this->appDirectory = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    }

    /**
     * Returns the path to the log directory.
     */
    public function logsDir(): string
    {
        return $this->logsDir;
    }

    /**
     * Returns the path to the static templates directory (public layout).
     */
    public function templatesDir(): string
    {
        return $this->templatesDir;
    }

    /**
     * Returns the path to the rendered MVC view templates.
     */
    public function viewsDir(): string
    {
        return $this->viewsDir;
    }

    /**
     * Returns the path to the environment definition file (.env).
     */
    public function envFile(): string
    {
        return $this->envFile;
    }

    /**
     * Returns the path to the front controller (index.php).
     */
    public function indexFile(): string
    {
        return $this->indexFile;
    }

    /**
     * Returns the path to the manage_items.php interface.
     */
    public function manageItemsFile(): string
    {
        return $this->manageItemsFile;
    }

    /**
     * Returns the URL base path under which the app is mounted.
     */
    public function appDirectory(): string
    {
        return $this->appDirectory;
    }
}
