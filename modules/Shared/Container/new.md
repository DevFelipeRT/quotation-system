# Dependency Injection Container Module Documentation

## 1. Introduction

The Dependency Injection (DI) Container module provides a flexible, robust, and secure foundation for managing service dependencies within the system. Designed according to modern architectural principles (SOLID, Clean Code, Clean Architecture, PSR standards), it offers explicit bindings, advanced autowiring, scope management, custom resolver support, and safeguards against circular dependencies. This module is the backbone of decoupled, testable, and maintainable application code across the project.

**When to use:**

* To centralize service management and dependency resolution
* To facilitate scalable and modular application architecture
* When requiring automated constructor injection and scope handling
* To enable extension and customization of service lifecycle and instantiation logic

---

## 2. Architecture Overview

The DI Container module implements a modular, extensible, and contract-oriented architecture. Its structure and main responsibilities are:

### Module Structure

```
Container/
├── Autowiring/
│   ├── FactoryResolver.php        # Resolves services using a provided factory map
│   └── ReflectionResolver.php     # Resolves services automatically via reflection
│
├── Bindings/
│   ├── Binding.php              # Represents a registered binding with factory, type, and scope
│   └── BindingType.php          # Enum for singleton/transient lifecycle
│
├── Contracts/
│   ├── ContainerScopeInterface.php # Contract for custom lifecycle strategies
│   └── ResolverInterface.php       # Contract for resolution strategies (reflection, factory, etc.)
│
├── Exceptions/
│   ├── CircularDependencyException.php # Thrown when a dependency cycle is detected
│   ├── ContainerException.php          # Generic container-related errors (PSR-11)
│   └── NotFoundException.php           # Service not found errors (PSR-11)
│
├── Scope/
│   ├── SingletonScope.php         # Scope that memoizes one instance per binding
│   └── TransientScope.php         # Scope that creates a new instance per request
│
├── Container.php                # Main dependency injection container implementation
├── ContainerBuilder.php         # Fluent builder for configuring and instantiating the container
└── ContainerKernel.php          # Singleton kernel for orchestrating global container state

PublicContracts/
└── Container/
    ├── ContainerInterface.php         # PSR-11 compatible public interface
    └── ServiceProviderInterface.php   # Contract for modular service registration
```

### Main Architectural Responsibilities

* **Service Binding:** Registering closures as service factories, wrapped in `Binding` objects (with scope and lifecycle type).
* **Autowiring:** Automated resolution of classes via constructor type hints, handled by `ReflectionResolver`.
* **Scoped Lifecycles:** Explicit support for singleton and transient lifecycles (and custom scopes).
* **Provider Registration:** Modular grouping of bindings through providers implementing `ServiceProviderInterface`, configured via builder or kernel.
* **Global Singleton Orchestration:** Managed through `ContainerKernel`, enabling global bootstrapping and singleton access.
* **Customizability:** New scopes, resolvers, and provider strategies can be added by implementing contracts in `/Contracts`.
* **Exception Safety:** All errors and resolution failures are managed by domain-specific exceptions (including PSR-11 compliance).

---

## 3. Installation and Integration

This module is part of the core `Shared` library. No Composer installation is required. To integrate:

1. Ensure the `Shared\Container\` namespace is registered in your PSR-4 autoloading strategy.
2. Instantiate the container manually using the `ContainerBuilder`, or configure it through `ContainerKernel`, which manages a singleton instance.
3. External modules should rely only on the public contracts:

   * `ContainerInterface`: PSR-11 compatible contract for runtime dependency resolution.
   * `ServiceProviderInterface`: defines a modular registration mechanism for grouped bindings.

The container must be explicitly configured before use. Service providers can be registered through `ContainerKernel::registerProvider()`, and the container must be finalized using `ContainerKernel::build()`. Once built, it becomes immutable and globally accessible via `ContainerKernel::container()`.

**Example: Manual container building**

```php
use Shared\Container\ContainerBuilder;
use Shared\Container\Bindings\BindingType;

$builder = new ContainerBuilder();

$builder->bind(
    LoggerInterface::class,
    fn() => new FileLogger(),
    BindingType::SINGLETON
);

$builder->bind(
    Request::class,
    fn() => new Request(),
    BindingType::TRANSIENT
);

$container = $builder->build();
```

**Example: Using ContainerKernel with providers**

```php
use Shared\Container\ContainerKernel;
use Acme\App\Infrastructure\UserProvider;

