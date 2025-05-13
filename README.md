# Quotation System

A professional, event-driven, modular quotation management system developed in PHP, structured with Clean Architecture, Domain-Driven Design (DDD), SOLID principles, and Object Calisthenics.

## Features

- Modular boundaries aligned with Domain-Driven Design (`Domains/`, `Modules/`)
- Clean separation between core logic and technical details (`Application/`, `Infrastructure/`, `Presentation/`)
- **Event-Driven Architecture** for logging, monitoring, and lifecycle orchestration
- Logger compatible with internal and PSR-based consumers (via adapter pattern)
- PDO-based persistence, abstracted behind driver-independent contracts
- Native autoloading, fully decoupled from Composer
- Kernel-based bootstrapping with dynamic module orchestration
- Designed for testability: business logic isolated from technical implementations

## Development Status

The project is now in the **event architecture stabilization and module integration phase**.

> ✅ The **Database, Logging, and EventListening modules are fully integrated and validated**  
> ✅ The **application dispatches domain events**, and listeners respond across isolated modules

### Known limitations

- The project does **not use Composer** and relies on a **custom autoloader**
- Controllers are present but not yet connected to application services
- Some modules are still in partial integration

### What's already in place

- Structured DDD-based modular design with clear technical boundaries
- Fully asynchronous event propagation (e.g., query, connection, and error events)
- Logger contract (`LoggerInterface`) and adapter (`PsrLoggerAdapter`) for decoupled usage
- Dynamic kernel dispatch of modules and listener mapping
- Consistent PSR-style coding standards, without external dependency on `psr/*`

### Next steps

- Wire HTTP controllers to application use cases via router abstraction
- Build a complete request→use case→response flow for one business domain
- Expand event bindings to include application-level and cross-cutting concerns
- Refactor or eliminate legacy modules stored under `deprecated/`

## Deprecated Code

This project includes a `deprecated/` directory for **temporarily retained legacy or transitional code**:

- Excluded from runtime and from the autoload system
- Used as a reference during refactoring and modularization
- Marked for complete removal upon stabilization

No new code should depend on content under `deprecated/`.

## Requirements

- PHP >= 8.1
- MySQL or MariaDB
