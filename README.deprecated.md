# Quotation System

A professional, event-driven, modular quotation management system developed in PHP, structured with Clean Architecture, Domain-Driven Design (DDD), SOLID principles, and Object Calisthenics.

## Features

* Modular boundaries aligned with Domain-Driven Design
* Clean separation between core logic and technical details (`Application/`, `Infrastructure/`, `Presentation/`)
* Logger compatible with internal and PSR-based consumers (via adapter pattern)
* PDO-based persistence, abstracted behind driver-independent contracts
* Native autoloading, fully decoupled from Composer
* Kernel-based bootstrapping with dynamic module orchestration
* Designed for testability: business logic isolated from technical implementations

### Known limitations

* The project does **not use Composer** and relies on a **custom autoloader**
* Some modules are still in partial integration

## Deprecated Code

This project includes a `deprecated/` directory for **temporarily retained legacy or transitional code**:

* Excluded from runtime and from the autoload system
* Used as a reference during refactoring and modularization
* Marked for complete removal upon stabilization

No new code should depend on content under `deprecated/`.

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