ContainerKernel::registerProvider(new UserProvider());
$container = ContainerKernel::build();
```

To retrieve the singleton instance later:

```php
$container = ContainerKernel::container();
```

All service configuration must be complete before the first call to `build()`. After that point, the container becomes immutable.

---

## 4. Core Concepts

### 4.1 Bindings

* **Explicit Bindings:** Services are bound using `Binding` objects, each wrapping a factory closure, a binding type (`SINGLETON` or `TRANSIENT`), and a scope strategy. Use explicit bindings for services that require custom instantiation logic, external dependencies, configuration parameters, or when the lifecycle must be strictly controlled. Typical candidates include database connections, loggers, external API clients, and any service where construction cannot be resolved via autowiring alone.
* **Singleton Bindings:** The service is instantiated once and cached for all subsequent resolutions. This is appropriate for stateful or resource-intensive services that must maintain identity or state across the application, such as database connections, configuration repositories, or service gateways.
* **Transient Bindings:** A new instance is produced for every resolution. Use this when the service is intended to be stateless or when each consumer requires an isolated instance, such as value objects, job/task handlers, or one-off utility services.

### 4.2 Autowiring

* **Reflection-based:** The `ReflectionResolver` uses constructor type hints to automatically resolve dependencies.
* **Constructor Injection Only:** Autowiring only supports constructor injection.
* **Strict Cycle Detection:** The resolution stack tracks instantiation calls and raises a `CircularDependencyException` if a dependency cycle is found.

### 4.3 Scopes

* **SingletonScope:** Returns the same cached instance for each resolution.
* **TransientScope:** Always produces a fresh instance.
* **Custom Scopes:** Implement `ContainerScopeInterface` to control instantiation policies (e.g., per-request or per-thread scopes).

### 4.4 Providers (via FactoryResolver)

* The `FactoryResolver` receives a map of closures and attempts to resolve services using that map.
* While explicit provider classes are not used, you can simulate provider behavior by grouping closures and passing them to the `ContainerKernel`.

### 4.5 Reset & Clear

* This module does not expose `reset()` or `clear()` functionality. To discard bindings, re-instantiate the container using `ContainerBuilder`.

### 4.6 Extensibility

* Implement `ResolverInterface` to create custom strategies for resolving services.
* Extend `ContainerScopeInterface` to create new instantiation lifecycles.
* Compose containers with different resolver configurations for specialized boot flows.

---

## 5. Usage: Patterns and Practical Scenarios

The following section expands on the usage of the Container module, offering practical, real-world examples for a wide range of dependency management situations. For each scenario, it demonstrates which container feature or contract to use, the rationale for its application, and recommendations for best results.

---

### 5.1 Transient vs Singleton: Deciding the Lifecycle

**Scenario:** Service should be stateless (e.g., value objects, helpers, temporary calculations).

```php
use Container\Bindings\BindingType;

$container = (new ContainerBuilder())
    ->bind('uuid_generator', fn() => new UuidGenerator(), BindingType::TRANSIENT)
    ->build();

$uuid1 = $container->get('uuid_generator');
$uuid2 = $container->get('uuid_generator');

assert($uuid1 !== $uuid2);
```

**When to use singleton:**

```php
$container = (new ContainerBuilder())
    ->bind('logger', fn() => new Logger(), BindingType::SINGLETON)
    ->build();

$logger1 = $container->get('logger');
$logger2 = $container->get('logger');

assert($logger1 === $logger2);
```

---

### 5.2 Autowiring via Reflection

**Scenario:** You want to resolve a class that is not explicitly registered.

```php
use Container\ContainerKernel;

$container = ContainerKernel::boot([
    Logger::class => fn() => new Logger()
]);

class Service {
    public function __construct(Logger $logger) {}
}

$service = $container->get(Service::class);
```

This works even though `Service` was not explicitly registered, thanks to the `ReflectionResolver`.

---

### 5.3 Advanced: Custom Scopes

**Scenario:** You want to create a scope that returns a new instance per HTTP request.

```php
class RequestScope implements ContainerScopeInterface {
    public function resolve(callable $factory, string $id): mixed {
        static $instances = [];
        if (!isset($_SERVER['REQUEST_ID'])) {
            $_SERVER['REQUEST_ID'] = uniqid();
        }
        $key = $_SERVER['REQUEST_ID'] . ":$id";
        return $instances[$key] ??= $factory();
    }
}
```

Then inject it manually when creating a `Binding`.

---

## 6. Exception Handling

| Exception                     | When it Occurs                                                       |
| ----------------------------- | -------------------------------------------------------------------- |
| `NotFoundException`           | Service not bound and not resolvable by any resolver                 |
| `ContainerException`          | Failure to instantiate a service (e.g., reflection or factory error) |
| `CircularDependencyException` | Cyclic dependency detected during autowiring                         |

---
