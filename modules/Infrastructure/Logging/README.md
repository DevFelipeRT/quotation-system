# Logging Module (App\Infrastructure\Logging)

## Overview

This module provides a structured, extensible, and secure logging infrastructure aligned with Clean Architecture and DDD Modular principles. It enables logging of typed events across application modules, ensuring traceability, security, and separation of concerns.

---

## Structure

```
Logging/
├── Application/
│   ├── LogEntryAssembler.php
│   ├── LogEntryAssemblerInterface.php
│   └── LoggableInputInterface.php
├── Domain/
│   ├── LogEntry.php
│   └── LogLevelEnum.php
├── Exceptions/
│   ├── InvalidLogLevelException.php
│   ├── LoggerNotConfiguredException.php
│   ├── LoggingException.php
│   ├── LogSanitizationException.php
│   ├── LogWriteException.php
│   └── MalformedLogEntryException.php
├── Infrastructure/
│   ├── Contracts/
│   │   ├── LoggerInterface.php
│   │   └── PsrLoggerInterface.php
│   ├── Adapter/
│   │   └── PsrLoggerAdapter.php
│   └── FileLogger.php
└── Security/
    └── LogSanitizer.php
```

---

## Key Components

### Application

* **LoggableInputInterface**: Defines a generic contract for messages that can be converted into a `LogEntry`.
* **LogEntryAssembler**: Transforms any `LoggableInputInterface` into a sanitized `LogEntry`. Centralizes security (via `LogSanitizer`) and mapping (via `LogLevelEnum`).

### Domain

* **LogEntry**: Immutable value object that represents a structured log entry.
* **LogLevelEnum**: Enum listing log severity levels, aligned with PSR-3.

### Infrastructure

* **FileLogger**: Writes log entries to disk, organized by channel or level.
* **PsrLoggerAdapter**: Bridges `LoggerInterface` to PSR-3 compatible format.
* **LoggerInterface**: Contract for structured logger implementations.
* **PsrLoggerInterface**: PSR-3-compatible logging abstraction.

### Security

* **LogSanitizer**: Redacts sensitive information from log context. Fully configurable and testable.

### Exceptions

Modular, typed exception hierarchy for logging failures:

* `LoggingException` (base)

  * `InvalidLogLevelException`
  * `MalformedLogEntryException`
  * `LogWriteException`
  * `LoggerNotConfiguredException`
  * `LogSanitizationException`

---

## Contracts

### `LoggerInterface`

```php
public function log(LogEntry $entry): void;
```

### `LoggableInputInterface`

```php
public function getMessage(): string;
public function getContext(): array;
public function getCode(): ?string;
public function getChannel(): ?string;
public function getTimestamp(): DateTimeImmutable;
```

### `LogEntryAssemblerInterface`

```php
public function assembleFromMessage(LoggableInputInterface $message): LogEntry;
```

---

## Usage Flow

1. An external module emits a message implementing `LoggableInputInterface` (e.g., `LoggableMessage`).
2. The `LogEntryAssembler` receives the message, sanitizes sensitive context using `LogSanitizer`, and constructs a valid `LogEntry` value object.
3. The `LogEntry` is passed to a concrete implementation of `LoggerInterface` (e.g., `FileLogger`) for persistence.
4. For interoperability with middleware or third-party packages, a `PsrLoggerAdapter` may be used to comply with PSR-3.

In scenarios where the log entry originates from a secure and controlled context—such as internal infrastructure components, trusted services, or deterministic system operations—it is acceptable to construct `LogEntry` instances directly. This avoids redundant sanitization or transformation and provides flexibility for low-level modules to produce logs without requiring the assembler.

This design ensures strict boundaries between input processing, validation, and I/O, while also allowing optimized usage for internal systems with full domain control.

---

## Initialization Guidance

To initialize the Logging module in a custom kernel or service container, follow these steps:

1. Instantiate the `LogSanitizer` (optionally passing custom sensitive keys):

   ```php
   $sanitizer = new LogSanitizer();
   ```

2. Instantiate the `LogEntryAssembler` with the sanitizer:

   ```php
   $assembler = new LogEntryAssembler($sanitizer);
   ```

3. Instantiate a concrete logger (e.g., `FileLogger`) with its configuration:

   ```php
   $logger = new FileLogger('/path/to/logs');
   ```

4. Optionally wrap the logger in a PSR-3 adapter:

   ```php
   $psrLogger = new PsrLoggerAdapter($logger);
   ```

5. Make the logger (or PSR logger) available to your application's services, middleware, or event handlers as needed.

Dependency injection containers can be used to manage lifecycle and composition, depending on your architectural context.

---

## Best Practices

* Sanitize context only once in the assembler.
* Never let `FileLogger` manipulate or reprocess domain data.
* Always use domain-specific exceptions for expected failures (`InvalidLogLevelException`, etc.).
* External modules must implement `LoggableInputInterface`, never depend on the Logging module.
* Maintain immutability and traceability in `LogEntry`.

---

## Extending

* Add `DatabaseLogger`, `SyslogLogger`, or cloud appenders implementing `LoggerInterface`.
* Implement new `LoggableInputInterface` DTOs in other modules.
* Customize `LogSanitizer` with additional keywords via constructor.

---

## Technical Notes

### Dependencies and Compatibility

* Logging context is formatted for output using native serialization functions, ensuring consistent and structured representation.
* Compatible with PSR-3 logging interfaces.
* Uses native PHP types and functions such as `DateTimeImmutable` and `json_encode`.
* Requires PHP 8.1+ for `enum` support.

### Stateless Design

All components in this module are stateless and safe for concurrent usage in CLI, HTTP, or asynchronous contexts.

### Testability

* `LogSanitizer` and `LogEntryAssembler` are designed to be injectable and mockable.
* The module enables unit testing of log construction logic independently from persistence concerns.

## Production Readiness

This module is designed for production environments, emphasizing safety, extensibility, and clean boundaries. It integrates seamlessly into modular DDD systems with full adherence to PSR, SOLID, and Clean Architecture principles.
