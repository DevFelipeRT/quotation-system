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

**Main files (Application Layer):**

* `Service/DiscoveryScanner.php` – Core entry point for discovery requests (namespace/directory to FQCN collection).
* `Extension/ExtensionDiscoveryService.php` – Specialized for scanning extension modules, plugins, or similar constructs.

### 2. Domain Layer

Defines all business contracts, value objects, and domain logic abstractions for the discovery process. This is the heart of the module's stability and testability:

* **Contracts** – Interfaces for namespace-directory resolution, file-to-FQCN mapping, and recursive file finding.
* **Value Objects** – Strongly-typed, immutable representations for FQCNs, namespaces, interface names, and directory paths.
* **Collections** – Typed collections to safely aggregate results (e.g., `FqcnCollection`).

**Main files (Domain Layer):**

* `Contracts/PhpFileFinder.php` – Finds PHP files under a directory (contract).
* `Contracts/NamespaceToDirectoryResolver.php` – Maps namespaces to directories (contract).
* `Contracts/FileToFqcnResolver.php` – Resolves file paths to FQCNs (contract).
* `ValueObjects/DirectoryPath.php`, `FullyQualifiedClassName.php`, `NamespaceName.php`, `InterfaceName.php` – Immutable value objects.
* `Collection/FqcnCollection.php` – Type-safe collection for discovered classes.

### 3. Infrastructure Layer

Provides concrete implementations for all domain contracts, making the module operational in real projects:

* Implements PSR-4 logic for namespace-directory and file-class resolution.
* Performs actual file system operations for recursive file scanning.
* Can be replaced or extended for legacy or non-PSR-4 environments by substituting only the infrastructure layer.

**Main files (Infrastructure Layer):**

* `PhpFileFinderRecursive.php` – Recursively finds PHP files in a directory.
* `NamespaceToDirectoryResolverPsr4.php` – Maps namespaces to directories as per PSR-4.
* `FileToFqcnResolverPsr4.php` – Converts file paths to FQCNs using PSR-4 rules.

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

* `src/Kernel/Discovery/DiscoveryKernel.php` – The orchestrator and provider for Discovery module services and lifecycle.

### Flow of Control

The Discovery module coordinates the process of scanning, identifying, and registering PHP classes in a predictable and auditable way. The flow of control is as follows:

1. **Kernel Initialization** – The `DiscoveryKernel` is instantiated with the root PSR-4 prefix and the corresponding base source directory. This configures all internal services and makes the kernel ready to serve discovery requests.
2. **Service Access** – Consumers (such as the application bootstrap, DI container, or providers) use the kernel as the single point of entry—either invoking `scanner()` for generic scanning or the specialized `discoverExtensions()`/`discoverImplementations()` methods for common discovery patterns.
3. **Resolution Pipeline**:

   * The kernel delegates to the configured `DiscoveryScanner`, which uses domain contracts and infrastructure services to:

     * Map a namespace to its physical directory.
     * Recursively scan for `.php` files.
     * Convert each PHP file to its FQCN, using value object enforcement for type safety.
   * For `discoverExtensions()`, an additional layer of semantic filtering is applied to only return valid extension modules (classes implementing a predefined `ExtensionInterface`).
   * For `discoverImplementations()`, the scanner locates and verifies all classes implementing a given interface within the specified namespace.
4. **Result Aggregation** – Results are always returned as immutable, typed `FqcnCollection` value objects—ready for further application use, such as module registration, dependency injection, or runtime introspection.
5. **Extensibility Point** – At any stage, consumers may inject custom contracts, extend the kernel, or override discovery logic to support advanced scenarios (legacy projects, non-PSR-4 autoloading, custom plugins, etc.), without breaking the overall architectural flow.

**This predictable flow ensures that all discovered elements are handled in a strongly-typed, decoupled, and auditable manner—maximizing reliability and maintainability across the system.**

### Batch Scanning and Multi-Namespace Support

