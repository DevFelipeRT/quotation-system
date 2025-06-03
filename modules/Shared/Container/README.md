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

### 2.1 Module Structure

```
Container/
├── Domain/
│   └── ValueObjects/
│       ├── Binding.php                     # Represents a registered binding with factory, type, and scope
│       └── BindingType.php                 # Enum for singleton/transient lifecycle
│
├── Infrastructure/
│   ├── Autowiring/
│   │   ├── FactoryResolver.php             # Resolves services using a provided factory map
│   │   └── ReflectionResolver.php          # Resolves services automatically via reflection
│   │
│   ├── Contracts/
│   │   ├── ContainerScopeInterface.php     # Contract for custom lifecycle strategies
│   │   └── ResolverInterface.php           # Contract for resolution strategies (reflection, factory, etc.)
│   │
│   ├── Exceptions/
│   │   ├── CircularDependencyException.php # Thrown when a dependency cycle is detected
│   │   ├── ContainerException.php          # Generic container-related errors (PSR-11)
│   │   └── NotFoundException.php           # Service not found errors (PSR-11)
│   │
│   ├── Scope/
│   │   ├── SingletonScope.php              # Scope that memoizes one instance per binding
│   │   └── TransientScope.php              # Scope that creates a new instance per request
│   │
│   ├── Container.php                       # Main dependency injection container implementation
│   ├── ContainerBuilder.php                # Fluent builder for configuring and instantiating the container
│   └── ContainerKernel.php                 # Singleton kernel for orchestrating global container state
└── README.md

PublicContracts/
└── Container/
    ├── ContainerInterface.php              # PSR-11 compatible public interface
    └── ServiceProviderInterface.php        # Contract for modular service registration
```

### 2.2 Main Architectural Responsibilities

* **Autowiring:** Automated resolution of classes via constructor type hints, handled by `ReflectionResolver`.
* **Scoped Lifecycles:** Explicit support for singleton and transient lifecycles (and custom scopes).
* **Service Binding:** Registering closures as service factories, wrapped in `Binding` objects (with scope and lifecycle type).
* **Provider Registration:** Modular grouping of bindings through providers implementing `ServiceProviderInterface`, configured via builder or kernel.
* **Global Singleton Orchestration:** Managed through `ContainerKernel`, enabling global bootstrapping and singleton access.
* **Customizable:** New scopes, resolvers, and provider strategies can be added by implementing contracts in `/Contracts`.
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

$builder->addProvider(new ProviderExample());

$container = $builder->build();
```

**Example: Using ContainerKernel with providers**

```php
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

This module provides explicit methods for removing service bindings and resetting the container state at runtime:

* `clear(string $id): void` — Removes a specific binding and any associated singleton instance for the given identifier. After calling this method, subsequent attempts to resolve the service will either fail (if no fallback exists) or trigger autowiring (if supported for class names).

* `reset(): void` — Removes all registered bindings and singleton instances, returning the container to a pristine, empty state. This allows the container to be fully reconfigured without requiring reinstantiation.

**Note:** These methods allow you to manage bindings and lifecycle within a single container instance. You do not need to re-instantiate the container using `ContainerBuilder` to discard or refresh bindings.

**Caution:** Clearing or resetting bindings at runtime may disrupt dependent components, especially if they rely on shared (singleton) state. Use these features judiciously in production environments. They are particularly useful in testing scenarios, dynamic module reloading, or controlled application lifecycles.

#### Example Usage

```php
// Remove a specific binding
$container->clear('MyService');

// Reset all bindings and singleton instances
$container->reset();
```

### 4.6 Extensibility

* Implement `ResolverInterface` to create custom strategies for resolving services.
* Extend `ContainerScopeInterface` to create new instantiation lifecycles.
* Compose containers with different resolver configurations for specialized boot flows.

---

## 5. Usage: Practical Scenarios

The following section expands on the usage of the Container module, offering practical, real-world examples for a wide range of dependency management situations. For each scenario, it demonstrates which container feature or contract to use, the rationale for its application, and recommendations for best results.

---

### 5.1 Choosing Between Transient and Singleton Bindings

A binding's lifecycle determines whether a new instance is created for each resolution (transient) or a single shared instance is reused (singleton). Selecting the appropriate lifecycle is critical for correct resource management and application behavior.

**Use a transient binding when:**

* The service is stateless.
* You require a fresh instance for each operation.
* Typical for value objects, pure helpers, utility classes, or temporary computations.

**Example: Transient Binding**

