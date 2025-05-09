# Messaging Module (App\Application\Messaging)

## Overview

This module provides a foundational structure for immutable, structured messages within the application layer. Messages represent typed communication artifacts—such as errors, audits, notifications, and log entries—intended for cross-cutting communication, logging, or UI presentation.

All messages conform to a shared contract and follow a consistent data model to ensure type safety, traceability, and reusability across the system.

---

## Structure

```
Messaging/
├── Application/
│   ├── AbstractMessage.php
│   └── Types/
│       ├── AuditMessage.php
│       ├── ErrorMessage.php
│       ├── LoggableMessage.php
│       └── NotificationMessage.php
└── Domain/
    └── MessageInterface.php
```

---

## Key Components

### Domain

* **MessageInterface**: Defines the base contract for all application messages. It includes message content, context, optional code, and a timestamp.

### Application

* **AbstractMessage**: Implements `MessageInterface`, encapsulating shared logic across all message types (immutability, context, timestamp).
* **AuditMessage**: Captures system actions for traceability, including actor and entity context.
* **ErrorMessage**: Represents application-level or system errors, typically used for reporting and diagnostics.
* **NotificationMessage**: Used for user-facing alerts and system notifications.
* **LoggableMessage**: A specialized message implementing `LoggableInputInterface`, making it compatible with the logging module.

All message types are immutable and serializable via `toArray()`, and return a unique identifier via `getType()`.

---

## Contracts

### `MessageInterface`

```php
public function getMessage(): string;
public function getContext(): array;
public function getCode(): ?string;
public function getTimestamp(): DateTimeImmutable;
```

### `getType()` (from AbstractMessage)

All subclasses implement:

```php
public function getType(): string;
```

This allows consistent identification of message categories.

---

## Usage

### Usage Flow

1. A component (e.g., a use case, service, or controller) instantiates a message (e.g., `AuditMessage`, `ErrorMessage`, `LoggableMessage`).
2. The message is enriched with context, codes, and a timestamp.
3. The message is passed to consumers:

   * **Logging**: `LoggableMessage` is compatible with `LogEntryAssembler` and structured logging.
   * **Notification**: `NotificationMessage` is routed to UI, email, or messaging platforms.
   * **Auditing**: `AuditMessage` is stored or processed in audit trails.

The message serves as a semantic, immutable data carrier and does not trigger behavior directly.

### Example

```php
AuditMessage::create('User deleted record', ['user_id' => 42]);
ErrorMessage::create('Database connection failed', [], 'DB_CONN_ERROR');
NotificationMessage::create('Your order has shipped', ['order_id' => 123]);
LoggableMessage::warning('Slow API response', ['duration' => 2100]);
```

---

## Initialization

This module requires no explicit initialization. All messages are stateless and ready for direct instantiation.

For advanced scenarios:

* A `MessageFactory` can be introduced for dynamic creation or injection.
* Controllers and services should depend on `MessageInterface` to promote decoupling.
* Messages may be decorated or extended if needed for specific integration contexts.

---

## Integration

* `LoggableMessage` integrates with the Logging module via `LoggableInputInterface`.
* Compatible with any messaging, event, or logging subsystem.
* Does not implement persistence, transport, or rendering logic.
* Works passively: other modules consume or dispatch messages based on structure.

---

## Best Practices

* Use the static factory methods (`create()`, `info()`, `error()`) to keep message construction consistent and immutable.
* Keep the `context` array structured and typed to facilitate downstream processing.
* Leverage `code` as a classifier to assist in grouping or filtering, rather than embedding logic into message strings.
* Design consumers (e.g., loggers, dispatchers) to operate on the shared `MessageInterface`, allowing flexibility to handle new message types without refactoring existing logic. (`create()`, `info()`, `error()`) to enforce immutability.
* Populate the `context` array with structured, typed metadata.
* Use `code` to classify messages without parsing raw text.
* Avoid tightly coupling consumers to concrete message types.

---

## Extending

To add a new message type:

1. Create a class extending `AbstractMessage`
2. Implement `getType()`
3. Optionally, define semantic factories for construction

---

## Production Readiness

This module is production-ready. It promotes clean separation of structure and behavior, supports modular communication across layers, and ensures consistency in logging, notifications, and auditing workflows.
