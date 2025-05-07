<?php

namespace App\Presentation\Http\Routing;

use InvalidArgumentException;

/**
 * ControllerAction
 *
 * Represents a reference to a controller and an action method,
 * without instantiating or executing them directly.
 */
final class ControllerAction
{
    private readonly string $controllerClass;
    private readonly string $method;

    /**
     * @param string $controllerClass Fully-qualified controller class name.
     * @param string $method Name of the method to invoke.
     */
    public function __construct(string $controllerClass, string $method)
    {
        if (!class_exists($controllerClass)) {
            throw new InvalidArgumentException("Controller class {$controllerClass} does not exist.");
        }

        if (!method_exists($controllerClass, $method)) {
            throw new InvalidArgumentException("Method {$method} does not exist in class {$controllerClass}.");
        }

        $this->controllerClass = $controllerClass;
        $this->method = $method;
    }

    /**
     * Returns the fully-qualified controller class name.
     *
     * @return string
     */
    public function controllerClass(): string
    {
        return $this->controllerClass;
    }

    /**
     * Returns the action method name.
     *
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Structural comparison with another ControllerAction.
     *
     * @param ControllerAction $other
     * @return bool
     */
    public function equals(ControllerAction $other): bool
    {
        return $this->controllerClass === $other->controllerClass &&
               $this->method === $other->method;
    }

    /**
     * Returns string representation for debugging or logs.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->controllerClass}::{$this->method}";
    }
}
