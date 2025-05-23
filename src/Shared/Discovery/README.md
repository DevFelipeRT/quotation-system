# Discovery Module

---

## Introduction

The Discovery module provides a robust, decoupled, and extensible mechanism for scanning, identifying, and registering PHP classes, interfaces, and extensions across the codebase. It is engineered to support modularization, dynamic plugin or module registration, and the automatic detection of components without relying on Composer or third-party autoloaders.

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

### Flow of Control

1. A discovery request is received by an application service (e.g., `DiscoveryScanner`).
2. The service uses domain contracts to:

   * Map the namespace root to its physical directory.
   * Recursively scan for `.php` files.
   * Convert each PHP file path to its FQCN.
   * Aggregate results into a `FqcnCollection` value object.
3. The service returns a safe, filtered, and validated collection ready for further application use (such as extension registration or auto-wiring).

### Extensibility

The infrastructure layer can be swapped for custom or legacy logic, as long as domain contracts are implemented. The rest of the system remains agnostic to these changes, ensuring robustness, flexibility, and maintainability.

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

### Registration/Bootstrap

* The module is stateless and ready to use; no bootstrap script or provider registration is needed by default.
* For dependency injection, bind the `Domain\Contracts` interfaces to their `Infrastructure` implementations, typically via your application’s DI container configuration.
* Example (pseudo-code):

```php
// Example: binding interfaces to implementations
$container->bind(PhpFileFinder::class, PhpFileFinderRecursive::class);
$container->bind(NamespaceToDirectoryResolver::class, NamespaceToDirectoryResolverPsr4::class);
$container->bind(FileToFqcnResolver::class, FileToFqcnResolverPsr4::class);
```

### Upgrading

* When upgrading the module, ensure all custom infrastructure implementations remain compatible with the unchanged domain contracts and value objects.

---

## Core Concepts

The Discovery module is organized around five core concepts, each reflected directly in the codebase and its architectural layering:

### 1. Discovery Services (Application Layer)

#### DiscoveryScanner

* The primary service for orchestrating class and file discovery operations.
* Receives a namespace or directory and coordinates the flow between the domain contracts for resolution.
* Returns collections of `FullyQualifiedClassName` value objects, ready for further processing or registration.

#### ExtensionDiscoveryService

* Specialized service focused on discovering extension modules or plugins.
* Leverages the same domain contracts, applying filtering or aggregation logic to locate extension points.

### 2. Contracts (Domain Layer)

#### PhpFileFinder

* Defines an interface for recursive discovery of `.php` files in a given directory.
* Decouples the application from the actual file system scanning logic.

#### NamespaceToDirectoryResolver

* Abstracts the logic to map a PHP namespace to its corresponding directory on the file system.
* Supports various mapping strategies (e.g., PSR-4, custom conventions).

#### FileToFqcnResolver

* Declares the contract for resolving a PHP file path (relative to a root) into its fully qualified class name (FQCN).
* Enables multiple naming or autoload strategies to coexist in the system.

### 3. Value Objects (Domain Layer)

#### FullyQualifiedClassName

* Immutable, validated representation of a fully qualified class name.
* Used to guarantee type safety and integrity when handling discovered classes.

#### NamespaceName

* Encapsulates and validates PHP namespace strings, allowing safe manipulation and extraction of sub-namespaces.

#### InterfaceName

* Specialized value object for interface names, inheriting from or structured similarly to `FullyQualifiedClassName`.

#### DirectoryPath

* Encapsulates directory paths, ensuring normalization and correct usage across filesystem operations.

### 4. Collections (Domain Layer)

#### FqcnCollection

* Typed, immutable collection of `FullyQualifiedClassName` value objects.
* Provides safe methods for iteration, filtering, addition, and uniqueness enforcement.

### 5. Infrastructure Implementations

#### PhpFileFinderRecursive

* Concrete implementation of `PhpFileFinder`, using recursive iteration to locate all `.php` files in a directory and its subdirectories.

#### NamespaceToDirectoryResolverPsr4

* Implements `NamespaceToDirectoryResolver` using PSR-4 mapping rules.
* Handles the translation from namespace to directory using registered prefixes and project structure.

#### FileToFqcnResolverPsr4