The Discovery module allows projects to configure multiple namespace-to-directory mappings. To scan across several namespaces or directories, simply repeat the discovery operation for each target and combine the results as needed. Coordination of batch discovery is done at the application or integration layer, not within the core API. This keeps responsibilities clear: resolvers handle mapping logic while the application controls multi-scope operations efficiently.

### Extensibility

The infrastructure layer can be swapped for custom or legacy logic, as long as domain contracts are implemented. The `DiscoveryKernel` itself can be subclassed or extended to override configuration, provide alternate resolvers, or inject project-specific behavior. The rest of the system remains agnostic to these changes, ensuring robustness, flexibility, and maintainability.

---

## Installation

### Prerequisites

* **PHP 8.0 or higher** – The module code uses strict types, modern syntax, and has no deprecated features.
* **PSR-4 directory structure** – The module relies on PSR-4 conventions for its core infrastructure implementations.
* **No Composer or external dependency is required** – The system uses a custom autoloader (`autoload.php` at the project root).

### Installation Steps

1. **Include or clone the project** in your target environment.
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

* Use `$discoveryKernel->scanner()` for general class discovery under the configured prefix.
* Use `$discoveryKernel->discoverExtensions()` to list all extension modules/plugins under the configured prefix, optionally filtered by namespace.
* Use `$discoveryKernel->discoverImplementations($interfaceName, $namespace)` to dynamically locate all classes implementing a given contract/interface in a given namespace.

#### **Integration with DI Container**

* Register the `DiscoveryKernel` as a singleton within your DI Container, making it injectable into providers, modules, or application services that require discovery operations.
* **Example:**

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

### 1. Using Discovery via DiscoveryKernel (Recommended)

**Scenario:** *You have a modular project and want to enumerate classes in a namespace for static analysis, auto-wiring, or bulk registration. For example, you may list all service classes in a module that implement a common interface.*

```php
use App\Kernel\Discovery\DiscoveryKernel;

$psr4Prefix = 'App\\Modules';
$baseSourceDir = '/path/to/project/src/Modules';

$discoveryKernel = new DiscoveryKernel($psr4Prefix, $baseSourceDir);
$scanner = $discoveryKernel->scanner();

$namespace = 'App\\Modules\\Invoices';
$fqcnCollection = $discoveryKernel->discoverImplementations(\App\Modules\Invoices\Services\ServiceInterface::class, $namespace);

foreach ($fqcnCollection as $fqcn) {
    echo $fqcn->value() . PHP_EOL;
}
```

**Best For:** General module-wide discovery, code introspection, bulk class registration (e.g., listing all services, controllers, or entities that share a common interface) in modular systems.

---

### 2. Manual Service Wiring (Advanced/Custom Setup)

**Scenario:** *You need to integrate legacy code, use a custom autoloading strategy, or have unique scanning requirements. In this case, you prefer to construct the discovery components manually (without `DiscoveryKernel`) to gain fine-grained control over the scanning process.*

```php
use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\PhpFileFinderRecursive;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;
use App\Shared\Discovery\Domain\ValueObjects\InterfaceName;
use App\Shared\Discovery\Domain\ValueObjects\NamespaceName;

$namespaceResolver = new NamespaceToDirectoryResolverPsr4('App\\Modules', '/path/to/project/src/Modules');
$fileFinder = new PhpFileFinderRecursive();
$fileToFqcn = new FileToFqcnResolverPsr4();
$scanner = new DiscoveryScanner($namespaceResolver, $fileToFqcn, $fileFinder);

$fqcnCollection = $scanner->discoverImplementing(
    new InterfaceName(\App\Modules\Invoices\Services\ServiceInterface::class),
    new NamespaceName('App\\Modules\\Invoices')
);

foreach ($fqcnCollection as $fqcn) {
    // e.g., register or analyze classes
    echo $fqcn->value() . PHP_EOL;
}
```

**When to use:**

* Custom infrastructure or non-standard autoloading (manual injection of resolvers and finder).
* Full control for legacy/edge cases, testing scenarios, or overriding domain contracts.

