# Persistence (Database) Module Documentation

---

## Overview

The **Persistence** module is responsible for all data persistence and retrieval operations within the system. Architected strictly under Clean Architecture and Domain-Driven Design (DDD) principles, it offers a robust, extensible, and observable framework for relational database operations (via PDO), including credential safety, exception handling, and event-based observability.

**Core objectives:**

* Provide multi-driver SQL support (MySQL, PostgreSQL, SQLite; extensible for others)
* Standardize connection and execution flows through contracts and service layers
* Enable complete observability by raising domain events for all relevant lifecycle steps
* Ensure credential safety and integrity using Value Objects and secure memory handling
* Centralize all error and exception management via explicit domain and infrastructure exceptions

This module is foundational to the reliability, maintainability, and traceability of the system’s data access layer.

---

## Structure

The module is organized into clearly defined layers and components, in alignment with DDD and Clean Architecture.

### 1. Domain Layer

**a) Contracts (`Domain/Contract/`)**

* `DatabaseConnectionInterface`:
  Declares essential methods for connecting, disconnecting, and validating database sessions.
  Supports transactional demarcation (begin, commit, rollback) and exposes native PDO/driver access when needed.

* `DatabaseExecutionInterface`:
  Defines the contract for executing SQL queries (prepared and raw), as well as transaction controls.

* `DatabaseCredentialsInterface`:
  Specifies interface for credential value objects, including methods for DSN construction and credential access.

**b) Value Objects (`Domain/ValueObject/`)**

* `DatabaseSecret`:
  Immutably encapsulates secrets (passwords) with secure access semantics. Prevents accidental logging/exposure.

* `MySqlCredentials`, `PostgreSqlCredentials`, `SqliteCredentials`:
  Specialized VOs for encapsulating driver-specific connection attributes, all implementing `DatabaseCredentialsInterface`.

**c) Domain Events (`Domain/Event/`)**

* `ConnectionSucceededEvent`
* `ConnectionFailedEvent`
* `RequestExecutedEvent`
* `RequestFailedEvent`

All events provide contextual details (driver, schema, DSN, credentials used, SQL, parameters, errors), supporting full observability and diagnostic capabilities.

**d) Support (`Domain/Support/`)**

* `CredentialsSecurity`:
  Utilities for secure handling of secrets, including masking and safe memory access.
  Useful for audit trails and defensive programming, preventing accidental leakage.

---

### 2. Infrastructure Layer

**a) Services**

* `PdoConnectionService`:
  Implements `DatabaseConnectionInterface`. Orchestrates DSN creation, manages PDO instantiation, and configures driver-specific options.
  Handles exceptions explicitly, raising domain events as side-effects.

* `PdoExecutionService`:
  Implements `DatabaseExecutionInterface`.
  Receives queries and parameters, always executes using prepared statements, and triggers events for success/failure.

* `PersistenceKernel`:
  Facade/orchestrator responsible for integrating all services and providing a unified interface for higher application layers.
  Handles schema-aware connection routing and execution delegation.

**b) Exceptions (`Infrastructure/Exceptions/`)**

* `DatabaseConnectionException`
* `MissingDriverConfigurationException`
* `UnsupportedDriverException`
* `RequestExecutionException`

All exceptions are typed, descriptive, and capture full context for downstream error handling and incident analysis.
Stack traces and contextual data (DSN, SQL, parameters, error code) are preserved.

**c) Support (`Infrastructure/Support/`)**

* `DriverValidator`:
  Validates presence, correctness, and completeness of driver configuration before any connection attempt.
  Guards against misconfiguration, unsupported drivers, or missing attributes, failing fast and observably.

---

## Core Execution Flow

1. **Initialization**

   * Application or test code provides driver configuration (driver, host, port, database, credentials) in a strongly-typed array or config object.
   * Credentials are instantiated as immutable Value Objects, preventing mutation and accidental exposure.
   * `DriverValidator` checks configuration completeness and driver support.

2. **Connection**

   * A call to `PersistenceKernel` requests a connection (optionally passing schema).
   * `PdoConnectionService` selects the correct driver and builds the DSN.
   * Upon connection attempt, the relevant event (`ConnectionSucceededEvent` or `ConnectionFailedEvent`) is emitted.

3. **Execution**

   * Application calls `execute` or `executeRaw` on the kernel.
   * `PdoExecutionService` receives the query and bindings.
   * All executions are performed via prepared statements (PDO).
   * Success or failure raises `RequestExecutedEvent` or `RequestFailedEvent`, both carrying detailed context.

4. **Transactions**

   * Transactional demarcation (`beginTransaction`, `commit`, `rollback`) is provided both in the connection interface and kernel facade.

5. **Error Handling**

   * All exceptions in connection or execution are typed and context-rich.
   * Application/service layer may catch specific exceptions for granular logic.

6. **Observability**

   * All domain events are dispatched through the event system.
   * Listeners can be registered to log, audit, or trace any aspect of database activity.

---

## Events Architecture

**Emitted Events**

* `ConnectionSucceededEvent`
* `ConnectionFailedEvent`
* `RequestExecutedEvent`
* `RequestFailedEvent`

Events are fired on both connection lifecycle and each query execution. Each carries:

* Driver and DSN
* Connection parameters (with secret masking)
* Schema (if applicable)
* SQL and all bindings (for executed queries)
* Detailed error message/stack (for failures)
* Timestamp and unique context id (when applicable)

**Event Listener Registration**

Listeners may be registered using the event dispatcher system (integration with `Shared\Event\Contracts\EventDispatcherInterface`).
Events are designed for downstream extensibility: metrics, tracing, logging, security auditing, etc.

Example test listeners and utilities are provided for integration testing, ensuring observability is always testable.

---

## Security Model

* **Credential Encapsulation:**
  All passwords/secrets are held only in `DatabaseSecret` VOs, never as raw strings. Utilities guarantee secrets are not leaked through logs or stack traces.

* **Prepared Statements Everywhere:**
  Every execution is through parameterized/prepared statements, eliminating classic SQL injection vectors.

* **Early and Defensive Validation:**
  Driver and configuration validation is enforced on startup. No connection is ever attempted with an incomplete or unsupported configuration.

* **Exception Contextualization:**
  Sensitive data is masked in exceptions; only non-sensitive context is exposed downstream, supporting secure observability.

* **Observability Without Exposure:**
  Emitted events pass only safe details, and masking utilities are provided to ensure compliance with audit and privacy requirements.

---

## Extending the Module

### 1. Adding a New Driver

* Implement a new Value Object for driver credentials, extending `DatabaseCredentialsInterface`.
* Extend `PdoConnectionService` to support DSN logic for the new driver.
* Register the driver and its options in the configuration array/object.
* Implement corresponding exception handling and (if necessary) event extensions.

### 2. Custom Execution Patterns

* Extend `PdoExecutionService` or implement your own `DatabaseExecutionInterface`.
* Register new events for custom execution phases (e.g., long-running query timeout, connection pool events).

### 3. Advanced Observability

* Implement listeners that log to external APMs, metrics or tracing systems.
* Add support for distributed tracing context propagation if required.

### 4. Credential Management

* Integrate with secret managers or rotate secrets dynamically via adapters.
* Extend `CredentialsSecurity` for external source integration or rotation events.

### 5. Schema and Multi-Tenant Support

* Expand Value Objects and kernel logic to handle dynamic schema routing.
* Support read/write split, replica selection, or per-tenant credentials.

---

## Usage Examples

### Connecting and Executing a Query

```php
use Persistence\Infrastructure\PersistenceKernel;

$kernel = new PersistenceKernel($config);
$connection = $kernel->getConnection(); // Returns DatabaseConnectionInterface

$result = $kernel->execute(
    'SELECT * FROM users WHERE email = :email',
    [':email' => 'foo@bar.com']
);
// $result is array|object depending on driver/fetch style
```

### Listening to Events

```php
use Persistence\Domain\Event\RequestExecutedEvent;

class LogQueryListener
{
    public function __invoke(RequestExecutedEvent $event)
    {
        // Log or process $event->getSql(), $event->getBindings(), etc.
    }
}

// Register with your event dispatcher system
```

### Transaction Management

```php
$connection->beginTransaction();
try {
    $kernel->execute('UPDATE ...');
    $connection->commit();
} catch (\Exception $e) {
    $connection->rollback();
    // Handle error
}
```

---

## Available Methods (Kernel/Service Layer)

### PersistenceKernel

* `getConnection(?string $schema = null): DatabaseConnectionInterface`
* `execute(string $sql, array $bindings = [], ?string $schema = null): mixed`
* `executeRaw(string $sql, ?string $schema = null): void`
* `beginTransaction(?string $schema = null): void`
* `commit(?string $schema = null): void`
* `rollback(?string $schema = null): void`

### PdoConnectionService

* `connect(): void`
* `disconnect(): void`
* `isConnected(): bool`
* `getNativeConnection(): \PDO`
* `beginTransaction(): void`
* `commit(): void`
* `rollback(): void`

### PdoExecutionService

* `execute(string $sql, array $bindings = []): mixed`
* `executeRaw(string $sql): void`

---

## Strengths and Future Improvements

**Strengths:**

* Highly modular and extensible
* Defensive by default: secure handling of secrets, prepared statements, strict validation
* Observable: events for every critical step, facilitating debugging and auditing
* Test-friendly: contracts and event system allow extensive unit and integration tests
* Architecture encourages separation of concerns and future expansion

**Suggested Improvements:**

* Pooling/connection multiplexing for high-concurrency scenarios
* Enhanced timeout controls and observability for slow queries
* Direct integration points for secret managers (AWS Secrets Manager, HashiCorp Vault, etc.)
* More granular event types (e.g., pre-execution, post-commit, connection retry)
* Potential refatoração para facilitar suporte a bancos não relacionais, se necessário futuramente
* Documentação de exemplos reais de listeners e integração com sistemas de monitoramento

---

## Additional Notes

* All internal logic is tested via `PersistenceTest` and corresponding integration helpers.
* Example listener implementations are provided for custom observability.
* The module was designed for extensibility, testability, and operational robustness as first principles.

---

*This document provides a comprehensive, in-depth reference for the Persistence (Database) module, aligned with the highest standards of maintainability, observability, and security.
It must be used as the canonical reference for any future extension or integration within the system.*
