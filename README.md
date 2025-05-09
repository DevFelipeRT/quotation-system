# Quotation System

A professional modular quotation management system developed in PHP, designed with Clean Architecture, SOLID principles, Object Calisthenics, and rigorous separation of concerns.

## Features

- Domain-Driven Design with modular boundaries (`Domains/`)
- Dedicated technical subsystems (`Infrastructure/`) for Logging, Rendering, Messaging, etc.
- HTTP controllers organized under `Presentation/Http/`
- PDO-based persistence with encapsulated schema and repository abstraction
- Fully extensible event-based logging and exception handling
- Testable structure by default: domain logic isolated from technical dependencies

## Development Status

The project is currently in the **modularization and architectural stabilization phase**.

> ✅ The **Database Connection module is functional and validated**, and all namespaces have been unified across the codebase.

### Known limitations

- The project **does not use Composer** and relies on a **custom native autoloader**
- Controllers are not yet wired to application use cases
- Some functional modules are being incrementally connected and integrated

### What's already in place

- Modular architecture based on Domain-Driven Design and Clean Architecture
- Strict internal layering within each module (`Domain`, `Application`, `Infrastructure`, `Presentation`)
- Technical subsystems (Database, Logging, Rendering, Session) are isolated and extensible
- A consistent naming strategy and separation of concerns are being enforced
- Namespace structure aligned with directory organization throughout the codebase

### Next steps

- Connect HTTP controllers to their corresponding use cases
- Establish routing and request flow for at least one business context
- Progressively integrate and test additional modules (e.g., Messaging, Rendering)

## Deprecated Code

This project includes a `deprecated/` directory used for **temporary storage of legacy or transitional code** during the ongoing refactoring and modularization process.

- Code in this folder is **not included in the application runtime** and is **excluded from the native autoloader**
- It exists solely to **preserve references** while equivalent functionality is being restructured elsewhere
- All content within `deprecated/` is intended to be **progressively removed or migrated**
- No new modules or logic should depend on any of the code located in this directory

Once the architecture stabilization is complete, this directory will be fully eliminated.

## Requirements

- PHP >= 8.1
- MySQL or MariaDB
