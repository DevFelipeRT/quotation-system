# Quotation System – Architectural Overview

This document provides a detailed architectural overview of the Quotation System, a modular PHP application implementing Clean Architecture, Domain-Driven Design (DDD), Event-Driven principles, and PSR standards.

## 1. High-Level Architecture

* **Modular and layered structure**
* **Clean separation** of domain logic, use cases, infrastructure, and delivery
* **Event-driven flow** for decoupled interaction across modules
* **No Composer**: modules and autoloading are handled manually

## 2. Folder Structure (Root Level)

```
quotation-system/
├── src/
├── config/
├── schema/
├── public/
├── bootstrap/
├── deprecated/
├── autoload.php
├── .env
```

## 3. Internal Layers (`src/`)

Each top-level layer under `src/` contains multiple modules. Modules are vertically sliced and independently structured.

### 3.1 Layers

* **Adapters/**: Entry points (event listeners, CLI, HTTP bindings)
* **Application/**: Use cases and orchestration
* **Domains/**: Entities, value objects, aggregates, events
* **Infrastructure/**: Database, network, and technical implementations
* **Presentation/**: Controllers and UI-facing logic
* **Shared/**: Contracts, interfaces, utility abstractions

### 3.2 Module Structure

Each module follows its own internal Clean Architecture layers. Not all modules are required to have all layers; the structure depends on the module's purpose. For example, an infrastructure module may only define `Infrastructure/` and `Domain/`, while a business domain module would include `Application/` and `Domain/`.

```
ModuleName/
├── Application/
│   ├── Services, UseCases, Coordination Logic
├── Domain/
│   ├── Entities, Events, Contracts
├── Infrastructure/
│   ├── Technical Implementations, Drivers, Gateways
├── Adapters/
│   ├── EventListeners, CLI, Protocol Interfaces
```

> Resolvers may appear in any layer, provided they are used for responsibilities appropriate to that layer. For example, infrastructure resolvers may configure database drivers, while application resolvers may orchestrate flows.

## 4. Event Architecture

### 4.1 Contracts

* `EventDispatcherInterface`: Used by modules to emit events.
* `EventListenerInterface`: Implemented by listeners.
* `EventListenerLocatorInterface`: Used to resolve listeners dynamically.

### 4.2 Roles

* **Producers (emitters)**: All modules may emit events using `EventDispatcherInterface`, defined in `Shared/Event/Contracts`.
* **Listeners**: Exclusively implemented in the `EventListening` module.
* **Binding**: Bindings between events and listeners are centralized in the `EventListening` module.

### 4.3 Binding Registration (via Kernel)

* Only the `EventListening` module provides listener bindings.
* These bindings are declared in a class implementing `EventBindingProviderInterface` and registered through `src/Kernel/Adapters/Providers`.
* During boot, the central `KernelManager` loads these bindings and wires them into the runtime `EventListenerMap`.
* Other modules are unaware of the bindings and do not reference listeners directly.

## 5. Kernel Structure

The `KernelManager` initializes and wires all functional modules:

* Loads `LoggingKernel`, `DatabaseConnectionKernel`, `UseCaseKernel`, etc.
* Ensures correct boot sequence
* Supports pluggable module design
* Integrates the event listener map and dispatcher

## 6. Principles

* **Clean Architecture**: Clear inward dependencies, inversion of control
* **DDD**: Business logic lives in `Domain`, orchestrated in `Application`
* **Object Calisthenics**: One level of indentation, no primitives, small classes
* **PSR compliance**: Naming, autoloading, HTTP layer style

## 7. Conventions for New Modules

A new module must:

* Live inside the appropriate top-level layer (e.g., `Adapters`, `Application`)
* Have at least `Domain` and `Application` layers
* Use the dispatcher, not implement listener resolution directly
* Must not define bindings for listeners
* Listeners must be declared only in `EventListening` and registered via `Kernel/Adapters/Providers`

### 7.1 Example Module Structure

```
src/Application/FooModule/
├── Application/
│   └── CreateFooUseCase.php
├── Domain/
│   ├── Entity/
│   │   └── Foo.php
│   ├── Events/
│   │   └── FooCreatedEvent.php
│   └── Contracts/
│       └── FooRepositoryInterface.php
├── Infrastructure/
│   └── Repository/
│       └── PdoFooRepository.php
```

## 8. Deprecated Code

Located in `/deprecated/`, excluded from autoload and execution. Used only for reference during refactorings.

## 9. Requirements

* PHP >= 8.1
* MySQL or MariaDB

---

This document serves as the canonical architectural guide for this project.
