<?php

namespace Config\Paths;

/**
 * Class PathsConfig
 *
 * Centralizes absolute filesystem paths used by the application runtime.
 * Ensures stable, predictable access to directories and files across components.
 *
 * @package Config\Paths
 */
final class PathsConfig
{
    private string $envFile;
    private string $logsDir;
    private string $templatesDir;
    private string $viewsDir;
    private string $indexFile;
    private string $appDirectory;

    /**
     * Initializes all filesystem path references from BASE_PATH,
     * or uses an explicitly provided base path.
     *
     * @param string|null $basePath Optional custom base path.
     */
    public function __construct(?string $basePath = null)
    {
        $base = $basePath ?? (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2));

        $this->envFile         = $base . '/.env';
        $this->logsDir         = $base . '/storage/logs';
        $this->templatesDir    = $base . '/src/Infrastructure/Rendering/Presentation/Templates';
        $this->viewsDir        = $base . '/src/Presentation/Http/Views';
        $this->indexFile       = $base . '/public/index.php';

        $this->appDirectory = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    }

    /** @return string */
    public function getEnvFilePath(): string
    {
        return $this->envFile;
    }

    /** @return string */
    public function getLogsDirPath(): string
    {
        return $this->logsDir;
    }

    /** @return string */
    public function getTemplatesPath(): string
    {
        return $this->templatesDir;
    }

    /** @return string */
    public function getViewsDirPath(): string
    {
        return $this->viewsDir;
    }

    /** @return string */
    public function getIndexFilePath(): string
    {
        return $this->indexFile;
    }

    /** @return string */
    public function getAppDirectory(): string
    {
        return $this->appDirectory;
    }
}
