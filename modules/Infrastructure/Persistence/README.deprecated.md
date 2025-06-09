# Database Module Documentation

---

## Overview

The Database module in the Quotation System is responsible for handling all aspects related to data persistence. It provides a structured, extensible, and decoupled mechanism for configuring, connecting, and executing operations on relational databases through PDO.

This module is architecturally designed following Clean Architecture and Domain-Driven Design (DDD) principles, splitting its responsibilities across configuration, domain abstractions, application factories, infrastructure implementations, and kernel service providers.

Its main goals are:

* Provide support for multiple SQL drivers (e.g., MySQL, PostgreSQL, SQLite)
* Standardize the way database connections and executions are handled
* Raise events for observability and traceability
* Encapsulate low-level details using interfaces and resolvers
* Centralize exception handling for database-related errors

---

## Structure

The module is composed of the following key layers:

### 1. Configuration (`config/Database`)

This layer defines the foundational settings for database connectivity and schema management.

* **`DatabaseConfig.php`**:

  * Specifies the `default_driver` (e.g., `mysql`, `pgsql`).
  * Contains a `drivers` array with credentials, host, port, DSN, and schema mapping per driver.
  * Optionally enables or disables key events (e.g., `query_executed`, `connection_failed`).

* **`DatabaseSchemaConfig.php`**:

  * Provides a configuration map for named schemas.
  * Supports schema-specific `prefix`, `connection`, and paths for migrations and seeds.
  * Enables multi-tenant or multi-schema setups.

* **`SupportedDrivers.php`**:

  * Lists supported SQL drivers.
  * Provides a single point of validation to verify driver availability.

This configuration layer promotes extensibility, enabling centralized control over connection logic and schema deployment strategies.

### 2. Domain (`src/Infrastructure/Database/Domain`)

This layer defines the core contracts and domain events governing database behavior. It ensures abstraction, decoupling, and a solid foundation for observability.

* **Connection Contracts**:

  * `DatabaseConnectionInterface`: Defines the core methods required for any connection to a database.
  * `DriverResolverInterface`: Encapsulates logic for resolving the correct driver based on configuration.

* **Execution Contracts**:

  * `DatabaseRequestInterface`: Represents the abstraction for executing SQL queries.
  * `RequestBuilderInterface`: Contract for classes that construct query execution requests.
  * `RequestBuilderResolverInterface`: Responsible for selecting the appropriate request builder per driver or context.

* **Events**:

  * `ConnectionSucceededEvent`, `ConnectionFailedEvent`: Track the lifecycle and success/failure of database connection attempts.
  * `QueryExecutedEvent`, `QueryFailedEvent`: Allow instrumentation of query execution for logging or metrics.

This layer isolates the definition of operations and signals, facilitating extensibility and aligning with the Dependency Inversion Principle.

### 3. Application (`src/Infrastructure/Database/Application`)

This layer bridges the domain abstractions and infrastructure implementations by exposing factories that produce domain-compliant instances based on configuration and context.

* **`DatabaseConnectionFactory.php`**:

  * Acts as a factory to instantiate the appropriate database connection implementation.
  * Uses driver name and configuration to resolve and instantiate a `DatabaseConnectionInterface`.
  * Handles exceptions for unsupported or misconfigured drivers.

* **`RequestFactory.php`**:

  * Responsible for creating database execution requests.
  * Determines the correct `DatabaseRequestInterface` implementation using the configured driver and schema.
  * Delegates to internal resolvers to build and return the correct request builder.

These factories encapsulate creation logic, improve testability, and support the Open/Closed Principle by allowing new drivers or behaviors to be added without modifying existing code.

### 4. Infrastructure (`src/Infrastructure/Database/Infrastructure`)

This layer contains concrete implementations that fulfill domain contracts using PHP's PDO. It bridges abstract logic and executable database operations.

* **Connection Implementations**:

  * `AbstractPdoConnection`: Provides a base class for PDO-based database connections, handling DSN creation, credential injection, and base error handling.
  * `MySqlConnection`, `PostgreSqlConnection`, `SqliteConnection`: Specific subclasses that implement connection logic for each supported driver.
  * `DriverClassMap`: Maps driver names to concrete implementation classes.

    * Acts as an internal service locator that decouples driver resolution from the rest of the application.
    * Used by `DefaultDriverResolver` to instantiate the correct `DatabaseConnectionInterface` based on configuration.
    * New drivers can be added simply by registering them here, without altering core logic.
  * `DefaultDriverResolver`: Chooses the correct connection class using the configured driver name.

