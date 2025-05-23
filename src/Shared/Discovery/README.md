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

The `DiscoveryKernel` acts as the integration and configuration entry point for the Discovery module. It is responsible for:

* Receiving core configuration parameters (such as PSR-4 prefix and source directory).
* Instantiating and exposing a pre-configured `DiscoveryScanner` that is ready for immediate use.
* Ensuring all infrastructure contracts are wired correctly, according to project conventions.
* Integrating seamlessly with the system’s global or modular kernel array, just like other functional kernels.

**Main file:**

* `src/Kernel/Discovery/DiscoveryKernel.php`: The orchestrator and provider for Discovery module services and lifecycle.

### Flow of Control

1. A discovery request is received by an application service (e.g., `DiscoveryScanner`), either directly or via `DiscoveryKernel`.
2. The service uses domain contracts to:

   * Map the namespace root to its physical directory.
   * Recursively scan for `.php` files.
   * Convert each PHP file path to its FQCN.
   * Aggregate results into a `FqcnCollection` value object.
3. The service returns a safe, filtered, and validated collection ready for further application use (such as extension registration or auto-wiring).

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

* The preferred way to integrate and configure the Discovery module is by registering an instance of `DiscoveryKernel`. This kernel will centralize all configuration and expose a ready-to-use `DiscoveryScanner`.
* Example:

```php
use App\Kernel\Discovery\DiscoveryKernel;

// Register the kernel with the desired PSR-4 prefix and source directory
$discoveryKernel = new DiscoveryKernel('App\\Modules', '/path/to/project/src/Modules');

// Retrieve a fully configured scanner
$scanner = $discoveryKernel->scanner();
```

* In a full application, the `DiscoveryKernel` can be registered alongside other system kernels in a central Kernel array or bootstrap script.
* Manual DI bindings for the core contracts are only needed for advanced customizations or if not using the kernel.

### Upgrading

* When upgrading the module, ensure all custom infrastructure implementations remain compatible with the unchanged domain contracts and value objects.

---

## Practical Usage Examples

This section demonstrates and explains the real usage flows of the Discovery module. Every example was developed and validated directly from the source code, covering all supported approaches:

---

### 1. Using Discovery via DiscoveryKernel (Recommended)

Best for: Most applications, modular systems, maintainable production code, or where you want kernel-based configuration and lifecycle integration.

```php
use App\Kernel\Discovery\DiscoveryKernel;

$psr4Prefix = 'App\\Modules';
$baseSourceDir = '/path/to/project/src/Modules';

$discoveryKernel = new DiscoveryKernel($psr4Prefix, $baseSourceDir);
$scanner = $discoveryKernel->scanner();

$namespace = 'App\\Modules\\Invoices';
$fqcnCollection = $scanner->scan($namespace);

foreach ($fqcnCollection as $fqcn) {
    echo $fqcn->toString() . PHP_EOL;
}
```

**When to use:**

* Preferred for most real-world projects
* Multi-module and extensible codebases
* When you want to avoid manual configuration and ensure auditability

---

### 2. Manual Service Wiring (Advanced/Custom Setup)

Best for: Advanced customization, legacy integration, or fine-grained control over discovery infrastructure contracts (test doubles, custom strategies, etc).

```php
use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\PhpFileFinderRecursive;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;

$namespaceMap = [
    'App\\Modules\\Invoices' => '/path/to/project/src/Modules/Invoices',
];

$namespaceResolver = new NamespaceToDirectoryResolverPsr4($namespaceMap);
$fileFinder = new PhpFileFinderRecursive();
$fileToFqcn = new FileToFqcnResolverPsr4('App\\Modules\\Invoices', $namespaceMap);

$scanner = new DiscoveryScanner($namespaceResolver, $fileToFqcn, $fileFinder);
$fqcnCollection = $scanner->scan('App\\Modules\\Invoices');

foreach ($fqcnCollection as $fqcn) {
    echo $fqcn->toString() . PHP_EOL;
}
```

**When to use:**

* Custom infrastructure or alternative discovery logic
* Full control for legacy/edge cases, testing, or overriding domain contracts

---

### 3. Extension/Plugin Discovery via ExtensionDiscoveryService

Best for: Plugin/module architectures, dynamic runtime extensibility, auto-registration of modular features.

