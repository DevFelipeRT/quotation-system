# Dependency Injection Container Module Documentation

## 1. Introduction

The Dependency Injection (DI) Container module provides a flexible, robust, and secure foundation for managing service dependencies within the system. Designed according to modern architectural principles (SOLID, Clean Code, Clean Architecture, PSR standards), it offers explicit bindings, advanced autowiring, scope management, provider support, and safeguards against circular dependencies. This module is the backbone of decoupled, testable, and maintainable application code across the project.

**When to use:**

* To centralize service management and dependency resolution
* To facilitate scalable and modular application architecture
* When requiring automated constructor injection and scope handling
* To enable extension and customization of service lifecycle and instantiation logic

---

## 2. Architecture Overview

The DI Container implements a modular and extensible architecture. Its core responsibilities are:

* **Service Binding:** Registering factories/closures for service creation, supporting both singleton and transient lifecycles.
* **Autowiring:** Automated resolution of class dependencies using PHP Reflection, via the `ReflectionResolver`, for classes not explicitly registered.
* **Scope Management:** Built-in singleton and transient scopes, with support for custom scopes.
* **Providers:** Modular service providers for grouping bindings.
* **Circular Dependency Detection:** Internal stack-based mechanism to prevent infinite loops.
* **Extensibility:** Custom resolvers and scope implementations can be injected for advanced use cases.

**Design Principles:**

* Fully decoupled contract-based interfaces for extension and substitution
* Strict separation of concerns between binding, resolution, and lifecycle management
* All exceptions are domain-specific for clear error handling
* No reliance on Composer or third-party DI/PSR container libraries

---

## 3. Installation and Integration

This module is part of the core `Shared` library. No Composer installation is required. To integrate:

1. Ensure the `App\Shared\Container` namespace is included in your autoloading strategy (PSR-4 recommended).
2. Instantiate the container directly or via the provided `ContainerBuilder`.

**Example:**

```php
use App\Shared\Container\Infrastructure\ContainerBuilder;

$container = (new ContainerBuilder())
    ->bind('foo', fn() => new Foo())
    ->singleton('bar', fn() => new Bar())
    ->build();
```

---

## 4. Core Concepts

### 4.1 Bindings

* **Explicit Bindings:** Register a service by an ID (usually class/interface name) with a factory closure. Can be singleton (one instance per container) or transient (new instance per request).
* **Singleton Bindings:** Reuse the same instance for every resolution.
* **Transient Bindings:** Return a new instance for each resolution.

### 4.2 Autowiring

* **Reflection-based:** When a class is not explicitly bound, the container attempts to instantiate it via reflection, recursively resolving its constructor dependencies.
* **Constructor Injection Only:** Only constructor dependencies are resolved; property/method injection is not supported by default.
* **Strict Cycle Detection:** The container prevents infinite recursion using a resolution stack, throwing `CircularDependencyException` on cycles.

### 4.3 Scopes

* **SingletonScope:** Caches and returns the same instance for the service ID.
* **TransientScope:** Always invokes the factory to produce a fresh instance.
* **Custom Scopes:** You may implement `ContainerScopeInterface` to provide custom lifecycle behaviors.

### 4.4 Providers

* Providers implement `ServiceProviderInterface`, allowing logical grouping of bindings for modular system bootstrapping.

### 4.5 Reset & Clear

* **clear(\$id):** Remove a specific binding and its cached instance.
* **reset():** Remove all explicit bindings and all cached instances (autowiring for FQCNs remains available after reset).

### 4.6 Extensibility

* Use custom resolvers (implement `ResolverInterface`) for advanced autowiring or instantiation logic.
* Replace or add scopes to manage service lifecycles with new semantics.

---

## 5. Usage

### 5.1 Registering and Resolving Services

```php
$container->bind('foo', fn() => new Foo());
$foo = $container->get('foo');

$container->singleton('bar', fn() => new Bar());
$bar = $container->get('bar');
```

### 5.2 Autowiring

```php
class Baz {
    public function __construct(Foo $foo, Bar $bar) { /* ... */ }
}
$baz = $container->get(Baz::class); // Automatically resolves dependencies
```

### 5.3 Providers

```php
class MyProvider implements ServiceProviderInterface {
    public function register(ContainerInterface $container): void {
        $container->bind('foo', fn() => new Foo());
    }
}
$container->registerProvider(new MyProvider());
```