---

### 3. Extension/Plugin Discovery via ExtensionDiscoveryService

**Scenario:** *Your system is extensible via plugins or modules. At startup or on-demand, you need to discover all extension classes for registration, initialization, or dynamic wiring.*

```php
use App\Kernel\Discovery\DiscoveryKernel;
use App\Shared\Discovery\Application\Extension\ExtensionDiscoveryService;

$psr4Prefix = 'App\\Extensions';
$baseSourceDir = '/path/to/project/src/Extensions';

$discoveryKernel = new DiscoveryKernel($psr4Prefix, $baseSourceDir);
$scanner = $discoveryKernel->scanner();
$extensionService = new ExtensionDiscoveryService($scanner);
$extensions = $extensionService->discoverExtensions('App\\Extensions\\ExtensionInterface', 'App\\Extensions');

foreach ($extensions as $extensionFqcn) {
    // Register or initialize extension
    echo $extensionFqcn->value() . PHP_EOL;
}
```

**Best For:** Plugin architectures, dynamic module registration, runtime modularity, and feature toggle systems (finding all extension classes that implement a standard extension interface).

---

### 4. Dynamic Provider Auto-Registration with Container Integration

**Scenario:** *You maintain a large platform with multiple modules or teams. New modules add providers implementing a standard contract. You want every compliant provider to be automatically discovered and registered with the DI container, without updating any static configuration.*

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
    $container->registerProvider(new ($fqcn->value())());
}
```

**Best For:** Automatic modular service provider registration, zero-boilerplate bootstrap, onboarding new modules/teams, and scalable dependency injection.

---

### 5. Discovering All Implementations of an Interface (e.g., Handlers, Strategies)

**Scenario:** *You want to implement patterns like Command Bus or event-driven architecture. The system should auto-discover every class that implements a given interface (e.g., all event listeners or domain handlers in a namespace), enabling runtime registration or auto-wiring.*

```php
$handlers = $kernel->discoverImplementations(
    \App\Modules\Billing\Domain\Handler\InvoiceHandlerInterface::class,
    'App\\Modules\\Billing\\Domain\\Handler'
);

foreach ($handlers as $handlerFqcn) {
    $container->bind($handlerFqcn->value(), $handlerFqcn->value());
}
```

**Best For:** Dynamic registration of handlers, strategies, listeners, and interface-driven modularity (CQRS, event-driven systems).

---

### 6. Custom Scenarios: Conditional Wiring or Advanced Filtering

**Scenario:** *You have legacy naming patterns or selective registration needs (e.g., only classes with a certain suffix like "Controller"). You want to partially filter discovered classes or use custom finders. This approach is also useful for advanced tests or bridging hybrid standards.*

```php
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\PhpFileFinderRecursive;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;
use App\Shared\Discovery\Application\Service\DiscoveryScanner;

$namespaceResolver = new NamespaceToDirectoryResolverPsr4('App', '/path/to/project/src');
$fileFinder = new PhpFileFinderRecursive();
$fileToFqcn = new FileToFqcnResolverPsr4();
$scanner = new DiscoveryScanner($namespaceResolver, $fileToFqcn, $fileFinder);

$fqcnCollection = $scanner->discoverImplementing(
    new \App\Shared\Discovery\Domain\ValueObjects\InterfaceName(\App\Custom\Contracts\BaseInterface::class),
    new \App\Shared\Discovery\Domain\ValueObjects\NamespaceName('App\\Custom')
);

// Example: filter only controllers by naming convention
$controllers = [];
foreach ($fqcnCollection as $fqcn) {
    if (str_ends_with($fqcn->value(), 'Controller')) {
        $controllers[] = $fqcn;
    }
}
```

**Best For:** Legacy integration, selective or conditional wiring, advanced test setups, hybrid standards, or migration scenarios where you need fine-grained filtering.

---

### Batch Discovery Across Multiple Roots

If you need to discover classes across multiple independent namespaces or directories (e.g., multiple module and extension directories), you can perform multiple discovery operations and aggregate the results:

```php
$namespaces = ['App\\Modules', 'App\\Extensions'];
$allCollections = [];

