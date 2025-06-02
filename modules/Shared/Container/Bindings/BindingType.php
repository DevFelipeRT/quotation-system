<?php

declare(strict_types=1);

namespace Container\Bindings;

/**
 * Enum BindingType
 *
 * Defines possible lifecycles for service bindings within the container.
 */
enum BindingType: string
{
    case SINGLETON = 'singleton';
    case TRANSIENT = 'transient';
    // Future cases: case SCOPED = 'scoped';
}
