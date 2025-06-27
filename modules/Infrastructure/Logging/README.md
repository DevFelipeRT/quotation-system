# Logging Module

A **Logging Module** for PHP 8 that provides structured, secure log writing to files. It uses a layered architecture to separate concerns of validation, formatting, and file I/O, ensuring log messages are recorded reliably and sensitive data is sanitized. This module is PSR-3 compatible and can be easily integrated into various PHP applications.

---

## General Description

This Logging Module is a comprehensive, security-focused solution for PHP 8+ applications, engineered as a pure PHP module with no external library dependencies. It provides a robust and structured mechanism for writing logs to the local filesystem, built upon a layered architecture that enforces a strict separation of concerns between domain logic, application services, and infrastructure. A primary focus of the module is security, featuring automated sanitization of sensitive data and rigorous input validation to ensure data integrity and prevent information leaks. It is fully PSR-3 compliant, ensuring seamless integration with third-party libraries and modern frameworks. The module offers developers a reliable, maintainable, and secure logging foundation, centralizing logging logic and promoting best practices for data handling.

#### Features

* **Advanced Security & Sanitization:** Automatically sanitizes log data by masking sensitive keys (e.g., `password`, `api_key`) and values matching configurable regex patterns (e.g., PII, credit cards). Includes advanced credential phrase detection to redact secrets from free-form messages.
* **Domain-Driven & Immutable Architecture:** Utilizes a multi-layer architecture with immutable Value Objects to guarantee that only validated, sanitized, and secure data enters the logging pipeline. This fail-fast design prevents data corruption and enforces strict domain invariants.
* **PSR-3 Compatibility:** Includes a PSR-3 compliant adapter, allowing the module to be used as a drop-in replacement wherever a `Psr\Log\LoggerInterface` is expected, ensuring seamless integration with frameworks and third-party libraries.
* **Structured File Organization:** Automatically organizes log files into a clean directory structure based on channel and severity level (e.g., `/logs/security/critical.log`), making logs easy to navigate and analyze.
* **Robust Context & Data Handling:** Supports rich contextual data with every log entry. The sanitization engine can recursively process deeply nested arrays and objects, featuring built-in circular reference detection to prevent infinite loops and ensure stability.
* **Zero-Dependency & Configurable:** Operates as a pure PHP 8+ module with no external Composer dependencies. Configuration is streamlined, requiring only a base log directory to get started, while allowing deep customization of validation rules, sensitive data patterns, and log levels.

---

### Examples of Use

Below are practical examples demonstrating how to use the Logging Module in a PHP application, including initialization, basic logging calls, context data, and channels.

---

#### **Example 1: Basic Setup and Logging**

This example shows the typical setup process and how to log messages at various severity levels.

```php
<?php
use Config\Modules\Logging\LoggingConfig;
use Logging\Infrastructure\LoggingKernel;

// 1. Configuration: specify the directory for log files.
$logDir = __DIR__ . '/logs'; // ensure this directory is writable
$config = new LoggingConfig($logDir);

// 2. Boot the logging kernel with the configuration.
$kernel = new LoggingKernel($config);

// 3. Get the logging facade (implements LoggingFacadeInterface).
$logger = $kernel->logger();

// 4. Log messages at various levels with context data.
$logger->info("User logged in", ["user" => "alice"]);
$logger->warning("Disk space low", ["disk" => "/dev/sda1", "free_percent" => 5]);
$logger->error("File not found", ["file" => "/path/to/file.txt", "error_code" => 404]);

// 5. Use the generic log method for a custom level (if allowed in config).
$logger->log("notice", "User profile updated", ["user" => "alice"]);

// 6. Logging with a different channel (using logInput to specify channel).
$logger->logInput("Admin privileges granted", "critical", "security", ["admin" => "bob"]);
```

**Execution Flow and Output:**

  * **Initialization:** The process begins by instantiating `LoggingConfig` with the desired path for log storage (`$logDir`). This configuration is then passed to the `LoggingKernel`, which bootstraps the entire module.
  * **Facade Access:** The primary logging interface is retrieved via the `$kernel->logger()` method, which returns a pre-configured facade.
  * **Standard Logging:** When methods like `$logger->info(...)` are called, they write to a log file corresponding to their level under the default `application` channel. The log entry includes a timestamp, channel, level, message, and the provided context data.
  * **Channel-Specific Logging:** The `logInput()` method allows for specifying a custom channel, such as `"security"`. This directs the log entry to a different subdirectory, enabling logical separation of logs.
  * **File Structure:** After running this code, the `logs` directory will contain subfolders for each channel used (`application/`, `security/`), and within them, log files for each level (e.g., `info.log`, `critical.log`). For instance, the first log call creates `logs/application/info.log`, while the last one creates `logs/security/critical.log`.

---

#### **Example 2: Using the PSR-3 Adapter**

For interoperability with external libraries or frameworks that expect a standard `Psr\Log\LoggerInterface`, the module provides a PSR-3 compatible adapter.

```php
use Logging\Infrastructure\LoggingKernel;
use Config\Modules\Logging\LoggingConfig;
// use Psr\Log\LoggerInterface; // if you have psr/log for type hints

$kernel = new LoggingKernel(new LoggingConfig('/var/log/myapp'));
$psrLogger = $kernel->psrLogger(); // Implements PsrLoggerInterface

// Now $psrLogger can be used wherever a PSR-3 LoggerInterface is required.
SomeLibrary::setLogger($psrLogger);

// For demonstration, using psrLogger directly:
$psrLogger->error("Payment failed", ["orderId" => 1001, "amount" => 50.00]);
$psrLogger->debug("Payment response", ["responsePayload" => "<xml>...</xml>"]);
```

**Integration and Benefits:**

  * **Seamless Integration:** The `$kernel->psrLogger()` method returns an adapter that implements `PsrLoggerInterface`, allowing it to be used wherever a standard PSR-3 logger is required.
  * **Centralized Security:** All log calls made through the PSR-3 adapter are processed by the module's internal pipeline. This ensures that any sensitive data passed in the context array is automatically masked according to the central configuration before being written to disk.
  * **Consistent Output:** The log entries from the example above will be written to `logs/application/error.log` and `logs/application/debug.log`, following the same structured file organization.

---

#### **Example 3: Handling Exceptions from Logging**

To build a more resilient application, you can gracefully handle exceptions that may occur during file write operations, such as when file permissions are incorrect.

```php
try {
  $logger->critical("Service outage", ["service" => "database", "duration" => "5m"]);
} catch (\Logging\Infrastructure\Exception\LogWriteException $e) {
  // Handle gracefully if writing to file failed
  error_log("Failed to write to application logs: " . $e->getMessage());
  // Optionally, fallback to an alternate logging mechanism
}
```

**Robust Error Handling Strategy:**

  * By wrapping logging calls in a `try...catch` block, you can prevent the application from crashing if a log cannot be written.
  * The module throws a specific `\Logging\Infrastructure\Exception\LogWriteException` for file writing failures, which can be caught to trigger contingency logic.
  * A fallback mechanism, such as using PHP's native `error_log()`, can be implemented in the `catch` block to ensure that critical error information is not silently lost.
  * While not always necessary in a properly configured environment, this pattern is a best practice for production systems where logging failures should not impact core application availability.

---

### Automated Data Sanitization

A core security feature of the Logging Module is its ability to automatically sanitize data before it is written to disk. This process is designed to prevent the accidental leakage of sensitive information, such as passwords, API keys, or personally identifiable information (PII). Sanitization is transparently applied to all log calls, inspecting both free-form messages and structured context arrays.

The sanitization engine employs a defense-in-depth approach, using several techniques to detect and mask sensitive data:

1.  **Key-Based Masking:** The most reliable method, where values associated with predefined sensitive context keys (e.g., `'password'`, `'secret'`) are fully masked.
2.  **Pattern-Based Detection:** Values that match configured regular expression patterns (e.g., credit card numbers, national IDs) are identified and masked.
3.  **Credential Phrase Analysis:** Free-text log messages are scanned for phrases that resemble credentials (e.g., `"password: somevalue"`), and the sensitive part of the phrase is masked.

The following example demonstrates these features in action.

#### **Sanitization Code Example**

```php
<?php
use Config\Modules\Logging\LoggingConfig;
use Logging\Infrastructure\LoggingKernel;

// Standard setup
$logDir = __DIR__ . '/logs';
$config = new LoggingConfig($logDir);
$kernel = new LoggingKernel($config);
$logger = $kernel->logger();

// --- Sanitization Examples ---

// 1. Logging with sensitive keys in the context array.
$logger->warning("User authentication attempt failed", [
    "username" => "j.doe",
    "password" => "S3cr3t-P@ss!",
    "apiKey" => "sk_123abc456def"
]);

// 2. Logging with a value that matches a sensitive pattern.
$logger->info("Payment transaction processed", [
    "transactionId" => "txn_78910",
    "creditCard" => "4987654321098765" 
]);

// 3. Logging a message containing a credential phrase.
$logger->error("Legacy system connection error: password: oldsystempassword was rejected", [
    "system" => "legacy_crm"
]);
```

#### **Sanitization in Action**

  * **In the first log (`warning`):** The context array contains the keys `"password"` and `"apiKey"`. The sanitization engine recognizes these as sensitive keys based on the module's configuration and replaces their corresponding values with the default mask token. The `"username"` value remains untouched.
  * **In the second log (`info`):** The value provided for the `"creditCard"` key (`"4987654321098765"`) matches a configured regular expression pattern for detecting credit card numbers. As a result, the value is fully masked.
  * **In the third log (`error`):** The message string itself contains the phrase `"password: oldsystempassword"`. The `CredentialPhraseSanitizer` detects this structure and masks the value that follows the key and separator. This provides a layer of protection even when sensitive data is not properly structured in the context.

#### **Expected Log Output**

The resulting content written to the log files (`warning.log`, `info.log`, `error.log`) would look similar to the following, demonstrating that the sensitive data has been replaced with the `[MASKED]` token:

```log
[2025-06-26T16:55:35-03:00] [application] [WARNING] User authentication attempt failed. | Context: {"username":"j.doe","password":"[MASKED]","apiKey":"[MASKED]"}
[2025-06-26T16:55:35-03:00] [application] [INFO] Payment transaction processed. | Context: {"transactionId":"txn_78910","creditCard":"[MASKED]"}
[2025-06-26T16:55:35-03:00] [application] [ERROR] Legacy system connection error: password: [MASKED] was rejected. | Context: {"system":"legacy_crm"}
```

---

## Architectural Overview

The Logging Module is designed with a multi-layer architecture, following principles of separation of concerns and domain-driven design. The codebase is organized into distinct namespaces and directories, each representing a layer or component group:

#### Domain Layer
Contains the core Value Objects and Security logic. This layer is responsible for the validity and integrity of logging data. It knows how to validate log messages, levels, contexts, etc., and how to sanitize sensitive data. Domain classes are pure logic with no external side effects (no file access or output).

#### Application Layer
Provides a Facade that serves as the public API for the module. The facade orchestrates the logging process by coordinating domain and infrastructure components. It offers simple methods to client code (including PSR-3 style methods) to log messages without needing to know the internal details.

#### Infrastructure Layer
Handles the lower-level concerns like constructing log entries from inputs, formatting log lines, and writing to files. It also includes a Kernel that ties all parts together using a configuration. The infrastructure layer is the bridge between the domain’s pure logic and the outside world (filesystem).

#### Security Layer
Implements centralized sanitization and validation services to enforce strict security and data integrity policies across the logging domain. This layer provides configurable routines for masking sensitive data, detecting confidential patterns, and validating all log-related input. By orchestrating both sanitizing and validation modules, it ensures that every log entry complies with security standards before being persisted or exposed.

#### Configuration Layer
Encapsulated under a Config\Modules\Logging namespace, it defines configurable parameters (such as default values, allowed log levels, sensitive data patterns) through simple classes and interfaces. This layer allows customization of the module’s behavior without altering core logic.

#### Public Contracts
A set of interfaces in PublicContracts\Logging that define the module’s API and extension points. These include interfaces for the Logging Facade, Kernel, PSR-3 logger, and configuration contracts. By coding against these interfaces, the module remains decoupled and interchangeable in different contexts.

#### Inter-layer interactions
The **LoggingKernel** (in Infrastructure) acts as the central orchestrator for the module’s initialization and wiring. Upon receiving a configuration object, it instantiates and connects all core components: domain services (validator, sanitizer), the log entry assembler, file path resolver, formatter, and writer. It then exposes a ready-to-use **LoggingFacade** (Application layer) as the main entry point for logging operations. 

The **LoggingFacade** coordinates domain logic (validation, sanitization, assembly) and infrastructure services (formatting, file writing) to process each log request. This clear separation of responsibilities across layers ensures robust validation, consistent formatting, and reliable file output, while making the system easy to maintain, extend, and test.

_Below is a conceptual diagram of the architecture for clarity:_

```
Client Code --> LoggingKernel (Infrastructure) --> LoggingFacade (Application)
| - boots Validator & Sanitizer (Security)
| - boots LogEntryAssembler, Logger, etc. (Infrastructure)
v
LoggingFacade (Application) --> LogEntryAssembler (Infrastructure) --> Domain Value Objects
| (uses LogSecurity for validation/sanitization)
v
Logger (Infrastructure) --> LogFilePathResolver (Infrastructure)
| --> LogLineFormatter (Infrastructure)
v
LogFileWriter (Infrastructure) --> File System (logs)
```

_In the above flow, solid arrows indicate direct use/composition, and the dashed arrow from Client Code to LoggingFacade indicates that client code ultimately calls the facade methods provided by the kernel.)_

---

## Description of Layers and Components

---

### Domain Layer

The Domain Layer constitutes the architectural core of the logging system, serving as the definitive authority for all business logic, data integrity, and security invariants. Architected following the principles of **Clean Architecture** and **Domain-Driven Design (DDD)**, this layer meticulously isolates the core logging logic from external application concerns, such as frameworks, delivery mechanisms, and persistence technologies. Its primary mandate is to ensure that every piece of data destined for a log entry is not only valid and well-formed but also secure and compliant with established domain rules.

The design is centered around two fundamental concepts that work in concert: **Immutable Value Objects** and a **Centralized Security Facade**.

1.  **Immutable Value Objects (VOs):** Each conceptual component of a log record—such as a message (`LogMessage`), a severity level (`LogLevel`), a channel (`LogChannel`), or a directory path (`LogDirectory`)—is encapsulated within its own distinct, self-validating Value Object. These objects are the building blocks of the domain, and they enforce their own invariants upon instantiation. This design guarantees that an invalid object can never exist within the domain's boundaries. The principle of **immutability** is strictly enforced; once a VO is created, its state cannot be altered, which eliminates side effects and ensures data remains consistent and reliable throughout its lifecycle.

2.  **Centralized Security Facade (`LogSecurity`):** All validation and sanitization logic is orchestrated through the `LogSecurity` facade, which implements the `LogSecurityInterface`. This component serves as a single, consistent entry point for applying security policies across the entire domain, including data masking, pattern validation, length constraints, and filtering of malicious input. By centralizing these routines, the system guarantees uniform policy enforcement, reduces code duplication, and simplifies maintenance and security audits.

The layer operates on a **fail-fast** basis. Any attempt to construct a Value Object with data that violates domain rules—such as an empty message, an unrecognized log level, or an unsafe directory path—results in an immediate, specific domain exception (e.g., `InvalidLogMessageException`, `InvalidLogLevelException`). This prevents the propagation of corrupt or insecure data and ensures that only fully-formed, trusted log entries, aggregated within the `LogEntry` object, are passed to downstream services for formatting, storage, or transmission.

In essence, the Domain Layer acts as the system's gatekeeper. It guarantees that every piece of data entering the logging pipeline is rigorously vetted, sanitized, and transformed into a trusted, immutable representation. By establishing this robust foundation, the Domain Layer ensures the reliability, security, and consistency required for all subsequent operations in mission-critical environments.

---

#### Domain Security

The Domain Security layer serves as the central nervous system for all validation and sanitization operations within the logging domain. Its fundamental purpose is to establish and enforce a single, unified security policy for all log-related data, ensuring that every input is rigorously vetted before it is accepted into the domain's value objects. Architected around the **Facade pattern** and the principles of **Clean Architecture**, this sub-layer enforces a strict separation of concerns, which is critical for achieving robust security, maintainability, and testability.

At its heart is the `LogSecurity` class, a stateless facade that acts as the sole point of entry for all security-related requests. Rather than implementing the logic itself, `LogSecurity` orchestrates the process by delegating tasks to specialized service contracts: all validation checks are routed to an implementation of `ValidatorInterface`, while all data cleansing and masking operations are handled by an implementation of `SanitizerInterface`. This delegation model ensures that the facade remains lean and focused on its primary responsibility of orchestration, while the complex rule logic is encapsulated within dedicated, interchangeable components.

The entire system is unified through the `LogSecurityInterface`, a composite contract that extends both the `ValidatorInterface` and `SanitizerInterface`. Domain Value Objects (e.g., `LogMessage`, `LogDirectory`) do not depend on the concrete `LogSecurity` class but solely on this interface. This strict adherence to the **Dependency Inversion Principle** is paramount: it decouples the domain's core logic from specific security implementations, allows for easy extension or replacement of security rules without affecting client code, and makes the entire domain inherently testable with mock implementations.

The result of this architecture is a highly secure and consistent domain. By routing all operations through a single, contract-bound facade, the system guarantees that no data can bypass the established security protocols. Changes to security policies—such as adding a new sensitive pattern to mask or tightening validation rules—can be made in one central location and are instantly propagated throughout the entire logging infrastructure. This creates a resilient, auditable, and maintainable security foundation for the entire logging system.

---

##### LogSecurity

Implements the `LogSecurityInterface` and serves as the centralized security facade within the logging domain. This class aggregates both a `Validator` and a `Sanitizer`, exposing a unified interface for all validation and sanitization operations required by log-related value objects and services.

###### **Responsibilities:**

* **Centralized Security Orchestration:**
  All validation and sanitization logic is routed through `LogSecurity`, ensuring consistent application of security policies and reducing code duplication across the domain.

* **Delegated Operations:**
  Each public method delegates its work to the appropriate specialized service: validation methods are delegated to the injected `ValidatorInterface`, and sanitization methods to the injected `SanitizerInterface`. The facade itself remains stateless, holding only service references.