foreach ($namespaces as $ns) {
    $allCollections[] = $kernel->discoverImplementations($interfaceName, $ns);
}

// Optionally, merge $allCollections (which are FqcnCollections) or process them as needed.
```

**Best For:** Unified processing or registration across multiple namespace roots – such as a core application plus plugins – where you want to onboard or analyze all components in one pass.

By iterating over each target namespace and invoking the appropriate discovery method (e.g., `discoverImplementations` for providers or handlers, or `discoverExtensions` for plugins), you maintain a simple API usage while supporting advanced modularity at the integration layer.

---

### Usage Pattern Summary

| Pattern                                               | Scenario                                              | Best For                                                 |
| ----------------------------------------------------- | ----------------------------------------------------- | -------------------------------------------------------- |
| **DiscoveryKernel + scanner()**                       | Bulk discovery for auto-wiring or analysis            | Static or bulk code registration in modular systems      |
| **DiscoveryKernel::discoverExtensions**               | Plugin/extension discovery, runtime modularization    | Feature toggles, dynamic module bootstrapping            |
| **DiscoveryKernel::discoverImplementations**          | Interface-driven discovery (handlers, providers)      | Interface-based runtime extensibility and decoupling     |
| **Container Integration (auto-discovered providers)** | Modular DI, dynamic bootstrapping, onboarding         | Plug-and-play modules, seamless feature team integration |
| **Manual Wiring / Custom Filtering**                  | Legacy setups, custom filtering or advanced use cases | Selective registration, hybrid or migration scenarios    |
| **Batch Discovery Across Roots**                      | Unified scanning across multiple namespaces           | Large-scale onboarding, plugin/extension platforms       |

> **Tip:** Use the `DiscoveryKernel` as your primary integration point and favor contract-driven, container-integrated flows for maximum flexibility and maintainability.

---

## Integration with the Container Module

The Discovery module is designed to operate as a first-class citizen alongside the Dependency Injection Container, enabling sophisticated scenarios of modularization, auto-registration, and dynamic wiring. This integration supports not only best practices in Clean Architecture and DDD, but also real scalability for large or plugin-driven systems.

---

### Why Integrate Discovery and the Container?

* **Eliminate Boilerplate:** Dynamically register every provider, handler, or service without maintaining manual lists.
* **True Plug-and-Play:** Drop a compliant module or provider into the codebase and it is registered automatically at bootstrap.
* **Extensibility:** Easily extend the system with new plugins, extensions, or feature modules, all auto-discoverable and injectable.
* **Separation of Concerns:** Let Discovery handle code scanning/identification, while the Container manages instantiation and lifecycle.
* **Maximum Testability:** Both modules support contract-driven injection, enabling full mockability in integration or module tests.

---

### Provider Auto-Discovery and Registration

A best-practice pattern is to use the `DiscoveryKernel` to scan for all service providers (classes implementing `ContainerProviderInterface`) across your modules, and register them automatically in the DI container. This approach completely decouples provider registration from static lists or configuration files.

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
    $container->registerProvider(new ($fqcn->value())());
}
```

**Key Points:**

* Any module that implements the standard provider interface is automatically discovered and registered.
* To add a new provider, simply create the class and ensure it implements `ContainerProviderInterface`—Discovery + Container will handle the rest.
* This approach eliminates the risk of human error from forgotten provider registrations.

---

### Dynamic Service and Handler Registration within Providers

Providers themselves can use Discovery for advanced scenarios:

* **Auto-binding event listeners, command handlers, strategies, or any interface-driven classes.**
* This pattern is ideal for event-driven, CQRS, or plugin-heavy architectures.

**Example: Handler Auto-Binding from within a Provider**