* **Execution Implementations**:

  * `PdoDatabaseRequest`: Concrete implementation of query execution using prepared statements with PDO. Ensures safety against SQL injection.
  * `PdoRequestBuilder`: Builds PDO-compatible request objects, abstracting query parameters and bindings.
  * `RequestBuilderResolver`: Dynamically selects the correct request builder based on driver or schema configuration.

* **Validation**:

  * `DriverValidator.php`: Validates driver configurations, checking for required attributes and supported driver values. Prevents misconfiguration before runtime.

* **Exceptions**:

  * `DatabaseConnectionException`, `MissingDriverConfigurationException`, `UnsupportedDriverException`, `QueryExecutionException`: Handle specific failure scenarios in connection and query operations. These promote safe and explicit error management.

This layer fulfills the contracts defined in the Domain, keeping infrastructure-specific code isolated and replaceable.

### 5. Kernel Integration (`src/Kernel/Infrastructure/Database`)

Provides internal service-layer wrappers to unify database access across the system, making the database module easy to use and test from higher-level components.

* **`DatabaseConnectionKernel.php`**:

  * Central access point to create and return fully resolved and validated database connection instances.
  * Uses the factory and resolver logic from lower layers.

* **`DatabaseExecutionKernel.php`**:

  * Orchestrates execution of database requests through resolved request builders and PDO objects.
  * Simplifies access to query execution while ensuring compliance with domain interfaces.

This layer promotes reuse, testability, and consistency by encapsulating the interaction logic and exposing clean service APIs to other parts of the system.

---

## Events Architecture

The Database module emits domain events to support observability and decoupled reactions to critical operations such as connection establishment and query execution.

### Emitted Events

The following events are dispatched by the infrastructure layer:

* `ConnectionSucceededEvent`
* `ConnectionFailedEvent`
* `QueryExecutedEvent`
* `QueryFailedEvent`

These events encapsulate context such as the driver, connection parameters, query, bindings, or error messages.

### Event Dispatching Contracts

The event system adheres to centralized contracts defined in:

* `Shared\Event\Contracts\EventDispatcherInterface`
* `Shared\Event\Contracts\EventListenerLocatorInterface`

Any service handling event dispatching must implement these interfaces to allow consistent and testable behavior.

An example mock implementation is provided in:

* `Shared\Event\Testing\InMemoryEventListenerLocator`

This is intended for unit/integration testing.

### Listener Registration

All listener classes for database-related events are implemented in:

```
src/Adapters/EventListening/Infrastructure/EventListeners
```

---

## Execution Flow & Initialization

This section describes the logical workflow and steps to initialize and use the Database module.

### Initialization

1. **Configure Supported Drivers**:

   * Define them in `config/Database/DatabaseConfig.php`.
   * Ensure each driver includes credentials, host, port, and DSN structure.

2. **Configure Schemas** *(Optional)*:

   * Use `DatabaseSchemaConfig.php` to register schema names, connection references, and paths for migrations/seeds.

3. **Bind the Kernel Services**:

   * Load `DatabaseConnectionKernel` and `DatabaseExecutionKernel` into your container or service provider.
   * These act as high-level gateways to open connections and execute queries.

4. **Validation and Logging**:

   * Ensure `DriverValidator` is called during bootstrapping to catch misconfigurations early.
   * Events such as `QueryExecutedEvent` and `ConnectionFailedEvent` should be bound to listeners for observability.

### Logical Execution Flow

1. A consumer (e.g., a service) calls `DatabaseConnectionKernel` to obtain a connection.
2. The kernel uses the `DatabaseConnectionFactory`, which relies on `DriverResolver` to determine the concrete connection class.
3. Once connected, a `DatabaseExecutionKernel` is used to dispatch a query.
4. This kernel uses the `RequestFactory` and `RequestBuilderResolver` to create the correct `DatabaseRequest` instance.
5. The request is executed via PDO, and events are emitted upon success or failure.
6. All exceptions are domain-specific and can be caught for granular error handling.

This design ensures clarity, extensibility, and maintainability across different database implementations.

---

## Extending the Database Module

This module was designed with extensibility in mind, supporting clean, decoupled integration of new behaviors, drivers, and execution patterns. Below are the key extension points and how to implement them:

### 1. Adding a New Driver

* Create a new connection class (e.g., `SqlServerConnection.php`) extending `AbstractPdoConnection`.
* Register it in `DriverClassMap`.
* Add its identifier (e.g., 'sqlsrv') to `SupportedDrivers.php` in `config/Database` to ensure it passes validation.
* Implement corresponding entries in `DatabaseConfig.php`.
* Optionally, create a custom request builder and resolver.

### 2. Custom Request Builders

* Implement `RequestBuilderInterface` with your custom builder.
* Extend `RequestBuilderResolver` to return it conditionally based on driver/schema/query type.
* Useful for vendor-specific SQL optimizations.

### 3. Advanced Events and Listeners

* Emit new domain events (e.g., `QueryTimedOutEvent`) from the request/connection layer.
* Register listeners under `src/Adapters/EventListening/Infrastructure/EventListeners`.
* Map them via the `EventListenerLocatorInterface` implementation.

### 4. Schema and Tenant Strategies

* Extend `DatabaseSchemaConfig.php` to include routing, partitioning rules, or read replicas.
* Adjust the factories to route schema-based decisions dynamically.

### 5. Validator Extensions

* Create per-driver validation logic (e.g., via a `ValidatorStrategyInterface`).
* Inject new strategies into `DriverValidator` and adapt validations accordingly.

### 6. Observability Integration

* Decorate `PdoDatabaseRequest` to record metrics.
* Extend listeners to emit logs, metrics, or traces to observability platforms.
* Standardize on tags and contexts for better cross-service tracing.

This modular design ensures that changes and additions can be made without modifying core behavior, preserving the integrity of the architecture while supporting evolving requirements.

---

## Usage Examples

Here are examples of how to interact with the Database module in real-world code:

### Executing a Query

```php
use Kernel\Infrastructure\Database\DatabaseExecutionKernel;

$kernel = new DatabaseExecutionKernel();
$result = $kernel->execute('SELECT * FROM quotations WHERE status = :status', [
    ':status' => 'approved'
]);
```

### Listening to a Query Event

```php
use EventListening\Infrastructure\EventListeners\LogQueryExecutedListener;
use Infrastructure\Database\Domain\Execution\Events\QueryExecutedEvent;

$listener = new LogQueryExecutedListener();
$event = new QueryExecutedEvent('SELECT * FROM quotations', ['status' => 'approved']);
$listener->__invoke($event);
```

---

## Available Methods (Kernel Access)

Below is a reference of primary methods exposed via kernel classes that can be used by application and service layers:

### `DatabaseConnectionKernel`

* `getConnection(?string $schema = null): DatabaseConnectionInterface`

  * Resolves and returns a database connection for the given schema (or default).

### `DatabaseExecutionKernel`

* `execute(string $sql, array $bindings = [], ?string $schema = null): ExecutionResult`

  * Executes a prepared SQL query with bindings and returns a structured result.
* `executeRaw(string $sql, ?string $schema = null): void`

  * Executes a raw SQL command without expecting a result (e.g., for migrations).
* `beginTransaction(?string $schema = null): void`

  * Starts a transaction on the selected connection.
* `commit(?string $schema = null): void`

  * Commits the current transaction.
* `rollback(?string $schema = null): void`

  * Rolls back the current transaction.

These methods encapsulate the full query execution lifecycle and offer schema-aware access to underlying database operations.

---

## Additional Notes

### Security

All queries are executed using PDO prepared statements (`PdoDatabaseRequest`), ensuring strong protection against SQL injection vulnerabilities.

### Resolver Fallback Behavior

If no explicit driver or builder is resolved, the system uses default resolution strategies:

* `DefaultDriverResolver` selects drivers from `DriverClassMap`
* `RequestBuilderResolver` assigns a standard builder per driver class

These defaults allow safe extensibility while maintaining predictable behavior.

### Supported Query Types

The `PdoDatabaseRequest` class supports all standard SQL operations:

* `SELECT`, `INSERT`, `UPDATE`, `DELETE`
* `BEGIN`, `COMMIT`, `ROLLBACK` for transactions
* Named and positional bindings

Custom behavior can be injected via custom builders or decorators.

---

*This documentation is part of a broader effort to formalize and elevate the quality, maintainability, and transparency of the Quotation System's internal components.*