* **Strict Configuration Compliance:**
  All configurable rules (such as forbidden patterns, maximum lengths, and masking tokens) are encapsulated within the validator and sanitizer dependencies, allowing global policy changes to be automatically enforced everywhere in the system.

###### **Key Methods:**

* `sanitize(mixed $input, ?string $maskToken = null): mixed`
  Delegates to the sanitizer to cleanse any input—scalar, string, array, or object—according to current security policy. Returns the sanitized value of the same type as input.

* `isSensitive(mixed $value): bool`
  Checks whether the input is considered sensitive under the configured patterns and key rules, without altering the input.

* `validateString(...)`, `validateChannel(...)`, `validateLevel(...)`, `validateContext(...)`, `validateDirectory(...)`, `validateMessage(...)`, `validateTimestamp(...)`
  Each method delegates to the validator, applying strict domain-specific rules to ensure that all log inputs are well-formed, secure, and compliant. Every violation triggers a domain-specific exception.

###### **Integration and Usage:**

* **Constructor Injection:**
  Receives instances of `ValidatorInterface` and `SanitizerInterface`, making the class suitable for dependency injection, extension, and testability.

* **Domain-Centric Value Objects:**
  Value objects in the domain (such as `LogMessage`, `LogDirectory`, and others) receive a `LogSecurityInterface` instance in their constructors and use it for all required validation and sanitization operations.

###### **Benefits:**

* **Maintainability and Consistency:**
  Centralizing all security-related routines means any changes to validation or sanitization rules propagate instantly throughout the logging subsystem.

* **Security and Compliance:**
  Guarantees that no value object, log entry, or context field can bypass domain security checks, enforcing strict input validation and masking at all critical points.

* **Extensibility:**
  The facade pattern and explicit separation of concerns enable easy adaptation or extension of validation and sanitization logic without affecting client code.

---

##### LogSecurityInterface

Contract for the security facade, guaranteeing that all necessary validation and sanitization methods are available via a unified interface.

###### **Responsibilities:**

* Extends both `ValidatorInterface` and `SanitizerInterface`.
* Ensures that all log-related Value Objects depend solely on this interface for their security needs.
* Promotes architectural decoupling, testability, and maintainability.
* Provides a single, reliable point of enforcement for domain security rules.

---

##### ValidatorInterface

Contract for all domain validation routines, responsible for enforcing invariants and input integrity.

###### **Responsibilities:**

* Provides granular validation methods for strings, channel names, log levels, dates, and other domain primitives.
* All methods must throw specific exceptions defined in `Logging\Domain\Exception` on failure, ensuring strict input data integrity.
* Guarantees that all Value Objects are constructed only from valid, normalized data.

---

##### SanitizerInterface

Contract for sanitization of input data within the logging domain.

###### **Responsibilities:**

* Defines methods to mask or remove sensitive/confidential information from values or complex structures before any persistence, transmission, or external exposure.
* Recursively sanitizes arrays and objects, masking sensitive keys, phrases and patterns.
* Detects whether a value (or any nested value/key) is considered sensitive under the security policy.

---

#### Value Objects

Value Objects are the foundational building blocks of the logging domain, representing the granular, conceptual components of a log record. Each Value Object (e.g., `LogMessage`, `LogLevel`, `LogChannel`) encapsulates a single, discrete piece of data, transforming a primitive type, such as a string or an array, into a rich, self-validating domain concept. Their primary responsibility is to enforce domain invariants at the point of creation, thereby guaranteeing that an invalid instance can never exist within the system's boundaries.

Central to their design are the principles of **immutability** and **self-validation**. Upon construction, each Value Object immediately leverages the domain's `LogSecurityInterface` to sanitize and validate its input data. If the data fails to meet the strict domain criteria—such as a forbidden character in a channel name or an empty log message—a specific, descriptive exception is thrown. This fail-fast mechanism halts the process and prevents the propagation of corrupt state. Once successfully instantiated, the object's internal value is immutable and cannot be altered, guaranteeing data integrity and predictability throughout its lifecycle.

These objects function within a clear compositional hierarchy. Initial log request data is typically encapsulated in the `LoggableInput` Data Transfer Object (DTO), which performs preliminary structural checks. This data is then used to construct the individual, highly-specialized Value Objects, each enforcing its own deep, context-specific rules. Finally, these validated components are assembled into the `LogEntry` aggregate—a composite Value Object that represents a complete, immutable, and fully-vetted log record. The `LogEntry` itself implicitly trusts the validity of its constituent parts, as they are guaranteed to be correct by their respective classes.

This architectural approach provides significant advantages. It eliminates "primitive obsession" by making the domain's contracts explicit and expressive. By distributing validation logic across small, focused classes, the system becomes more maintainable, readable, and less prone to error. Ultimately, the rigorous use of Value Objects ensures that the entire domain operates exclusively with data that is not only of the correct type but is also semantically valid, secure, and consistent with all established business rules.

---

##### LogDirectory

`LogDirectory` is an immutable Value Object responsible for securely encapsulating a file-system directory path intended for log storage. This class guarantees that the provided path is sanitized, strictly validated, the directory is created if missing, and correct permissions are enforced prior to use. All validation and sanitization are performed via a domain security facade.

###### **Responsibilities**

* **Sanitization:** Delegates to a domain security service (`LogSecurityInterface`) to remove or neutralize unsafe path input.
* **Validation:** Ensures the directory path is not only sanitized but strictly complies with business and security rules.
* **Existence Enforcement:** Creates the directory recursively with restrictive permissions (`0770`) if it does not exist.
* **Permission Enforcement:** Ensures the directory is writable, attempting to apply the correct permissions if necessary.
* **Immutability:** Once instantiated, the path cannot be changed; the internal state is read-only.

###### **Construction and Validation Logic**

Upon instantiation, the LogDirectory constructor applies the following sequence:

1. **Sanitization:**

   * The raw path is passed to the security facade's `sanitize` method.
   * If the sanitized result is empty, an `InvalidLogDirectoryException` is thrown.

2. **Integrity Check:**

   * If the sanitized value does not exactly match the original input, an `InvalidLogDirectoryException` is thrown.

3. **Validation:**

   * The sanitized path is passed to the security facade's `validateDirectory` method for strict validation against domain rules.
   * If validation fails, an `InvalidLogDirectoryException` is thrown.
   * If validation passes, the result is assigned to the internal path property.

4. **Directory Creation:**

   * The class checks whether the directory exists. If not, it attempts to create it recursively with restrictive permissions (0770).
   * If creation fails, a `RuntimeException` is thrown.

5. **Permission Enforcement:**

   * The class checks if the directory is writable. If not, it attempts to set the correct permissions.
   * If permission adjustment fails, a `RuntimeException` is thrown.

If any of these steps fail, construction is aborted and the relevant exception is thrown, preventing unsafe directory usage.

###### **Public Interface**

* `__construct(string $path, LogSecurityInterface $security)`
  Instantiates and secures the directory path.

* `getPath(): string`
  Returns the validated and sanitized directory path.

* `isWritable(): bool`
  Returns `true` if the directory is currently writable.

* `__toString(): string`
  Casts the object to its string path.

###### **Exception Handling**

* **`InvalidLogDirectoryException`**
  Thrown if the path is empty, sanitized to a different value, or fails validation.

* **`RuntimeException`**
  Thrown if the directory cannot be created or made writable.

###### **Usage Example**

```php
use Logging\Domain\ValueObject\LogDirectory;
use Logging\Domain\Security\Contract\LogSecurityInterface;

$logDirectory = new LogDirectory('/var/log/myapp', $security);
$path = $logDirectory->getPath();

if (!$logDirectory->isWritable()) {
    // Handle unwritable directory (e.g., log or raise an alert)
}
```

###### **Kernel Integration** (Exclusive Use)

The `LogDirectory` object must be instantiated and managed exclusively by the logging kernel. This ensures that all directory paths used by the logging system are subject to consistent security checks and file-system guarantees enforced centrally.

Access to the validated log directory should only occur through the kernel’s internal references. Direct instantiation or external handling of `LogDirectory` is discouraged to prevent configuration drift, security gaps, or inconsistent enforcement of validation logic.

After `LogDirectory` is created in the kernel, it is passed to components that manage log files, such as the log file path resolver. This guarantees that every log file operation is performed within a validated and secured directory context, under the control of the kernel lifecycle.

Example:

```php
$this->logDirectory = new LogDirectory(
    $config->baseLogDirectory(),
    $this->security
);

$this->logFilePathResolver = new LogFilePathResolver($this->logDirectory);
```

By enforcing access and lifecycle management solely within the kernel, the system eliminates the risk of using unsafe or misconfigured directories in any part of the logging infrastructure.


###### **Design Considerations and Security Enforcement**

* **Path Validation Responsibility:** The `LogDirectory` class is dedicated to validating and securing the base log directory path. It ensures the base directory is safe and writable, but does not construct or validate the full file paths for individual log files. The responsibility for resolving specific log file paths lies with other components, such as the path resolver.
* **Mandatory Security Enforcement:** All directory paths used by the logging system must be introduced via the `LogDirectory` object. This guarantees that consistent security and permission policies are applied throughout the logging infrastructure.

---

##### LoggableInput

A strictly immutable Data Transfer Object (DTO) for carrying validated log request data throughout the logging domain. Implements `LoggableInputInterface`.

###### **Responsibilities:**

  * Encapsulates all attributes required to represent a loggable event:

      * `message` (required, non-empty string)
      * `level` (optional, non-empty string)
      * `context` (optional, associative array with non-empty string keys and mixed values)
      * `channel` (optional, non-empty string)
      * `timestamp` (optional, `DateTimeImmutable` instance; defaults to current time)

  * Enforces strict validation on all fields at construction time, ensuring immutability and reliability.

  * Throws `InvalidLoggableInputException` for any violation of property requirements.

###### **Construction and Validation Logic:**

  * **Message:**

      * Must be a non-empty string after trimming. The trimmed value is stored.
      * An empty or whitespace-only string triggers `InvalidLoggableInputException::emptyMessage()`.

  * **Level:**

      * If provided, must be a non-empty string after trimming. The trimmed value is stored.
      * An empty string triggers `InvalidLoggableInputException::emptyLevel()`.
      * If omitted (`null`), the property remains `null`.

  * **Context:**

      * If provided, must be an associative array.
      * Keys must be of type `string` and must not be empty after trimming. An invalid key triggers `InvalidLoggableInputException::invalidContextKey($key)`.
      * **Values are not validated or modified.** They are stored as-is (`mixed`).
      * If omitted, defaults to an empty array (`[]`).

  * **Channel:**

      * If provided, must be a non-empty string after trimming. The trimmed value is stored.
      * An empty string triggers `InvalidLoggableInputException::emptyChannel()`.
      * If omitted (`null`), the property remains `null`.

  * **Timestamp:**

      * If omitted, defaults to the current date and time (`new DateTimeImmutable()`).

###### **Usage Pattern:**

  * `LoggableInput` is instantiated internally by the logging subsystem, typically by the facade responsible for receiving and preprocessing raw log input.
  * Each logging call results in a new instance, ensuring every log event is validated and timestamped before reaching downstream processing.
  * The object provides type-safe, readonly accessors for each attribute, preventing any mutation post-construction.
  * Intended for internal use—application code should not instantiate this DTO directly.

###### **Immutability Guarantee:**

  * All properties are declared as `private readonly`.
  * No setters or mutator methods are present; values are established exclusively during instantiation and never altered thereafter.

###### **Interface:**

  * `getMessage(): string`
  * `getLevel(): ?string`
  * `getContext(): array<string, mixed>`
  * `getChannel(): ?string`
  * `getTimestamp(): DateTimeImmutable`

###### **Exception Handling:**

  * Any invalid parameter triggers a domain-specific exception from `InvalidLoggableInputException`, enforcing fail-fast semantics and maintaining system integrity.

###### **Example Construction**

```php
use Logging\Domain\ValueObject\LoggableInput;

$basicInput = new LoggableInput(
    message: 'Request succeeded.'
);

$input = new LoggableInput(
    message: 'User login failed',
    level: 'error',
    context: ['username' => 'admin', 'attempt' => 3, 'success' => false],
    channel: 'auth'
    // timestamp is optional
);
```

---

##### LogMessage

`LogMessage` is a Value Object dedicated to encapsulating, sanitizing, and validating the content of a log message within the logging domain. This class ensures that any message passed into a log entry is strictly checked for compliance with security and domain policies before it can be stored, displayed, or transmitted.

###### **Responsibilities**

* **Sanitization:** The raw log message is passed through the `LogSecurityInterface`, which removes or neutralizes unsafe or sensitive content according to system security policies.
* **Validation:** The sanitized message is validated by the security facade to ensure compliance with domain requirements, such as non-emptiness, length restrictions, and prohibition of forbidden characters or content.
* **Immutability:** Once created, the internal message cannot be altered.

###### **Construction and Validation Logic**

When a `LogMessage` instance is constructed:

1. The input string is sanitized using the security facade.
2. The sanitized message is then validated for domain compliance. If validation fails, an `InvalidLogMessageException` is thrown.
3. The validated message is stored in a private, immutable property.

If any step fails, construction is aborted, and the message is not accepted by the logging infrastructure.

###### **Usage in the Logging Workflow**

* In the `LogEntryAssembler`, the log message is always instantiated as a `LogMessage` object using a string that has already been sanitized in the context of the associated log channel (to further mitigate information leaks).
* `LogMessage` is then passed to the `LogEntry` Value Object, which composes all log metadata (level, context, channel, timestamp, message) into an immutable record. This composition guarantees that every persisted or emitted log entry contains a message that is both secure and policy-compliant.

###### **Public Interface**

* `__construct(string $message, LogSecurityInterface $security)`
* `value(): string` — Retrieves the sanitized and validated log message as a string.

###### **Exception Handling**

* **`InvalidLogMessageException`**
  Thrown if the message cannot be sanitized or fails domain-specific validation.

###### **Design Considerations**

* **No Fallback:** No fallback value is applied for log messages. A valid, policy-compliant message must always be provided at input time.
* **Security Enforcement:** All message content that reaches the logging infrastructure passes through two layers of security checks before it is stored, ensuring domain-wide consistency and preventing leakage of sensitive or malformed data.
* **Domain Consistency:** By encapsulating message content in a strict Value Object, log records (`LogEntry`) across the system are consistent and reliable, supporting traceability and auditability in secure environments.

---

##### LogLevel

Represents an **immutable value object** encapsulating the severity level of a log entry (such as `"info"`, `"error"`), enforcing strict validation and normalization according to domain and configuration policies.

###### **Responsibilities:**

