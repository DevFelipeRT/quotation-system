# Quotation System

A professional, event-driven, modular quotation management system developed in PHP, structured with Clean Architecture, Domain-Driven Design (DDD), SOLID principles, and Object Calisthenics.

## Features

* Modular boundaries aligned with Domain-Driven Design (`Domains/`, `Modules/`)
* Clean separation between core logic and technical details (`Application/`, `Infrastructure/`, `Presentation/`)
* **Event-Driven Architecture** for logging, monitoring, and lifecycle orchestration
* Logger compatible with internal and PSR-based consumers (via adapter pattern)
* PDO-based persistence, abstracted behind driver-independent contracts
* Native autoloading, fully decoupled from Composer
* Kernel-based bootstrapping with dynamic module orchestration
* Designed for testability: business logic isolated from technical implementations

## Development Status

The project is now in the **event architecture stabilization and module integration phase**.

> ✅ The **Database, Logging, and EventListening modules are fully integrated and validated**
> ✅ The **application dispatches domain events**, and listeners respond across isolated modules

### Known limitations

* The project does **not use Composer** and relies on a **custom autoloader**
* Controllers are present but not yet connected to application services
* Some modules are still in partial integration

### What's already in place

* Structured DDD-based modular design with clear technical boundaries
* Fully asynchronous event propagation (e.g., query, connection, and error events)
* Logger contract (`LoggerInterface`) and adapter (`PsrLoggerAdapter`) for decoupled usage
* Dynamic kernel dispatch of modules and listener mapping
* Consistent PSR-style coding standards, without external dependency on `psr/*`
* Event-driven interactions between modules using **listener resolution**, **runtime maps**, and **module-specific contracts**
* A centralized `KernelManager` loads and registers all available modules dynamically based on their declared capabilities
* Modules expose their event listeners via implementations of `EventBindingProviderInterface`, making the system fully pluggable and discoverable

### Next steps

* Wire HTTP controllers to application use cases via router abstraction
* Build a complete request→use case→response flow for one business domain
* Expand event bindings to include application-level and cross-cutting concerns
* Refactor or eliminate legacy modules stored under `deprecated/`

## Deprecated Code

This project includes a `deprecated/` directory for **temporarily retained legacy or transitional code**:

* Excluded from runtime and from the autoload system
* Used as a reference during refactoring and modularization
* Marked for complete removal upon stabilization

No new code should depend on content under `deprecated/`.

## Architectural Organization Rules

### Internal Organization (src/)

* Each top-level layer (`Adapters`, `Application`, `Domains`, `Infrastructure`, `Presentation`, `Shared`) is composed of **independent functional modules**.
* Each module is fully self-contained, encapsulating its own substructure (e.g., `Application`, `Domain`, `Infrastructure`).
* Internal module structure must respect Clean Architecture:

  * `Domain/` contains only domain entities, value objects, aggregates, events, and domain logic.
  * `Application/` holds use cases, services, listener maps, and ports.
  * `Infrastructure/` implements interfaces defined in upper layers (e.g., repositories, drivers).
  * `Adapters/` provides entry points such as event listeners, CLI handlers, or API adapters.
  * `Presentation/` includes controllers and protocol-bound interfaces.
* Events are immutable data objects declared in each module's `Domain/.../Events/` and resolved via listener maps in `Application/Resolver`.
* Listeners implement the shared `EventListenerInterface` and are resolved by `EventListenerResolver` using an immutable runtime map.
* Modules register their bindings by implementing `EventBindingProviderInterface`, enabling discovery by the central `KernelManager`.
* `KernelManager` orchestrates startup, scanning for kernels and loading module capabilities dynamically.
* **No circular dependencies are allowed** between layers or modules.
* **All communication between modules occurs via well-defined contracts**, located in `Shared/` or injected explicitly.
* **Autoloading is governed by a custom PSR-style resolver**, mapping namespaces to paths per module.
* Every new module must:

  * Contain at least a `Domain` and `Application` layer.
  * Be bootstrap-ready and discoverable by the kernel.
  * Declare its event listeners and handlers clearly.
* Reuse of logic must happen through interfaces, never through direct internal references.
* Legacy code must be isolated under `deprecated/` and excluded from all runtime logic.

### External Organization (Project Root)

* `/public/`: Exposes the HTTP entry point (`index.php`) and public assets. No domain or application logic is permitted here.
* `/config/`: Contains environment-specific settings and kernel initialization parameters.
* `/schema/`: Stores database schema definitions and version-controlled migration files.
* `/bootstrap/`: Used during project startup for preloading environment settings.
* `/autoload.php`: Central PSR-style loader mapping namespace roots to folders under `src/`.
* `/.env`: Defines environment-level configuration. Must never contain secrets in production.
* `/deprecated/`: Retains transitional or legacy files for migration tracking; excluded from execution.

## Requirements

* PHP >= 8.1
* MySQL or MariaDB
