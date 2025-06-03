<?php

declare(strict_types=1);

namespace Container\Domain\ValueObjects;

use Closure;
use InvalidArgumentException;

/**
 * Class Binding
 *
 * Value Object representing the binding of a service or value in the container,
 * including its identifier, factory (closure), and lifecycle type.
 *
 * This class is strictly immutable and final. 
 */
final class Binding
{
    /**
     * The unique service identifier (commonly a FQCN or a container key).
     *
     * @var string
     */
    public readonly string $id;

    /**
     * The factory responsible for instantiating the service or value.
     *
     * @var Closure
     */
    public readonly Closure $factory;

    /**
     * The lifecycle type of the binding (singleton, transient, etc).
     *
     * @var BindingType
     */
    public readonly BindingType $type;

    /**
     * Constructs a new immutable Binding value object.
     *
     * @param string $id The unique identifier for the binding.
     * @param Closure $factory The instantiation factory for the service/value.
     * @param BindingType $type The lifecycle type of the binding.
     *
     * @throws InvalidArgumentException If any argument is invalid.
     */
    public function __construct(
        string $id,
        Closure $factory,
        BindingType $type
    ) {
        $this->id = $this->validateId($id);
        $this->factory = $this->validateFactory($factory);
        $this->type = $this->validateType($type);
    }

    /**
     * Returns the unique identifier of the binding.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the factory closure of the binding.
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
     * Compares this binding with another for value equality.
     * Note: closures are not comparable, so equality is limited to id and type.
     *
     * @param Binding $other
     * @return bool
     */
    public function equals(Binding $other): bool
    {
        return $this->id === $other->id
            && $this->type === $other->type;
    }

    /**
     * Prevents serialization of closures, which is unsafe and not supported.
     */
    public function __serialize(): array
    {
        throw new \LogicException('Binding objects cannot be serialized due to closure property.');
    }

    /**
     * Prevents unserialization for the same reason.
     */
    public function __unserialize(array $data): void
    {
        throw new \LogicException('Binding objects cannot be unserialized due to closure property.');
    }

    /**
     * Validates the binding identifier.
     *
     * @param string $id
     * @return string
     * @throws InvalidArgumentException
     */
    private function validateId(string $id): string
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Binding id must be a non-empty string.');
        }
        return $id;
    }

    /**
     * Validates the binding factory.
     *
     * @param Closure $factory
     * @return Closure
     * @throws InvalidArgumentException
     */
    private function validateFactory(Closure $factory): Closure
    {
        // Additional logic can be added here if factory requirements become stricter in the future
        return $factory;
    }

    /**
     * Validates the binding type.
     *
     * @param BindingType $type
     * @return BindingType
     * @throws InvalidArgumentException
     */
    private function validateType(BindingType $type): BindingType
    {
        // Type safety is already enforced by PHP, but additional checks could be added if necessary
        return $type;
    }
}