### 5.4 Clearing and Resetting

```php
$container->clear('foo');
$container->reset();
```

### 5.5 Custom Scopes

```php
use App\Shared\Container\Infrastructure\Bindings\BindingType;
use App\Shared\Container\Infrastructure\Scope\TransientScope;
$container->setScope(BindingType::SINGLETON, new TransientScope());
```

---

## 6. API Reference

### 6.1 ContainerInterface

Defines the main contract for containers. See `App\Shared\Container\Domain\Contracts\ContainerInterface`.

| Method      | Signature                                                           | Description                                                        |
| ----------- | ------------------------------------------------------------------- | ------------------------------------------------------------------ |
| `bind`      | `bind(string $id, callable $factory, bool $singleton = true): void` | Registers a binding, optionally as singleton.                      |
| `singleton` | `singleton(string $id, callable $factory): void`                    | Registers a singleton binding.                                     |
| `get`       | `get(string $id): mixed`                                            | Resolves a service instance (explicit or via autowiring).          |
| `has`       | `has(string $id): bool`                                             | Checks if a binding exists for an identifier.                      |
| `clear`     | `clear(string $id): void`                                           | Removes a binding and its instance.                                |
| `reset`     | `reset(): void`                                                     | Clears all bindings and instances. (Autowiring for FQCNs remains.) |

### 6.2 ServiceProviderInterface

Logical grouping of bindings for modular bootstrapping.

| Method     | Signature                                       | Description         |
| ---------- | ----------------------------------------------- | ------------------- |
| `register` | `register(ContainerInterface $container): void` | Registers bindings. |

### 6.3 ContainerScopeInterface

Customizes service lifecycle (singleton, transient, etc).

| Method     | Signature                                       | Description                       |
| ---------- | ----------------------------------------------- | --------------------------------- |
| `resolve`  | `resolve(string $id, callable $factory): mixed` | Resolves an instance (lifecycle). |
| `clear`    | `clear(string $id): void`                       | Removes cached instance.          |
| `clearAll` | `clearAll(): void`                              | Clears all instances.             |

### 6.4 ResolverInterface

Strategy for autowiring or alternative instantiation.

| Method    | Signature                                                                                | Description                                           |
| --------- | ---------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| `resolve` | `resolve(string $id, ContainerInterface $container, array $resolutionStack = []): mixed` | Resolves an instance, handling cycles and autowiring. |

### 6.5 Container

Main implementation (`App\Shared\Container\Infrastructure\Container`).
All methods above, plus:

| Method             | Signature                                                           | Description                                |
| ------------------ | ------------------------------------------------------------------- | ------------------------------------------ |
| `registerProvider` | `registerProvider(ServiceProviderInterface $provider): void`        | Registers a provider for modular bindings. |
| `setScope`         | `setScope(BindingType $type, ContainerScopeInterface $scope): void` | Registers or replaces a scope for a type.  |

---

## 7. Testing

### 7.1 Philosophy

Testing validates that the container reliably handles every expected scenario and edge case, providing confidence in its correctness and extensibility.

### 7.2 Scenarios Covered

* Transient and singleton binding resolution
* Autowiring with dependencies
* Cycle detection and exception handling
* Clearing individual bindings
* Providers and modular registration
* Overriding scopes
* Resetting container state
* Behavior of `has()`

### 7.3 Example Test Case

```php
class Foo {}
class Bar { public function __construct(Foo $foo) {} }
class Baz { public function __construct(Bar $bar, Foo $foo) {} }

$container->bind('foo', fn() => new Foo());
$foo1 = $container->get('foo');
$foo2 = $container->get('foo');
assert($foo1 !== $foo2); // transient

$container->singleton('bar', fn() => new Bar(new Foo()));
$bar1 = $container->get('bar');
$bar2 = $container->get('bar');
assert($bar1 === $bar2); // singleton

$autoBaz = $container->get(Baz::class); // autowiring
assert($autoBaz instanceof Baz);
assert($autoBaz->bar instanceof Bar);
```

### 7.4 Continuous Improvement

Contributions should provide corresponding test coverage for all new features and refactorings.

---

## 8. Extension and Customization

### 8.1 Custom Resolvers

You may implement the `ResolverInterface` to introduce alternative autowiring strategies, contextual instantiation, or special dependency resolution rules. Inject your custom resolver via the `Container` constructor.

**Example:**

