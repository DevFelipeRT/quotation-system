<?php

declare(strict_types=1);

namespace Container;

use Container\Bindings\BindingType;
use Container\Scope\SingletonScope;
use Container\Scope\TransientScope;
use PublicContracts\Container\ContainerInterface;
use PublicContracts\Container\ServiceProviderInterface;

/**
 * Class ContainerBuilder
 *
 * Builds and configures the application's dependency container.
 *
 * This builder provides a fluent interface for defining service bindings,
 * registering custom scopes, and applying service providers before generating
 * a fully functional Container instance.
 *
 * Designed to support modular configuration and runtime flexibility.
 *
 * @package Container
 */
class ContainerBuilder
{
    /**
     * Service bindings to be registered in the container.
     *
     * Format: [service ID => [factory callable, isSingleton]]
     *
     * @var array<string, array{callable, bool}>
     */
    protected array $bindings = [];

    /**
     * List of service providers to be registered.
     *
     * @var ServiceProviderInterface[]
     */
    protected array $providers = [];

    /**
     * Mapping of binding types to scope implementations.
     *
     * @var array<string, object>
     */
    protected array $scopes = [];

    /**
     * Initializes default binding scopes: singleton and transient.
     */
    public function __construct()
    {
        $this->scopes = [
            BindingType::SINGLETON->value => new SingletonScope(),
            BindingType::TRANSIENT->value => new TransientScope(),
        ];
    }

    /**
     * Registers a service binding in the container.
     *
     * If a custom scope is defined prior to registration, it will take effect.
     *
     * @param string $id Service identifier (usually FQCN or alias).
     * @param callable $factory Factory function used to create the instance.
     * @param bool $singleton Whether the binding is singleton-scoped.
     * @return $this
     */
    public function bind(string $id, callable $factory, bool $singleton = false): self
    {
        $this->bindings[$id] = [$factory, $singleton];
        return $this;
    }

    /**
     * Registers a singleton binding.
     *
     * Alias for `bind(..., true)`.
     *
     * @param string $id
     * @param callable $factory
     * @return $this
     */
    public function singleton(string $id, callable $factory): self
    {
        return $this->bind($id, $factory, true);
    }

    /**
     * Adds a service provider to be applied during container build.
     *
     * @param ServiceProviderInterface $provider
     * @return $this
     */
    public function addProvider(ServiceProviderInterface $provider): self
    {
        $this->providers[] = $provider;
        return $this;
    }

    /**
     * Adds or overrides a scope implementation.
     *
     * Warning: calling this method will clear all previously registered bindings
     * to avoid inconsistent behavior caused by scope changes.
     *
     * @param BindingType $type
     * @param object $scope
     * @return $this
     */
    public function addScope(BindingType $type, object $scope): self
    {
        $this->scopes[$type->value] = $scope;
        $this->bindings = []; // Clear bindings to avoid scope mismatch
        return $this;
    }

    /**
     * Finalizes and builds the Container instance.
     *
     * This method applies all defined scopes, bindings, and service providers
     * into a fresh Container instance.
     *
     * @return ContainerInterface Fully initialized container.
     */
    public function build(): ContainerInterface
    {
        $container = new Container(null, null);

        // Apply custom scopes
        foreach ($this->scopes as $key => $scope) {
            $container->setScope(BindingType::from($key), $scope);
        }

        // Register explicit bindings
        foreach ($this->bindings as $id => [$factory, $singleton]) {
            if ($singleton) {
                $container->singleton($id, $factory);
            } else {
                $container->bind($id, $factory, false);
            }
        }

        // Apply service providers
        foreach ($this->providers as $provider) {
            $container->registerProvider($provider);
        }

        return $container;
    }
}