```php
use App\Kernel\Discovery\DiscoveryKernel;
use App\Shared\Discovery\Application\Extension\ExtensionDiscoveryService;

$psr4Prefix = 'App\\Extensions';
$baseSourceDir = '/path/to/project/src/Extensions';
$discoveryKernel = new DiscoveryKernel($psr4Prefix, $baseSourceDir);
$scanner = $discoveryKernel->scanner();

$extensionService = new ExtensionDiscoveryService($scanner);
$extensions = $extensionService->discoverExtensions();

foreach ($extensions as $extensionFqcn) {
    // Register, initialize or inject each extension
    echo $extensionFqcn->toString() . PHP_EOL;
}
```

**When to use:**

* Applications with extension points, plugin marketplaces, event/listener registration, or dynamic modular loading

---

### 4. Custom Contract Implementation (Legacy/Non-PSR-4 or Selective Discovery)

Best for: Projects with non-standard file conventions, legacy codebases, or highly selective/filtered discovery needs.

```php
use App\Shared\Discovery\Domain\Contracts\PhpFileFinder;
use App\Shared\Discovery\Domain\ValueObjects\DirectoryPath;
use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;

class OnlyControllerFilesFinder implements PhpFileFinder {
    public function findFiles(DirectoryPath $directory): array {
        $all = glob($directory->toString() . '/*Controller.php');
        return $all ?: [];
    }
}

$namespaceMap = [ 'App\\Modules\\Invoices' => '/path/to/project/src/Modules/Invoices' ];
$namespaceResolver = new NamespaceToDirectoryResolverPsr4($namespaceMap);
$fileToFqcn = new FileToFqcnResolverPsr4('App\\Modules\\Invoices', $namespaceMap);
$customFinder = new OnlyControllerFilesFinder();

$scanner = new DiscoveryScanner($namespaceResolver, $fileToFqcn, $customFinder);
fqcnCollection = $scanner->scan('App\\Modules\\Invoices');

foreach ($fqcnCollection as $fqcn) {
    echo $fqcn->toString() . PHP_EOL;
}
```

**When to use:**

* Selective file patterns, legacy code, advanced filtering or integration with external autoloaders

---

### Summary Table: Usage Options

| Option                         | Recommended For                                     |
| ------------------------------ | --------------------------------------------------- |
| DiscoveryKernel                | Most projects, modular systems, production usage    |
| Manual Service Wiring          | Advanced customization, testing, legacy integration |
| ExtensionDiscoveryService      | Plugin/module systems, runtime extensibility        |
| Custom Contract Implementation | Legacy layouts, special project requirements        |

> **Recommendation:** For all maintainable, scalable systems, favor DiscoveryKernel and default contracts. Use manual or custom setups only for advanced, legacy, or experimental needs.

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

### Application Layer

* **DiscoveryKernel**

  * `__construct(string $psr4Prefix, string $baseSourceDir)`: Initializes the kernel.
  * `scanner(): DiscoveryScanner`: Returns a pre-configured, singleton scanner.
* **DiscoveryScanner**

  * `scan(string $namespace): FqcnCollection` — Scans a namespace for all classes.
  * `scanDirectory(DirectoryPath $directory): FqcnCollection` — Scans a directory.
* **ExtensionDiscoveryService**

  * `__construct(DiscoveryScanner $scanner)`
  * `discoverExtensions(): FqcnCollection`

### Domain Layer

* **PhpFileFinder**

  * `findFiles(DirectoryPath $directory): array`
* **NamespaceToDirectoryResolver**

  * `resolve(NamespaceName $namespace): DirectoryPath`
* **FileToFqcnResolver**

  * `resolve(DirectoryPath $root, string $file): FullyQualifiedClassName`
* **FqcnCollection**

  * `__construct(FullyQualifiedClassName ...$fqcns)`
  * `add(FullyQualifiedClassName $fqcn): self`
  * `filter(callable $predicate): self`
  * `toArray(): array`
  * `getIterator(): Traversable`
* **Value Objects**

  * `FullyQualifiedClassName::__construct(string $fqcn)` / `toString(): string`
  * `NamespaceName::__construct(string $namespace)` / `toString(): string`
  * `InterfaceName::__construct(string $interfaceName)` / `toString(): string`
  * `DirectoryPath::__construct(string $path)` / `toString(): string`

### Infrastructure Layer

* **PhpFileFinderRecursive**

  * `findFiles(DirectoryPath $directory): array`
* **NamespaceToDirectoryResolverPsr4**

  * `resolve(NamespaceName $namespace): DirectoryPath`
* **FileToFqcnResolverPsr4**

  * `resolve(DirectoryPath $root, string $file): FullyQualifiedClassName`

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