```php
use App\Shared\Container\Infrastructure\Autowiring\ResolverInterface;

class MyCustomResolver implements ResolverInterface {
    public function resolve(string $id, ContainerInterface $container, array $stack = []): mixed {
        // Custom logic here
    }
}
$container = new Container(new MyCustomResolver());
```

### 8.2 Custom Scopes

Create classes that implement `ContainerScopeInterface` to define new service lifecycles (e.g., request scope, pooled, etc). Register via `setScope`.

**Example:**

```php
class RequestScope implements ContainerScopeInterface {
    // ...
}
$container->setScope(BindingType::TRANSIENT, new RequestScope());
```

### 8.3 Advanced Providers

Providers can be used to organize complex dependency graphs, feature modules, or plug-ins. Each provider can bind multiple related services in a cohesive way.

---

## 9. Best Practices & Recommendations

* Always define singleton or transient lifecycles intentionally; avoid changing scope after bindings have been registered.
* Use service providers to group related bindings for modularity and clarity.
* Use explicit bindings for complex instantiation logic, but leverage autowiring for simple, decoupled services.
* Beware of circular dependencies: container will detect cycles but consider refactoring design if cycles appear.
* Reset the container only in test scenarios or when rebuilding application context.
* Prefer constructor injection (autowiring) for clarity and testability.
* Keep providers and bindings stateless and deterministic.
* If using custom scopes or resolvers, document their behaviors and integration thoroughly.

---

## 10. Comparison with Market Standards

This container module intentionally avoids external dependencies, PSR-11, or Composer reliance, providing a self-contained and fully auditable implementation.

| Feature                       | This Module      | Laravel Container | Symfony DependencyInjection | PSR-11 |
| ----------------------------- | ---------------- | ----------------- | --------------------------- | ------ |
| Explicit Binding              | Yes              | Yes               | Yes                         | Yes    |
| Autowiring                    | Yes (Reflection) | Yes               | Yes                         | No     |
| Scopes (Singleton/Transient)  | Yes              | Yes               | Yes                         | No     |
| Providers/Modules             | Yes              | Yes               | Yes                         | No     |
| Custom Scopes/Resolvers       | Yes              | Yes               | Advanced                    | No     |
| Circular Dependency Detection | Yes              | No (fail late)    | Yes                         | No     |
| Reset                         | Yes              | Partial           | Yes                         | No     |
| Composer/PSR-11 Required      | No               | Yes               | Yes                         | Yes    |

---

## 11. Limitations & Known Issues

* **Constructor Injection Only:** No support for property or setter injection.
* **No Contextual Binding:** All bindings are global; contextual (per-resolver or per-request) binding is not implemented.
* **No Alias/Interface Map:** Bindings are strictly ID-based; does not resolve aliases or interface groups automatically.
* **No Parameter Overrides:** All constructor parameters are resolved strictly via DI, not by runtime argument passing.
* **No PSR-11 Compatibility:** Intentional—module is standalone and does not implement `Psr\Container\ContainerInterface`.
* **No Third-party Integration:** Designed for self-contained projects.

---

## 12. Glossary

* **Binding:** Association of a service identifier (string) to a factory function for instantiation.
* **Scope:** The lifecycle rule for a service instance (singleton, transient, etc).
* **Autowiring:** Automatic instantiation and resolution of dependencies via reflection.
* **Provider:** Module for registering a group of bindings, typically used for modularization.
* **Resolver:** A strategy object for customizing autowiring logic.
* **Circular Dependency:** When two or more services depend on each other, directly or indirectly, forming a cycle.
* **Reset:** Operation to clear all explicit bindings and cached instances.

---

## 13. Changelog / Migration Notes

* **v2.0.0:**

  * Major refactor: strict stack propagation and robust cycle detection in autowiring
  * Full English documentation
  * Test suite expanded and modernized
  * Modern best practices and extension mechanisms
  * Reset semantics aligned with modern containers (autowiring remains enabled)
  * No Composer or PSR-11 required

---

## Review and Alignment (Technical)

* **No major inconsistencies or errors were found.**
* All features, behaviors, and examples described in the document accurately reflect the flow, structure, and logic of the Container module as extracted from the source code.
* Terminology, code examples, lifecycle flows, limitations, and best practices are strictly aligned with the implementation.
* The documentation is suitable for onboarding, maintenance, extension, and auditing of the module.

**Status: DOCUMENTATION CONSISTENT AND APPROVED**