```php
class MyModuleProvider implements ContainerProviderInterface {
    public function register(Container $container): void {
        // The DiscoveryKernel can be injected via the container or kept as a singleton
        global $discoveryKernel;
        $handlers = $discoveryKernel->discoverImplementations(
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

* The provider does not need prior knowledge of concrete handler class names.
* New handlers are automatically discovered and registered as the codebase grows.

---

### Advanced Patterns

* **Conditional Registration:** Providers can filter discovered classes based on attributes, naming conventions, or environment checks before registration.
* **Feature Toggle:** Only register providers/handlers if certain extensions or plugins are present (as discovered by Discovery).
* **Hybrid Mode:** Use multiple `DiscoveryKernel` instances to target different namespace roots (e.g., `App\\Modules` vs `App\\Extensions`).
* **Scope Control via Container:** Discovery can identify which services should be singletons or transient, allowing providers to register them with the correct scope in the Container (e.g., using naming conventions or tagging).
* **Custom Discovery Contracts:** Implement any Discovery contract (e.g., a specialized `PhpFileFinder`) and inject it (via a subclassed `DiscoveryKernel` or manual `DiscoveryScanner` construction) for unique file naming or loading strategies.
* **Lazy/Deferred Loading:** Invoke Discovery on-demand (e.g., upon first use of a feature) to register providers or services only when needed, reducing initial overhead.
* **Cross-Module Resolution:** Use Discovery to locate services or providers across module boundaries, enabling the Container to wire cross-cutting concerns (e.g., shared infrastructure, event buses) without static dependencies.
* **Runtime Extension Loading:** Discovered extension/plugin classes can even self-register or be loaded on-the-fly, supporting hot-pluggable architectures and marketplace models.

---

### Boot Order and Best Practices

* **Bootstrap Early:** Always instantiate the `DiscoveryKernel` **before** any provider auto-discovery or dynamic registration logic.
* **Singleton Kernel:** Treat the `DiscoveryKernel` as a singleton (e.g., one instance in the container) to avoid multiple scans and ensure a consistent discovery state.
* **Interface Contracts:** Require all auto-discovered providers to implement a common interface (like `ContainerProviderInterface`) for a uniform registration pattern.
* **Document Your Patterns:** Clearly document how Discovery is integrated and any custom behaviors for future maintainers.
* **Testing:** Inject mock `DiscoveryScanner` or alternate `DiscoveryKernel` instances in tests to simulate and isolate discovery behavior.

---

## Core Concepts

The Discovery module is organized around five core concepts, each implemented and enforced in code:

### 1. Discovery Services (Application Layer)

* **DiscoveryScanner:** Orchestrates class and file discovery. Exposes methods to scan namespaces or directories, always returning a validated `FqcnCollection`.
* **ExtensionDiscoveryService:** Focused on finding extension modules or plugins (classes implementing a known extension interface), usually under dedicated namespaces or directories, for dynamic registration or plugin systems.

### 2. Contracts (Domain Layer)

* **PhpFileFinder:** Interface for recursive searching of `.php` files within directories, decoupling file system logic.
* **NamespaceToDirectoryResolver:** Maps namespaces to directories, supporting multiple strategies (PSR-4 by default, but extensible for custom or legacy mappings).
* **FileToFqcnResolver:** Resolves a PHP file path (relative to a base directory) to its fully qualified class name (FQCN).

### 3. Value Objects (Domain Layer)

* **FullyQualifiedClassName:** Immutable, validated FQCN representation, ensuring strong typing (e.g., wraps a string class name).
* **NamespaceName:** Encapsulates and validates namespace strings for consistency.
* **InterfaceName:** Specialized value object for interface FQCNs, structurally similar to FullyQualifiedClassName.
* **DirectoryPath:** Normalizes and validates directory paths throughout infrastructure.

### 4. Collections (Domain Layer)

* **FqcnCollection:** Typed, immutable collection of FQCN value objects, with methods for safe iteration and aggregation of results.

### 5. Infrastructure Implementations

* **PhpFileFinderRecursive:** Default recursive implementation of `PhpFileFinder`.
* **NamespaceToDirectoryResolverPsr4:** PSR-4-compliant implementation of `NamespaceToDirectoryResolver`.
* **FileToFqcnResolverPsr4:** PSR-4-compliant implementation of `FileToFqcnResolver`.
* **DiscoveryKernel:** Integration orchestrator that sets up default infrastructure and exposes a `DiscoveryScanner` ready to use.

---

## API Reference

### Application Layer

* **DiscoveryKernel**

  * `__construct(string $psr4Prefix, string $baseSourceDir)`: Initializes the kernel with a namespace root and base directory.
  * `discoverExtensions(?string $namespace = null, bool $fallbackToProject = false): FqcnCollection`: Discovers extension modules/plugins under the given (or root) namespace, optionally falling back to the entire project.
  * `discoverImplementations(string $interfaceName, string $namespace): FqcnCollection`: Finds all classes implementing a given interface within the specified namespace.
  * `scanner(): DiscoveryScanner`: Returns a pre-configured, singleton `DiscoveryScanner` for general scanning.

* **DiscoveryScanner**

  * `discoverImplementing(InterfaceName $interface, NamespaceName $namespace): FqcnCollection` — Scans a namespace for all classes implementing the given interface.

* **ExtensionDiscoveryService**

  * `__construct(DiscoveryScanner $scanner)`
  * `discoverExtensions(string $extensionInterfaceName, string $extensionsNamespace): FqcnCollection`

### Domain Layer

* **PhpFileFinder**

  * `findAll(DirectoryPath $directory): string[]`

* **NamespaceToDirectoryResolver**

  * `resolve(NamespaceName $namespace): DirectoryPath`

* **FileToFqcnResolver**

  * `resolve(DirectoryPath $baseDirectory, string $filePath, NamespaceName $baseNamespace): FullyQualifiedClassName`

* **FqcnCollection**

  * `__construct(array $items = [])`
  * `isEmpty(): bool`
  * `withAdded(FullyQualifiedClassName $fqcn): self`
  * `getIterator(): Traversable`

* **Value Objects**

  * `FullyQualifiedClassName::__construct(string $fqcn)` / `value(): string`
  * `NamespaceName::__construct(string $namespace)` / `value(): string`
  * `InterfaceName::__construct(string $interfaceName)` / `value(): string`
  * `DirectoryPath::__construct(string $path)` / `value(): string`

### Infrastructure Implementations

* **PhpFileFinderRecursive**

  * `findAll(DirectoryPath $directory): string[]`

* **NamespaceToDirectoryResolverPsr4**

  * `resolve(NamespaceName $namespace): DirectoryPath`

* **FileToFqcnResolverPsr4**

  * `resolve(DirectoryPath $baseDirectory, string $filePath, NamespaceName $baseNamespace): FullyQualifiedClassName`

---

## Extension and Customization

The Discovery module is designed for maximal extensibility:

* **Custom Contracts:** Implement any discovery contract (`PhpFileFinder`, `NamespaceToDirectoryResolver`, or `FileToFqcnResolver`) for custom, legacy, or hybrid logic. Inject these via manual `DiscoveryScanner` construction or a subclassed `DiscoveryKernel`.
* **Testing:** Inject mock or fake implementations for any contract to isolate and test the discovery process in a controlled manner.
* **Subclassing DiscoveryKernel:** Override configuration or protected methods in a custom kernel to centralize your customization.

**Example – Custom File Finder:**

```php
class OnlyControllerFilesFinder implements PhpFileFinder {
    public function findAll(DirectoryPath $directory): array {
        return glob($directory->value() . '/*Controller.php') ?: [];
    }
}
```

In this example, `OnlyControllerFilesFinder` is a custom implementation of `PhpFileFinder` that limits file discovery to only those PHP files ending with "Controller.php" (handy if you only want to load controllers). You could inject this custom finder into `DiscoveryScanner` (or a `DiscoveryKernel` subclass) to modify discovery behavior without changing other components.

---

## Best Practices

1. **Use DiscoveryKernel for Standard Integration:** Rely on `DiscoveryKernel` in production code for consistency and maintainability. Reserve manual wiring for special cases or testing.
2. **Leverage Value Objects:** Always wrap raw strings (namespaces, directories, class names) in their respective value objects when extending or customizing Discovery to avoid stringly-typed errors.
3. **Type-Hint Contracts:** In application code, depend on discovery interfaces (contracts) rather than concrete implementations to allow easy swapping or extension.
4. **Aggregate via FqcnCollection:** Use `FqcnCollection` for collecting and manipulating discovered class names. This ensures type safety and provides utility methods (like iteration and immutability).
5. **Inject Mocks for Testing:** Thanks to the contract-driven design, you can inject mock `PhpFileFinder`, `NamespaceToDirectoryResolver`, or `FileToFqcnResolver` implementations (or even a mock `DiscoveryKernel`) to fully control the discovery process in tests.
6. **Document Customizations:** Clearly document any custom discovery behavior or overrides (e.g., in a subclassed kernel) to assist future maintainers.
7. **Stay PSR-4 Compliant:** By default, Discovery assumes PSR-4 compliance. If you deviate (e.g., legacy autoloaders), be prepared to implement and inject custom resolvers or finders.
8. **Sync Code and Documentation:** When modifying discovery logic or extending the module, update both code comments and relevant documentation to avoid drift.

---

## Known Limitations

1. **PSR-4 Only (by default):** The provided infrastructure assumes PSR-4 directory conventions. To support other autoloading standards, custom resolvers must be implemented.
2. **No Class Existence Guarantee:** Discovery is based on file paths and naming. It does not confirm that each discovered class can be loaded or instantiated. If needed, validate class existence or interface implementation after discovery.
3. **PHP Files Only:** The module scans for `.php` files. Other file types or code definitions (like classes defined in runtime or via eval) are not considered.
4. **No Virtual Filesystem Support:** The default finders and resolvers operate on the local filesystem. Projects using streams, phars, or remote file systems would need custom implementations.
5. **No Persistent Cache:** Discovery happens at runtime on each invocation. For very large codebases, consider implementing a caching layer outside the module if performance becomes an issue.
6. **No Dependency Analysis:** The module does not introspect class hierarchies or dependencies beyond interface implementation. It's focused on file presence and naming conventions.
7. **Requires Controlled Bootstrapping:** The discovery lifecycle assumes use of `DiscoveryKernel` or an equivalent coordinated setup. Running discovery without proper initialization or autoload configuration may yield incomplete results.

---

## Glossary

* **FQCN (Fully Qualified Class Name):** A class name with its namespace (e.g., `App\Module\Service\MyClass`).
* **PSR-4:** A standard for autoloading classes based on namespace and directory structure.
* **Namespace Root:** The top-level namespace for a module or project segment (e.g., `App\Modules`).
* **Extension:** A dynamically discovered plugin or module component (often implementing a shared `ExtensionInterface`).
* **Value Object:** An immutable object representing a core domain concept (e.g., a namespace or file path), enforcing validation and consistency.
* **Collection:** A typed aggregate of value objects (e.g., `FqcnCollection` for class names).
* **Contract:** An interface defining expected behavior (e.g., `PhpFileFinder` contract for file discovery).
* **Infrastructure:** A concrete implementation of a contract, often tied to environment specifics (e.g., a PSR-4 resolver implementation).
* **DI Container:** Dependency Injection Container, responsible for managing object creation and lifecycle.
* **DiscoveryKernel:** The central orchestrator that configures and exposes discovery services.
* **Statelessness:** Discovery operations are side-effect-free; calling them does not alter application state outside returned results.

---

## Changelog

### [Unreleased]

* Initial full documentation with architecture overview, API reference, and customization examples.
* Kernel-based integration with PSR-4 compliant defaults.
* Complete contract/value-object–driven API for safe discovery operations.