* **Validation Against Allowed Levels:**
  The `LogLevel` ensures that any provided log level string is strictly validated against an allowed set of levels.

  * By default, the allowed levels correspond to the [PSR-3 standard](https://www.php-fig.org/psr/psr-3/) (`debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`).
  * The allowed set may be extended with custom levels via constructor injection.

* **Domain-Driven Security Enforcement:**
  All level input is sanitized and validated using a `LogSecurityInterface` instance. This centralizes all logic for string cleansing, normalization, and validation. If the input is not recognized or contains invalid data, an `InvalidLogLevelException` is thrown.

* **Normalization:**
  Level names are normalized—typically lowercased—during validation, ensuring consistent comparison and equality checks.

###### **Constructor and Properties:**

* The constructor receives the raw log level, a `LogSecurityInterface` instance, and an optional array of custom levels.
* It builds the complete set of allowed levels, sanitizes and validates both built-in and custom levels, and stores only the final, validated level.

###### **Public Methods:**

* `value(): string`
  Returns the validated, normalized log level string.

* `equals(LogLevel $other): bool`
  Checks strict equality with another `LogLevel` instance.

* `validLevels(): array`
  Returns the complete list of allowed log levels for this instance.

* `__toString(): string`
  Returns the log level as a string, suitable for output or serialization.

###### **Internal Behavior:**

* The internal helper `buildValidLevels` merges the standard and custom levels, applies sanitization and validation to each, and ensures uniqueness.
* All operations leverage the security facade, making this value object fully compliant with system-wide security and input policy.

**Error Handling:**

* If any provided level (standard or custom) is invalid after sanitization and normalization, an `InvalidLogLevelException` is thrown, preventing the creation of a malformed `LogLevel` instance.

---

##### LogContext

Represents an immutable, domain-driven value object that encapsulates a sanitized and validated associative array of context data for logging purposes. It centralizes all logic for securely managing key-value metadata that may accompany log records.

###### **Responsibilities:**

* **Sanitization and Validation:**
  Utilizes a domain security facade (`LogSecurityInterface`) to sanitize all context data (masking sensitive values such as passwords, tokens, or other confidential information) and then applies strict validation rules to the sanitized result.

  * Keys are validated to ensure they are strings, meet content requirements (e.g., not empty, not containing control characters), and do not conflict or repeat.
  * Values are validated to be serializable, non-resource types, and of manageable size, ensuring compatibility with log outputs and downstream processing.

* **Immutability:**
  Once constructed, the encapsulated context cannot be altered. All accessors return either copies or immutable data.

* **Exception Handling:**
  Throws a domain-specific `InvalidLogContextException` if any sanitization or validation rules are violated—preventing the creation of invalid or unsafe log contexts.

###### **Interface and Methods:**

* **`__construct(array $context, LogSecurityInterface $security)`**

  * Receives a raw associative array and a security facade.
  * Immediately applies sanitization and validation, guaranteeing only safe and valid data is encapsulated.

* **`value(): array`**

  * Returns the entire sanitized and validated context array.

* **`get(string $key): mixed|null`**

  * Retrieves the value associated with the specified key, or `null` if the key does not exist.

* **`keys(): array`**

  * Returns a list of all context keys, in the order they appear.

###### **Security and Data Integrity:**

* **Centralized Security:**
  All context-related security logic is delegated to the domain’s `LogSecurityInterface` implementation, ensuring that both sanitization (e.g., masking sensitive data) and validation (e.g., key/value checks) are applied consistently and according to domain requirements.

* **Prevents Unsafe Data:**
  Enforces that no context containing unsafe, unserializable, or overly large entries can be logged, supporting robust application security and log reliability.

###### **Typical Usage:**

A valid `LogContext` instance guarantees that any log context provided to the logging infrastructure is already cleaned and verified, ready for safe inclusion in persistent logs or audit trails. This abstraction prevents accidental leakage of sensitive data or invalid metadata structures.

---

##### LogChannel

Represents an immutable, domain-driven value object for a log channel (a logical category or destination for log entries, such as "application", "persistence", or "payment"). This abstraction ensures that the channel identifier is always valid, sanitized, and safe for use in file or directory names, which is critical in logging systems that separate log streams by channel.

###### **Responsibilities:**

* **Validation and Sanitization:**

  * Uses a domain security facade (`LogSecurityInterface`) to sanitize and validate the supplied channel name.
  * Enforces that the channel name is a non-empty string, free from forbidden characters (such as slashes, null bytes, or any character that could compromise file system safety or application security).
  * Applies any additional domain-specific constraints (such as allowed character sets or reserved words).

* **Immutability:**

  * Once constructed, the channel value cannot be altered.
  * All accessor methods return either the validated value or its direct string representation.

* **Exception Handling:**

  * Throws an `InvalidLogChannelException` if the provided channel name fails validation or is otherwise unsafe for use.

###### **Interface and Methods:**

* **`__construct(string $channel, LogSecurityInterface $security)`**

  * Receives a raw channel string and the security facade.
  * Immediately sanitizes and validates the value before assignment.

* **`value(): string`**

  * Returns the sanitized and validated channel name.

* **`__toString(): string`**

  * Provides direct string conversion for convenience in logging or output contexts.

###### **Security and Safety:**

* **Centralized Enforcement:**

  * All sanitization and validation logic is delegated to the domain security service, ensuring that all channel names are processed uniformly and in compliance with application security policies.

* **Prevents Log Injection and Filesystem Risks:**

  * Rigorously blocks empty or unsafe values, preventing issues such as path traversal, log injection, or accidental overwriting of log files.
  * Guarantees that all log output is properly segregated and managed by a safe, domain-controlled identifier.

* **Default Channel:**

  * The architectural standard for defaulting, if not otherwise configured, is `"application"` (as determined by the caller or the surrounding application logic).

###### **Typical Usage:**

A valid `LogChannel` instance guarantees that all downstream log operations are performed under a strictly validated and sanitized channel name, eliminating the risk of unsafe output destinations or category misidentification.

---

##### LogEntry

A domain aggregate Value Object representing a fully-validated, immutable log entry. Implements `LogEntryInterface` for a consistent contract across the logging domain.

###### **Responsibilities:**

* Aggregates and encapsulates all components that constitute a complete log record:

  * `LogLevel` (severity, required)
  * `LogMessage` (log message, required)
  * `LogContext` (contextual data, required)
  * `LogChannel` (categorization channel, required)
  * `timestamp` (`DateTimeImmutable`, optional; defaults to current time)
* Delegates all validation and sanitization strictly to its constituent value objects. Assumes all arguments are already validated and sanitized.
* Guarantees that, once constructed, a `LogEntry` represents a fully-formed, internally consistent, and secure log record suitable for downstream processing or storage.

###### **Construction and Invariants:**

* Receives only validated and sanitized Value Objects (`LogLevel`, `LogMessage`, `LogContext`, `LogChannel`).
* All properties are assigned once, at construction, and never mutated thereafter.
* Immutability is enforced at the domain level; although PHP does not provide a `readonly` modifier for objects (only for properties), external mutation is not supported by contract or design.

###### **Usage Pattern:**

* Instantiated internally by the logging domain after all components have undergone rigorous validation and sanitization.
* Serves as the definitive, atomic representation of a log event—once a `LogEntry` is constructed, it is guaranteed to be valid for formatting, writing, or downstream transformation.
* Never exposes unvalidated data; security and data integrity are strictly preserved.

###### **Interface:**

* `getLevel(): LogLevel`
* `getMessage(): LogMessage`
* `getContext(): LogContext`
* `getChannel(): LogChannel`
* `getTimestamp(): DateTimeImmutable`

###### **Security Controls:**

* Explicitly disallows PHP serialization and unserialization by overriding `__serialize` and `__unserialize` with `LogicException`. This prevents sensitive log data from being inadvertently persisted or reconstituted outside domain control.

###### **Immutability and Domain Guarantees:**

* All attributes are set only in the constructor, ensuring immutability after instantiation.
* No mutator methods exist.
* Delegates all input integrity to its component value objects, reinforcing separation of concerns and domain safety.

###### **Example Construction**

```php
use Logging\Domain\ValueObject\LogEntry;

$entry = new LogEntry(
    $level,         // LogLevel instance, already validated
    $message,       // LogMessage instance, already validated
    $context,       // LogContext instance, already validated
    $channel        // LogChannel instance, already validated
    // $timestamp is optional
);
```

###### **Exception Handling:**

* No exceptions are thrown within the `LogEntry` constructor, as all data must be validated beforehand.
* Attempts to serialize or unserialize will throw `LogicException`.


###### **Important Notes:**

* `LogEntry` is not responsible for the validation or sanitization of its contents, these tasks are handled by the domain Value Objects provided as constructor arguments.
* Its primary role is to aggregate, guarantee immutability, and prevent invalid or insecure states in the logging workflow.
* Designed for internal use in the logging subsystem and not intended for direct instantiation by application code.

---

#### Domain Exceptions

The domain layer defines a set of exception classes to signal invalid inputs or configurations:

- **`InvalidLogChannelException`**
- **`InvalidLogContextException`**
- **`InvalidLogDirectoryException`**
- **`InvalidLoggableInputException`**
- **`InvalidLogLevelException`**
- **`InvalidLogMessageException`**
- **`InvalidSanitizationConfigException`**

Each exception extends PHP’s `InvalidArgumentException` class and indicates a specific rule violation. These exceptions typically include a descriptive message, such as:

- `"Log message cannot be empty"`
- `"Log level 'VERBOSE' is not recognized"`

They are thrown during the creation of value objects or during validation steps. `InvalidLoggableInputException` is a general exception that may be thrown when a higher-level validation fails (for example, if an entire log input object is inconsistent).

These exceptions are intended to be caught by the application using the module if needed, or at least to fail fast during development so that improper usage is corrected.

---

### Application Layer (Facade)
 
The application layer provides a streamlined and unified interface for interacting with the logging system. It abstracts the underlying domain and infrastructure complexities, enabling application code to perform logging operations through a simple and consistent API.

---

#### LoggingFacade
 
The `LoggingFacade` is the primary entry point for all logging operations within the application. By implementing `LoggingFacadeInterface`, it exposes high-level methods for logging messages and orchestrates the necessary domain and infrastructure components internally.

##### Responsibilities:

- **Receives Logging Requests:**  
    Handles logging calls from user code, such as `$logger->error("message", [...])`, and provides methods for all standard log levels.

- **Input Construction:**  
    Constructs a `LoggableInput` value object from the provided parameters, encapsulating the message, level, channel, and context.

- **Domain Assembly:**  
    Delegates the creation of a `LogEntry` to the assembler, ensuring that all domain-specific validation and sanitization are applied.

- **Persistence:**  
    Passes the validated and sanitized `LogEntry` to the `Logger`, which is responsible for writing the entry to the appropriate log file or storage.

- **Error Propagation:**  
    Any exceptions (such as invalid input or failed validation) are propagated to the caller, allowing the application to handle them as needed.

- **API Bridging:**  
    Bridges simple, user-facing method calls to the complete sequence of assembling, validating, and persisting log entries.

##### Methods:

- **PSR-3 Level Methods:**  
    - `emergency($message, array $context)`
    - `alert($message, array $context)`
    - `critical($message, array $context)`
    - `error($message, array $context)`
    - `warning($message, array $context)`
    - `notice($message, array $context)`
    - `info($message, array $context)`
    - `debug($message, array $context)`
    - Each method logs at the specified severity level and typically delegates to a generic logging method.

- **Generic Logging:**  
    - `log(string $level, $message, array $context)`  
        Allows logging at any arbitrary level, validating the level string and delegating to the appropriate internal logic.

- **Extended Logging:**  
    - `logInput(Stringable|string $message, ?string $level, ?string $channel, ?array $context)`  
        Supports custom channels and uses default values when parameters are omitted. This method constructs a `LoggableInput`, assembles a `LogEntry`, and logs it.

##### PSR-3 Integration:

The module includes a `PsrLoggerAdapter` that implements `PublicContracts\Logging\PsrLoggerInterface`, which mirrors the standard PSR-3 `LoggerInterface` main methods. This adapter is provided to the `LoggingFacade` as a dependency, enabling seamless integration with any library or framework that requires a PSR-3-compliant logger, without additional configuration.

As a result, the logging module can be used transparently wherever a PSR-3 logger is required, including with third-party libraries. For strict PSR-3 compliance, especially when working with external dependencies, the `PsrLoggerAdapter` ensures that all logging operations conform to the expected interface and behavior.

##### Instantiation:

The `LoggingFacade` instance is not created directly by user code. Instead, its instantiation and lifecycle are managed by the `LoggingKernel`, the central component of the Infrastructure layer. Access to the facade is provided via the `$kernel->logger()` method, ensuring that all internal dependencies are properly configured and that the validation, assembly, and persistence flow for log entries adheres to the module’s architectural standards.

This approach allows developers to interact solely with the public, simplified interface of the facade, abstracting away the complexity of the underlying components.

---

#### Internal Workflow and Operation Flow

1. **Input Creation (`LoggableInput`):**  
   The workflow begins with the instantiation of a `LoggableInput`, a value object serving as a Data Transfer Object (DTO) for raw log request data. This object encapsulates the message, level, channel, context, and timestamp as immutable properties. Its constructor performs basic validation and raises an `InvalidLoggableInputException` if any required fields are missing or invalid.

2. **Domain Validation and Assembly (`LogEntryAssembler`):**  
    The `LogEntryAssembler` receives the `LoggableInput` and is responsible for assembling a fully validated and sanitized `LogEntry`. Rather than validating or sanitizing the input directly, the assembler constructs the relevant value objects (`LogLevel`, `LogMessage`, `LogContext`, `LogChannel`), each of which receives the input data and a `LogSecurityInterface` to perform internal validation and sanitization according to domain rules.  
    - When creating the `LogMessage`, the assembler leverages the security service to sanitize the message in the context of the `LogChannel`. For sensitive channels, the message is additionally masked using the configured `maskToken`.
    - The assembler also ensures that any missing optional fields in the input are filled with default values from configuration (such as default level, channel, or context), resulting in a complete log entry.
    - If any value object fails validation, a `LogEntryAssemblyException` is thrown.

3. **Log Entry Generation (`LogEntry`):**  
   The outcome is an immutable `LogEntry` object, representing a fully validated and sanitized log entry that complies with all domain constraints and is ready for persistence.

4. **Persistence (`Logger`):**  
   The `Logger` is responsible for writing structured log entries to flat files on the local filesystem. It orchestrates the process by resolving the correct file path based on the log entry’s channel or level (using `LogFilePathResolver`), formatting the log entry as a line of text (`LogLineFormatter`), and writing the formatted line to the file (`LogFileWriter`). The `Logger` does not modify the log entry’s content or perform additional validation; it ensures that each entry is persisted in the appropriate file according to the system’s configuration.

5. **Exception Handling:**  
   At any stage of the process, if an error occurs (e.g., invalid log level, message length violation, or storage failure), the relevant exception (`InvalidLoggableInputException`, `LogEntryAssemblyException`, etc.) is thrown. The application layer does not suppress these exceptions, allowing the calling code to manage error handling as appropriate.

This structured workflow ensures that only secure, validated, and complete log entries are persisted, upholding the integrity and reliability of the logging system.

#### Example Usage

```php
// Obtain the logger from the kernel
$logger = $kernel->logger();

// Log a generic message.
$logger->logInput('System loading succeeded.');

// Log an error message
$logger->error('User not found', ['user_id' => 123]);

// Log with an allowed level
$logger->log('debug', 'User not found', []);

// Log with a determined level, custom channel and context
$logger->logInput('Invalid payment request.', 'warning', 'payments', ['amount' => 100.00]);
```

#### Design Benefits

- **Separation of Concerns:**  
  The application layer isolates application code from domain and infrastructure details, promoting maintainability and testability.

- **Consistency:**  
  All logging operations go through the same validation, sanitization, and persistence pipeline, ensuring uniformity across the system.

- **Extensibility:**  
  New log levels, channels, or storage mechanisms can be added with minimal changes to the application interface.

- **Compliance:**  
  Full PSR-3 compatibility allows integration with a wide range of PHP libraries and frameworks.

---

### Infrastructure Layer

The infrastructure layer serves as the integration point between the core logging logic and the external environment, orchestrating the end-to-end flow of log data from validated domain objects to persistent storage. This layer is responsible for the practical implementation of log output, ensuring that log entries are reliably written to the filesystem in a structured and secure manner.

##### Key responsibilities of the infrastructure layer include:

- **Component Orchestration:**  
  Acts as the central hub that wires together all lower-level components—such as file writers, path resolvers, and formatters—ensuring seamless collaboration between domain logic and I/O operations.

- **Bootstrapping:**  
  The `LoggingKernel` class is solely responsible for bootstrapping the entire logging subsystem. It handles dependency injection, configuration loading, and instantiates all required services, making the logging module easy to set up and integrate into any PHP application.

- **File Handling:**  
  Manages the creation, formatting, and writing of log files. This includes resolving appropriate file paths based on log channels and levels, formatting log entries into standardized text lines, and performing atomic file writes with proper error handling and directory management.

- **Adapters and Integration:**  
  Supplies adapters (such as the PSR-3 logger adapter) to ensure compatibility with external libraries and frameworks. These adapters translate standardized logging calls into the module’s internal workflow, maintaining compliance with industry standards while leveraging the module’s robust validation and sanitization features.

- **Error Handling and Reliability:**  
  Implements comprehensive error detection and reporting mechanisms, converting low-level I/O failures into meaningful exceptions and ensuring that logging failures are never silent. This guarantees that the application is always informed of any issues affecting log persistence.

By encapsulating all integration and I/O concerns, the infrastructure layer abstracts away the complexities of file management and system interaction, allowing the rest of the application to focus on business logic while relying on a consistent, secure, and extensible logging foundation.

---

#### LoggingKernel

The `LoggingKernel` serves as the central composition root and orchestrator for the Logging Module. Its primary role is to bootstrap, configure, and wire all domain, application, and infrastructure components necessary for robust and secure logging. The kernel abstracts the entire setup process, exposing only the finalized logging facades and adapters to the consuming application.

##### Responsibilities

* **Centralized Composition:**
  Instantiates, wires, and manages the lifecycle of all logging components—ensuring correct dependency injection, configuration propagation, and isolation of domain security logic.

* **Configuration-Driven Instantiation:**
  Receives a `LoggingConfigInterface` implementation, extracting all required configuration objects (log directory path, sanitization/validation/assembler configs) and enforcing their correct distribution throughout the module.

##### Construction and Boot Process

Upon instantiation, the kernel executes a two-stage bootstrapping process:

1. **Configuration Bootstrapping (`bootConfig`):**
  Reads and stores all critical configuration values:

    * **Base Log Directory:** The root filesystem path for log storage.
    * **Sanitization Configuration:** Rules and policies for masking sensitive information.
    * **Validation Configuration:** Key and value validation requirements for logs.
    * **Assembler Configuration:** Default values and conventions for assembling log entries.

2. **Component Initialization (`bootComponents`):**
   Sequentially constructs all required components, wiring dependencies as follows:

   * **Domain Security Services:**

     * Instantiates a `Sanitizer` (with sanitization config) and a `Validator` (with validation config).
     * Composes these into a `LogSecurity` service, encapsulating all domain-level security logic.

   * **Value Objects and Infrastructure:**

     * Instantiates a `LogDirectory` value object using the sanitized and validated log directory path.
     * Creates a `LogEntryAssembler`, injecting both the security service and assembler config, ensuring proper log entry construction.

   * **Path and Formatting:**

     * Instantiates a `LogFilePathResolver`, providing path resolution rooted at the validated log directory.
     * Creates a stateless `LogLineFormatter` (standardized date formatting).
     * Instantiates a `LogFileWriter` (managing file write operations, using default or system-level permissions).

   * **Core Logger:**

     * Assembles the main `Logger` service by injecting the path resolver, formatter, and writer.
     * This service handles the full pipeline: determining file paths, formatting entries, and persisting log data.

   * **Application Facade and Adapter:**

     * Instantiates a `LoggingFacade`, injecting the logger, log entry assembler, and PSR adapter.
     * Constructs a `PsrLoggerAdapter` to provide full PSR-3 compatibility, enabling integration with third-party libraries and frameworks.

    All these components are privately retained, exposing only the appropriate interfaces for external consumption.

##### Public API

The kernel exposes the following core methods for integration with the broader application:

* **`logger(): LoggingFacadeInterface`**
  Returns the configured logging facade, abstracting all internal wiring. Use this as the main entry point for logging operations within the application.

* **`psrLogger(): PsrLoggerInterface`**
  Returns a PSR-3 compliant logger adapter. Use this when integrating with external libraries or frameworks that expect a `Psr\Log\LoggerInterface`.

##### Architectural and Design Notes

* **Decoupled Composition:**
  All configuration and security policies are injected, not hardcoded, ensuring flexibility, testability, and adherence to clean architecture principles.

* **Immutability and Safety:**
  All configuration and component properties are `readonly`, guaranteeing that no mutation occurs after kernel construction. This enforces stability and predictability.

* **Error Propagation and Fail-Fast:**
  Any misconfiguration or validation failure during component construction will propagate as an exception, preventing partial or unsafe logging setups.

##### Usage Example

```php
$config = new LoggingConfig('/var/log/myapp', ...);
$kernel = new LoggingKernel($config);
$logger = $kernel->logger(); // LoggingFacadeInterface
$psrLogger = $kernel->psrLogger(); // PsrLoggerInterface
```

After initialization, the consuming application interacts exclusively with the logging facade or the PSR logger interface, remaining agnostic of the internal component wiring, security concerns, or file system operations.

---

#### LogEntryAssembler

The `LogEntryAssembler` is the pivotal component responsible for converting generic, potentially untrusted log input into rigorously validated and sanitized domain log entries. By implementing the `LogEntryAssemblerInterface`, it ensures that every aspect of a log event adheres to the application’s security, consistency, and integrity policies.

##### Responsibilities

* **Input Transformation:**
  Accepts a `LoggableInputInterface` instance (a DTO-like contract) and systematically converts it into a structured, immutable `LogEntry` domain object.

* **Domain Validation and Sanitization:**
  The assembler delegates validation and sanitization to specialized domain Value Objects (`LogLevel`, `LogMessage`, `LogContext`, `LogChannel`), each of which uses the injected `LogSecurityInterface` for their own security checks. Specifically:

  * **Level:** Passed to the `LogLevel` value object, which validates against allowed/custom levels using domain security rules.
  * **Message:** Passed to the `LogMessage` value object, which sanitizes and validates the message; the assembler may also pre-mask the message if the channel is sensitive.
  * **Context:** Passed to the `LogContext` value object, which recursively sanitizes and validates all keys and values, masking sensitive data as necessary.
  * **Channel:** Passed to the `LogChannel` value object, which enforces naming and safety rules.
  * **Timestamp:** Propagated as-is from the input; no sanitization or validation is applied at this stage.

* **Fallback Policies:**
  Where input is missing or invalid, the assembler leverages default values from the injected `AssemblerConfigInterface` (e.g., default log level, channel, or context), ensuring the logging pipeline remains robust even under incomplete input.

* **Exception Handling:**
  Any construction failure of domain value objects (such as invalid keys, unsafe values, or unknown log levels) is caught and rethrown as a `LogEntryAssemblyException`, providing a unified failure mode to consumers.

##### Assembly Workflow

1. **LogLevel Construction:**
   Attempts to instantiate a `LogLevel` using the input’s value. If this fails due to invalidity and a default level is configured, it falls back and retries with the default level.

2. **LogMessage Construction:**
   Builds a `LogMessage` after potentially sanitizing the message based on the channel. If the channel is deemed sensitive, the message is replaced by the configured mask token to prevent data leakage.

3. **LogContext Construction:**
   Builds a `LogContext` value object, defaulting to the preconfigured context if none is provided. The context is fully sanitized and validated against domain rules.

4. **LogChannel Construction:**
   Instantiates a `LogChannel`, falling back to the default channel when necessary, and validates it according to domain and file system safety requirements.

5. **Timestamp Propagation:**
   Retrieves the timestamp directly from the input, with fallback policies handled upstream if required.

6. **Domain Object Creation:**
   If all components are valid, assembles them into a new, immutable `LogEntry`, ready for logging.

##### Configuration and Customization

* **AssemblerConfigInterface:**
  Supplies all necessary defaults (log level, context, channel), as well as any custom log level definitions and the mask token used for sensitive masking.

* **Mask Token Policy:**
  The mask token is used to replace messages when the associated channel is deemed sensitive, ensuring compliance with security requirements.

##### Public API

* **`assembleFromInput(LoggableInputInterface $input): LogEntryInterface`**
  Main entry point. Converts a loggable input to a validated and sanitized `LogEntry`. Throws `LogEntryAssemblyException` if any stage fails.

##### Internal Methods Overview

* **`buildLogLevel()`**:
  Assembles a `LogLevel` with validation and fallback.

* **`buildLogMessage()`**:
  Sanitizes and assembles the message, masking as needed.

* **`buildLogContext()`**:
  Sanitizes and validates all context key-value pairs.

* **`buildLogChannel()`**:
  Validates channel name with fallback.

* **`sanitizeMessageByChannel()`**:
  Masks the message if the channel is sensitive; otherwise, returns the original message.

##### Usage Flow

1. The application or facade layer provides a `LoggableInputInterface` to the assembler.
2. The assembler constructs each domain Value Object (`LogLevel`, `LogMessage`, `LogContext`, `LogChannel`), applying defaults from configuration where needed. Each Value Object internally delegates validation and sanitization to the domain security services.
3. If construction is successful, a valid, immutable `LogEntry` is returned and ready for logging.
4. If any part fails (due to invalid or unsafe data), a `LogEntryAssemblyException` is thrown, signaling an assembly error to the caller.

##### Design Guarantees

* All produced log entries are sanitized, validated, and immutable.
* Sensitive information is consistently masked or excluded, including context data and log messages associated with sensitive channels.
* The logging pipeline remains stable and predictable, regardless of input source or completeness.

---

#### Logger

The `Logger` is the core infrastructure service responsible for persisting structured log entries to flat files on the local filesystem. By implementing the `LoggerInterface`, it abstracts and orchestrates the final stage of the logging pipeline, ensuring each entry is reliably and consistently recorded.

##### Responsibilities

* **Orchestration:**
  Coordinates the sequence of log file path resolution, log line formatting, and file writing, relying on injected service collaborators.

* **Abstraction:**
  Shields higher application layers (such as `LoggingFacade` and `PsrLoggerAdapter`) from file system and formatting details, offering a simple contract for structured log persistence.

##### Logging Workflow

When `log(LogEntryInterface $entry)` is called:

1. **File Path Resolution:**
   Invokes the `LogFilePathResolver` to determine the appropriate file path for the log entry, typically organizing files by channel (folder) and level (filename).

2. **Line Formatting:**
   Uses the `LogLineFormatter` to serialize the structured `LogEntry` into a string suitable for flat-file storage.

3. **File Writing:**
   Delegates to the `LogFileWriter`, which appends the formatted line to the resolved file, handling all file system operations and write modes.

4. **Error Handling:**
   If any stage fails (for example, due to I/O errors or path resolution issues), exceptions such as `LogWriteException` may be thrown. Critical failures may optionally be reported via `error_log()` to ensure issues are surfaced.

##### Design and Usage Notes

* **Dependency Injection:**
  All collaborators (`LogFilePathResolver`, `LogLineFormatter`, and `LogFileWriter`) are injected via the constructor, supporting testability and adherence to SOLID principles.

* **Immutable Log Entries:**
  Assumes that received `LogEntryInterface` objects are fully constructed, sanitized, and validated—no further mutation or checking is performed at this stage.

* **Integration Point:**
  Used internally by both the `LoggingFacade` (application layer) and `PsrLoggerAdapter` (PSR-3 adapter), ensuring a consistent, centralized log output process across all entry points.

---

#### LogFilePathResolver

The `LogFilePathResolver` is a utility component tasked with determining the absolute file path where a given log entry should be persisted. It encapsulates all path construction logic, ensuring log files are organized consistently based on channel and level.

##### Responsibilities

* **Path Construction:**
  Assembles the full file path for a log entry, following the pattern:

  ```
  {baseLogPath}/{channel}/{level}.log
  ```

  If the log entry does not specify a channel, the path defaults to:

  ```
  {baseLogPath}/{level}.log
  ```

* **Directory Normalization:**
  Normalizes constructed paths, collapsing redundant separators and ensuring compatibility with the underlying operating system.

* **Immutability and Safety:**
  The base log directory is encapsulated in an immutable, validated `LogDirectory` value object, guaranteeing that all generated paths are rooted in a secure, pre-validated location.

* **Non-intrusive:**
  Does **not** create directories or files—its sole responsibility is path computation.

##### Typical Examples

* Channel: `"auth"`, Level: `"error"`
  Path: `/var/log/myapp/auth/error.log`

* Channel: `"application"`, Level: `"info"`
  Path: `/var/log/myapp/application/info.log`

* No channel, Level: `"warning"`
  Path: `/var/log/myapp/warning.log`

##### Method Overview

* **`__construct(LogDirectory $logDirectory)`**
  Receives the validated base log directory as a value object.

* **`resolve(LogEntryInterface $entry): string`**
  Returns the absolute, normalized path for the target log file based on the log entry’s channel and level.

* **`normalizePath(string $path): string`**
  Utility method that ensures all file paths are normalized, removing redundant separators and unnecessary trailing slashes (except for root).

##### Integration Notes

* Intended for use by the `Logger` service, abstracting away all filesystem path construction details and ensuring consistent file organization.
* All returned paths are safe, normalized, and compatible with the underlying operating system.

---

#### LogLineFormatter

The `LogLineFormatter` is a stateless utility responsible for serializing a structured `LogEntry` domain object into a single, human-readable text line for log file output. It enforces a standardized format to ensure all logs are clear, consistent, and easily parsed by both humans and tools.

##### Default Log Line Format

```
[<timestamp>] [<channel>] [<LEVEL>] <message> | Context: <context_json>
```

* **Timestamp:**
  Rendered in ISO 8601 format (`DateTimeInterface::ATOM`), ensuring time zone and precision consistency.

* **Channel:**
  The log channel, enclosed in brackets.

* **Level:**
  Uppercase log level (e.g., `INFO`, `ERROR`).

* **Message:**
  The sanitized log message.

* **Context:**
  If non-empty, context data is appended as a JSON-encoded string (with Unicode and slashes unescaped), prefixed by `| Context:`.

##### Example Output

```
[2025-06-20T16:42:05-03:00] [application] [INFO] User logged in. | Context: {"user":"tester"}
```

##### Responsibilities

* **Consistent Serialization:**
  Guarantees that every log line conforms to the expected schema, facilitating downstream parsing, searching, and monitoring.

* **Stateless Operation:**
  Maintains no internal state; formatting is purely functional.

* **Graceful Handling of Empty Context:**
  Omits the context segment when no additional data is present, reducing log noise.

##### Method Overview

* **`format(LogEntryInterface $entry): string`**
  Accepts a `LogEntryInterface` and returns a single, formatted string representing the log entry, ready to be written to a file.

##### Integration Notes

* Typically used by the `Logger` service to serialize entries just before file writing.
* The output format is intentionally human-friendly but also suitable for automated log collection tools.

---

#### LogFileWriter

The `LogFileWriter` abstracts the low-level file-writing operation for log lines, ensuring reliable and atomic persistence of log data on the local filesystem. It isolates file system concerns from the rest of the logging module and guarantees that required directories exist before attempting to write.

##### Responsibilities and Internal Workflow

* **Directory Management:**
  Before writing, the writer extracts the parent directory from the provided file path. If the directory does not exist, it attempts to create it recursively using `mkdir` with 0777 permissions. On failure, it logs an error via `error_log()` and throws a `LogWriteException`.

* **Atomic Appending:**
  Once the directory is ensured, the writer appends the log line to the specified file using `file_put_contents` with the `FILE_APPEND` flag. This guarantees that each log entry is added to the end of the file without overwriting previous entries. If the write operation fails, an error is logged and a `LogWriteException` is thrown.

* **Error Reporting:**
  At every stage—directory creation or file writing—any failure is both recorded via `error_log()` for system visibility and escalated to the calling layer through a domain-specific exception, ensuring that failures are not silently ignored.

##### Method Overview

* **`write(string $filepath, string $line): void`**
  Main entry point. Ensures the directory exists and appends the provided log line to the file. Throws on failure.


##### Performance Notes

* The implementation is robust and suitable for most applications and moderate log volumes.
* For scenarios with very high write rates, the class can be extended to use buffered writes or persistent stream handles for increased performance.

##### Design Notes

* **Single Responsibility:**
  Does not perform formatting or path resolution—these concerns are handled by other services in the module.
* **Resilience:**
  Proactively handles file system edge cases to reduce log loss risk and facilitate operational troubleshooting.

---

#### PsrLoggerAdapter

The `PsrLoggerAdapter` provides a seamless bridge between the internal, domain-driven logging infrastructure and any library, framework, or package that expects a PSR-3 compatible logger. By conforming to the PSR-3 interface, it guarantees that every log message, regardless of its source, is subject to the same validation, sanitization, and formatting rules that apply to the entire application. This adapter ensures uniformity, security, and reliability for all logging activity across project boundaries.

#### Construction

The adapter is constructed with two dependencies: a `LoggerInterface`, responsible for persisting log entries, and a `LogEntryAssemblerInterface`, which transforms raw loggable input (level, message, context) into validated domain `LogEntry` objects. This arrangement ensures that every log routed through the adapter passes through the same strict domain policies as internal application logs.

#### Key Methods

The adapter implements a local contract, `PublicContracts\Logging\PsrLoggerInterface`, which mirrors the main methods of the official PSR-3 `Psr\Log\LoggerInterface`. This design guarantees full interface compatibility with PSR-3, while allowing the application to remain decoupled from any external package. All PSR-3 standard methods are available: `log`, `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, and `debug`. Each severity-specific method delegates to the generic `log` method, passing the appropriate log level string. The adapter interface thus matches the expectations of any PSR-3 consumer.

#### Logging Workflow

* Interpolates message placeholders according to PSR-3, replacing tokens (such as `{user}`) with corresponding scalar or stringable values from the context array.
* Constructs a `LoggableInput` object using the interpolated message, the specified log level, and the remaining context.
* Passes the input to the assembler, which applies domain validation and sanitization, returning a secure, validated `LogEntry`.
* Delegates the assembled entry to the logger, which handles formatting and file writing.
* Handles errors as required by the PSR-3 specification, such as throwing `InvalidArgumentException` for unsupported log levels.

#### Usage

To use the adapter, obtain an instance from the logging kernel and pass it to any component or library expecting a PSR-3 logger:

```php
$psrLogger = $kernel->psrLogger();
$psrLogger->info('User {user} created', ['user' => 'felipe', 'ip' => '127.0.0.1']);
```

This guarantees that all messages are processed, validated, and persisted consistently, regardless of their origin.

#### Integration and Design Notes

* Ensures all logs, including those from third-party code, are subject to centralized validation, sanitization, and formatting.
* Implements a local PSR-3 compatible contract, avoiding hard dependencies on external packages while remaining fully interoperable.
* Fully PSR-3 compatible, supporting drop-in use with frameworks and libraries in the PHP ecosystem.
* Preserves non-interpolated context data for structured logging or audit trails.
* Stateless, supporting safe concurrent use throughout the application.

---

### Security Layer

The Security layer is composed of two core modules: **Sanitizing** and **Validation**. These modules provide centralized, configuration-driven routines for input sanitization and validation, ensuring that all log data complies with strict security and integrity policies.

Both modules are initialized and orchestrated by the `SecurityKernel`, which acts as the entry point for all security-related operations within the logging domain. The kernel exposes two main methods: `validator()` and `sanitizer()`, which return instances of `ValidationFacade` and `SanitizingService`, respectively. These classes implement the `ValidatorInterface` and `SanitizerInterface` contracts defined in the Domain layer.

This design guarantees that value objects and assemblers consistently enforce security rules through a unified, maintainable interface, and allows for easy extension or replacement of validation and sanitization logic as needed.

---

#### Sanitizing Module

The `Sanitizing` module provides a comprehensive set of services, contracts, and utilities for masking and handling sensitive data in PHP applications. Its primary purpose is to identify, sanitize, and partially mask sensitive strings, array values, and object properties before data is logged, stored, or exposed, ensuring compliance with security standards and privacy policies (e.g., GDPR, LGPD).

This module is designed to be **extensible, dependency-injectable, and configurable**, supporting recursive sanitization, custom sensitivity patterns, and robust circular reference detection.

##### Features:

* **Sensitive Data Detection**: Identifies sensitive keys and value patterns, including credentials, tokens, personal data, and more.
* **Partial and Full Sanitization**: Masks only the sensitive fragments within strings or data structures, preserving non-sensitive data.
* **Configurable Mask Token**: Supports custom mask tokens, with validation and normalization.
* **Recursive Array/Object Handling**: Processes deeply nested arrays and objects, including circular reference detection.
* **Unicode Normalization**: Ensures reliable key and value matching, regardless of input encoding.
* **Extensible Contracts**: Replaceable detectors and sanitizers for custom project requirements.

##### Directory Structure

```
Sanitizing/
├── Contract/
│   ├── ArraySanitizerInterface.php
│   ├── CircularReferenceDetectorInterface.php
│   ├── ObjectSanitizerInterface.php
│   ├── SensitiveKeyDetectorInterface.php
│   ├── SensitivePatternDetectorInterface.php
│   └── StringSanitizerInterface.php
├── Detector/
│   ├── CircularReferenceDetector.php
│   ├── SensitiveKeyDetector.php
│   └── SensitivePatternDetector.php
├── Service/
│   ├── ArraySanitizer.php
│   ├── CredentialPhraseSanitizer.php
│   ├── ObjectSanitizer.php
│   ├── SanitizingService.php
│   ├── SensitivePatternSanitizer.php
│   └── StringSanitizer.php
└── Tools/
    ├── MaskTokenValidator.php
    └── UnicodeNormalizer.php
```

##### Main Components

###### **Contracts**

All core functionality is defined via interfaces under `Contract/`, enabling dependency injection and custom implementations:

* **ArraySanitizerInterface**: Recursive array masking.
* **CircularReferenceDetectorInterface**: Safely tracks object/array cycles during recursion.
* **ObjectSanitizerInterface**: Object property extraction and masking.
* **SensitiveKeyDetectorInterface**: Heuristic key matching (e.g., "password", "api\_key").
* **SensitivePatternDetectorInterface**: Regex or pattern-based value detection.
* **StringSanitizerInterface**: Partial masking for sensitive strings.

###### **Detectors**

* **SensitiveKeyDetector**: Identifies sensitive keys (such as "password", "token", etc.) in arrays and objects using configurable heuristics, Unicode normalization, and fuzzy matching.
* **SensitivePatternDetector**: Detects sensitive values based on regular expression patterns, enabling the identification of confidential data such as credit card numbers, CPFs, or tokens.
* **CircularReferenceDetector**: Tracks object references during recursive sanitization to prevent infinite loops and accurately flag circular references.

###### **Services**

* **ArraySanitizer**: Recursively sanitizes arrays, masking sensitive values detected by key or pattern, and handling nested structures with depth control.
* **ObjectSanitizer**: Recursively extracts and sanitizes object properties, using detectors to identify and mask sensitive data within objects. Implements circular reference detection to prevent infinite loops during the sanitization of nested structures.
* **StringSanitizer**: Performs partial sanitization of strings, masking only sensitive fragments and credential phrases using pattern detection and Unicode normalization.
* **CredentialPhraseSanitizer**: Specializes in detecting and masking credential-like phrases within strings, such as "password: value" or similar constructs.
* **SensitivePatternSanitizer**: Focuses on masking substrings within values that match configured sensitive patterns, providing fine-grained control over data exposure.
* **SanitizingService**: Orchestrates the sanitization process by coordinating detectors and sanitizers, providing a unified interface for comprehensive input sanitization across arrays, objects, and strings.

###### **Tools**

* **MaskTokenValidator**: Enforces mask token security and formatting.
* **UnicodeNormalizer**: Standardizes Unicode input for reliable matching.

###### **Limitations**

* Detection is as strong as the patterns and keys provided/configured.
* Extremely nested or large data structures may require tuning of maximum recursion/depth.

---

##### CircularReferenceDetector

The `CircularReferenceDetector` is a utility that ensures safe and reliable recursive sanitization of complex data structures. Its primary responsibility is to **detect and prevent infinite recursion** caused by circular references in arrays and objects.

This implementation achieves high accuracy and eliminates false positives by performing true variable identity checks, rather than relying on potentially ambiguous proxies.

###### **Responsibilities**

  * Tracks object instances using `SplObjectStorage` for optimal performance and accuracy.
  * Tracks array references and verifies their true identity using a temporary marker technique.
  * Detects when a specific array or object instance has already been visited in the current sanitization cycle.
  * Marks data as visited to prevent redundant processing and stack overflows.
  * Resets its internal state at the beginning of each new sanitization run.
  * Provides a standardized marker to identify where circular references were found.

###### **How It Works**

The detector's accuracy stems from using different, optimized strategies for objects and arrays:

  * **For Objects**: It leverages the native `SplObjectStorage` class. This data structure is specifically designed to store and check for the existence of unique object instances in a highly efficient and correct manner, eliminating the risk of ID collision that can occur with `spl_object_id`.
  * **For Arrays**: Since arrays cannot be used as keys in standard hash maps, it employs a robust identity-check mechanism. It temporarily adds a unique, non-colliding key to one array and then checks for that key's presence in another array reference. This reliably confirms if both variables point to the same data in memory. The key is immediately removed after the check to ensure the original data structure is not mutated.

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Detector\CircularReferenceDetector;

$detector = new CircularReferenceDetector();

$data = [/* complex array or object structure, possibly circular */];

// At the start of each new sanitization run:
$detector->reset();

if ($detector->isCircularReference($data)) {
    // Handle the circular reference (e.g., mask or skip).
    $output = $detector->handleCircularReference();
} else {
    $detector->markSeen($data);
    // Proceed with further processing...
}
```

###### **Typical Workflow in Sanitization**

1.  **Start of Recursion**: `reset()` is called to clear any prior state.
2.  **Before Processing an Array/Object**: `isCircularReference()` checks if the exact structure has already been seen.
3.  **If Circular**: `handleCircularReference()` provides a standardized marker to replace the value.
4.  **If Not Circular**: `markSeen()` records the structure, and recursion proceeds safely.

###### **Security and Robustness**

  * Provides a strong guarantee against infinite recursion by using memory identity-based checks instead of unreliable proxies.
  * Eliminates the risk of false positives that can occur with simpler ID-based tracking mechanisms, ensuring that only true circular references are flagged.
  * Ensures data integrity by performing non-destructive checks; the temporary marker used for array identity verification is immediately removed.
  * Enables the safe traversal and masking of real-world data, including user input, configuration trees, and complex entity graphs.

###### **Integration**

It is designed to be used as a dependency of high-level sanitizers (such as `ArraySanitizer` and `ObjectSanitizer`) and implements the `CircularReferenceDetectorInterface` for interchangeability and testability.

---

##### SensitiveKeyDetector

The `SensitiveKeyDetector` is responsible for identifying sensitive keys in key-value pairs to ensure that critical data—such as passwords, tokens, and personal identifiers—is properly masked during logging or data processing.

###### **Features**

* **Multilingual & Locale-Aware:** Supports detection in multiple languages (e.g., English, Portuguese) by default and can be extended with project-specific sensitive keys.
* **Advanced Heuristics:** Uses Unicode normalization, lowercasing, fuzzy transforms (removal of underscores, dashes, special characters), and vowel removal to maximize detection accuracy across diverse input formats.
* **Configurable:** Accepts custom sensitive keys during instantiation, merging them with a robust default set.
* **Optimized for Recursion:** Designed for high-performance usage in recursive sanitization workflows.

###### **Responsibilities**

* Detects whether a provided key should be considered sensitive based on multiple normalization and transformation strategies.
* Prepares an internal registry of sensitive keys using all supported detection heuristics.
* Validates custom keys for correct format and absence of control characters.

###### **Typical Usage**

```php
use Logging\Security\Sanitizing\Detector\SensitiveKeyDetector;

$detector = new SensitiveKeyDetector([
    'custom_sensitive_field', 'new_sensitive_field'
]);

if ($detector->isSensitiveKey('API_KEY')) {
    // This key will be masked during sanitization
}
```

###### **Detection Strategy**

* **Lowercasing**: Case-insensitive comparison.
* **Unicode Normalization**: Handles accents and diacritics.
* **Fuzzy Transform**: Removes separators and common non-alphanumerics.
* **Vowel Removal**: Improves match resilience to obfuscated or shortened keys.

###### **Security and Audit**

* Prevents accidental exposure of sensitive fields by maximizing detection accuracy.
* Exposes the prepared keys for debugging, validation, or audit purposes via `getPreparedKeys()`.

###### **Integration**

This class is typically used as a dependency of string, array, or object sanitizers, and implements the `SensitiveKeyDetectorInterface` for interchangeability and testability.

---

##### SensitivePatternDetector

The `SensitivePatternDetector` is responsible for identifying sensitive values within data by applying configurable regular expression patterns. This mechanism enables the detection and masking of structured sensitive information such as personal identifiers (e.g., CPF), credit card numbers, and email addresses during logging or processing operations.

###### **Features**

* **Default and Custom Patterns:**
  Merges a robust default set of sensitive data patterns (for CPF, credit card numbers, email addresses, etc.) with any additional custom patterns provided at instantiation.
* **Validated Patterns:**
  Validates the syntax of all regular expressions at construction time, ensuring configuration safety.
* **Extensible:**
  Easily extended with project-specific or locale-specific regex patterns to cover additional sensitive data types.
* **Efficient Matching:**
  Optimized for high-throughput environments—applies all patterns in sequence, returning on the first match.

###### **Responsibilities**

* Detects whether a value matches any configured sensitive data pattern using regular expressions.
* Restricts detection to string values only; non-string values are never marked as sensitive by pattern.
* Provides access to the active set of patterns for auditing and debugging purposes.
* Throws precise configuration exceptions if any invalid pattern is supplied.

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Detector\SensitivePatternDetector;

$customPatterns = [
    '/\\b\\d{4}-\\d{4}-\\d{4}-\\d{4}\\b/', // Example: alternate credit card format
];
$detector = new SensitivePatternDetector($customPatterns);

if ($detector->matchesSensitivePatterns('123.456.789-09')) {
    // Value is considered sensitive (matches CPF pattern)
}
```

###### **Security and Audit**

* Ensures robust protection against accidental exposure of structured personal or credential data by applying up-to-date, project-relevant patterns.
* All pattern logic is transparent and auditable via `getPatterns()`.

###### **Integration**

Designed for use as a dependency in higher-level sanitizers, such as string or array sanitizers, and implements `SensitivePatternDetectorInterface` for flexibility in testing and replacement.

---

##### CredentialPhraseSanitizer

The `CredentialPhraseSanitizer` is responsible for detecting and masking credential phrases within free-form text, such as log messages. It targets values clearly associated with sensitive keys (e.g., `"password: mySecret"` or `"token = abc123"`), supporting a wide range of separator formats and a configurable word tolerance between the key and its value.

###### **Features**

  * **Sensitive Key Detection:**
    Integrates with a `SensitiveKeyDetectorInterface` to obtain and use all normalized representations of sensitive keys.
  * **Flexible Separator Support:**
    Uses a default list of separators (`:`, `=`, `is`, `foi`, `é`, etc.) and allows for valid custom separators to be merged.
  * **Bidirectional Analysis:**
    Detects and masks both `key → separator → value` (forward) and `value ← separator ← key` (backward) phrases.
  * **Tolerance for Intermediate Words:**
    Allows for a fixed number of intermediate words (currently **3**) between the key, separator, and value, matching phrases like `"password is now: mySecret"`.
  * **Preserves Formatting:**
    Masks only the sensitive value, preserving original whitespace, punctuation, and phrase structure by reconstructing the surrounding components.

###### **Workflow**

The sanitization process follows a specific "forward-first" order to prevent incorrect masking:

1.  When `sanitizePhrase` is called, it first attempts to find and mask all **forward** phrases (`key → value`).
2.  If one or more forward matches are found and replaced, the process stops, and the sanitized string is returned.
3.  Only if **no** forward matches are found does the sanitizer proceed to find and mask **backward** phrases (`value ← key`).
4.  The final string is then returned.

###### **Key Logic and Configuration**

  * **Default Separators:** The class includes a built-in list of common separators: `':', '=', '-', '->', '=>', '|', '/', ';', ',', 'is', 'foi', 'é'`.
  * **Custom Separators:** You can provide an array of custom separators during construction. These are merged with the default list.
  * **Separator Validation:** All custom separators are strictly validated. They must be non-empty strings and **must not contain any whitespace**. An invalid separator will throw an `InvalidSanitizationConfigException`.
  * **Intermediate Word Limit:** The number of words allowed between a key and its value is defined by the internal constant `MAX_INTERMEDIATE_WORDS` (set to `3`).

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Service\CredentialPhraseSanitizer;
use Logging\Security\Sanitizing\Detector\SensitiveKeyDetector;

// Key detector is required
$keyDetector = new SensitiveKeyDetector(['password', 'secret']);

// Custom separators are optional and are merged with the defaults
$sanitizer = new CredentialPhraseSanitizer($keyDetector, ['is:', 'was']);

$input = 'the old secret was: hunter2';
$masked = $sanitizer->sanitizePhrase($input, '[REDACTED]');
// Output: 'the old secret was: [REDACTED]'
```

###### **Integration**

Designed for use by higher-level string sanitizers, the `CredentialPhraseSanitizer` is suitable for processing log messages, configuration files, or any free-form text where credentials might appear in natural language.

###### **Security Considerations**

  * Effectively prevents accidental exposure of credentials, even when logged in non-standard formats. The forward-first logic helps reduce false positives where a value might resemble a sensitive key.
  * The set of sensitive keys, provided by the `SensitiveKeyDetectorInterface`, is the most critical factor for effective sanitization.

---

##### SensitivePatternSanitizer

The `SensitivePatternSanitizer` is responsible for **masking all occurrences of sensitive data patterns** within strings, using a configurable set of regular expressions.
It is designed to efficiently and flexibly perform partial sanitization of any substring that matches known or custom patterns, such as PII (Personally Identifiable Information), credentials, or other data signatures.

###### **Features**

* **Pattern-Based Masking:**
  Uses the configured `SensitivePatternDetectorInterface` to retrieve all active patterns for sensitive data (e.g., CPF, credit card, emails, and custom signatures).
* **Partial and Flexible:**
  Masks only the fragments of the string that match sensitive patterns, preserving the rest of the content for context and readability.
* **Unicode Normalization:**
  Normalizes input strings before matching to ensure robust and consistent detection, regardless of input encoding or locale.
* **Unified Matching:**
  Dynamically composes a unified regular expression from all configured patterns, supporting efficient single-pass detection and masking.
* **Extensible:**
  Easily integrates with custom or extended pattern detectors for project-specific needs.

###### **Responsibilities**

* Orchestrates normalization, pattern extraction, and replacement operations.
* Ensures only known sensitive patterns are masked, reducing false positives.
* Supports efficient, high-throughput use cases (e.g., logging pipelines).

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Service\SensitivePatternSanitizer;
use Logging\Security\Sanitizing\Detector\SensitivePatternDetector;
use Logging\Security\Sanitizing\Tools\UnicodeNormalizer;

$patternDetector = new SensitivePatternDetector([...]);
$unicodeNormalizer = new UnicodeNormalizer();

$sanitizer = new SensitivePatternSanitizer($patternDetector, $unicodeNormalizer);

$input = "User CPF: 123.456.789-09, email: user@example.com";
$masked = $sanitizer->sanitizePatterns($input, '[MASKED]');
// Output: "User CPF: [MASKED], email: [MASKED]"
```

###### **Integration**

Typically used by higher-level string or array sanitizers, and may be composed with other masking and detection services for layered protection.

###### **Security Considerations**

* Patterns should be reviewed and updated regularly to reflect new types of sensitive data.
* Does not alter non-matching content, minimizing the risk of over-masking or loss of context.

---

##### StringSanitizer

The `StringSanitizer` is responsible for **partial sanitization of strings** by masking only sensitive fragments and credential phrases, while preserving the remaining content for context and traceability.
It combines advanced phrase analysis with pattern-based detection, making it suitable for applications where both explicit credentials and generic sensitive data may appear within strings.

###### **Features**

* **Credential Phrase Masking:**
  Identifies and masks sensitive credential phrases (e.g., "password: 123", "token = abc") using advanced contextual analysis, supporting flexible word order and custom separators.
* **Pattern-Based Masking:**
  Detects and masks any substring matching known or custom sensitive data patterns (e.g., CPF, emails, credit card numbers).
* **Unicode Normalization:**
  Normalizes all input to ensure consistent detection and masking across various locales and encodings.
* **Configurable Mask Token:**
  Applies the provided mask token exactly as received, supporting project-specific masking strategies.
* **Compositional Design:**
  Integrates with dedicated phrase and pattern sanitizers, allowing easy extension and replacement.

###### **Responsibilities**

* Orchestrates the detection and masking of both credential phrases and sensitive data patterns.
* Ensures partial masking, leaving non-sensitive fragments intact.
* Normalizes and trims input before applying sanitization routines.

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Service\StringSanitizer;
use Logging\Security\Sanitizing\Service\SensitivePatternSanitizer;
use Logging\Security\Sanitizing\Service\CredentialPhraseSanitizer;
use Logging\Security\Sanitizing\Tools\UnicodeNormalizer;

// Assume dependencies are configured and injected as needed
$stringSanitizer = new StringSanitizer(
    new SensitivePatternSanitizer(...),
    new CredentialPhraseSanitizer(...),
    new UnicodeNormalizer()
);

$input = 'API key: 1234567890abcdef, email: user@example.com';
$masked = $stringSanitizer->sanitizeString($input, '[MASKED]');
// Output: 'API key: [MASKED], email: [MASKED]'
```

###### **Integration**

Implements `StringSanitizerInterface` and is typically used within higher-level sanitization services for log messages, user input, or data exports.

###### **Security Considerations**

* Effectively reduces risk of sensitive data leakage by masking only detected fragments, preserving auditability and debuggability of logs and output.
* Relies on up-to-date phrase and pattern definitions for best coverage; review and update regularly.

---

##### ObjectSanitizer

The `ObjectSanitizer` is responsible for **recursively sanitizing objects** for secure logging or data exposure. It systematically converts an object into a sanitized associative array by traversing its public properties, applying masking and sanitization rules, and handling complex structures like nested arrays, recursion limits, and circular references.

###### **Features**

  * **Public Property Traversal:**
    Sanitizes objects by converting them into associative arrays based on their **public properties**, as determined by `get_object_vars()`.
  * **Class Name Identification:**
    The resulting sanitized array always includes an `object_class` key containing the name of the original object's class.
  * **Sensitive Property Masking:**
    Entirely masks values of properties whose names are detected as sensitive by a `SensitiveKeyDetectorInterface`.
  * **Partial String Sanitization:**
    Uses a `StringSanitizerInterface` to sanitize fragments within string property values.
  * **Array and Nested Object Support:**
    Recursively processes arrays and nested objects found in properties, applying the same sanitization policies.
  * **Recursion Depth Control:**
    Enforces a maximum recursion depth. If the limit is reached, the representation of the object or array is replaced with a single-element array containing the mask token (e.g., `['[MASKED]']`).
  * **Circular Reference Detection:**
    Utilizes a `CircularReferenceDetectorInterface` to safely handle self-referential or cyclic data structures.
  * **Private Property Handling:**
    Objects with no public properties are represented as a special array indicating their class and that their properties are private (e.g., `['object_class' => 'ClassName[PRIVATE_PROPERTIES]']`).

###### **Workflow**

The sanitization process follows a strict order of operations to ensure security and stability:

1.  The main `sanitizeObject` method **resets the circular reference detector** to begin a fresh run.
2.  It then starts the recursive process, which for each object checks for structural hazards in a specific order:
    * **Is the max depth reached?** If so, recursion stops, and an array containing only the mask token is returned.
    * **Is it a circular reference?** If an object has already been seen, it is replaced with a standardized marker provided by the detector.
3.  If the object is safe to process, it is converted to an array:
    * The object's class name is added under the `object_class` key.
    * If the object has no public properties, a special token is appended to the class name, and the process for this object stops.
    * Otherwise, it iterates through each public property and sanitizes its value based on a clear priority:
        * **Sensitive Key:** The entire value is replaced with the mask token.
        * **Array Value:** The sanitization process recurses into the nested array.
        * **Object Value:** The sanitization process recurses into the nested object.
        * **String Value:** The string is passed to the `StringSanitizerInterface`.
        * **Other Types:** The value is returned as-is.

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Service\ObjectSanitizer;
use Logging\Security\Sanitizing\Detector\CircularReferenceDetector;
use Logging\Security\Sanitizing\Detector\SensitiveKeyDetector;
use Logging\Security\Sanitizing\Service\StringSanitizer;

$objectSanitizer = new ObjectSanitizer(
    new CircularReferenceDetector(),
    new SensitiveKeyDetector(...),
    new StringSanitizer(...),
    '[MASKED]',
    8 // Maximum recursion depth
);

$sanitized = $objectSanitizer->sanitizeObject($someObject);
// $sanitized is a fully sanitized associative array ready for logging,
// including an 'object_class' key.
```

###### **Integration**

This class implements `ObjectSanitizerInterface` and is typically used as a dependency within higher-level logging or audit modules that require secure serialization and masking of potentially sensitive objects.

###### **Security Considerations**

  * Ensures sensitive information from an object's **public properties** is not leaked in logs, error traces, or serialized outputs.
  * Prevents stack overflows and performance issues by enforcing a maximum recursion depth and detecting circular references.
  * Can be composed with custom key detectors or string sanitizers for project-specific policies.

---

##### ArraySanitizer

The `ArraySanitizer` provides robust, **recursive sanitization for arrays**, ensuring that data is safe for secure logging, auditing, or API exposure. It is engineered to handle complex and deeply nested data structures, including those with circular references, guaranteeing both security and application stability.

###### **Features**

  * **Sensitive Key Masking:**
    Fully masks values for keys identified as sensitive by a `SensitiveKeyDetectorInterface`.
  * **Partial String Sanitization:**
    Delegates string values to a `StringSanitizerInterface` for sanitization, which can perform tasks like partial masking.
  * **Circular Reference Handling:**
    Leverages a `CircularReferenceDetectorInterface` to safely traverse data structures with circular dependencies, preventing infinite recursion. It passes arrays by reference for accurate detection.
  * **Object Sanitization Support:**
    Delegates the sanitization of any objects found in array values to an `ObjectSanitizerInterface`.
  * **Maximum Depth Control:**
    Enforces a configurable recursion depth limit as a fail-safe against excessively deep or runaway data structures.

###### **Workflow**

The sanitization process follows a strict order of operations within its recursive methods to ensure security and stability:

1.  The main `sanitizeArray` method is called, which first **resets the circular reference detector** to begin a fresh run before calling the internal recursive sanitizer.
2.  Within the recursive process, it checks for structural hazards for each array it processes:
    * **Is it a circular reference?** If an array has already been seen in the current run, the `CircularReferenceDetectorInterface` is used to handle it, preventing an infinite loop.
    * **Is the max depth reached?** If the current depth exceeds the limit, recursion stops, and an array with a `['[SANITIZATION_HALTED]' => 'MAX_DEPTH_REACHED']` marker is returned.
3.  If the element is safe to process, the `sanitizeElement` method handles each key-value pair based on a clear priority:
    * **Sensitive key:** If the key is identified as sensitive, the entire value is replaced with the mask token, regardless of its type.
    * **Array value:** The sanitization process recurses into the nested array.
    * **Object value:** The object is passed to the `ObjectSanitizerInterface` for processing.
    * **String value:** The string is passed to the `StringSanitizerInterface` for sanitization.
    * **Other types (int, float, bool, null):** The value is returned as-is.

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Service\ArraySanitizer;
use Logging\Security\Sanitizing\Service\StringSanitizer;
use Logging\Security\Sanitizing\Service\ObjectSanitizer;
use Logging\Security\Sanitizing\Detector\SensitiveKeyDetector;
use Logging\Security\Sanitizing\Detector\CircularReferenceDetector;

$arraySanitizer = new ArraySanitizer(
    new StringSanitizer(...),
    new SensitiveKeyDetector(...),
    new ObjectSanitizer(...),
    new CircularReferenceDetector(),
    '[MASKED]',
    10 // max depth
);

// $dataArray could be a simple array or a complex structure with circular references.
$sanitized = $arraySanitizer->sanitizeArray($dataArray);

// $sanitized now contains the fully sanitized and safe array.
```

###### **Integration**

The `ArraySanitizer` implements the `ArraySanitizerInterface` and is designed for composition. It acts as a central orchestrator, delegating specific tasks to specialized components (for strings, objects, sensitive key detection, and circular references), making the system modular and easy to test.

###### **Security and Robustness**

  * **Prevents Data Leakage:** Ensures that sensitive information does not leak into logs, API responses, or other outputs by masking data based on keys.
  * **Denial-of-Service Prevention:** Prevents application crashes and resource exhaustion by correctly handling infinite recursion in circular data structures. The maximum depth limit serves as an additional layer of protection.
  * **Extensibility:** Its behavior can be customized via dependency injection, allowing for different detection strategies, sanitization rules, or masking tokens.

---

##### SanitizingService

The `SanitizingService` orchestrates all sanitization logic in the logging domain, providing a **centralized entry point** for recursively masking or redacting sensitive data across any structure—string, array, or object.
It coordinates specialized collaborators for detection and masking, automatically delegating each input to the appropriate strategy based on its type, and ensuring domain-wide consistency.

###### **Key Features**

* **Unified Entry Point:**
  Centralizes all sanitization and sensitivity checks behind a single interface (`SanitizerInterface`), simplifying integration and maintenance.
* **Type Routing:**
  Automatically detects the type of input and delegates sanitization to the correct service—array, object, or string sanitizer.
* **Recursive Handling:**
  Traverses and sanitizes complex, deeply nested structures, applying masking policies at every level.
* **Mask Token Validation:**
  Validates or defaults the mask token via `MaskTokenValidator` for each operation, protecting against unsafe or ambiguous masking.
* **Comprehensive Sensitivity Detection:**
  Determines whether any given value (or nested structure) is or contains sensitive data, leveraging pattern and key detectors.
* **Configurable and Extensible:**
  Composed of interchangeable dependencies (detectors, validators, sanitizers), supporting custom security policies and domain needs.

###### **Responsibilities**

* Receives arbitrary input, routes it to the appropriate sanitizer, and returns the sanitized result.
* Validates and applies the configured mask token or falls back to a secure default.
* Provides domain-consistent sensitivity detection via `isSensitive`.
* Ensures non-string scalars are returned as-is (for performance and relevance).

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Service\SanitizingService;
use Logging\Security\Sanitizing\Service\ArraySanitizer;
use Logging\Security\Sanitizing\Service\ObjectSanitizer;
use Logging\Security\Sanitizing\Service\StringSanitizer;
use Logging\Security\Sanitizing\Detector\SensitivePatternDetector;
use Logging\Security\Sanitizing\Detector\SensitiveKeyDetector;
use Logging\Security\Sanitizing\Tools\MaskTokenValidator;

$service = new SanitizingService(
    new ArraySanitizer(...),
    new ObjectSanitizer(...),
    new StringSanitizer(...),
    new SensitivePatternDetector(...),
    new SensitiveKeyDetector(...),
    new MaskTokenValidator(),
    '[MASKED]'
);

$data = [
    'user' => 'admin',
    'password' => 'secret123',
    'profile' => (object)['cpf' => '123.456.789-09']
];

$sanitized = $service->sanitize($data);
// $sanitized: sensitive values replaced with '[MASKED]'
```

###### **Integration**

Implements `SanitizerInterface` and is designed to be injected into higher-level application services, controllers, or middleware that require secure, consistent data sanitization.

###### **Security Considerations**

* Prevents exposure of sensitive data in logs, APIs, or error traces across any PHP type.
* Enforces strict validation and safe fallback for mask tokens.
* Modular architecture supports continuous policy evolution.

---

##### MaskTokenValidator

The `MaskTokenValidator` is responsible for **validating and normalizing mask tokens** used to replace sensitive data during sanitization.
It enforces domain-specific security policies, ensuring that every mask token used throughout the system is safe, properly formatted, and resistant to misuse or injection vulnerabilities.

###### **Features**

* **Validation:**
  Checks that the mask token is non-empty, within the maximum allowed length, and does not match any forbidden patterns (e.g., control characters, "base64", "script", or "php").
* **Normalization:**
  Standardizes all valid tokens by trimming whitespace, unwrapping any brackets, converting to uppercase, and re-wrapping in brackets (e.g., "masked" → "\[MASKED]").
* **Configurable Policy:**
  Allows for custom forbidden patterns and length constraints via constructor parameters, supporting project-specific security requirements.
* **Strict Exception Handling:**
  Throws a precise domain exception (`InvalidSanitizationConfigException`) on invalid input, enabling safe fail-fast behavior.

###### **Responsibilities**

* Guarantees that every mask token used for data sanitization is secure, clear, and predictable in log output.
* Prevents accidental use of unsafe, ambiguous, or potentially malicious tokens in production environments.

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Tools\MaskTokenValidator;

$validator = new MaskTokenValidator();

try {
    $validatedToken = $validator->validate('  masked  '); // Returns: '[MASKED]'
} catch (InvalidSanitizationConfigException $e) {
    // Handle invalid mask token: log error, fallback, etc.
}
```

###### **Integration**

Typically used by sanitizers and orchestrator services to ensure that all sensitive data is masked using only safe, validated tokens, and to enforce consistent output formatting across the logging domain.

###### **Security Considerations**

* Prevents the use of mask tokens that could be misinterpreted, injected, or abused in logs or external systems.
* Configuration flexibility enables adaptation to evolving security policies and compliance requirements.

---

##### UnicodeNormalizer

The `UnicodeNormalizer` is a domain service dedicated to **normalizing strings to Unicode FORM\_KC**, ensuring consistent representation and comparison of textual data throughout the application.
This process is especially important for applications handling multilingual, accented, or visually ambiguous Unicode data, as it guarantees that equivalent strings are treated identically for detection, comparison, and sanitization.

###### **Key Features**

* **Unicode Normalization:**
  Converts strings to [Unicode Normalization Form KC (NFKC)](https://unicode.org/reports/tr15/), standardizing visually and semantically equivalent characters for robust processing.
* **Graceful Fallback:**
  Automatically returns the original string if the PHP [intl extension](https://www.php.net/manual/en/book.intl.php) or the `Normalizer` class is not available, preserving system stability and compatibility.
* **Transparency:**
  Operates silently; applications do not need to check or handle the presence of the extension.

###### **Responsibilities**

* Provides a simple interface for normalizing input or stored text, supporting downstream detection (e.g., sensitive key matching) and sanitization logic.
* Ensures that Unicode normalization is consistently applied across all relevant code paths.

###### **Usage Example**

```php
use Logging\Security\Sanitizing\Tools\UnicodeNormalizer;

$normalizer = new UnicodeNormalizer();

$input = "Café";
$normalized = $normalizer->normalize($input);
// Output: "Café" (normalized to Unicode FORM_KC, if supported)
```

###### **Integration**

Typically used by detectors and sanitizers that rely on string comparison, pattern matching, or linguistic consistency, such as sensitive key detectors and string sanitizers.

###### **Security and Data Integrity**

* Prevents bypass of sensitive data detection due to Unicode anomalies, visually deceptive characters, or accented inputs.
* Fosters internationalization-readiness in any application that processes user input, log data, or configuration files.

---

#### Validation Module

The `Validation` module provides a comprehensive suite of services and utilities for rigorously validating all value objects and core properties within PHP logging systems. Its primary purpose is to enforce domain integrity, normalization, and compliance with security and operational policies before data is persisted, logged, or further processed.

This module is designed to be **modular, extensible, and fully configurable**. It supports centralized rule enforcement, custom validation constraints, and easy adaptation to varied application requirements via dependency injection and externalized configuration.

###### **Architecture**

The module is organized into three primary layers:

* **Facade**: The `ValidationFacade` provides a single entry point and unifies all value object validation logic, implementing the domain’s `ValidatorInterface`.
* **Validators (Services)**: Each service encapsulates the validation rules for a single value object, such as log channels, contexts, directories, levels, messages, and timestamps.
* **Tools**: Utility traits and helpers, such as `StringValidationTrait`, provide reusable logic to reduce duplication across validators.

External configuration (via `ValidationConfigInterface`) allows runtime adaptation of all limits and rules, ensuring flexibility across diverse projects.

###### **Features**

* **Dedicated Value Object Validators**: Specialized services for validating log channels, levels, contexts, directories, messages, and timestamps, each enforcing domain-specific rules.
* **Configurable Constraints**: All maximum lengths, forbidden patterns, allowed values, and normalization policies are externally configurable and environment-agnostic.
* **Unified Facade Interface**: The `ValidationFacade` provides a single entry point for all log-related validations, decoupling domain logic from implementation details.
* **Exception-Based Failure Handling**: Domain-specific exceptions are thrown on invalid input, supporting robust error management and traceability.
* **Reusable String Logic**: A shared trait delivers consistent string sanitization and normalization across all validators.
* **Extensible Contracts**: All validators are designed for substitution or extension, enabling custom rule sets for unique domain needs.

###### **Directory Structure**

```
Validation/
├── Services/
│   ├── ChannelValidator.php
│   ├── ContextValidator.php
│   ├── DirectoryValidator.php
│   ├── LevelValidator.php
│   ├── MessageValidator.php
│   └── TimestampValidator.php
├── Tools/
│   └── StringValidationTrait.php
└── ValidationFacade.php
```

###### **Main Components**

* **ValidationFacade**

  Aggregates all validation services and exposes a unified interface for domain object validation, implementing the main `ValidatorInterface`.

* **Services**

  * **ChannelValidator**: Enforces non-emptiness, normalization, and forbidden character rules for log channel names.
  * **ContextValidator**: Validates associative log context arrays, checking key/value types, allowed lengths, and forbidden characters.
  * **DirectoryValidator**: Checks directory paths for non-emptiness, safe usage (no traversal), and allowed character sets.
  * **LevelValidator**: Ensures log level strings are non-empty, normalized, and present in the allowed set.
  * **MessageValidator**: Validates message strings for length, forbidden characters, and required punctuation.
  * **TimestampValidator**: Asserts that timestamps are valid instances of `DateTimeImmutable`.

* **Tools**

  * **StringValidationTrait**: Provides reusable string sanitation, normalization, and emptiness checks used internally by all validators.

###### **Integration**

The `Validation` module is initialized and managed by the `SecurityKernel`, which automatically handles all dependency wiring. There is no need to manually instantiate or configure individual validators. The kernel provides a fully configured, production-ready validation service that can be used throughout your application.

* **Initialization:**
  Instantiate `SecurityKernel` with a `ValidationConfigInterface` implementation.

* **Validator Access:**
  Retrieve the validation facade by calling:

  ```php
  $kernel = new SecurityKernel($sanitizationConfig, $validationConfig);
  $validator = $kernel->validator(); // Implements ValidatorInterface
  ```

* **Usage:**
  Use `$validator` to validate log channels, levels, contexts, messages, directories, and timestamps according to your configuration. All domain validation rules are enforced transparently.

###### **Limitations**

* Effectiveness depends on the provided configuration, including all length limits and allowed/disallowed value sets.
* Only validates as thoroughly as the contracts and external configuration define—application-level policies may require further extensions.

-----

##### ValidationFacade

The `ValidationFacade` serves as a centralized, high-level entry point for all data validation within the logging domain. It implements the **Facade design pattern** to provide a simple, unified interface that abstracts the complexity of multiple specialized validation services. Its core purpose is to ensure that all log-related data—such as channels, messages, and context—is consistently and reliably validated against domain rules before use.

###### **Responsibilities**

  * **Simplifies Complexity:** Provides a single, simplified API for a complex validation subsystem, hiding the underlying individual validator services from the client.
  * **Delegates Tasks:** Delegates each specific validation task (e.g., for a channel, log level, or message) to a dedicated, single-responsibility validator service.
  * **Centralizes Enforcement:** Acts as the single point of enforcement for all domain validation rules, ensuring consistency across the application.
  * **Promotes Decoupling:** Decouples domain objects from concrete validation implementations. This allows validation logic to be modified or swapped without impacting business code, as long as the facade's contract is maintained.

###### **How It Works**

The facade's operation is straightforward and focuses on delegation:

1.  **Instantiation:** The `ValidationFacade` is instantiated by injecting a complete set of specialized validator services (e.g., `ChannelValidator`, `ContextValidator`, etc.) via its constructor. This is typically handled by a dependency injection container.
2.  **Execution:** When a validation method like `validateChannel()` is called on the facade, it does not contain the validation logic itself.
3.  **Delegation:** It transparently delegates the call and its arguments to the corresponding injected service—in this case, `channelValidator->validate(...)`.
4.  **Return Value:** The result (a normalized value) or any thrown exception from the specialized service is returned directly to the original caller, making the facade a clean, pass-through entry point.

###### **Usage Example**

```php
use Logging\Security\Validation\ValidationFacade;
use Logging\Security\Validation\Services\ChannelValidator;
use Logging\Security\Validation\Services\ContextValidator;
use Logging\Security\Validation\Services\DirectoryValidator;
use Logging\Security\Validation\Services\LevelValidator;
use Logging\Security\Validation\Services\MessageValidator;
use Logging\Security\Validation\Services\TimestampValidator;

// These could be instantiated by a DI container.
$channelValidator = new ChannelValidator();
$contextValidator = new ContextValidator();
$directoryValidator = new DirectoryValidator();
$levelValidator = new LevelValidator();
$messageValidator = new MessageValidator();
$timestampValidator = new TimestampValidator();

// Create the facade instance with all its dependencies.
$validationFacade = new ValidationFacade(
    $channelValidator,
    $contextValidator,
    $directoryValidator,
    $levelValidator,
    $messageValidator,
    $timestampValidator
);

// Use the simple, unified interface to validate different data types.
try {
    $validChannel = $validationFacade->validateChannel(' application.events ');
    $validMessage = $validationFacade->validateMessage('User logged in successfully.');
    $validContext = $validationFacade->validateContext(['user_id' => 123, 'ip_address' => '127.0.0.1']);

    // echo "Channel: $validChannel"; // Output: 'application.events' (if normalized)
} catch (Exception $e) {
    // Handle any validation exceptions...
}
```

###### **Architectural Significance**

  * **Facade Pattern:** Implements the Facade pattern to provide a simplified interface to a complex subsystem, making the validation framework much easier to use for client code.
  * **SOLID Principles:** Adheres strictly to SOLID principles, promoting a clean and maintainable architecture.
      * **Single Responsibility Principle:** Each validator (`ChannelValidator`, etc.) has only one reason to change. The facade's single responsibility is to orchestrate these services.
      * **Dependency Inversion Principle:** The facade and its clients depend on stable contracts. By using dependency injection, concrete implementations can be easily swapped without affecting the client.
  * **Testability:** Greatly enhances testability. Each specialized validator can be unit-tested in isolation. Furthermore, client code that uses the facade can be tested by injecting a mock version of the facade or its dependencies.

---

##### Validator Services

Each validator is a specialized, single-responsibility service designed to enforce the integrity of a specific domain concept. They ensure that all data conforms to predefined business rules before it is used, throwing a meaningful, domain-specific exception upon any rule violation. All critical validation parameters (like length limits and forbidden characters) are derived from a central `ValidationConfigInterface`, ensuring consistency and ease of maintenance.

###### **ChannelValidator**

Validates log channel names to ensure they are well-formed and adhere to the application's naming policy.

* **Validation Rules:**
    * The channel name must not be empty after trimming whitespace.
    * Its length must not exceed the configured `channelMaxLength`.
    * It must not contain any characters matching the configured `stringForbiddenCharsRegex`.

* **Normalization Steps:**
    * Trims whitespace from the beginning and end of the string.
    * Converts the entire string to lowercase for consistency.

* **Configuration Dependencies:**
    * `channelMaxLength()`: Defines the maximum allowed length.
    * `stringForbiddenCharsRegex()`: Defines a regular expression for disallowed characters.

* **Exception Thrown:** `InvalidLogChannelException` on failure.

###### **ContextValidator**

Performs a comprehensive validation of associative arrays used for logging context, examining both keys and values, including recursive validation of nested arrays.

* **Key Validation Rules:**
    * Keys must be of type `string`.
    * Keys must not be empty after trimming.
    * Keys must not have duplicate names at the same array level (comparison is performed on the cleaned key).
    * Key length must not exceed the configured `contextKeyMaxLength`.
    * Keys must not contain characters matching `stringForbiddenCharsRegex`.

* **Value Validation Rules:**
    * Values can be of a scalar type (`string`, `int`, `float`, `bool`), `null`, or an `array`.
    * For scalar types, when cast to a string, the value must not be empty (unless the original value was `0`, `false`, or `null`).
    * The string-cast value's length must not exceed `contextValueMaxLength`.
    * The string-cast value must not contain characters matching `stringForbiddenCharsRegex`.
    * Values of type `array` are recursively validated, applying the same key and value rules to each of their elements.

* **Normalization Steps:**
    * Keys are trimmed of whitespace.
    * Scalar and `null` values are cast to their `string` representation.
    * Array values are processed recursively to normalize their keys and values.

* **Configuration Dependencies:**
    * `contextKeyMaxLength()`: Maximum length for a context key.
    * `contextValueMaxLength()`: Maximum length for a string-cast context value.
    * `stringForbiddenCharsRegex()`: Regex for disallowed characters in both keys and values.

* **Exception Thrown:** `InvalidLogContextException` on any violation.

###### **DirectoryValidator**

Validates and normalizes directory paths to ensure they are safe and suitable for log storage.

* **Validation Rules:**
    * The path must not be empty after normalization.
    * The path must not be the configured root directory string (e.g., "/").
    * The path must not contain the parent directory traversal string (e.g., "..").
    * The path must not contain characters matching `stringForbiddenCharsRegex`.

* **Normalization Steps:**
    * Trims whitespace.
    * Removes `NULL` bytes (`\0`).
    * Removes any trailing forward or backslashes.

* **Configuration Dependencies:**
    * `directoryRootString()`: The string representing the root directory.
    * `directoryTraversalString()`: The string representing parent directory traversal.
    * `stringForbiddenCharsRegex()`: Regex for disallowed characters.

* **Exception Thrown:** `InvalidLogDirectoryException` on failure.

###### **LevelValidator**

Validates log level strings against a dynamic set of allowed values.

* **Validation Rules:**
    * The level must not be an empty string.
    * The normalized, lowercase level must exist within the provided `$allowedLevels` array.

* **Normalization Steps:**
    * Trims whitespace.
    * Converts the string to lowercase for comparison and for the return value.

* **Configuration Dependencies:** None. The allowed levels are passed as a method argument.

* **Exception Thrown:** `InvalidLogLevelException` on error.

###### **MessageValidator**

Validates and normalizes log message strings to ensure they are clean, properly formatted, and within size limits.

* **Validation Rules:**
    * The message must not be empty.
    * It must not contain characters matching `stringForbiddenCharsRegex`.
    * Its length must not exceed the configured `logMessageMaxLength` (which can be overridden per call).

* **Normalization Steps:**
    * Trims whitespace.
    * Capitalizes the first letter of the message in a Unicode-safe way.
    * Appends a period (`.`) if the message does not already end with punctuation matching the `logMessageTerminalPunctuationRegex`.

* **Configuration Dependencies:**
    * `logMessageMaxLength()`: The default maximum message length.
    * `stringForbiddenCharsRegex()`: Regex for disallowed characters.
    * `logMessageTerminalPunctuationRegex()`: Regex to check for existing terminal punctuation.

* **Exception Thrown:** `InvalidLogMessageException` if invalid.

###### **TimestampValidator**

Provides a strict type check for timestamp values used in log entries.

* **Validation Rules:**
    * The value must be an instance of `DateTimeImmutable`.

* **Normalization Steps:** None.

* **Configuration Dependencies:** None.

* **Exception Thrown:** `InvalidArgumentException` if the type is incorrect.

---

##### Tools

* StringValidationTrait

  * Provides shared logic for string sanitization, trimming, emptiness checks, etc.
  * Methods never throw exceptions; instead, they return normalized values or boolean results.
  * Used internally by validators to enforce string-related constraints.

---

#### Extension and Customization

* **Custom Validators**: Additional validators can be created for new domain concepts by following the existing service pattern and injected into the facade.
* **Configuration**: Substitute or extend the configuration provider to adapt rules and limits as required.
* **Exception Handling**: Downstream code should handle domain-specific exceptions to provide actionable error messages or remediation.
* **Trait Reuse**: The `StringValidationTrait` can be reused in custom validators to maintain consistency.

---

#### Exception Contracts

Every validation service throws explicit, domain-specific exceptions when constraints are violated. Consumers should handle these exceptions to maintain system integrity and provide user feedback or error reporting.

---

### Configuration Layer

The configuration layer defines how the logging module’s validation and sanitization rules are set and customized. All configuration classes are under the `Config\Modules\Logging` namespace, separate from the main `Logging` namespace to clarify their role as configuration data.

-----

#### LoggingConfig

`LoggingConfig` is the main configuration aggregator for the logging module, implementing `LoggingConfigInterface`. It encapsulates and exposes all internal configuration objects necessary for the logging infrastructure and is passed to the `LoggingKernel` upon initialization.

##### Construction

The constructor requires a non-empty string path to the base log directory.

```php
$config = new LoggingConfig('/path/to/log/directory');
```

  * If the path is empty or contains only whitespace, an `InvalidArgumentException` is thrown.
  * The class does **not** validate, create, or normalize the directory path on disk; it only stores the provided string.

##### Composition

Upon instantiation, `LoggingConfig` creates and stores the following configuration objects:

  * `SanitizationConfig` (`SanitizationConfigInterface`)
  * `ValidationConfig` (`ValidationConfigInterface`)
  * `AssemblerConfig` (`AssemblerConfigInterface`)

Each is exposed through a dedicated accessor as defined in the `LoggingConfigInterface`.

##### Accessors

  * `baseLogDirectory(): string`
    Returns the path to the log storage directory.

  * `sanitizationConfig(): SanitizationConfigInterface`
    Returns the configuration object responsible for log data sanitization.

  * `validationConfig(): ValidationConfigInterface`
    Returns the configuration object governing value object validation.

  * `assemblerConfig(): AssemblerConfigInterface`
    Returns the configuration object used for log entry assembly.

##### Extensibility

As this class is `final`, it cannot be extended via inheritance. To customize the logging behavior, you must either:

1.  Modify the underlying configuration classes directly (`SanitizationConfig`, `ValidationConfig`, `AssemblerConfig`).
2.  Create your own implementation of `LoggingConfigInterface` that allows for dependency injection of custom configuration objects.

-----

#### AssemblerConfig

`AssemblerConfig` is the concrete implementation of `AssemblerConfigInterface`. It centralizes and exposes the default configuration values and custom log levels used by the log entry assembler component. All values are sourced from the `AssemblerDefaultValues` enum and the `CustomLogLevels` provider.

##### Construction

Upon instantiation, all configuration values are set using static providers:

  * The default level, context, channel, and mask token are obtained from the corresponding entries in `AssemblerDefaultValues`.
  * The allowed custom log levels are retrieved via `CustomLogLevels::list()`.

##### Accessors

  * `defaultLevel(): ?string`
    Returns the default log level (e.g., `"info"`).

  * `defaultContext(): ?array`
    Returns the default log context (an empty array).

  * `defaultChannel(): ?string`
    Returns the default log channel (e.g., `"application"`).

  * `customLogLevels(): ?array`
    Returns the complete list of permitted log levels.

  * `maskToken(): ?string`
    Returns the token used to mark or mask sanitized log content (default: `"[SANITIZED_BY_CHANNEL]"`).

##### Usage Notes

All configuration properties are immutable and set at instantiation. Since the class is `final`, customization must be achieved by modifying the provider components it relies on (`AssemblerDefaultValues` and `CustomLogLevels`).

-----

#### AssemblerDefaultValues

`AssemblerDefaultValues` is an `enum` that centralizes all default fallback values required for log entry assembly. Each case corresponds to a distinct configuration attribute, ensuring a single source of truth for default behaviors within the logging domain.

##### Enum Cases

  * `DEFAULT_LEVEL`
    The default log level assigned to new log entries (`'info'`).

  * `DEFAULT_CONTEXT`
    The default context array assigned to new log entries (`[]`).

  * `DEFAULT_CHANNEL`
    The default channel name for log entries (`'application'`).

  * `DEFAULT_MASK_TOKEN`
    The default token used to indicate sanitized content (`'[SANITIZED_BY_CHANNEL]'`).

##### Methods

  * `getValue(): string|array|null`
    Returns the value associated with the enum case:

      * `DEFAULT_LEVEL`: Returns `'info'`.
      * `DEFAULT_CONTEXT`: Returns an empty array.
      * `DEFAULT_CHANNEL`: Returns `'application'`.
      * `DEFAULT_MASK_TOKEN`: Returns `'[SANITIZED_BY_CHANNEL]'`.

##### Usage Notes

  * The enum ensures all assembler defaults are explicit, immutable, and type-safe.
  * To update default values, modify the `getValue` method within the enum source code.

-----

#### CustomLogLevels (`Config\Modules\Logging\CustomLogLevels`)

`CustomLogLevels` provides a static, centralized list of all log levels recognized and accepted by the logging domain. It serves as the single point of truth for the application's permitted log levels, supporting both standard and custom strategies.

##### Responsibilities

  * Defines a static method for retrieving all accepted log levels in the domain.
  * Prevents instantiation by having a private constructor.

##### Accessors

  * `list(): array`
    Returns an indexed array of all allowed log levels as strings (e.g., `"debug"`, `"info"`, `"warning"`, `"error"`, `"critical"`, `"alert"`, `"emergency"`). Additional log levels (such as `"audit"`) may be uncommented or added as needed to support specific application requirements.

##### Usage Notes

  * This class cannot be instantiated.
  * To tailor the accepted log levels for your application, extend or modify the array returned by the `list()` method.
  * By default, the returned list includes all standard [PSR-3](https://www.php-fig.org/psr/psr-3/) log levels.

---

#### SanitizationConfig

`SanitizationConfig` implements `SanitizationConfigInterface` and encapsulates all settings related to data sanitization for the logging module. It aggregates its configuration from dedicated enums and provider classes, centralizing all sanitization rules and operational limits.

##### Responsibilities

* Loads custom sensitive keys from `CustomSensitiveKeys`.
* Loads custom sensitive value patterns from `CustomSensitivePatterns`.
* Loads recursion depth, mask token, and the mask token's validation pattern from the `DefaultSanitizationValues` enum.

##### Properties

* **Sensitive Keys:** A list of string keys whose values must be masked or sanitized.
* **Sensitive Patterns:** A list of regular expression patterns used to detect sensitive values.
* **Max Depth:** An integer limit for recursion depth during the sanitization of nested data structures.
* **Mask Token:** The string token used to replace or mark sanitized values.
* **Mask Token Forbidden Pattern:** A regular expression pattern specifying forbidden characters or sequences within the mask token itself to prevent injection attacks.

##### Accessors

* `sensitiveKeys(): array`
    Returns the list of keys to be sanitized.
* `sensitivePatterns(): array`
    Returns the list of regular expressions to match sensitive values.
* `maxDepth(): int`
    Returns the maximum recursion depth permitted during sanitization.
* `maskToken(): string`
    Returns the token used to mask sensitive data.
* `maskTokenForbiddenPattern(): string`
    Returns the regex pattern used to validate the mask token.

##### Export

* `toArray(): array`
    Exports the configuration properties as an associative array for consumption by other modules or for debugging.

##### Usage Notes

* All configuration values are loaded upon instantiation and are immutable for the object's lifetime.
* As this class is `final`, it cannot be extended. Customization must be achieved by modifying the provider classes it depends on (`CustomSensitiveKeys`, `CustomSensitivePatterns`, or `DefaultSanitizationValues`).

---

#### DefaultSanitizationValues

`DefaultSanitizationValues` is an enum that provides the default values for core sanitization settings. It centralizes defaults for recursion depth, mask tokens, and mask token validation, ensuring a consistent and secure baseline configuration.

##### Enum Cases

* `MAX_DEPTH`
    The default maximum recursion depth for nested data sanitization operations (`8`).
* `MASK_TOKEN`
    The default string token used to mask sensitive values (`'[MASKED]'`).
* `MASK_TOKEN_FORBIDDEN_PATTERN`
    The default regex pattern identifying forbidden characters or strings within the mask token (`'/[\x00-\x1F\x7F]|base64|script|php/i'`).

##### Methods

* `getValue(): int|string`
    Returns the raw value associated with each enum case.

##### Usage Notes

* This enum defines baseline defaults for the sanitizer's operational parameters. Sensitive keys and value patterns are managed in separate provider classes.
* To adjust global defaults for sanitization operations, modify the values directly within this enum's `getValue()` method.

---

#### CustomSensitiveKeys / CustomSensitivePatterns

These configuration-only classes provide application-specific sensitive keys and regular expression patterns for data sanitization. They are designed for static management of custom rules, which are then merged with the sanitizer's other rules.

##### CustomSensitiveKeys

* **Purpose:**
    Centralizes a list of additional sensitive keys (parameter names) whose values must be masked in logs.
* **Usage:**
    * `CustomSensitiveKeys::list(): array` returns a hardcoded array of custom sensitive keys. Examples include:
        * `"auth_token"`, `"refresh_token"`, `"jwt"`
        * `"pin"`, `"creditcard"`, `"ccv"`
        * `"accesskey"`, `"apikey"`, `"secretkey"`
        * `"biometria"`, `"passport"`, `"telefone"`
* **Design Notes:**
    The class is non-instantiable (`final` with a `private constructor`) and intended strictly for static configuration.

##### CustomSensitivePatterns

* **Purpose:**
    Centralizes a list of additional regular expression patterns used to identify sensitive values by their format, not just by their key.
* **Usage:**
    * `CustomSensitivePatterns::list(): array` returns a hardcoded array of regex patterns. Examples include:
        * Brazilian cellphone numbers (`'/\b(?:\+?55)?\s?\d{2}\s?\d{4,5}-?\d{4}\b/'`)
        * Visa, MasterCard, and American Express card numbers
        * Brazilian RG (ID card) numbers (`'/\b\d{1,2}\.?\d{3}\.?\d{3}-?[\dXx]\b/'`)
        * Custom application tokens (`'/app_[a-z0-9]{32}/i'`)
* **Design Notes:**
    This class is also non-instantiable and intended exclusively for static configuration.

---

#### ValidationConfig

`ValidationConfig` is the default implementation of `ValidationConfigInterface`. It sources all of its values from the `DefaultValidationValues` enum, providing a consistent and immutable source for validation rules used throughout the logging module.

##### Responsibilities

* Implements the `ValidationConfigInterface` contract.
* Delegates every method call to the `DefaultValidationValues` enum to retrieve the corresponding rule.

##### Accessors

* `defaultStringMaxLength(): int`: Returns the max length for generic strings.
* `stringForbiddenCharsRegex(): string`: Returns the regex for forbidden characters.
* `contextKeyMaxLength(): int`: Returns the max length for context keys.
* `contextValueMaxLength(): int`: Returns the max length for context values.
* `channelMaxLength(): int`: Returns the max length for channel names.
* `directoryRootString(): string`: Returns the string representing the root directory.
* `directoryTraversalString(): string`: Returns the string used to detect directory traversal.
* `logMessageMaxLength(): int`: Returns the max length for log messages.
* `logMessageTerminalPunctuationRegex(): string`: Returns the regex for validating terminal punctuation.

##### Usage Notes

* All values are immutable and resolved at runtime from `DefaultValidationValues`.
* As this class is `final`, it cannot be extended. To provide custom validation rules, you must either modify the `DefaultValidationValues` enum or create a new class that implements `ValidationConfigInterface`.

---

#### DefaultValidationValues

`DefaultValidationValues` is an enum that defines all default validation parameters used in the logging domain. It serves as the single source of truth for built-in validation rules.

##### Enum Cases

* `DEFAULT_STRING_MAX_LENGTH`: Max length for generic strings (`255`).
* `STRING_FORBIDDEN_CHARS_REGEX`: Regex for forbidden control characters (`'/[\x00-\x1F\x7F]/'`).
* `CONTEXT_KEY_MAX_LENGTH`: Max length for context keys (`128`).
* `CONTEXT_VALUE_MAX_LENGTH`: Max length for context values (`256`).
* `CHANNEL_MAX_LENGTH`: Max length for channel names (`64`).
* `DIRECTORY_ROOT_STRING`: String representing the root directory (`'/'`).
* `DIRECTORY_TRAVERSAL_STRING`: String used to detect directory traversal (`'..'`).
* `LOG_MESSAGE_MAX_LENGTH`: Max length for log messages (`2000`).
* `LOG_MESSAGE_TERMINAL_PUNCTUATION_REGEX`: Regex for required terminal punctuation (`'/[.!?]$/u'`).

##### Methods

* `getValue(): int|string`
    Returns the default value associated with each enum case.

##### Usage Notes

* All validation rules are centralized for easy maintenance.
* To alter these global default values, update the return values within the enum's `getValue()` method.

---

### Public Contracts (Interfaces)

The `PublicContracts\Logging` namespace and its sub-namespaces define the formal API for the logging module. These interfaces ensure that consuming code remains decoupled from concrete implementations, which allows for robust testing, extensibility, and substitution.

---

#### Core Service Interfaces

These interfaces define the main entry points and capabilities of the logging system.

##### LoggingKernelInterface

This is the primary contract for the module's kernel. It acts as a service locator or factory, responsible for building and exposing the main logging components.

* **Purpose:** To provide access to the pre-configured, ready-to-use logging facades.
* **Key Methods:**
    * `logger(): LoggingFacadeInterface`: Returns the main logging facade, which is the primary entry point for most application-level logging.
    * `psrLogger(): PsrLoggerInterface`: Returns a PSR-3 compliant logger instance, suitable for integration with third-party libraries or frameworks that expect a standard logger.

##### LoggingFacadeInterface

This interface defines the main, high-level API for logging. It orchestrates the entire logging cycle, from receiving input to assembling and dispatching log entries.

* **Purpose:** To provide a simple, unified set of methods for all logging needs, including PSR-3 compatibility.
* **Key Methods:**
    * `logInput(...)`: A flexible, high-level method designed for general application use, allowing the explicit setting of level, channel, and context.
    * `log(...)`: The standard PSR-3 method for logging with an arbitrary level.
    * `emergency()`, `alert()`, `critical()`, `error()`, `warning()`, `notice()`, `info()`, `debug()`: The eight standard PSR-3 logging methods for logging at specific levels.

##### PsrLoggerInterface

This interface mirrors the official `Psr\Log\LoggerInterface` to provide full PSR-3 compatibility without creating a direct dependency on the `psr/log` package.

* **Purpose:** To allow any component expecting a standard PSR-3 logger to seamlessly use this module's implementation.
* **Key Methods:**
    * Defines all standard PSR-3 methods: `emergency`, `alert`, `critical`, `error`, `warning`, `notice`, `info`, `debug`, and the generic `log`.

---

#### Configuration Interfaces

These interfaces define the contracts for configuration objects, allowing different configuration strategies (e.g., loading from environment variables, files, or enums) to be used as long as the interface is satisfied.

##### LoggingConfigInterface

This is the top-level configuration contract, acting as an aggregator for all other, more specific configuration objects.

* **Key Methods:**
    * `baseLogDirectory(): string`: Returns the absolute path for log storage.
    * `sanitizationConfig(): SanitizationConfigInterface`: Returns the sanitization-specific configuration.
    * `validationConfig(): ValidationConfigInterface`: Returns the validation-specific configuration.
    * `assemblerConfig(): AssemblerConfigInterface`: Returns the configuration for the log entry assembler.

##### AssemblerConfigInterface

Defines the configuration needed by the `LogEntryAssembler` to construct log entries, especially when information is missing.

* **Key Methods:**
    * `defaultLevel(): ?string`: The log level to use if none is provided.
    * `defaultContext(): ?array`: The context array to use if none is provided.
    * `defaultChannel(): ?string`: The channel name to use if none is provided.
    * `customLogLevels(): ?array`: The complete list of log levels accepted by the system.
    * `maskToken(): ?string`: A specific token for masking messages based on the channel context.

##### SanitizationConfigInterface

Defines the contract for all rules and parameters required by the sanitization services.

* **Key Methods:**
    * `sensitiveKeys(): array`: Returns a list of keys whose values must be masked.
    * `sensitivePatterns(): array`: Returns a list of regex patterns for identifying sensitive data within values.
    * `maxDepth(): int`: The maximum recursion depth for sanitizing nested structures.
    * `maskToken(): string`: The default string to use as a replacement for masked data.
    * `maskTokenForbiddenPattern(): string`: A regex to validate the mask token itself against forbidden content.

##### ValidationConfigInterface

Defines the contract for all rules and parameters required by the validation services.

* **Key Methods:**
    * `defaultStringMaxLength(): int`: Max length for generic strings.
    * `stringForbiddenCharsRegex(): string`: Regex for forbidden characters.
    * `contextKeyMaxLength(): int`: Max length for context keys.
    * `contextValueMaxLength(): int`: Max length for context values.
    * `channelMaxLength(): int`: Max length for channel names.
    * `directoryRootString(): string`: String representing the root directory (e.g., "/").
    * `directoryTraversalString(): string`: String for detecting directory traversal (e.g., "..").
    * `logMessageMaxLength(): int`: Max length for log messages.
    * `logMessageTerminalPunctuationRegex(): string`: Regex to validate terminal punctuation.

---

## Requirements and Dependencies

---

### Environment Requirements

- **PHP Version:**  
  The module requires **PHP 8.1 or higher**. It uses PHP 8+ features such as:
  - `readonly` properties in value objects
  - Typed properties
  - Union types (e.g., `string|Stringable` type hints)
  - Enums for config default values  
  PHP 8.1 introduced enums and readonly properties, so 8.1 is the minimum version for full compatibility.

- **PHP Extensions:**  
  No special extensions are needed for basic functionality, except:
  - The module uses the `Normalizer` class from the PHP **Intl (Internationalization)** extension in the Sanitizer (for Unicode normalization of strings).
  - In most PHP distributions, Intl is enabled by default.  
    **Ensure the `intl` extension is available** if you plan to use sanitization features on Unicode text (e.g., user names with accents).

- **File System Access:**  
  The module writes log files to disk.  
  - The PHP process must have **write permissions** to the configured base log directory.
  - Before using in production, ensure the directory (and its parents) are writable by the web server or CLI user.
  - Example: If logs go to `/var/log/myapp`, set proper ownership or permissions for that path.

- **No External Libraries:**  
  This is a **pure PHP solution**.  
  - No external Composer packages are required (even PSR-3 compliance is handled internally).
  - You can use it without additional installs, as long as your autoloader can load its classes.

---

### Dependencies Between Components

- The module is **self-contained**; its components depend on each other as described (e.g., Facade depends on Assembler and Logger, Logger on Writer, etc.), but **no outside library integration is required** out of the box.
- If integrated into a larger project, it will depend on that project’s autoloading mechanism (**PSR-4 autoload** for the namespaces `Logging\*`, `PublicContracts\Logging\*`, and `Config\Modules\Logging\*`).
- **PSR-3 Interop:**  
  If you use the `psr/log` package or type hints from it, note that `PublicContracts\Logging\PsrLoggerInterface` is functionally equivalent.  
  The module’s `PsrLoggerAdapter` can be used wherever a `Psr\Log\LoggerInterface` is expected.

---

### Performance Considerations

- **Writing logs is I/O bound.**  
  For high-frequency logging, ensure the storage medium can handle frequent small writes.
- The module **appends to files**; for extremely high log volume, consider using a RAM disk for logs or batching log writes (not currently built-in).
- **Validation and sanitization checks** (string lengths, regex) are lightweight and will not significantly impact performance for normal log message sizes.
- The most expensive operations are regex replacements for sanitization and JSON encoding for context, but these are negligible for small context data.

- **Log Rotation:**  
  The module does **not implement log rotation**.  
  - For long-running applications, log files will grow indefinitely.
  - Use external log rotation tools (e.g., `logrotate` on Linux or custom scripts) to rotate or archive logs periodically.
  - The Logging Module will continue appending to whatever file name is configured; if an external rotation renames the file and creates a new empty file with the same name, the module will seamlessly start writing to the new file.

---

## Installation

Installing the Logging Module in your project is straightforward. As a standalone module with no external dependencies, you can integrate it manually.

---

### Manual Integration

You can add the module manually by following these steps:

1. **Download or copy the source code**, preserving the folder structure:
  - `Logging/` (with `Domain/`, `Application/`, `Infrastructure/`)
  - `PublicContracts/Logging/` (interfaces)
  - `Config/Modules/Logging/` (configuration classes)

2. **Place these directories** in your project, such as under `src/` or `modules/`.

3. **Set up PSR-4 autoloading** in your `composer.json`:

  ```json
  "autoload": {
    "psr-4": {
    "Logging\\": "src/modules/Logging/",
    "PublicContracts\\": "src/modules/PublicContracts/",
    "Config\\Modules\\Logging\\": "src/modules/Config/Modules/Logging/"
    }
  }
  ```

  Adjust the paths as needed for your project structure.

4. **Update Composer’s autoloader**:

  ```bash
  composer dump-autoload
  ```

  Alternatively, if not using Composer, ensure your own autoloader or manual includes can load the classes according to PSR-4 (e.g., `Logging\Infrastructure\LoggingKernel` should be in `Logging/Infrastructure/LoggingKernel.php`).

5. **Verify autoloading** by writing a simple script that creates a `LoggingConfig` and `LoggingKernel`. If no "class not found" errors occur, your setup is correct.

---

### Directory Permissions

Before first use, decide where your log files will be stored. Common practice is a dedicated `logs/` directory in your project or a system log directory. Ensure this path is writable by the PHP process. For example, on Linux:

```bash
mkdir /var/www/myapp/logs
chown www-data:www-data /var/www/myapp/logs
```

Then use `/var/www/myapp/logs` as the base path in `LoggingConfig`.

---

## Configuration

Configuring the Logging Module is straightforward, as most defaults are sensible. The primary configuration you must provide is the **log directory path**. Additional configuration is optional and involves tweaking the default values through code.

---

### Basic Configuration via `LoggingConfig`

To configure, instantiate the `LoggingConfig` class with your chosen log directory. For example:

```php
use Config\Modules\Logging\LoggingConfig;
use Logging\Infrastructure\LoggingKernel;

$logDir = __DIR__ . '/logs'; // directory where log files will be stored
$config = new LoggingConfig($logDir);
$kernel = new LoggingKernel($config);
$logger = $kernel->logger();
```

- `$logDir` is set to a `logs` directory in the current directory. You can change this to any path (absolute or relative). If relative, it will be resolved relative to the current working directory of the script.
- `new LoggingConfig($logDir)` creates the config. It will throw an exception if `$logDir` is an empty string. If `$logDir` does not exist on disk, that’s okay at this point; it will be created later by the writer as needed.
- `new LoggingKernel($config)` boots the module using that configuration. The kernel now knows about the base path and uses default values for everything else.

After these steps, `$logger` (the LoggingFacade) is ready to use. This is essentially the minimal configuration needed: just the log directory.

---

### Customizing Validation and Sanitization

You can customize the module’s behavior by editing the relevant config classes:

- **Add/Remove Log Levels:**  
  Edit `Config\Modules\Logging\CustomLogLevels::list()`. For example, to add a `"verbose"` level:

  ```php
  public static function list(): array {
      return [
          'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency',
          'verbose' // added custom level
      ];
  }
  ```
  Now the Validator will accept `"verbose"` as a valid level. You can call it via `$logger->log('verbose', '...')`.

- **Sensitive Keys:**  
  Update `CustomSensitiveKeys::list()` to include any context keys your application considers sensitive:

  ```php
  public static function list(): array {
      return ['password', 'passwd', 'secret', 'api_key', 'ssn', 'credit_card'];
  }
  ```
  Any context logged with keys like `"ssn"` or `"credit_card"` will have their values masked.

- **Sensitive Patterns:**  
  Update `CustomSensitivePatterns::list()` with regex patterns for sensitive info:

  ```php
  public static function list(): array {
      return [
          '/\d{3}-\d{2}-\d{4}/' // pattern for US SSN (###-##-#### format)
      ];
  }
  ```
  If any context value matches that pattern, it will be replaced by the mask token.

- **Mask Token:**  
  Change the mask token globally by editing the default in `DefaultSanitizationValues::MASK_TOKEN`:

  ```php
  self::MASK_TOKEN => '***',
  ```
  The assembler’s mask token (for entire messages) can be changed via `AssemblerDefaultValues::DEFAULT_MASK_TOKEN`.

- **Length Limits and Regex:**  
  Adjust `DefaultValidationValues::LOG_MESSAGE_MAX_LENGTH` if 2000 chars is too short or long for log messages.  
  To allow newline characters in log messages (currently disallowed by the control char regex), modify `STRING_FORBIDDEN_CHARS_REGEX`.  
  **Caution:** Multi-line log entries can break the one-line-per-entry format.

- **Time Zone:**  
  The timestamp uses the server’s default timezone when formatting with `DateTimeInterface::ATOM`.  
  Ensure your PHP timezone configuration is correct (`date.timezone` in `php.ini`).  
  Alternatively, modify `LogLineFormatter` to use UTC or another timezone if required.

---

### Integration with Frameworks

If integrating with a framework (Laravel, Symfony, etc.), you can wrap this module in a service provider:

- **Laravel:** Bind `LoggingFacadeInterface` in the service container to an instance created as above.
- **Symfony:** Configure a service and inject the path from parameters.

Outside of a framework, using the `LoggingKernel` directly as shown is perfectly fine.

---

## Best Practices and Limitations

To make the most of the Logging Module and avoid common pitfalls, follow these best practices and be aware of the module’s limitations.

---

### Best Practices

- **Use the Facade or PSR Interface:**  
  Always use `LoggingFacadeInterface` (via the facade instance from the kernel) or the provided PSR logger interface. These ensure the proper sequence of operations (assemble then log) and maintain log entry integrity. Avoid directly using lower-level components like `LogEntryAssembler` or `Logger` in client code, as this bypasses important safety checks.

- **Log Sufficient Context:**  
  Use the context parameter to include relevant data with each log. Instead of concatenating variables into the message, pass them in the context array.  
  **Example:**
  ```php
  $logger->error("Payment failed", [
      "orderId" => $order->id,
      "amount" => $order->amount
  ]);
  ```
  This produces structured context in the log, which is easier to parse later. Avoid logging extremely large context data (such as images or large blobs); log references or summaries instead.
  
* **Handling Sensitive Data in Log Messages**

  For maximum security and reliability, sensitive information should **always be placed within the log context array** rather than directly in the message string. This approach allows for robust, key-based masking, which is the most effective way to ensure data is consistently redacted.

  However, the logging module provides defense-in-depth mechanisms to detect and sanitize sensitive data that may inadvertently appear in free-form message strings. These protections are handled by two specialized sanitizers:

  * **Pattern-Based Sanitization (`SensitivePatternSanitizer`)**
    This sanitizer scans strings for values that match a configured list of regular expressions. To be effective, developers must populate the `CustomSensitivePatterns` configuration with regex patterns that correspond to sensitive data formats specific to the application (e.g., credit card numbers, custom API tokens, or national ID numbers).

  * **Credential Phrase Detection (`CredentialPhraseSanitizer`)**    
    This sanitizer uses advanced heuristics to identify and mask common credential-like phrases, such as `"password: value"` or `"token = abc123"`. Its effectiveness depends on the sensitive keys configured, the separators defined (such as `:`, `=`, `-`), and the number of words allowed between the sensitive key and the separator. The sanitizer supports flexible word order to increase detection accuracy. The content following the separator will be masked. If a separator is not found after the sensitive key, the sanitizer will also search before the key to identify possible exposed credentials. This helps catch accidental leaks in messages, even when credentials are embedded in free-form text.

  **Recommended**

  Place all variable and potentially sensitive data in the context array. The value associated with a recognized sensitive key (like `'password'`) will be reliably masked.

  ```php
  // The value of 'password' will be masked automatically by key.
  $logger->warning("User password validation failed", ["password" => $pass]);
  ```

  **Avoid**

  Avoid embedding sensitive data directly into the message string. While the `CredentialPhraseSanitizer` or a custom pattern might catch it, this method is inherently less reliable than key-based masking.

  ```php
  // AVOID: This message will likely not be sanitized.
  // Heuristic phrase detection can fail if this phrase is not configured,
  // or if the number of words between the key ("password") and its separator exceeds the search limit.
  $logger->warning("Password has been defined as: $pass");
  ```

  While the sanitization engine offers powerful tools for scanning message strings, the primary strategy for protecting sensitive data is to **structure it within the context array**. If logging sensitive information in messages is absolutely unavoidable, you must ensure that your `CustomSensitivePatterns` and credential phrase detection rules are comprehensive enough to provide adequate protection. For advanced scenarios, the core `StringSanitizer` can also be extended with custom logic to meet specific security requirements.

- **Review and Update Sensitive Patterns:**  
  Ensure the default sensitive keys and patterns match your application’s data. Add entries as needed (API keys, tokens, etc.) to prevent accidental leakage of secrets in logs.

- **Manage Log File Size:**  
  The module does not rotate logs. Use external tools (like Linux’s `logrotate`) to rotate logs daily or by size.  
  - If you implement your own rotation, ensure the module writes to the correct file after rotation.
  - Clean up old logs regularly to prevent disk space issues.

- **Thread Safety / Parallel Usage:**  
  In standard PHP web usage, each request is isolated. For CLI or event-loop scenarios, file writes are atomic per call, but simultaneous writes may interleave at the byte level. For critical logs, consider using file locks (`flock`) in `LogFileWriter` to serialize writes.

- **Exception Handling:**  
  Decide how to handle exceptions thrown by the logger in production. You may wrap logging calls in try-catch blocks if logging failures should not break the application. During development, let exceptions bubble up to fix root causes.

- **Extend with Caution:**  
  If you need to extend the module (e.g., to change formatting or integrate with a cloud API), prefer composition over modification.  
  - Implement a new class for `LoggerInterface` if needed.
  - If modifying core classes, maintain validation and sanitization steps for security.

- **Security of Log Files:**  
  Logs may contain sensitive information.  
  - Set proper filesystem permissions (no public read access).
  - If logs are in a web-accessible directory, deny direct access (e.g., via `.htaccess` or by placing logs outside the web root).
  - Regularly review logged data to ensure sanitization is working as intended.

---

### Known Limitations

- **No Log Level Filtering:**  
  The module does not filter logs by level. All log calls (e.g., `$logger->debug()`) will write to disk. If you want to suppress certain levels in production, implement that logic in your application or extend the facade.

- **No Built-in Rotation/Archiving:**  
  Logs grow indefinitely. Use external solutions for log rotation and cleanup.

- **Synchronous I/O:**  
  Logging is synchronous. For high-load web apps, disk writes can be a bottleneck. There is no built-in asynchronous or buffering mechanism.

- **Single Destination (Files):**  
  The module logs only to local files. To log to multiple destinations, extend the logger or call multiple loggers in your code. There is no multi-handler support.

- **Fixed Formatting:**  
  The log line format is not configurable at runtime. To change the format, modify `LogLineFormatter`.

- **Simple Context Serialization:**  
  Context is JSON-encoded. Non-serializable objects will be output as `{}` or may trigger notices. Large arrays produce large JSON strings. For complex data, consider custom formatting or preprocessing.

- **No Internationalization:**  
  The module does not support multiple languages for log messages or localized timestamp formats. Timestamps use ISO8601.

- **Memory Usage:**  
  The module has a small footprint, but logging very large data structures can increase memory usage temporarily.

- **Upgradability:**  
  If you modify core files, upgrading may require merging changes. Prefer subclassing or composition to ease upgrades.

---

## License

The Logging Module is open-source software, released under the **MIT License**.

You are free to **use**, **modify**, and **distribute** this module in your own projects, including commercial applications, provided that you include the license notice.

The MIT License is a permissive license with limited restrictions—primarily requiring preservation of copyright notices and the license text in any redistribution.

For the full license text, see the `LICENSE` file distributed with the module.

> The module is provided "as is", without warranty of any kind, express or implied. The authors or copyright holders are not liable for any claims or damages arising from its use.

By using this module, you agree to the terms of the MIT License.

---

_End of README_