```php
use Container\Bindings\BindingType;

$container = (new ContainerBuilder())
    ->bind('uuid_generator', fn() => new UuidGenerator(), BindingType::TRANSIENT)
    ->build();

$uuid1 = $container->get('uuid_generator');
$uuid2 = $container->get('uuid_generator');

assert($uuid1 !== $uuid2); // Different instance each time
```

**Use a singleton binding when:**

* The service maintains internal state across requests.
* You want a single, shared instance reused throughout the application's lifetime.
* Suitable for loggers, configuration services, database connections, or expensive resource managers.

**Example: Singleton Binding**

```php
$container = (new ContainerBuilder())
    ->bind('logger', fn() => new Logger(), BindingType::SINGLETON)
    ->build();

$logger1 = $container->get('logger');
$logger2 = $container->get('logger');

assert($logger1 === $logger2); // Always the same instance
```

---

### 5.2 Autowiring via Reflection

The container supports autowiring through reflection: when you attempt to resolve a class that has **not** been explicitly registered as a binding, the container will automatically analyze its constructor type hints and recursively resolve dependencies, provided that the class is instantiable and all dependencies can also be resolved (either via autowiring or explicit binding).

**When to use autowiring:**

* When you want to avoid registering every service manually.
* When your services are designed with constructor dependency injection and explicit type hints.
* When you aim to reduce configuration and leverage convention.

**Example: Autowiring without explicit bindings**

```php
use Container\Infrastructure\ContainerBuilder;

// No explicit bindings registered for FooService or its dependency BarHelper.

class BarHelper
{
    // No dependencies
}

class FooService
{
    public function __construct(BarHelper $helper) {}
}

// Build the container with no bindings at all.
$container = (new ContainerBuilder())->build();

// FooService and BarHelper will be resolved automatically using reflection.
$service = $container->get(FooService::class);

assert($service instanceof FooService);
assert($service !== null);
```

In this example, neither `FooService` nor `BarHelper` was registered with the container. When calling `$container->get(FooService::class)`, the container uses its internal `ReflectionResolver` to inspect the constructor of `FooService`, see that it requires a `BarHelper`, and recursively resolve and instantiate all dependencies using reflection.

**How it works:**

* If a requested class is not found in the container’s bindings, the container delegates resolution to the `ReflectionResolver`.
* The `ReflectionResolver` inspects the class constructor, resolves each type-hinted dependency, and assembles the object graph automatically.
* If any dependency cannot be resolved (e.g., missing type hint, scalar parameter, or circular dependency), the container will throw a resolution exception.

**Caution:**
Autowiring is only possible for classes that:

* Are instantiable (not interfaces or abstract).
* Have all dependencies resolvable (either autowirable or explicitly bound).
* Do not have untyped or scalar constructor parameters, unless defaults are provided.

---

### 5.3 Detecting and Handling Circular Dependencies

**Scenario:** You want to ensure your architecture is safe from accidental cycles.

```php
// This will throw CircularDependencyException if Foo <-> Bar depend on each other
try {
    $container->get(Foo::class);
} catch (CircularDependencyException $e) {
    // Handle, log, or redesign the dependency graph
}
```

---

### 5.4 has() Method: Checking for Explicit Bindings

**Scenario:** Conditionally act based on service registration status.

```php
if ($container->has('special_feature')) {
    $feature = $container->get('special_feature');
}
```

**Note:**

* `has()` only checks explicit bindings, not autowirable classes.

---

### 5.5 Using the Container in Factories and Higher-Level Services

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

### 5.6 Advanced: Custom Scopes

In advanced scenarios, you may need to control the lifecycle of a service beyond the built-in singleton and transient scopes. The container supports custom scopes through the `ContainerScopeInterface` contract.

**Example: Implementing a per-request scope**

Suppose you want each HTTP request to receive a new instance of a service, but within the same request, all resolutions share the same object.

```php
use Container\Infrastructure\Contracts\ContainerScopeInterface;

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

**Registering a binding with a custom scope:**

If your binding API accepts a `ContainerScopeInterface` instance instead of the standard `BindingType` enum, you can register a binding with the custom scope as follows:

```php
use Container\Domain\ValueObjects\Binding;

