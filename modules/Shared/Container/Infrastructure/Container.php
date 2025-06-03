<?php

declare(strict_types=1);

namespace Container\Infrastructure;

use Container\Infrastructure\Contracts\ContainerScopeInterface;
use Container\Infrastructure\Contracts\ResolverInterface;
use Container\Infrastructure\Autowiring\FactoryResolver;
use Container\Infrastructure\Autowiring\ReflectionResolver;
use Container\Infrastructure\Scope\SingletonScope;
use Container\Infrastructure\Scope\TransientScope;
use Container\Infrastructure\Exceptions\NotFoundException;
use Container\Infrastructure\Exceptions\ContainerException;
use Container\Infrastructure\Exceptions\CircularDependencyException;
use Container\Domain\ValueObjects\Binding;
use Container\Domain\ValueObjects\BindingType;
use PublicContracts\Container\ServiceProviderInterface;
use PublicContracts\Container\ContainerInterface;
use Closure;


/**
 * Dependency Injection Container
 *
 * Provides service binding and resolution supporting singleton and transient lifecycles,
 * modular service providers, and advanced reflection-based autowiring.
 *
 * The container manages the full service lifecycle, detects circular dependencies,
 * and allows runtime extension of its internal scopes and resolvers.
 *
 * @package Container\Infrastructure
 */
class Container implements ContainerInterface
{
    /**
     * @var array<string, Binding> Registered service bindings.
     */
    protected array $bindings = [];

    /**
     * @var array<string, ContainerScopeInterface> Registered lifecycle scopes, keyed by BindingType value.
     */
    protected array $scopes = [];

    /**
     * @var ResolverInterface Autowiring resolver for services not explicitly bound.
     */
    protected ResolverInterface $autowiringResolver;

    /**
     * @var FactoryResolver Resolver for factory-based bindings.
     */
    protected FactoryResolver $factoryResolver;

    /**
     * Container constructor.
     *
     * @param ResolverInterface|null $autowiringResolver Optional custom resolver for autowiring.
     * @param FactoryResolver|null $factoryResolver Optional custom resolver for factories.
     */
    public function __construct(?ResolverInterface $autowiringResolver = null, ?FactoryResolver $factoryResolver = null)
    {
        $this->scopes = [
            BindingType::SINGLETON->value => new SingletonScope(),
            BindingType::TRANSIENT->value => new TransientScope(),
        ];
        $this->autowiringResolver = $autowiringResolver ?? new ReflectionResolver();
        $this->factoryResolver = $factoryResolver ?? new FactoryResolver();
    }

    /**
     * Registers a service binding.
     *
     * @param string $id Service identifier.
     * @param callable $factory Factory function to create the service.
     * @param bool $singleton If true, uses singleton lifecycle; otherwise transient.
     */
    public function bind(string $id, callable $factory, bool $singleton = true): void
    {
        $type = $singleton ? BindingType::SINGLETON : BindingType::TRANSIENT;
        $this->bindings[$id] = new Binding($id, Closure::fromCallable($factory), $type);
        $this->scopes[$type->value]->clear($id);
    }

    /**
     * Registers a singleton binding (always reused).
     *
     * @param string $id Service identifier.
     * @param callable $factory Factory function to create the service.
     */
    public function singleton(string $id, callable $factory): void
    {
        $this->bind($id, $factory, true);
    }

    /**
     * Resolves a service instance by its identifier.
     * Supports autowiring fallback for unbound classes.
     *
     * @param string $id Service identifier or class name.
     * @return mixed Resolved service instance.
     * @throws NotFoundException
     * @throws ContainerException
     * @throws CircularDependencyException
     */
    public function get(string $id): mixed
    {
        // Only allow autowiring for valid FQCNs if not explicitly bound
        if (!array_key_exists($id, $this->bindings) && !class_exists($id)) {
            throw new NotFoundException($id);
        }
        return $this->resolve($id, []);
    }

    /**
     * Internal recursive resolution engine.
     * Tracks dependency resolution stack to detect cycles.
     *
     * @param string $id Service identifier.
     * @param array $resolutionStack Current resolution stack for cycle detection.
     * @return mixed Resolved service instance.
     * @throws NotFoundException
     * @throws ContainerException
     * @throws CircularDependencyException
     */
    protected function resolve(string $id, array $resolutionStack): mixed
    {
        if (in_array($id, $resolutionStack, true)) {
            throw new CircularDependencyException($id, [...$resolutionStack, $id]);
        }

        // Explicit binding
        if (array_key_exists($id, $this->bindings)) {
            $binding = $this->bindings[$id];
            $scopeKey = $binding->getType()->value;
            $scope = $this->scopes[$scopeKey];
            return $scope->resolve($id, function () use ($binding) {
                return $this->factoryResolver->resolveFactory($binding->getFactory(), $this);
            });
        }

        // Autowiring fallback (recursion handled by the resolver)
        try {
            // CORREÇÃO CRÍTICA: Não inclua $id no stack aqui!
            return $this->autowiringResolver->resolve($id, $this, $resolutionStack);
        } catch (NotFoundException $e) {
            throw new NotFoundException($id, $e);
        } catch (CircularDependencyException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ContainerException("Failed to autowire dependency: {$id}", 0, $e);
        }
    }

    /**
     * Checks if a binding exists for the given identifier.
     * Only returns true for explicit bindings.
     *
     * @param string $id Service identifier.
     * @return bool True if an explicit binding exists, false otherwise.
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->bindings);
    }

    /**
     * Removes a specific binding and its instance from the container.
     *
     * @param string $id Service identifier to remove.
     */
    public function clear(string $id): void
    {
        if (isset($this->bindings[$id])) {
            $scopeKey = $this->bindings[$id]->getType()->value;
            $scope = $this->scopes[$scopeKey];
            $scope->clear($id);
            unset($this->bindings[$id]);
        }
    }

    /**
     * Clears all bindings and singleton/transient instances.
     */
    public function reset(): void
    {
        foreach ($this->scopes as $scope) {
            $scope->clearAll();
        }
        $this->bindings = [];
    }

    /**
     * Registers a service provider, allowing modular service definitions.
     *
     * @param ServiceProviderInterface $provider Provider to register.
     */
    public function registerProvider(ServiceProviderInterface $provider): void
    {
        $provider->register($this);
    }

    /**
     * Registers or replaces a scope for a given binding lifecycle type.
     *
     * @param BindingType $type
     * @param ContainerScopeInterface $scope
     */
    public function setScope(BindingType $type, ContainerScopeInterface $scope): void
    {
        $this->scopes[$type->value] = $scope;
    }
}
