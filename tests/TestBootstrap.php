<?php

declare(strict_types=1);

use Config\Container\ConfigContainer;

require_once __DIR__ . '/../bootstrap.php';

/**
 * TestBootstrap
 *
 * Provides a shared instance of the application configuration container
 * for use in real integration tests of system modules.
 */

return new ConfigContainer();