* Implements `FileToFqcnResolver` as per PSR-4 rules, converting file paths to FQCNs via string manipulation and path normalization.

---

## Practical Usage Examples

### Example 1: Discover All Classes Under a Namespace

```php
use App\Shared\Discovery\Application\Service\DiscoveryScanner;
use App\Shared\Discovery\Infrastructure\NamespaceToDirectoryResolverPsr4;
use App\Shared\Discovery\Infrastructure\PhpFileFinderRecursive;
use App\Shared\Discovery\Infrastructure\FileToFqcnResolverPsr4;

// Define your PSR-4 prefix mapping for the namespace root
$namespaceMap = [
    'App\\Modules\\Invoices' => '/path/to/project/src/Modules/Invoices',
];
$namespaceRoot = 'App\\Modules\\Invoices';

// Create infrastructure implementations for contracts
$namespaceResolver = new NamespaceToDirectoryResolverPsr4($namespaceMap);
$fileFinder = new PhpFileFinderRecursive();
$fileToFqcn = new FileToFqcnResolverPsr4($namespaceRoot, $namespaceMap);

// Construct the DiscoveryScanner
$scanner = new DiscoveryScanner($namespaceResolver, $fileFinder, $fileToFqcn);

// Scan the namespace and retrieve a collection of FQCNs
$fqcnCollection = $scanner->scan($namespaceRoot);

foreach ($fqcnCollection as $fqcn) {
    // $fqcn is an instance of FullyQualifiedClassName
    echo $fqcn->toString() . PHP_EOL;
}
```

### Example 2: Discover Extension Modules

```php
use App\Shared\Discovery\Application\Extension\ExtensionDiscoveryService;

// Suppose $scanner is already configured as in the previous example
$extensionService = new ExtensionDiscoveryService($scanner /*, ...additional dependencies if required */);

$extensions = $extensionService->discoverExtensions();

foreach ($extensions as $extensionFqcn) {
    // $extensionFqcn is an instance of FullyQualifiedClassName
    // Perform registration or instantiation logic here
}
```

### Example 3: Custom File Finder Implementation

```php
use App\Shared\Discovery\Domain\Contracts\PhpFileFinder;
use App\Shared\Discovery\Domain\ValueObjects\DirectoryPath;

class MyCustomFileFinder implements PhpFileFinder {
    public function findFiles(DirectoryPath $directory): array {
        // Your custom logic (e.g., only return files matching certain patterns)
        // ...
    }
}

// Bind your custom finder in the DI container or inject it into DiscoveryScanner
```

---

## API Reference

### 1. Application Layer

#### DiscoveryScanner

* **\_\_construct(NamespaceToDirectoryResolver \$namespaceResolver, PhpFileFinder \$fileFinder, FileToFqcnResolver \$fileToFqcn)**

  * Binds the core contracts for namespace resolution, file scanning, and FQCN resolution.
* **scan(string \$namespace): FqcnCollection**

  * Receives a namespace root, resolves it to a directory, scans recursively for PHP files, and returns a collection of FQCNs.
* **scanDirectory(DirectoryPath \$directory): FqcnCollection**

  * Directly scans a directory for PHP files and returns a collection of FQCNs.

#### ExtensionDiscoveryService

* **\_\_construct(DiscoveryScanner \$scanner, ...additional dependencies)**

  * Binds the DiscoveryScanner and optionally other dependencies.
* **discoverExtensions(): FqcnCollection**

  * Scans the configured directories/namespaces for extension classes and returns a collection of FQCNs for each discovered extension module.

### 2. Domain Layer

#### PhpFileFinder

* **findFiles(DirectoryPath \$directory): array**

  * Finds and returns an array of PHP file paths within the given directory.

#### NamespaceToDirectoryResolver

* **resolve(NamespaceName \$namespace): DirectoryPath**

  * Maps a namespace string to its corresponding directory path.

#### FileToFqcnResolver

* **resolve(DirectoryPath \$root, string \$file): FullyQualifiedClassName**

  * Resolves a file path (relative to a root) to a fully qualified class name (FQCN).

#### FqcnCollection

* **\_\_construct(FullyQualifiedClassName ...\$fqcns)**

  * Initializes a new, immutable collection of FQCNs.
* **add(FullyQualifiedClassName \$fqcn): self**

  * Returns a new collection with the given FQCN added.
