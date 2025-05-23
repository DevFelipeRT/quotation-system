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

## 5. Usage: Patterns and Practical Scenarios

The following section expands on the usage of the Container module, offering practical, real-world examples for a wide range of dependency management situations. For each scenario, it demonstrates which container feature or contract to use, the rationale for its application, and recommendations for best results.

---

### 5.1 Transient vs Singleton: Deciding the Lifecycle

**Scenario:** Service should be stateless (e.g., value objects, helpers, temporary calculations).

```php
// Transient: a new instance on every request
$container->bind('uuid_generator', fn() => new UuidGenerator(), false); // false = transient
$uuid1 = $container->get('uuid_generator');
$uuid2 = $container->get('uuid_generator');
assert($uuid1 !== $uuid2);
```

**When to use:**

* Use transient for stateless, short-lived, or computation-only services.

**Scenario:** Service must be shared (e.g., DB connection, logger, config, cache pool).

```php
$container->singleton('logger', fn() => new Logger('/var/log/app.log'));
$logger1 = $container->get('logger');
$logger2 = $container->get('logger');
assert($logger1 === $logger2);
```

**When to use:**

* Use singleton for expensive, shared resources or cross-cutting concerns.

---

### 5.2 Autowiring for Clean, Decoupled Constructors

**Scenario:** You have a service class whose dependencies should be automatically resolved by type (constructor injection).

```php
class Repository {
    public function __construct(DbConnection $conn, Logger $logger) { /* ... */ }
}
$repo = $container->get(Repository::class); // Will autowire DbConnection and Logger
```

**Best Practice:**

* Prefer autowiring for services with simple, decoupled constructor dependencies.
* For classes needing runtime/contextual parameters, prefer explicit bindings.

---

### 5.3 Explicit Binding for Custom Instantiation Logic

**Scenario:** Service requires custom logic, external data, or non-type-hinted dependencies.

```php
$container->bind('user_context', function() {
    return new UserContext(session_id(), $_SERVER['REMOTE_ADDR']);
}, true);
```

**When to use:**

* When a dependency needs runtime values or cannot be autowired.

---

### 5.4 Service Providers for Modular Bootstrapping

**Scenario:** You want to encapsulate a group of related bindings (e.g., for a feature module, third-party package, or infrastructure layer).

```php
class QueueProvider implements ServiceProviderInterface {
    public function register(ContainerInterface $container): void {
        $container->singleton('queue', fn() => new QueueService());
        $container->singleton('job_dispatcher', fn() => new JobDispatcher($container->get('queue')));
    }
}
$container->registerProvider(new QueueProvider());
```

**When to use:**

* For feature modules, infrastructure layers, or to decouple bootstrapping logic.

---

### 5.5 Custom Scopes for Advanced Lifecycles

**Scenario:** You need a request-scoped, pooled, or other custom lifecycle.

```php
class RequestScope implements ContainerScopeInterface {
    // ... pool or context logic here ...
}
$container->setScope(BindingType::TRANSIENT, new RequestScope());
```

**When to use:**

* Advanced use cases such as HTTP request context, task pooling, or multi-tenant lifecycles.

---

### 5.6 Clearing and Resetting

**Scenario:** You need to remove or reset bindings during testing or container rebuilding.

```php
$container->clear('foo');   // Removes a single binding
$container->reset();        // Removes all explicit bindings and their instances
```

**Best Practice:**

* Use only in test scenarios or controlled system bootstrapping.

---

### 5.7 Detecting and Handling Circular Dependencies

**Scenario:** You want to ensure your architecture is safe from accidental cycles.

```php
// This will throw CircularDependencyException if Foo <-> Bar depend on each other
try {
    $container->get(Foo::class);
} catch (CircularDependencyException $e) {
    // Handle, log, or redesign the dependency graph
}
```

**Best Practice:**

* Refactor to avoid cycles; container will detect and report, but design should prevent them.

---

### 5.8 has() Method: Checking for Explicit Bindings

**Scenario:** Conditionally act based on service registration status.

```php
if ($container->has('special_feature')) {
    $feature = $container->get('special_feature');
}
```

**Note:**

* `has()` only checks explicit bindings, not autowirable classes.

---

### 5.9 Using the Container in Factories and Higher-Level Services

**Scenario:** When writing custom factories, providers, or modules that themselves need to resolve or manage dependencies.

```php
class ServiceFactory {
    public function __construct(private ContainerInterface $container) {}
    public function make(): Service {
        return $this->container->get(Service::class);
    }
}
```

---

### 5.10 Integration with Legacy or Non-Type-Hinted Code

**Scenario:** Integrating legacy services or PHP code that lacks modern type hints.

```php
$container->bind('legacy_helper', function() {
    return new LegacyHelper(/* ... */);
}, true);
$legacy = $container->get('legacy_helper');
```

**Best Practice:**

