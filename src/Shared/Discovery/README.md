# Discovery Module

---

## Introduction

The Discovery module provides a robust, decoupled, and extensible mechanism for scanning, identifying, and registering PHP classes, interfaces, and extensions across the codebase. It is engineered to support modularization, dynamic plugin or module registration, and the automatic detection of components without relying on Composer or third-party autoloaders.

**Integration and Bootstrapping:** The Discovery module exposes a dedicated `DiscoveryKernel`, which centralizes all configuration and provides a pre-configured `DiscoveryScanner`. This kernel enables seamless integration with the application’s lifecycle and with other modular kernels in the system, eliminating manual service wiring.

This module is a core part of the application's infrastructure, acting as the foundational layer for runtime discovery and introspection of code elements. It enables advanced features such as auto-wiring, service registration, and modular extension discovery. All logic is encapsulated behind well-defined contracts and value objects, ensuring high testability and strict adherence to SOLID, Clean Architecture, and Domain-Driven Design (DDD) principles.

Unlike common solutions, Discovery is implemented with native PHP mechanisms and strict PSR-4 compliance, making it portable and framework-agnostic. The entire resolution process—mapping namespaces to directories, finding PHP files, and resolving fully qualified class names—is abstracted behind interfaces, making it trivial to adapt to legacy codebases or custom conventions.

---

## Architectural Overview

The Discovery module is structured in strict compliance with Clean Architecture and Domain-Driven Design (DDD) principles. Its source code is divided into three primary layers:

### 1. Application Layer

Orchestrates use cases for scanning and discovering PHP code elements. It coordinates services that:

* Initiate discovery processes given a namespace or directory root.
* Aggregate, filter, and return collections of discovered Fully Qualified Class Names (FQCNs).
* Delegate the low-level logic to domain contracts, maintaining agnostic orchestration.

**Main files:**

* `Service/DiscoveryScanner.php`: Core entry point for discovery requests (namespace/directory to FQCN collection).
* `Extension/ExtensionDiscoveryService.php`: Specialized for scanning extension modules, plugins, or similar constructs.

### 2. Domain Layer

Defines all business contracts, value objects, and domain logic abstractions for the discovery process. This is the heart of the module's stability and testability:

* **Contracts**: Interfaces for namespace-directory resolution, file-to-FQCN mapping, and recursive file finding.
* **Value Objects**: Strongly-typed, immutable representations for FQCNs, namespaces, interface names, and directory paths.
* **Collections**: Typed collections to safely aggregate results (e.g., FqcnCollection).

**Main files:**

* `Contracts/PhpFileFinder.php`: Finds PHP files under a directory.
* `Contracts/NamespaceToDirectoryResolver.php`: Maps namespaces to directories.
* `Contracts/FileToFqcnResolver.php`: Resolves file paths to FQCNs.
* `ValueObjects/DirectoryPath.php`, `FullyQualifiedClassName.php`, `NamespaceName.php`, `InterfaceName.php`: Immutable value objects.
* `Collection/FqcnCollection.php`: Safe collection for discovered classes.

### 3. Infrastructure Layer

Provides concrete implementations for all domain contracts, making the module operational in real projects:

* Implements PSR-4 logic for namespace-directory and file-class resolution.
* Performs actual file system operations for recursive file scanning.
* Can be replaced or extended for legacy or non-PSR-4 environments by substituting only the infrastructure layer.

**Main files:**

* `PhpFileFinderRecursive.php`: Recursively finds PHP files in a directory.
* `NamespaceToDirectoryResolverPsr4.php`: Maps namespaces to directories as per PSR-4.
* `FileToFqcnResolverPsr4.php`: Converts file paths to FQCNs using PSR-4 rules.

### 4. DiscoveryKernel Integration

The `DiscoveryKernel` acts as both the entry point and the main façade for all Discovery module operations. It is responsible for:

* Receiving the root PSR-4 prefix and the base source directory as configuration.
* Instantiating and exposing a ready-to-use `DiscoveryScanner` (singleton).
* Providing direct methods for:

  * Discovering all classes (via `scanner()`).
  * Discovering available extension modules/plugins (`discoverExtensions()`).
  * Discovering all implementations of a given interface under a namespace (`discoverImplementations()`).
* Encapsulating and orchestrating all infrastructure and service wiring, in a way that is consistent with the system’s modular kernel pattern.
* Enabling seamless integration with the application's boot process, DI container, or custom providers.

**This design means the DiscoveryKernel is the only entry point you need to access all standard Discovery functionality, ensuring consistency and maintainability throughout your system.**

**Main file:**

* `src/Kernel/Discovery/DiscoveryKernel.php`: The orchestrator and provider for Discovery module services and lifecycle.

### Flow of Control

The Discovery module coordinates the process of scanning, identifying, and registering PHP classes in a predictable and auditable way. The flow of control is as follows:

1. **Kernel Initialization**: The `DiscoveryKernel` is instantiated with the root PSR-4 prefix and the corresponding base source directory. This configures all internal services and makes the kernel ready to serve discovery requests.
2. **Service Access**: Consumers (such as the application bootstrap, DI container, or providers) use the kernel as the single point of entry—either invoking `scanner()` for generic scanning or the specialized `discoverExtensions()`/`discoverImplementations()` methods for common discovery patterns.
3. **Resolution Pipeline**:

   * The kernel delegates to the configured `DiscoveryScanner`, which uses domain contracts and infrastructure services to:

     * Map a namespace to its physical directory.
     * Recursively scan for `.php` files.
     * Convert each PHP file to its FQCN, using value object enforcement for type safety.
   * For `discoverExtensions()`, an additional layer of semantic filtering is applied to only return valid extension modules.
   * For `discoverImplementations()`, the scanner locates and verifies all classes implementing a given interface within the specified namespace.
4. **Result Aggregation**: Results are always returned as immutable, typed `FqcnCollection` value objects—ready for further application use, such as module registration, dependency injection, or runtime introspection.
5. **Extensibility Point**: At any stage, consumers may inject custom contracts, extend the kernel, or override discovery logic to support advanced scenarios (legacy projects, non-PSR-4, custom plugins, etc.), without breaking the overall architectural flow.

**This predictable flow ensures that all discovered elements are handled in a strongly-typed, decoupled, and auditable manner—maximizing reliability and maintainability across the system.**

### Batch Scanning and Multi-Namespace Support

The Discovery module allows projects to configure multiple namespace-to-directory mappings. To scan across several namespaces or directories, simply repeat the scan operation for each target and combine the results as needed. Coordination of batch discovery is done at the application or integration layer, not within the core API. This keeps responsibilities clear, ensuring that resolvers handle mapping logic while the application controls multi-scope operations efficiently.

### Extensibility

The infrastructure layer can be swapped for custom or legacy logic, as long as domain contracts are implemented. The `DiscoveryKernel` itself can be subclassed or extended to override configuration, provide alternate resolvers, or inject project-specific behavior. The rest of the system remains agnostic to these changes, ensuring robustness, flexibility, and maintainability.

---

## Installation

### Prerequisites

* **PHP 8.0 or higher** (verified by strict type usage, modern syntax, and absence deprecation in all code files)
* **PSR-4 directory structure** for namespace-directory mapping (the module relies on PSR-4 conventions for its core infrastructure implementations)
* **No Composer or external dependency is required**; the system uses a custom autoloader (`autoload.php` at project root)

### Installation Steps

1. **Clone or include the project** in your target environment.
2. Ensure your autoloader is active and covers the `src/Shared/Discovery` directory as per your PSR-4 mapping.
3. No further installation or configuration steps are required for the Discovery module to function.

### Bootstrap and Kernel Registration (Recommended)