* **filter(callable \$predicate): self**

  * Returns a new collection filtered by the predicate.
* **toArray(): array**

  * Returns the collection as a plain array of FullyQualifiedClassName objects.
* **getIterator(): Traversable**

  * Supports foreach iteration (implements IteratorAggregate).

#### Value Objects

* **FullyQualifiedClassName**

  * **\_\_construct(string \$fqcn)**: Validates and encapsulates an FQCN string.
  * **toString(): string**: Returns the FQCN as a string.
* **NamespaceName**

  * **\_\_construct(string \$namespace)**: Validates and encapsulates a namespace string.
  * **toString(): string**: Returns the namespace as a string.
* **InterfaceName**

  * **\_\_construct(string \$interfaceName)**: Validates and encapsulates an interface name string.
  * **toString(): string**: Returns the interface name as a string.
* **DirectoryPath**

  * **\_\_construct(string \$path)**: Validates and encapsulates a directory path string.
  * **toString(): string**: Returns the directory path as a string.

### 3. Infrastructure Layer

#### PhpFileFinderRecursive

* **findFiles(DirectoryPath \$directory): array**

  * Returns an array of all `.php` files in the directory and its subdirectories.

#### NamespaceToDirectoryResolverPsr4

* **resolve(NamespaceName \$namespace): DirectoryPath**

  * Maps a namespace to its root directory using registered PSR-4 prefixes.

#### FileToFqcnResolverPsr4

* **resolve(DirectoryPath \$root, string \$file): FullyQualifiedClassName**

  * Converts a file path (relative to root) to its FQCN via PSR-4 mapping logic.

---

## Extension and Customization

The Discovery module is architected for maximum extensibility and adaptability, allowing real projects to customize or replace its behavior without modifying core logic. All extension points are defined by domain contracts and are fully decoupled from application or infrastructure details.

### Implementing Custom Contracts

You can extend the Discovery module by implementing any of the following interfaces:

* **PhpFileFinder**
* **NamespaceToDirectoryResolver**
* **FileToFqcnResolver**

For example, to support a legacy autoloader, special directory conventions, or project-specific filtering rules, create your own implementation:

```php
use App\Shared\Discovery\Domain\Contracts\PhpFileFinder;
use App\Shared\Discovery\Domain\ValueObjects\DirectoryPath;

class MyCustomFileFinder implements PhpFileFinder {
    public function findFiles(DirectoryPath $directory): array {
        // Custom logic goes here
    }
}
```

### Registering Implementations

To activate your custom implementation, bind it to the corresponding contract in your application's DI Container or service provider configuration:

```php
$container->bind(PhpFileFinder::class, MyCustomFileFinder::class);
```

Repeat for other contracts as necessary (e.g., provide your own NamespaceToDirectoryResolver for a different namespace mapping logic).

### Advanced Use Cases

* **Composite or Conditional Resolvers:** Combine multiple resolvers or finders to support fallback logic or handle multiple codebase layouts.
* **Testing:** Inject mock implementations for unit testing or integration testing, fully decoupling tests from filesystem or environment dependencies.

### Key Principle

All extension or customization is done **solely via the contract interfaces**. The application layer and domain objects remain untouched, maximizing maintainability and upgradeability.

---

## Best Practices

These recommendations are based on the real structure and logic of the Discovery module and are intended to help maintain a robust, maintainable, and scalable discovery layer in your application.

1. **Always Use Value Objects**

   * Use `FullyQualifiedClassName`, `NamespaceName`, `DirectoryPath`, and `InterfaceName` value objects when passing data between layers.
   * Do not pass raw strings; value objects enforce validation and prevent common errors with naming, case, or path delimiters.
2. **Prefer Contracts Over Concrete Implementations**

   * Always reference and type-hint domain contracts (`PhpFileFinder`, `NamespaceToDirectoryResolver`, `FileToFqcnResolver`) in your services, not concrete classes.
   * This allows seamless replacement of infrastructure strategies and promotes testability.
3. **Leverage Collections for Results**

   * Use `FqcnCollection` to aggregate discovered classes. Collections provide filtering, iteration, and extension safety, reducing the risk of type errors or duplicate processing.
4. **Register Extensions and Plugins via Discovery**

   * Use the discovery services to automatically register extensions or modules, rather than hardcoding class names or relying on static configuration.