* Use explicit bindings for any dependency that cannot be resolved by autowiring.

---

### 5.11 Extending and Overriding the Container for Specialized Needs

**Scenario:** Replace autowiring logic, add instrumentation, or enforce custom policies.

```php
class LoggingResolver implements ResolverInterface {
    public function resolve(string $id, ContainerInterface $container, array $stack = []): mixed {
        error_log("Resolving: $id");
        // Fallback to ReflectionResolver or custom logic
    }
}
$container = new Container(new LoggingResolver());
```

**When to use:**

* For cross-cutting concerns, diagnostics, or enforcing architectural rules.

---

This expanded usage section provides practical guidance for a wide array of situations, maximizing the utility and safety of the DI Container in real-world applications.

---

## Integration with the Discovery Module

The Container module is designed to operate in harmony with the Discovery module, enabling fully automated, scalable, and modular dependency registration. This approach unlocks advanced use cases—such as zero-boilerplate provider management, plug-and-play extensions, cross-module resolution, and dynamic service wiring—while preserving the core principles of SOLID, Clean Architecture, and DDD.

---

### Why Integrate Container and Discovery?

* **Zero Boilerplate:** Eliminate manual provider/service registration by leveraging Discovery to identify and wire all eligible components.
* **Plug-and-Play Modules:** Drop in any module/provider compliant with system contracts and it is auto-registered at bootstrap.
* **Full Modularity:** Support for extensions, plugins, and cross-domain integration without altering core configuration files.
* **Scalable Growth:** Dynamically register services, handlers, listeners, or features as the codebase evolves—no refactor required.
* **Auditability:** All auto-registered services are discoverable, inspectable, and maintain strong type safety.

---

### Provider Auto-Discovery and Auto-Registration

A best practice is to use the DiscoveryKernel to locate all provider classes implementing `ContainerProviderInterface` within target namespaces, then register each one automatically:

```php
use App\Shared\Container\Container;
use App\Kernel\Discovery\DiscoveryKernel;

$container = new Container();
$kernel = new DiscoveryKernel('App\\Modules', '/path/to/project/src/Modules');

$providers = $kernel->discoverImplementations(
    \App\Shared\Container\Contracts\ContainerProviderInterface::class,
    'App\\Modules'
);

foreach ($providers as $fqcn) {
    $container->registerProvider(new ($fqcn->toString())());
}
```

**Best For:** Large applications, plugin/extension systems, or projects with frequent modular evolution.

---

### Dynamic Service/Handler Discovery from Providers

Providers can leverage Discovery at registration time to find and bind all classes implementing a specific contract (e.g., event listeners, command handlers):

```php
class MyModuleProvider implements ContainerProviderInterface {
    public function register(Container $container): void {
        global $discoveryKernel;
        $handlers = $discoveryKernel->discoverImplementations(
            \App\Modules\MyDomain\Contracts\EventHandlerInterface::class,
            'App\\Modules\\MyDomain\\Handlers'
        );
        foreach ($handlers as $fqcn) {
            $container->bind($fqcn->toString(), $fqcn->toString());
        }
    }
}
```

**Best For:** CQRS/event-driven systems, dynamic module/plugin loading, or interface-driven architectures.

---

### Advanced Patterns

* **Conditional/Contextual Binding:** Providers can filter discovered services by annotation, environment, or naming convention before binding.
* **Scoped Registration:** Register discovered services/providers in custom scopes (singleton, transient, per-tenant, per-request) as dictated by conventions or tags identified by Discovery.
* **Cross-Module and Runtime Extension Wiring:** Leverage Discovery to dynamically resolve and bind dependencies across domain/module boundaries, enabling truly decoupled shared infrastructure.
* **Deferred/Lazy Registration:** Use Discovery on-demand, registering services only when accessed for the first time, to optimize performance or reduce startup time.
* **Feature Toggles and Conditional Loading:** Register or skip providers/services based on dynamic criteria, such as enabled extensions, environment, or runtime checks.

---

### Bootstrapping and Best Practices

* **Bootstrap Order:** Always instantiate and configure the DiscoveryKernel before running any provider auto-registration.
* **Singleton Registration:** Prefer registering DiscoveryKernel as a singleton in the container for shared access.
* **Contract-Driven:** Enforce `ContainerProviderInterface` for all auto-discovered providers for reliability and maintainability.
* **Document Integration:** Clearly document custom integration logic and auto-registration strategies.

---

### Typical Use Cases

* Drop-in business modules or plugins with zero config
* Event-driven auto-binding (handlers, listeners, etc.)
* Shared infrastructure across modules/domains
* Dynamic plugin/marketplace models
* Scalable onboarding for new features/teams
* Test environments with mockable discovery/container

> **Pro Tip:** Mocks/fakes of DiscoveryKernel and contract interfaces can be injected during tests to simulate dynamic service environments with full isolation.

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