The recommended approach for integrating the Discovery module into your system is to instantiate and register the `DiscoveryKernel` during the application bootstrap or within the central DI container configuration. This pattern ensures all discovery services are available and consistently configured for all modules and providers.

#### **Bootstrap Example**

```php
use App\Kernel\Discovery\DiscoveryKernel;

$psr4Prefix = 'App\\Modules';
$baseSourceDir = '/path/to/project/src/Modules';

// Instantiate and register the kernel globally (as a singleton or in your container)
$discoveryKernel = new DiscoveryKernel($psr4Prefix, $baseSourceDir);
```

#### **Accessing Discovery Services**

* Use `$discoveryKernel->scanner()` for general class discovery in any namespace under the configured prefix.
* Use `$discoveryKernel->discoverExtensions()` to list all extension modules/plugins under the configured prefix, optionally filtered by namespace.
* Use `$discoveryKernel->discoverImplementations($interfaceName, $namespace)` to dynamically locate all classes implementing a contract/interface in a given namespace.

#### **Integration with DI Container**

* Register the `DiscoveryKernel` as a singleton within your DI Container, making it injectable into providers, modules, or application services that require discovery operations.
* Example:

  ```php
  $container->singleton(DiscoveryKernel::class, fn() =>
      new DiscoveryKernel('App\\Modules', '/path/to/project/src/Modules'));
  ```
* This allows seamless sharing of a preconfigured kernel instance across the entire application lifecycle.

#### **Boot Order Recommendation**

* Always ensure the `DiscoveryKernel` is initialized and available **before** any modules, providers, or services that depend on dynamic discovery.
* Prefer explicit dependency wiring or constructor injection over global/static access to maximize testability and traceability.


### Upgrading

* When upgrading the module, ensure all custom infrastructure implementations remain compatible with the unchanged domain contracts and value objects.

---

## Practical Usage Examples

This section presents advanced, real-world scenarios for leveraging the Discovery module—especially when integrated into modular systems, DI containers, or auto-registration flows. Each example is mapped to a concrete usage scenario and includes best-practice recommendations for its application.

---

### 1. Standard Discovery via DiscoveryKernel

**Scenario:** *You have a modular project and want to enumerate all classes in a namespace for static analysis, auto-wiring, or bulk registration (e.g., listing all available services, controllers, or entities).*

```php
use App\Kernel\Discovery\DiscoveryKernel;

$kernel = new DiscoveryKernel('App\\Modules', '/path/to/project/src/Modules');
$scanner = $kernel->scanner();
$fqcnCollection = $scanner->scan('App\\Modules\\Invoices');

foreach ($fqcnCollection as $fqcn) {
    echo $fqcn->toString() . PHP_EOL;
}
```

**Best For:** General module/class discovery, code introspection, onboarding automation, and static dependency mapping in modular systems.

---

### 2. Discovering Extensions or Plugins

**Scenario:** *Your system is extensible via plugins, extensions, or modules. At startup or reload, you need to discover all extension classes for registration, initialization, or dynamic wiring.*

```php
$kernel = new DiscoveryKernel('App\\Extensions', '/path/to/project/src/Extensions');
$extensions = $kernel->discoverExtensions();

foreach ($extensions as $extensionFqcn) {
    // Register or initialize extension
}
```

**Best For:** Plugin architectures, dynamic registration of modules, runtime modularization, feature toggle systems.

---

### 3. Dynamic Provider Auto-Registration with Container Integration

**Scenario:** *You maintain a growing platform with multiple teams or features. New modules add providers implementing a standard contract. You want every compliant provider to be automatically registered with the DI container, without ever updating a static config or code list.*

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

**Best For:** Automatic modularization, zero-boilerplate provider registration, onboarding new modules/teams, scalable DI.

---

### 4. Discovering All Implementations of an Interface (e.g., Handlers, Strategies)

**Scenario:** *You want to implement the Command Pattern, CQRS, event-driven logic, or support dynamic handlers/strategies. The system should auto-discover every class that implements a given contract (e.g., all event listeners or domain handlers in a namespace), enabling runtime registration or auto-wiring.*