$binding = new Binding(
    'my_service',
    fn() => new MyService(),
    new RequestScope() // Custom scope for per-request lifecycle
);
```

If the default API only supports enum types (singleton/transient), you may need to extend the binding mechanism to accept custom scope objects.

---

**Note:**

* Custom scopes are powerful for advanced application lifecycles (e.g., HTTP request, CLI command, job queue, etc.).
* Ensure that your container implementation supports injecting custom scope strategies.


---

## 6. Exception Handling

| Exception                     | When it Occurs                                                       |
| ----------------------------- | -------------------------------------------------------------------- |
| `NotFoundException`           | Service not bound and not resolvable by any resolver                 |
| `ContainerException`          | Failure to instantiate a service (e.g., reflection or factory error) |
| `CircularDependencyException` | Cyclic dependency detected during autowiring                         |

---

## 7. Integration with the ClassDiscovery Module

The Container module is fully compatible with the ClassDiscovery module, enabling powerful patterns for automatic provider registration, dynamic wiring, and scalable modularization. When combined, they eliminate manual configuration of service providers and accelerate onboarding of new modules or extensions.

### 7.1 Why Integrate the Container and Discovery?

* **Zero Manual Registration:** All compliant providers or services are discovered and registered automatically at bootstrap.
* **Plug-and-Play Extension:** Add a new provider or handler class in the codebase and it will be auto-registered if it implements the correct contract/interface.
* **Runtime Modularity:** Dynamically load, register, and wire new features or plugins without changing configuration files.
* **Separation of Responsibilities:** Discovery scans and filters the codebase, the Container manages instantiation and lifecycle.
* **Testability:** Contract-driven registration supports mocks and isolated testing out of the box.

---

### 7.2 Service Provider Auto-Registration

A standard integration pattern is to use the `DiscoveryKernel` to discover all service providers implementing `ContainerProviderInterface` and automatically register them in the Container.

**Example: Service Provider Auto-Registration**

```php
$kernel = new ClassDiscoveryKernel('App\\Modules', '/path/to/project/src/Modules');
$classScanner = $kernel->facade();

$providers = $classScanner->implementing(
    \App\Shared\Container\Contracts\ServiceProviderInterface::class,
    'App\\Modules'
);

foreach ($providers as $fqcn) {
    ContainerKernel::registerProvider(new ($fqcn)());
}

$container = ContainerKernel::container();
```

**Key Points:**

* Any class implementing the provider contract is auto-registered; to add a new one, just implement `ServiceProviderInterface`.
* No need to update registration code when new providers are created.
* Prevents common errors like forgotten or duplicate registrations.

---

### 7.3 Handler, Listener, or Strategy Registration via Providers

Providers themselves can use Discovery to find and bind related components (e.g., event listeners, handlers) during their own registration logic.

**Example: Handler Registration from within a Provider**

```php
class MyModuleProvider implements ServiceProviderInterface {
    public function register(Container $container): void {
        global $classScanner;
        $handlers = $discoveryKernel->implementing(
            \App\Modules\MyDomain\Contracts\EventHandlerInterface::class,
            'App\\Modules\\MyDomain\\Handlers'
        );
        foreach ($handlers as $fqcn) {
            $container->bind($fqcn->value(), $fqcn->value());
        }
    }
}
```

**Key Points:**

* Providers need not be aware of individual handler class names.
* Any compliant handler is discovered and registered as the codebase evolves.
* Scales well for CQRS, plugin systems, or event-driven architectures.

---

### 7.4 Advanced Patterns and Recommendations

* **Conditional Registration:** Filter classes by attribute, naming, or configuration before binding.
* **Multiple Discovery Kernels:** Use separate kernels for different namespace roots (e.g., modules vs. extensions).
* **Tag-Based or Scope-Based Registration:** Use conventions, tags, or annotations to control binding scopes.
* **Custom Contracts:** Implement custom discovery contracts for non-standard scenarios.
* **Deferred/Lazy Registration:** Register providers or services on demand for efficiency.

---

### 7.5 Best Practices

* **Initialize Early:** Instantiate and configure `ClassDiscoveryKernel` before any dynamic registration begins.
* **Singleton Kernel:** Keep a single instance of `ClassDiscoveryKernel` accessible throughout the application.
* **Document Integration:** Maintain clear documentation and onboarding examples for the integration between Container and Discovery.
* **Test with Mocks:** Leverage contract-driven discovery for isolated and reliable automated tests.

---

## 8. Comparison with Market Standards

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

## 9. Limitations & Known Issues

* **Constructor Injection Only:** No support for property or setter injection.
* **No Contextual Binding:** All bindings are global; contextual (per-resolver or per-request) binding is not implemented.
* **No Alias/Interface Map:** Bindings are strictly ID-based; does not resolve aliases or interface groups automatically.
* **No Parameter Overrides:** All constructor parameters are resolved strictly via DI, not by runtime argument passing.
* **No Direct PSR-11 Compatibility:** Intentional—module is standalone and does not implement `Psr\Container\ContainerInterface`.
* **No Third-party Integration:** Designed for self-contained projects.
