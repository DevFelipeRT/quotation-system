<?php

declare(strict_types=1);

namespace App\Shared\Container\Infrastructure\Bindings;

use Closure;

/**
 * Class Binding
 *
 * Represents a service or value registered in the container, with associated factory and lifecycle type.
 */
class Binding
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var Closure
     */
    protected Closure $factory;

    /**
     * @var BindingType
     */
    protected BindingType $type;

    /**
     * Binding constructor.
     *
     * @param string $id
     * @param Closure $factory
     * @param BindingType $type
     */
    public function __construct(string $id, Closure $factory, BindingType $type = BindingType::SINGLETON)
    {
        $this->id = $id;
        $this->factory = $factory;
        $this->type = $type;
    }

    /**
     * Returns the factory callable for the binding.
     *
     * @return Closure
     */
    public function getFactory(): Closure
    {
        return $this->factory;
    }

    /**
     * Returns the lifecycle type of the binding.
     *
     * @return BindingType
     */
    public function getType(): BindingType
    {
        return $this->type;
    }

    /**
     * Returns the identifier of the binding.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