```php
$handlers = $kernel->discoverImplementations(
    \App\Modules\Billing\Domain\Handler\InvoiceHandlerInterface::class,
    'App\\Modules\\Billing\\Domain\\Handler'
);

foreach ($handlers as $handlerFqcn) {
    $container->bind($handlerFqcn->toString(), $handlerFqcn->toString());
}
```

**Best For:** Dynamic registration of handlers, strategies, listeners, interface-driven modularity, CQRS/event-driven architectures.

---

### 5. Custom Scenarios: Manual Wiring or Advanced Filtering

**Scenario:** *You are integrating legacy code, have unique naming or location patterns, or need selective registration (e.g., only controllers, only classes with a given suffix, etc). This is also useful for advanced tests, hybrid bridges, or migrations between standards.*

```php
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\PhpFileFinderRecursive;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;
use App\Shared\Discovery\Application\Service\DiscoveryScanner;

$namespaceMap = ['App\\Custom' => '/path/to/project/src/Custom'];
$namespaceResolver = new NamespaceToDirectoryResolverPsr4($namespaceMap);
$fileFinder = new PhpFileFinderRecursive();
$fileToFqcn = new FileToFqcnResolverPsr4('App\\Custom', $namespaceMap);

$scanner = new DiscoveryScanner($namespaceResolver, $fileToFqcn, $fileFinder);
$fqcnCollection = $scanner->scan('App\\Custom');

// Example: filter only controllers
$controllers = $fqcnCollection->filter(function($fqcn) {
    return str_ends_with($fqcn->toString(), 'Controller');
});
```

**Best For:** Legacy migration, selective/conditional wiring, advanced test setups, hybrid standards, custom filtering.

---

### 6. Batch Discovery Across Multiple Namespaces or Directories

**Scenario:** *You maintain several modules or feature sets, each under its own namespace or directory, and need to discover classes from all of them together for unified registration or analysis.*

```php
$namespaces = ['App\\Modules', 'App\\Extensions'];
$all = [];
foreach ($namespaces as $ns) {
    $all[] = $scanner->scan($ns);
}
// Optionally, merge FqcnCollections or process the results.
```

*Best For:* Unified provider registration, large-scale plugin systems, onboarding workflows, or cross-domain processing where coordination across multiple roots is required.

To achieve this, simply list the target namespaces or directories and perform an individual scan for each one. Aggregate or process the results as needed, such as building a unified service registry, registering providers, or performing cross-domain analysis. This approach maintains API simplicity while supporting advanced modularity at the integration layer.

---

### Usage Pattern Summary Table

| Pattern                                              | Scenario                                                      | Best For                                            |
| ---------------------------------------------------- | ------------------------------------------------------------- | --------------------------------------------------- |
| DiscoveryKernel::scanner                             | Bulk discovery for auto-wiring or analysis                    | Static or bulk code registration in modular systems |
| DiscoveryKernel::discoverExtensions                  | Plugins/extensions, runtime modularization                    | Feature toggles, dynamic bootstrapping              |
| DiscoveryKernel::discoverImplementations             | Handler/strategy/event-driven auto-registration               | Interface-driven runtime extensibility              |
| Container Integration with auto-discovered providers | Modular DI, dynamic bootstrapping, onboarding                 | Plug-and-play modules, onboarding new features      |
| Manual Service Wiring / Filtering                    | Legacy, migration, advanced filtering, test customization     | Selective registration, hybrid/migration setups     |
| **Batch Discovery Across Multiple Roots**            | Unified processing or registration across multiple namespaces | Large-scale onboarding, plugin/extension platforms  |

**Tip:** For all scalable, maintainable systems, use the DiscoveryKernel as your integration point and always favor contract-driven, container-integrated flows for maximum flexibility.

---

## Integration with the Container Module