5. **For Testing, Inject Mocks**

   * In unit or integration tests, inject mock implementations of the contracts to simulate file systems, namespace mappings, or FQCN resolution, decoupling your tests from the real environment.
6. **Keep Infrastructure Layer Swappable**

   * Only the infrastructure layer should perform I/O or contain environment-specific logic. Keep application and domain layers pure and agnostic.
7. **Document Custom Implementations**

   * When extending or customizing contracts, document their behavior, edge cases, and intended use for future maintainers.
8. **Stay PSR-4 Compliant Unless Necessary**

   * Only implement non-PSR-4 strategies for legacy or edge cases; PSR-4 compliance guarantees interoperability with standard PHP tools and loaders.
9. **Synchronize Documentation with Code**

   * Maintain README and API documentation synchronized with code changes, especially when adding new contracts or value objects.
10. **Use Dependency Injection for All Services**

* Bind all discovery services and contract implementations in the DI Container, never instantiate them directly in application code.

---

## Known Limitations

The following limitations are inherent to the current implementation of the Discovery module, as observed in the codebase:

1. **PSR-4 Only (Out-of-the-Box)**

   * The provided infrastructure implementations (`NamespaceToDirectoryResolverPsr4`, `FileToFqcnResolverPsr4`) are strictly PSR-4 compliant.
   * Composer classmaps, classmap-dev, and non-PSR-4 autoloading schemes are not supported unless you provide a custom resolver.
2. **No Autoload/Class Existence Validation**

   * The discovery process maps files to FQCNs based on file/directory naming, but does not reflectively load or validate that the class/interface actually exists or is autoloadable.
   * Additional checks (Reflection, class\_exists) must be performed by the consumer if required.
3. **Only PHP Files Are Supported**

   * The discovery process only considers `.php` files for scanning and resolution. Files in other languages, or PHP files with atypical extensions, are ignored.
4. **Symlink and Filesystem Edge Cases**

   * Complex filesystem structures, such as symbolic links, case-insensitive filesystems, or non-standard separators, may not be handled as expected and may require custom implementations.
5. **Statelessness and No Caching**

   * All discovery operations are performed in-memory, without persistent or shared cache. Scans may have a performance impact on very large codebases if repeated frequently.
6. **No Dependency or Hierarchy Analysis**

   * Discovery returns lists of FQCNs but does not analyze class dependencies, inheritance, or interface implementation. Further logic is needed to filter or reflect on discovered types.
7. **Strict Dependency on DI Container**

   * For advanced extensibility or replacement of infrastructure, a properly configured DI Container is required; otherwise, only default implementations are used.

These limitations are subject to change as the module evolves. Custom implementations of domain contracts can overcome most limitations when required by the project context.

---

## Glossary

Definitions for core terms and concepts in the Discovery module domain:

* **FQCN (Fully Qualified Class Name):** The complete namespace and class name for a PHP class or interface (e.g., `App\Module\Service\MyClass`). Encapsulated by the `FullyQualifiedClassName` value object.
* **PSR-4:** PHP Standard Recommendation 4, defining autoloading standards for mapping namespaces to directory structures. Used by the default Discovery infrastructure implementations.
* **Namespace Root:** The top-level namespace under which a module, plugin, or package organizes its classes (e.g., `App\Shared` or `App\Modules`).
* **Extension:** A module, plugin, or class intended for dynamic registration or discovery at runtime. Located by the `ExtensionDiscoveryService`.
* **Value Object:** An immutable object representing a single, well-defined concept (e.g., FQCN, directory path) that enforces validation and type safety.
* **Collection:** An object that aggregates multiple value objects (e.g., `FqcnCollection`), supporting iteration, filtering, and safe operations.
* **Contract:** An interface defining expected behaviors or responsibilities (e.g., `PhpFileFinder`). Used to decouple implementation from usage.
* **Infrastructure:** The layer providing concrete, environment-specific implementations of domain contracts, such as file system or PSR-4 resolvers.
* **DI Container (Dependency Injection Container):** The application's central mechanism for wiring up service dependencies, contract bindings, and configuration.
* **Statelessness:** The principle that Discovery services maintain no persistent state; each operation is independent and free of side effects.

---

