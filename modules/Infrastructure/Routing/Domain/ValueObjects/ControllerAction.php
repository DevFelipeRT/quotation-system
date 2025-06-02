<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * ControllerAction
 *
 * Represents a validated reference to a controller class and an action method,
 * without instantiating or executing them directly.
 */
final class ControllerAction
{
    private readonly string $controllerClass;
    private readonly string $method;

    /**
     * ControllerAction constructor.
     *
     * @param string $controllerClass Fully-qualified controller class name.
     * @param string $method Name of the method to invoke.
     * @throws InvalidArgumentException If the class or method are invalid.
     */
    public function __construct(string $controllerClass, string $method)
    {
        $this->controllerClass = $this->normalizeControllerClass($controllerClass);
        $this->method = $this->normalizeMethod($method);

        $this->validateControllerClass($this->controllerClass);
        $this->validateControllerMethod($this->controllerClass, $this->method);
    }

    /**
     * Returns the fully-qualified controller class name.
     *
     * @return string
     */
    public function class(): string
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

    /**
     * Normalizes the controller class name.
     *
     * @param string $controllerClass
     * @return string
     */
    private function normalizeControllerClass(string $controllerClass): string
    {
        return ltrim(trim($controllerClass), '\\');
    }

    /**
     * Normalizes the method name.
     *
     * @param string $method
     * @return string
     */
    private function normalizeMethod(string $method): string
    {
        return trim($method);
    }

    /**
     * Validates that the controller class exists.
     *
     * @param string $controllerClass
     * @throws InvalidArgumentException
     */
    private function validateControllerClass(string $controllerClass): void
    {
        if (!class_exists($controllerClass)) {
            throw new InvalidArgumentException("Controller class {$controllerClass} does not exist.");
        }
    }

    /**
     * Validates that the controller method exists in the given class.
     *
     * @param string $controllerClass
     * @param string $method
     * @throws InvalidArgumentException
     */
    private function validateControllerMethod(string $controllerClass, string $method): void
    {
        if (!method_exists($controllerClass, $method)) {
            throw new InvalidArgumentException("Method {$method} does not exist in class {$controllerClass}.");
        }
    }
}