The Discovery module is architected to operate as a first-class citizen alongside the Dependency Injection Container, enabling sophisticated scenarios of modularization, auto-registration, and dynamic wiring. This integration supports not only best practices in Clean Architecture and DDD, but also real scalability for large or plugin-driven systems.

---

### Why Integrate Discovery and the Container?

* **Eliminate Boilerplate:** Dynamically register every provider, handler, or service without manual list management.
* **True Plug-and-Play:** Drop a compliant module or provider into the source tree and it is registered automatically at bootstrap.
* **Extensibility:** Easily extend the system with plugins, extensions, or feature modules, all auto-discoverable and injectable.
* **Separation of Concerns:** Let Discovery handle code scanning/identification, while the Container manages instantiation and lifecycle.
* **Maximum Testability:** Both modules support contract-driven injection, enabling full mockability in integration or module tests.

---

### Provider Auto-Discovery and Registration

A best-practice pattern is to use the DiscoveryKernel to scan for all service providers (classes implementing `ContainerProviderInterface`) across your modules, and register them automatically in the DI container. This approach fully decouples provider registration from static lists or configuration files.

**Example: Full Provider Auto-Registration at Bootstrap**

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

**Key Points:**

* Any module that implements the standard interface is automatically registered and initialized.
* To add a new provider, simply create the class and ensure it implements `ContainerProviderInterface`—Discovery + Container does the rest.
* No risk of human error from forgotten manual provider registration.

---

### Dynamic Service and Handler Registration within Providers

Providers themselves can use Discovery for advanced scenarios:

* **Auto-binding event listeners, command handlers, strategies, or any interface-driven class.**
* This pattern is ideal for event-driven, CQRS, or plugin-heavy architectures.

**Example: Handler Auto-Binding from within a Provider**

```php
class MyModuleProvider implements ContainerProviderInterface {
    public function register(Container $container): void {
        // The DiscoveryKernel can be injected, resolved from the container, or kept as a singleton
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

**Key Points:**

* The provider does not need to know concrete handler class names in advance.
* Easily accommodates growth: new handlers are discovered and registered automatically as code evolves.

---

### Advanced Patterns

* **Conditional Registration:** Providers can filter discovered classes based on annotations, naming conventions, or environment checks before registration.
* **Feature Toggle:** Only register providers/handlers if certain extensions or plugins are present (as discovered by Discovery).
* **Hybrid Mode:** Use multiple DiscoveryKernels to target different namespaces or layers (e.g., `App\\Modules`, `App\\Extensions`).
* **Scope Control via Container:** Discovery can identify which services should be singleton or transient, allowing providers to register them with the correct scope in the Container (e.g., tagging classes or using conventions for scope assignment).
* **Provider Discovery for Custom Scopes:** Register providers in custom scopes (such as per-tenant, per-request, or per-feature) dynamically, enabling isolation and flexible lifecycles.
* **Lazy/Deferred Service Registration:** Discovery may be invoked on-demand (e.g., upon first request for a feature) to register providers or services only when actually needed, minimizing memory footprint.
* **Cross-module Dependency Resolution:** Discovery can be used to locate services or providers across module boundaries, allowing the Container to wire cross-cutting concerns (e.g., shared infrastructure, event buses, cross-module handlers) without static dependencies.
* **Runtime Extension Loading:** Extensions/plugins discovered at runtime can register themselves or be injected on-the-fly, supporting hot-pluggable architectures and marketplace models.

---

### Boot Order and Best Practices

* **Bootstrap Order:** Always instantiate the DiscoveryKernel **before** any provider auto-discovery or registration logic.
* **Singleton Kernel:** Register the DiscoveryKernel as a singleton in the container to avoid multiple instantiations and guarantee consistent discovery state.
* **Interface Contracts:** Require all auto-registered providers to implement `ContainerProviderInterface` for a uniform, maintainable registration pattern.
* **Documentation:** Explicitly document your integration logic and patterns for future maintainers.

---

### Typical Use Cases

* Modular business logic with drop-in modules (microkernel pattern)
* Plugin and extension systems in enterprise, EAD, or marketplace platforms
* Dynamic event-driven architectures (listeners auto-registered)
* Cross-module service resolution and shared infrastructure
* Codebase evolution with minimal configuration churn
* Automated, zero-boilerplate service registration for rapid development and onboarding
* Deferred/lazy provider/service loading to optimize startup
* Context-specific (scoped) provider registration for multitenancy or isolation

> **Pro Tip:** For full testability, inject mocks or alternate DiscoveryKernels into the container for test environments. Both Discovery and Container are built for contract-driven replacement and isolation.

---

## Core Concepts

The Discovery module is organized around five core concepts, each implemented and enforced in code:

### 1. Discovery Services (Application Layer)

* **DiscoveryScanner:** Orchestrates class and file discovery. Exposes methods to scan namespaces or directories, always returning a validated `FqcnCollection`.
* **ExtensionDiscoveryService:** Focused on finding extension modules or plugins, usually under dedicated namespaces or directories, for dynamic registration or plugin systems.

### 2. Contracts (Domain Layer)

* **PhpFileFinder:** Interface for recursive searching of `.php` files within directories, decoupling file system logic.
* **NamespaceToDirectoryResolver:** Maps namespaces to directories, supporting multiple strategies (PSR-4, custom, hybrid, legacy).
* **FileToFqcnResolver:** Resolves a PHP file path (relative to a root) to its fully qualified class name (FQCN).

### 3. Value Objects (Domain Layer)

* **FullyQualifiedClassName:** Immutable, validated FQCN representation, ensuring strong typing.
* **NamespaceName:** Encapsulates and validates namespace strings for consistency.
* **InterfaceName:** Specialized for interface FQCNs, structurally similar to FullyQualifiedClassName.
* **DirectoryPath:** Normalizes and validates directory usage throughout infrastructure.

### 4. Collections (Domain Layer)

* **FqcnCollection:** Typed, immutable collection of FQCNs with methods for iteration, filtering, extension, and uniqueness enforcement.

### 5. Infrastructure Implementations

* **PhpFileFinderRecursive:** Default recursive implementation for file discovery.
* **NamespaceToDirectoryResolverPsr4:** PSR-4-compliant namespace-to-directory resolution.
* **FileToFqcnResolverPsr4:** File-to-FQCN mapping under PSR-4 rules.
* **DiscoveryKernel:** Modular orchestrator, receiving configuration, wiring up all services, and exposing a ready-to-use DiscoveryScanner.

---

## API Reference

### DiscoveryKernel

* `__construct(string $psr4Prefix, string $baseSourceDir)`

  * Initializes the kernel with a namespace root and base source directory. Must be called before any discovery operation.
* `scanner(): DiscoveryScanner`

  * Returns a fully configured, singleton DiscoveryScanner, for generic class discovery and advanced scanning needs.
* `discoverExtensions(?string $namespace = null, bool $fallbackToProject = false): FqcnCollection`

  * Discovers all available extension modules or plugins under the provided (or root) namespace, returning a strongly-typed collection of FQCNs.
* `discoverImplementations(string $interfaceName, string $namespace): FqcnCollection`

  * Finds all classes implementing a given interface within a specified namespace, returning a value-object–safe FQCN collection.

### DiscoveryScanner

* `scan(string $namespace): FqcnCollection`

  * Scans a namespace for all PHP classes.
* `scanDirectory(DirectoryPath $directory): FqcnCollection`

  * Scans a specific directory for all PHP classes.

### ExtensionDiscoveryService

* `__construct(DiscoveryScanner $scanner)`
* `discoverExtensions(): FqcnCollection`

### Domain Contracts & Value Objects

* `PhpFileFinder::findFiles(DirectoryPath $directory): array`
* `NamespaceToDirectoryResolver::resolve(NamespaceName $namespace): DirectoryPath`
* `FileToFqcnResolver::resolve(DirectoryPath $root, string $file): FullyQualifiedClassName`
* `FqcnCollection::__construct(FullyQualifiedClassName ...$fqcns)`
* `FqcnCollection::add(FullyQualifiedClassName $fqcn): self`
* `FqcnCollection::filter(callable $predicate): self`
* `FqcnCollection::toArray(): array`
* `FqcnCollection::getIterator(): Traversable`
* `FullyQualifiedClassName::__construct(string $fqcn)` / `toString(): string`
* `NamespaceName::__construct(string $namespace)` / `toString(): string`
* `InterfaceName::__construct(string $interfaceName)` / `toString(): string`
* `DirectoryPath::__construct(string $path)` / `toString(): string`

### Infrastructure Implementations

* `PhpFileFinderRecursive::findFiles(DirectoryPath $directory): array`
* `NamespaceToDirectoryResolverPsr4::resolve(NamespaceName $namespace): DirectoryPath`
* `FileToFqcnResolverPsr4::resolve(DirectoryPath $root, string $file): FullyQualifiedClassName`

---

## Extension and Customization

The Discovery module is designed for maximal extensibility:

* **Custom contracts:** Implement any contract (`PhpFileFinder`, `NamespaceToDirectoryResolver`, `FileToFqcnResolver`) for custom, legacy, or hybrid discovery logic. Inject these via manual `DiscoveryScanner` construction or a subclassed `DiscoveryKernel`.
* **Testing:** Use mocks/fakes for any contract.
* **Subclassing DiscoveryKernel:** Centralize your customization by overriding protected methods or bindings in a custom kernel.

**Example — Custom File Finder:**

```php
class OnlyControllerFilesFinder implements PhpFileFinder {
    public function findFiles(DirectoryPath $directory): array {
        return glob($directory->toString() . '/*Controller.php') ?: [];
    }
}
```

---

## Best Practices

1. **Use DiscoveryKernel** for standard, maintainable integration. Reserve manual DI for custom/legacy cases.
2. **Value objects only:** Never pass raw strings; always wrap namespaces, directories, and FQCNs.
3. **Type-hint contracts:** In all app code, type-hint interfaces, not implementations.
4. **Aggregate via FqcnCollection:** Always use collections to manipulate lists of classes.
5. **Inject mocks for testing** via contract-driven architecture.
6. **Document all custom/override logic.**
7. **Stay PSR-4 compliant by default.**
8. **Sync documentation with code.**

---

## Known Limitations

1. **PSR-4 only (default):** For other autoloading, implement custom resolvers.
2. **No class existence validation:** Discovery is string-based; check class/interface existence at runtime if needed.
3. **PHP files only.**
4. **No symlink/case-insensitive/filesystem abstraction.**
5. **No persistent cache.**
6. **No dependency/hierarchy introspection.**
7. **Lifecycle assumes DiscoveryKernel or kernel array for correct setup.**

---

## Glossary

* **FQCN (Fully Qualified Class Name):** Complete namespace+class (e.g., `App\Module\Service\MyClass`).
* **PSR-4:** Namespace-directory autoloading standard.
* **Namespace Root:** Top-level namespace for a module or project.
* **Extension:** Dynamically discovered plugin/module.
* **Value Object:** Immutable representation of a core domain primitive (namespace, directory, FQCN).
* **Collection:** Typed aggregate of value objects (e.g., FqcnCollection).
* **Contract:** Interface for expected module behavior.
* **Infrastructure:** Concrete implementation of a contract (e.g., PSR-4 resolver).
* **DI Container:** Mechanism for resolving dependencies and contracts.
* **DiscoveryKernel:** Central orchestrator for module setup/configuration.
* **Statelessness:** Discovery is always side-effect-free.

---

## Changelog

### \[Unreleased]

* Initial full documentation, with architecture, API, and customization.
* Kernel-based integration and PSR-4-compliant defaults.
* Complete contract/value object–safe API.
