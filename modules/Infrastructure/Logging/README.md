# Logging Module

A **Logging Module** for PHP 8 that provides structured, secure log writing to files. It uses a layered architecture to separate concerns of validation, formatting, and file I/O, ensuring log messages are recorded reliably and sensitive data is sanitized. This module is PSR-3 compatible and can be easily integrated into various PHP applications.

---

## General Description

This module’s primary purpose is to offer a robust logging solution in pure PHP. It addresses common logging needs such as categorizing logs by level (info, warning, error, etc.), including contextual data with log messages, and writing outputs to log files. Additionally, it tackles security concerns by validating inputs and masking sensitive information (like passwords or personal identifiers) before writing to disk.

In context, the Logging Module can be used in web applications, CLI scripts, or any PHP project that requires structured logging. It helps developers track events and debug issues by creating timestamped log entries in an organized file structure (for example, separate files per log level or channel). By using this module, applications gain a consistent logging interface and avoid scattering file write logic and data sanitation code throughout the codebase.

---

## Architectural Overview

The Logging Module is designed with a multi-layer architecture, following principles of separation of concerns and domain-driven design. The codebase is organized into distinct namespaces and directories, each representing a layer or component group:


#### Domain Layer
Contains the core Value Objects and Security logic. This layer is responsible for the validity and integrity of logging data. It knows how to validate log messages, levels, contexts, etc., and how to sanitize sensitive data. Domain classes are pure logic with no external side effects (no file access or output).

#### Application Layer
Provides a Facade that serves as the public API for the module. The facade orchestrates the logging process by coordinating domain and infrastructure components. It offers simple methods to client code (including PSR-3 style methods) to log messages without needing to know the internal details.

#### Infrastructure Layer
Handles the lower-level concerns like constructing log entries from inputs, formatting log lines, and writing to files. It also includes a Kernel that ties all parts together using a configuration. The infrastructure layer is the bridge between the domain’s pure logic and the outside world (filesystem).

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
| - boots Validator & Sanitizer (Domain Security)
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

### Domain Layer (Core Logging Logic)

The domain layer ensures that every piece of data that goes into a log entry is valid and safe. It is comprised of immutable value objects and related domain services for security.

---

#### Domain Security (Logging\Domain\Security):
These classes provide centralized validation and sanitization routines, used by the value objects and assemblers to enforce security rules:

---

##### Validator

Implements `ValidatorInterface` and provides robust validation methods for all log-related inputs, enforcing security and consistency based on configurable rules.

- **`validateMessage(string $message)`:**  
    Ensures the log message is not empty, does not exceed the maximum allowed length, and contains only permitted characters. Applies regex checks to block control characters or forbidden patterns as defined in the `ValidationConfig`.

- **`validateLevel(string $level, array $allowedLevels)`:**  
    Verifies that the provided log level matches one of the allowed values (typically the standard PSR-3 levels, plus any custom levels from configuration). Throws `InvalidLogLevelException` if the level is not recognized.

- **`validateChannel(string $channel)`:**  
    Checks that the channel name is a non-empty, safe string, free from illegal filesystem characters or forbidden substrings (such as directory traversal patterns). Ensures the channel is suitable for use in file paths. Throws `InvalidLogChannelException` on failure.

- **`validateContext(array $context)`:**  
    Validates each key and value in the context array. Keys must be strings of acceptable length and format; values must be serializable and within size limits. May recursively validate nested arrays up to a configured maximum depth. Throws `InvalidLogContextException` if any part is invalid.

- **`validateDirectory(string $path)`:**  
    Ensures the directory path is absolute or within allowed locations, does not contain traversal sequences (like `..`), and matches security constraints from `ValidationConfig`. Throws `InvalidLogDirectoryException` if the path is unsafe.

- **`validateTimestamp($date)`:**  
    Confirms that the timestamp is a valid `DateTimeInterface` instance or a properly formatted date string. Ensures log entries are timestamped accurately.

The `Validator` uses settings from the `ValidationConfig` (see Configuration Layer), such as maximum string lengths, forbidden character regex patterns, and directory rules. If any input fails validation, the corresponding domain exception is thrown (e.g., `InvalidLogLevelException`, `InvalidLogContextException`, or a general `InvalidLoggableInputException` for other cases). This strict validation ensures that only safe, well-formed data is ever written to log files.

---

##### Sanitizer

Implements `SanitizerInterface` and provides robust functionality to cleanse or mask sensitive data, focusing primarily on strings and arrays (especially log context arrays).

The Sanitizer operates according to rules defined in a `SanitizationConfig`, which includes:

- **Sensitive Keys:**  
    A list of field names (such as `"password"`, `"secret"`, `"cpf"`) whose values should always be masked.

- **Sensitive Patterns:**  
    An array of regular expressions used to detect sensitive data within values (for example, credit card numbers or personal identifiers).

- **Mask Token:**  
    A string (e.g., `"[MASKED]"`) used to replace sensitive values or portions of values.

- **Maximum Depth:**  
    A limit for recursive sanitization to prevent performance issues with deeply nested structures.

**Key Method:**

- `sanitize(mixed $input, ?string $maskToken = null): mixed`  
    Sanitizes sensitive data from any input value.  
    Returns a sanitized version of the input, masking or removing confidential data according to the domain policy. Arrays and objects are sanitized recursively up to the configured maximum depth. Strings are sanitized individually; other scalar types are returned unchanged.  
    - **Parameters:**  
        - `$input` (mixed): The value to sanitize (can be array, object, string, or scalar).  
        - `$maskToken` (string|null): Optional custom mask token; if null, uses the default from config.  
    - **Returns:**  
        - (mixed): Sanitized value, of the same type as input.

**Additional Features:**

- **Unicode Normalization:**  
    The Sanitizer may use PHP's `Normalizer` class to ensure consistent text representation, especially for Unicode strings.

- **Error Handling:**  
    If the sanitization configuration is invalid or missing required parameters (such as a mask token), the Sanitizer throws an `InvalidLogSanitizerConfigException`. However, the module provides sensible defaults to minimize this risk.

The Sanitizer ensures that sensitive information is never written to log files, providing a critical layer of security in the logging process.

---

##### LogSecurity

The **LogSecurity** class acts as a facade within the domain security package (`LogSecurity` implements `LogSecurityInterface`). It aggregates both a **Validator** and a **Sanitizer**, providing high-level methods for centralized validation and sanitization of log data.

###### Main Responsibilities

- **Centralization:** All validation and sanitization processes are routed through this class, ensuring consistent application of security rules.
- **Delegation:** Each call is delegated to the corresponding Validator or Sanitizer, keeping the class stateless.
- **Configurability:** Changes to validation or sanitization rules (via configuration) are automatically propagated throughout the system.

###### Example Methods

- `sanitize(mixed $input)`
- `validateString(...)`
- Domain-specific operations, such as validating a complete log entry.

###### Practical Usage

Value Object constructors receive a `LogSecurityInterface` instance to perform their checks. For example, the `LogMessage` class uses `$security->sanitize()` on the raw message and then calls `$security->getValidator()` to validate it, or relies on the sanitized output being already suitable.

###### Benefits

By centralizing operations in `LogSecurity`, any changes to security rules are automatically reflected across the system. This approach improves maintainability, increases security, and reduces code duplication.

---

#### Value Objects (Logging\Domain\ValueObject)
These classes represent the components of a log entry, each encapsulating validation logic for that part:

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

###### **Key Responsibilities:**

* Encapsulates all attributes required to represent a loggable event:

  * `message` (required, non-empty string)
  * `level` (optional, string)
  * `context` (optional, associative array with string keys and values)
  * `channel` (optional, string)
  * `timestamp` (optional, `DateTimeImmutable` instance; defaults to current time)
* Enforces strict validation on all fields at construction time, ensuring immutability and reliability.
* Throws `InvalidLoggableInputException` for any violation of property requirements or data integrity.

###### **Construction and Validation Logic:**

* **Message:**

  * Must be a non-empty string after trimming.
  * An empty or whitespace-only string triggers `InvalidLoggableInputException::emptyMessage()`.

* **Level:**

  * If provided, must be a non-empty string.
  * An empty string triggers `InvalidLoggableInputException::emptyLevel()`.
  * If omitted (`null`), no default is set at this stage.

* **Context:**

  * If provided, must be an associative array where both keys and values are non-empty strings.
  * Any invalid key or value triggers `InvalidLoggableInputException::invalidContextKey($key)` or `::invalidContextValue($key)` respectively.
  * If omitted, defaults to an empty array.

* **Channel:**

  * If provided, must be a non-empty string.
  * An empty string triggers `InvalidLoggableInputException::emptyChannel()`.
  * If omitted (`null`), no default is set.

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
* `getContext(): array<string, string>`
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
    context: ['username' => 'admin'],
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


##### **LogLevel:** 

Represents the severity level of a log (e.g., "info", "error"). It ensures the level provided is one of the allowed values. By default, the standard PSR-3 levels are permitted (debug, info, notice, warning, error, critical, alert, emergency), and this set can be extended via configuration. If an unknown level is given, an InvalidLogLevelException is thrown. Internally, it may convert level names to a normalized form (e.g., lowercase).

##### LogContext: 

Encapsulates an associative array of context data (key-value pairs providing additional info for the log). It validates context keys and values: for example, ensuring keys are strings of acceptable length, and values are serializable (no resources or overly large data by default). It uses domain security services to sanitize sensitive information within the context (e.g., masking values of keys like "password"). If the context data structure is invalid, an InvalidLogContextException can be thrown. A valid LogContext object can provide the sanitized context via methods (e.g., toArray() to retrieve the clean array).

##### LogChannel: 

Represents a log channel or category (for example, an application area like "application", "auth", or "payment"). This is used to segregate log outputs (often into different files or folders). It validates that the channel name is a safe string (not empty, no illegal path characters, etc.), since it might be used as part of a directory or file name. The default channel, if none is provided, is "application". An InvalidLogChannelException is thrown if the channel contains forbidden characters or is otherwise deemed insecure.

---

##### LogEntry

A domain aggregate Value Object representing a fully-validated, immutable log entry. Implements `LogEntryInterface` for a consistent contract across the logging domain.

###### **Key Responsibilities:**

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

#### Domain Exceptions (`Logging\Domain\Exception`)

The domain layer defines a set of exception classes to signal invalid inputs or configurations:

- **`InvalidLogMessageException`**
- **`InvalidLogLevelException`**
- **`InvalidLogContextException`**
- **`InvalidLogChannelException`**
- **`InvalidLogDirectoryException`**
- **`InvalidLoggableInputException`**

Each exception extends PHP’s `Exception` class and indicates a specific rule violation. These exceptions typically include a descriptive message, such as:

- `"Log message cannot be empty"`
- `"Log level 'VERBOSE' is not recognized"`

They are thrown during the creation of value objects or during validation steps. `InvalidLoggableInputException` is a general exception that may be thrown when a higher-level validation fails (for example, if an entire log input object is inconsistent).

These exceptions are intended to be caught by the application using the module if needed, or at least to fail fast during development so that improper usage is corrected.

---

### Application Layer (Facade)
 
The application layer provides a streamlined and unified interface for interacting with the logging system. It abstracts the underlying domain and infrastructure complexities, enabling application code to perform logging operations through a simple and consistent API.

---

#### LoggingFacade (`Logging\Application\LoggingFacade`)
 
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



---

#### Example Usage

```php
// Obtain the logger from the kernel
$logger = $kernel->logger();

// Log an error message
$logger->error('User not found', ['user_id' => 123]);

// Log with a custom channel and context
$logger->logInput('Payment processed', 'info', 'payments', ['amount' => 100.00]);
```

---

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



This layer ensures that application code interacts with a simple, robust API, while all validation, sanitization, and file I/O are handled by lower layers.

---


### Infrastructure Layer (Integration and I/O)

**Purpose:** The infrastructure layer brings everything together and handles the actual output of logs to
external systems (the filesystem, in this case). It includes factories/bootstrap classes, file handlers, and
adapters.

```
LoggingKernel (Logging\Infrastructure\LoggingKernel): This is a central orchestrator that
bootstraps the Logging Module. It implements LoggingKernelInterface. Typically, your
application will create a LoggingKernel by passing a configuration object, and then use it to
obtain the logging facade.
Construction: When you construct a LoggingKernel, you provide a
LoggingConfigInterface instance (usually an instance of the provided LoggingConfig
class – see Configuration section). The kernel immediately calls its internal setup routines:
bootConfig($config): This method reads the provided configuration. It stores key
configuration objects and values such as the base log path (directory where logs will be
saved), the SanitizationConfig, ValidationConfig, and AssemblerConfig (extracted from the
LoggingConfig). Essentially, it makes the config’s contents available for the next boot
step.
bootComponents(): This method creates all the necessary components of the logging
system, wiring them together:
Domain Security Initialization: It creates a new Validator and Sanitizer,
supplying them with the ValidationConfigInterface and
SanitizationConfigInterface obtained from config. Then it creates a
LogSecurity instance with those. At this point, the domain security services are ready
with the rules from configuration.
Assembler: It instantiates a LogEntryAssembler (see below) with the LogSecurity
instance and the AssemblerConfigInterface from config. The assembler now knows
how to assemble log entries using the domain logic and any default values (like default
channel/level).
Formatter & Path Resolver: It creates a LogLineFormatter (no special inputs
needed, as it likely uses standard date formats and is stateless), and a
LogFilePathResolver, passing in the base log directory path from config. The path
resolver will use this path as the root for all log files.
Writer: It creates a LogFileWriter (which may not need special config, beyond using
default file permissions or modes).
Core Logger: It creates a Logger service (implements LoggerInterface) by
injecting the LogFilePathResolver, LogLineFormatter, and LogFileWriter
```
#### • • • • • ◦ ◦ ◦ ◦ ◦ ◦ ◦


```
created in prior steps. This Logger is now capable of taking a LogEntry and performing
the sequence: resolve path -> format -> write.
Facade: It creates the LoggingFacade (Application layer) by injecting the Logger, the
LogEntryAssembler, and a PSR adapter instance (see next). The LoggingFacade thus
has everything it needs to handle log requests.
PSR Adapter: It also creates a PsrLoggerAdapter (explained below) with the Logger
and LogEntryAssembler. This adapter is kept if someone needs a
PsrLoggerInterface implementation.
After bootstrapping, the kernel holds references to the facade ($this->facade) and
the adapter ($this->adapter).
Usage: The kernel provides two main methods:
logger(): LoggingFacadeInterface – Returns the LoggingFacade instance. In
normal use, your code calls this after constructing the kernel, and then uses the returned
facade to log messages.
psrLogger(): PsrLoggerInterface – Returns the PSR-3 compliant logger adapter.
You would use this if you need to pass the logger to a library or framework that expects a
Psr\Log\LoggerInterface.
```
```
The kernel makes it easy to set up the module: you only need to supply configuration, and it
prepares all components correctly. It also encapsulates the wiring, so the rest of your app just
deals with the LoggingFacadeInterface rather than manually coordinating validators,
writers, etc.
```
```
LogEntryAssembler (Logging\Infrastructure\LogEntryAssembler): This class is responsible
for converting a raw log input (LoggableInput) into a structured LogEntry domain object. It
implements LogEntryAssemblerInterface (defined in the Application contract).
```
```
Responsibilities: Given a LoggableInput, the assembler will perform the following:
Use the provided LogSecurity instance to validate and sanitize each part of the input.
Under the hood, it will likely create new domain value objects:
Construct a LogLevel object from the input’s level string (using LogSecurity to
ensure it’s valid against allowed levels from config).
Construct a LogMessage from the input’s message (sanitizing any problematic content).
Construct a LogContext from the input’s context array (validating keys/values and
masking sensitive data via the sanitizer).
Construct a LogChannel from the input’s channel (or a default channel if none was in
the input, though LoggableInput already defaults it).
Retrieve or set the timestamp (the input already carries a timestamp, defaulted to current
time if not provided).
Handle any exceptions from the above – if any of the domain objects fail to construct due
to invalid data (throwing domain exceptions), the assembler might catch them and throw
a higher-level LogEntryAssemblyException to indicate the assembly process failed.
This gives context that the error happened during log assembly.
If all parts are valid, it creates a new LogEntry object by passing in the LogLevel,
LogMessage, LogContext, LogChannel, and timestamp. Because each of those
objects is already validated, the LogEntry should construct without error.
Return the LogEntry to the caller (usually the LoggingFacade or PsrLoggerAdapter).
AssemblerConfig: The assembler is constructed with an AssemblerConfigInterface. This
config can supply default values or behaviors for assembly. For example, it provides the default
log level and channel to use if the input is missing them (though in our design the
```
#### ◦ ◦ ◦ • ◦ ◦ • • •

#### 1.

#### 2.

#### 3.

#### 4.

#### 5.

#### 6.

#### 7.

#### 8.

#### 9.

#### •


```
LoggableInput already handled defaults). It also provides any custom log levels defined by the
user. The assembler uses this info when creating a LogLevel object – it passes the list of
allowed levels (default + custom) so that LogLevel knows the full set.
```
```
The assembler is a crucial component ensuring that by the time we reach the writing stage, we
have a well-formed, safe LogEntry. It effectively encapsulates the business logic of turning a
request into a log record.
```
```
Logger (Logging\Infrastructure\Logger): The Logger is the service that actually writes logs by
coordinating the path resolution, formatting, and file writing. It implements the
LoggerInterface (defined in Application contract, not to be confused with PSR
LoggerInterface).
```
```
Method: It typically has a single main method log(LogEntryInterface $entry): void.
When called:
It calls the LogFilePathResolver to get the file path for the given LogEntry. The resolver
uses the log entry’s channel and level (and possibly timestamp or other info) to compute
the appropriate file path (for example, "/var/log/myapp/application/info.log"
for a channel "application" and level "info").
It calls the LogLineFormatter to format the LogEntry into a string line. The formatter will
produce a textual representation of the log, including timestamp, level, message, and
context data.
It then calls the LogFileWriter to append this formatted line to the file at the resolved
path.
If any step fails, the Logger may throw exceptions (for instance, if the writer throws a
LogWriteException because the file can’t be written). The Logger might also perform
minimal error handling like using PHP’s error_log() to report if writing fails (ensuring
that a failure to log doesn’t go completely silent).
```
```
The Logger service is used by both the LoggingFacade and the PsrLoggerAdapter. It
encapsulates the final stage of the logging pipeline so that those higher-level components don’t
need to repeat the file-writing logic. By depending on a LoggerInterface, the facade also allows
for the possibility of swapping out the underlying logging mechanism (for example, writing to a
database or external service could be implemented in another LoggerInterface without
changing the facade).
```
```
LogFilePathResolver (Logging\Infrastructure\LogFilePathResolver): A utility class
responsible for determining the correct filesystem path for a given log entry.
```
```
Behavior: It takes in the base log directory (provided during construction) and likely constructs
file paths following a rule:
Typically: {baseLogPath}/{channel}/{level}.log
If a channel is provided and not empty, it creates a subdirectory for that channel under
the base path. If no channel or a default channel is used, it might write directly under
base or under a folder named for the default channel (the default channel "application"
results in an "application" subdirectory).
Example: With base path /var/log/myapp, a log with channel "auth" and level "error"
would map to /var/log/myapp/auth/error.log. A log with the default channel
"application" and level "info" would go to /var/log/myapp/application/info.log.
If channel were an empty string (not normally the case due to defaults), the resolver
might omit the channel directory and use just the level (e.g., /var/log/myapp/
```
#### •

#### •

#### •

#### 1.

#### 2.

#### 3.

#### 4.

#### • • • ◦ ◦ ◦ ◦


```
info.log). However, by default channel is never empty (defaults to "application"), so you
will always see a channel directory.
The resolver does not create directories or files; it purely returns the path string. It assumes the
writer will handle directory creation if needed. This separation ensures the resolver has no side-
effects, making it easy to test and modify (it’s purely string logic).
Internally, it might use the LogDirectory value object to normalize the base path (ensuring
it’s a valid directory string). It then probably uses the LogEntry’s channel and level values
(which are value objects) by retrieving their underlying string values. It constructs the path using
DIRECTORY_SEPARATOR for portability.
```
```
There is minimal chance for exceptions here, except if the base path is somehow invalid (but that
would likely have been caught when the LoggingConfig was set up or by LogDirectory). Thus,
LogFilePathResolver is straightforward: given a valid log entry, it will always return a path.
```
```
LogLineFormatter (Logging\Infrastructure\LogLineFormatter): This class handles converting
a LogEntry object into a formatted text line for the log file.
```
```
Format: The formatter typically includes key pieces of the LogEntry:
Timestamp: formatted as an ISO 8601 string or other recognizable format. In this
module, the code formats the timestamp using DateTimeInterface::ATOM format,
which is the ISO 8601 standard (e.g., "2025-06-20T16:42:05-03:00"). This ensures
timestamps in logs are unambiguous and sortable.
Level: the log level, likely converted to uppercase (e.g., "ERROR", "INFO") for visibility.
Channel: the channel name of the log entry.
Message: the log message text.
Context: the context data, usually serialized to a string. Here, likely the context is
appended as a JSON string or a key-value list.
By default, the format could be something like:
```
```
[<timestamp>] <channel>.<LEVEL>: <message> <context_json>
```
```
For example:
```
```
[2025-06-20T16:42:05-03:00] application.INFO: User logged in
{"user":"tester"}
```
```
In this example, the timestamp is in brackets, followed by the channel and level separated by a
dot, then the message, and the context as a JSON object. The context is included only if non-
empty; otherwise, it might be omitted or just {}.
The use of JSON for context ensures that complex data structures (arrays, etc.) are represented
in a single line. The presence of quotes around keys/strings in JSON is fine; in the above example,
the substring "user" appears in the log line, which is how the test can assert that the key
user is present.
Customization: The formatting style is currently fixed in code. If needed, a developer could
modify this class to change the format (for example, to CSV or to include/exclude certain fields).
There is no config for format provided, but since the class is isolated, it can be swapped or
adjusted if necessary.
```
```
The formatter is stateless and doesn’t throw exceptions under normal circumstances. It assumes
the LogEntry provided is complete and will always produce a string.
```
#### • • • • • ◦ ◦ ◦ ◦ ◦ • • • •


```
LogFileWriter (Logging\Infrastructure\LogFileWriter): This class abstracts the file-writing
operation. It is responsible for taking a file path and a log line and appending the log line to the
file, creating the file or directories if necessary.
```
```
Method: Likely write(string $filepath, string $line): void. When invoked:
It determines the directory portion of the given file path. If the directory does not exist, it
attempts to create it (mkdir with recursive option). It uses 0777 permissions by
default (which usually end up as 0755 on creation due to umask, allowing broad read/
write within reason). If directory creation fails (e.g., permission issue), it will log an error
using PHP’s error_log() and throw a LogWriteException. This exception indicates
that the log could not be written at all (which is critical for the application to know).
After ensuring the directory, it opens or appends to the file. The implementation uses
file_put_contents($filepath, $line, FILE_APPEND). The FILE_APPEND flag
ensures that new log entries are added to the end of the file without truncating existing
content. If the file doesn’t exist, file_put_contents will create it.
It checks the result of the write operation. If file_put_contents returns false, that
means the write failed (disk full, permission error, etc.). In that case, it again uses
error_log() to note the failure and throws LogWriteException to signal the error.
On success, the method returns (void). The log line would now be persisted in the
appropriate file.
The writer handles low-level errors gracefully by converting them to exceptions and logging
minimal info to PHP’s system logger. It does not attempt retries or further logic – it’s a simple
straight write. The expectation is that if writing fails, it’s an environmental issue that the
application needs to address (e.g., fix permissions or disk space).
```
```
Performance: Writing directly with file_put_contents for each log entry is straightforward
but could be slow under very high log volumes (each call opens and closes the file). For most
applications and moderate logging, this is acceptable. If needed, this could be extended to use
stream handles or buffering, but the current implementation prioritizes simplicity and reliability
(ensuring each log entry is written immediately).
```
```
PsrLoggerAdapter (Logging\Infrastructure\PsrLoggerAdapter): This class implements
PublicContracts\Logging\PsrLoggerInterface, which is equivalent to the PSR-
LoggerInterface. Its role is to adapt the module’s internal logging to the standardized PSR
interface.
```
```
Construction: The adapter is constructed with two things: a LoggerInterface (the module’s
core Logger service) and a LogEntryAssemblerInterface. Notice it does not use
LoggingFacade; instead, it bypasses the facade to directly use the lower-level services. This is
intentional: the adapter is meant for external code that calls PSR-3 methods. The adapter will
take those calls and do assembly + writing in one go, without going through the facade’s channel
logic (because PSR-3 doesn’t have a channel concept).
Methods: It implements all methods of PsrLoggerInterface:
log($level, $message, array $context) – the generic method, and specific level
methods like info($message, $context), etc. The specific methods typically call
log() with the appropriate level.
When log() is called on the adapter:
It will likely create a LoggableInput from the provided $message, $level, and
$context. Here, it might always use the default channel (since PSR calls don’t specify a
channel). By default, that would be "application".
```
#### •

#### •

#### 1.

#### 2.

#### 3.

#### 4.

#### • • • • • ◦ ◦ ◦


```
It then calls $assembler->assembleFromInput($input) to get a LogEntry (this
triggers validation and sanitization exactly as it would via the facade).
Then it calls $logger->log($entry) to actually write the entry out.
If any exception is thrown during assembly (invalid level or message), the adapter might
catch a domain exception and either rethrow it as a
Psr\Log\InvalidArgumentException (as PSR-3 specifies for invalid level) or let it
bubble. Proper PSR-3 implementation should throw
Psr\Log\InvalidArgumentException for an invalid log level, which may be done
internally if they mapped InvalidLogLevelException accordingly. It’s not explicitly
stated here, but a robust adapter would handle that.
The adapter essentially replicates what the LoggingFacade does, but it’s solely focused on
compliance with the PSR interface. It does not expose the channel-based logInput
method (PSR-3 knows nothing of channels).
Usage: Normally, you don’t use PsrLoggerAdapter directly in your code. Instead, you obtain it via
$kernel->psrLogger() after initializing the kernel. You then pass that $psrLogger to any
library or component that expects a Psr\Log\LoggerInterface. From that point, when the
library calls (for example) $psrLogger->error("Something broke", [...]), our adapter
will execute the module’s logging process and write to the files just like if you called the facade.
This ensures third-party integrations log through the same system, keeping logs unified.
By implementing the PSR interface ourselves, we avoid requiring an external psr/log package
and we have control over how context and levels are handled (we still align with PSR standards,
e.g., context placeholders could be resolved if implemented, but in our case we mainly just pass
context along for JSON output).
```
### Configuration Layer

**Purpose:** The configuration classes define default behaviors and allow for customization of the logging
module’s validation and sanitization rules. All config classes reside under the
Config\Modules\Logging namespace (which is separate from the Logging module’s main
namespace to indicate they can be treated as configuration data).

```
LoggingConfig (Config\Modules\Logging\LoggingConfig): This is the main configuration class
that implements LoggingConfigInterface. It aggregates all other config parts and is the
one you pass to LoggingKernel.
Usage: When instantiating LoggingConfig, you provide the base directory path for logs:
```
```
$config = new LoggingConfig('/path/to/log/directory');
```
```
In the constructor, it will validate that the path string is not empty (throwing an
InvalidArgumentException if it is). It does not create the directory (that’s handled later by
writer), but it ensures you gave a sensible path.
Composition: LoggingConfig creates internally:
a SanitizationConfig object,
a ValidationConfig object,
an AssemblerConfig object,
possibly sets up any custom values. It stores these, as well as the base path.
Interface: It provides methods as per LoggingConfigInterface:
baseLogPath(): string – returns the base log directory path.
sanitizationConfig(): SanitizationConfigInterface – returns the
SanitizationConfig instance.
```
#### ◦ ◦ ◦ ◦ • • • • • ◦ ◦ ◦ ◦ • ◦ ◦


```
validationConfig(): ValidationConfigInterface – returns the ValidationConfig
instance.
assemblerConfig(): AssemblerConfigInterface – returns the AssemblerConfig
instance. The LoggingKernel uses these to get the config objects for initializing
components.
```
```
This class is also a convenient place to change which config classes are used. If a developer
wanted to extend or modify configuration behavior, they could subclass LoggingConfig to
override the creation of these components (for example, use a custom ValidationConfig with
different rules). By default, it uses the module’s provided configs which cover common needs.
```
```
AssemblerConfig (Config\Modules\Logging\AssemblerConfig): Implements
AssemblerConfigInterface. It defines configuration for the assembler and related domain
defaults:
```
```
defaultLevel(): string – The default log level to use if none is provided. By default, this
returns "info". This ensures that every log entry has a level; the LoggableInput uses it when
$level is null.
defaultContext(): array – The default context array to use if none provided. Typically, this
is an empty array []. It’s there for interface completeness.
defaultChannel(): string – The default channel name if none is specified. By default, this
returns "application". This value is used by LoggableInput/assembler so that even if you
don’t specify a channel, logs are categorized under a general "application" channel.
customLogLevels(): array – Any additional log levels (beyond the standard ones) that the
system should recognize. By default, this might return the list of standard levels (since in code
they provided a CustomLogLevels class that initially lists the standard levels anyway). However,
the intention is that a developer can extend the list. For instance, if your application wants a level
"verbose" or "trace", you could add it here and the Validator/LogLevel would allow it. Out of the
box, customLogLevels() uses the static list from CustomLogLevels::list(). By default
that list includes
'debug','info','notice','warning','error','critical','alert','emergency' –
essentially reiterating the standard levels. To truly add custom ones, you’d modify that class.
maskToken(): string – This returns a token used to mark sanitized content in logs. It ties
into how the assembler or sanitizer might indicate that something was removed. The default
implementation might draw this from a default values enum. In our module, the default mask
token in AssemblerConfig is defined as "[SANITIZED_BY_CHANNEL]" (meaning content was
sanitized; the phrase hints it could mention the channel, although by default it's a static string).
This token might be inserted in logs in place of entire messages or context entries if they were
deemed too sensitive. It’s worth noting that the Sanitizer uses a different default mask token
("[MASKED]") at a lower level for context values, but the assembler’s mask token could be
used for marking entire log messages or perhaps for channel-specific sanitization notes. In
practice, for simplicity, consider the mask token as "[MASKED]" in output, as that is what the
Sanitizer applies to context values.
```
```
AssemblerConfig uses AssemblerDefaultValues (an enum AssemblerDefaultValues)
internally to get the defaults for level, context, channel, and mask token. This enum is basically a
set of constants:
```
```
DEFAULT_LEVEL => 'info'
DEFAULT_CONTEXT => []
DEFAULT_CHANNEL => 'application'
```
#### ◦ ◦ • • • • • • • • ◦ ◦ ◦


```
DEFAULT_MASK_TOKEN => '[SANITIZED_BY_CHANNEL]' The AssemblerConfig returns
those, unless overridden.
```
```
ValidationConfig (Config\Modules\Logging\Security\ValidationConfig): Implements
ValidationConfigInterface. It provides all the parameters needed by the Validator to
enforce rules.
```
```
Key methods include:
defaultStringMaxLength(): int – Maximum length for generic strings if not
otherwise specified. Default is 255 characters. This could be used as a catch-all limit to
prevent extremely long strings (like an entire stack trace pasted into a single context field)
from overwhelming logs.
stringForbiddenCharsRegex(): string – A regex pattern of characters disallowed
in strings. By default, this might be /[\x00-\x1F\x7F]/, which blocks control
characters (non-printable ASCII and DEL). This prevents log messages or context from
containing newlines or other control codes that could mess up log formatting.
contextKeyMaxLength(): int – Max length for context array keys. Default could be
```
128. This ensures keys (usually short identifiers) are not absurdly long.
    contextValueMaxLength(): int – Max length for individual context values (if they
are strings). Default could be 256. Larger values might be truncated or cause validation to
fail.
    directoryRootString(): string – A string that represents the root directory.
Default is / (forward slash). This could be used by the Validator to check if a given
directory path attempts to reference the filesystem root inappropriately or to enforce
absolute paths.
    directoryTraversalString(): string – A substring that should not appear in
directory paths, defaulting to ... The Validator uses this to prevent directory traversal
(for example, if someone tried to set a base path to /var/log/../etc, it would be
caught).
    logMessageMaxLength(): int – Maximum length for a log message. Default is 2000
characters. This prevents extremely verbose messages from a single log entry. If a
message is longer, the Validator might truncate it or throw an exception.
    logMessageTerminalPunctuationRegex(): string – A regex pattern to detect
undesired ending punctuation in messages. Default is /[.!?]$/u, which matches if a
message ends in a period, exclamation mark, or question mark. This might be a stylistic
choice: perhaps the system chooses to warn or modify messages that end with
punctuation (to avoid duplicate punctuation when appending context, or just a logging
style guideline). The Validator could strip a trailing period from messages, for example.

```
ValidationConfig uses an enum DefaultValidationValues to get its default constants. This
enum defines the values mentioned above (e.g., 255 for max length, regex for control chars,
etc.). The idea is that if you need to change one of these defaults, you could either override
ValidationConfig or modify the enum values (the latter is not dynamic at runtime, but can be
edited in code).
```
```
SanitizationConfig (Config\Modules\Logging\Security\SanitizationConfig): Implements
SanitizationConfigInterface. It supplies the Sanitizer with what it needs to know:
```
```
sensitiveKeys(): array – Returns a list of keys that are considered sensitive. By default,
this is provided by CustomSensitiveKeys::list(). Out of the box, this list might include
common sensitive terms (the code allows extension, but likely includes "password",
```
#### ◦ • • ◦ ◦ ◦ ◦ ◦ ◦ ◦ ◦ • • •


```
"passwd", "secret", "api_key", "cpf", etc., and it’s meant to be adjusted as needed).
Any context entries with a key in this list will have their values masked.
sensitivePatterns(): array – Returns a list of regex patterns for sensitive data in values.
Provided by CustomSensitivePatterns::list(). For example, this might include patterns
matching a credit card number, an email address, or a Brazilian CPF format. If a context value
matches one of these patterns, it will be masked even if the key name isn’t explicitly sensitive.
maxDepth(): int – The maximum depth for nested context structures that the Sanitizer will
traverse. Default is 8 (via DefaultSanitizationValues::MAX_DEPTH). This prevents
extremely deep arrays from causing recursion issues or performance problems.
maskToken(): string – The string used to replace sensitive data. Default is "[MASKED]"
(from DefaultSanitizationValues::MASK_TOKEN). This is the literal token that will appear
in place of sensitive values in the context. For example, if context was ["password" =>
"secret123"], after sanitization it might become ["password" => "[MASKED]"].
maskTokenForbiddenPattern(): string – A regex that flags any dangerous content in the
mask token or related usage. Default might be /[\x00-\x1F\x7F]|base64|script|php/i.
This is a security check ensuring that the mask token itself (or any replacement text) doesn’t
inadvertently contain something that looks like an attempt to hide malicious data. In simpler
terms, it might ensure the mask token doesn’t contain non-printable chars or certain substrings
like "base64" or "script" that could hint at an attempt to inject masked content. The Sanitizer
could use this to avoid masking with a token that might be interpreted as something else.
SanitizationConfig’s constructor sets up the arrays using CustomSensitiveKeys and
CustomSensitivePatterns (which by default might just return an empty array or a basic set
```
- the idea is developers extend those). It uses DefaultSanitizationValues for the numeric
and token defaults.

```
This config can be extended if needed: for example, an application could subclass
SanitizationConfig to add more keys or patterns at runtime, or simply edit the
CustomSensitiveKeys/Patterns classes to include more entries.
```
```
CustomSensitiveKeys / CustomSensitivePatterns
(Config\Modules\Logging\Security\CustomSensitiveKeys, CustomSensitivePatterns): These
classes (or enums, if implemented as such) are simple containers for additional user-defined
sensitive terms.
```
```
CustomSensitiveKeys::list() returns an array of strings that should be considered
sensitive keys. By default, it might include some placeholders or be empty. It is intended for the
developer to modify. For instance, one could add 'ssn' or 'credit_card' to this list to
ensure those keys are always masked.
CustomSensitivePatterns::list() returns an array of regex patterns (strings or RegExp
objects) for sensitive data in values. For example, a pattern for a 11-digit number (CPF) or a
pattern for credit card numbers (/\d{4}-\d{4}-\d{4}-\d{4}/ etc.). By default, it might
include none or some example patterns. The developer can add to this list if certain sensitive
data should be caught by content rather than key.
```
```
These classes make it easy to adjust what the Sanitizer looks for without digging into the
Sanitizer code. They centralize such configuration.
```
```
CustomLogLevels (Config\Modules\Logging\CustomLogLevels): Similarly, this class provides
a list of non-standard log levels accepted by the system.
```
#### • • • • • • • • • • •


```
CustomLogLevels::list(): array – By default, this returns the standard PSR-3 levels
(which technically aren’t "custom", but the idea is that all allowed levels are defined here). Out of
the box, the list is
['debug','info','notice','warning','error','critical','alert','emergency'].
If a developer wants to support an additional level name, they would add it to this list (for
example 'verbose' or 'trace').
The Assembler/Validator uses this combined with the LogLevel’s internal defaults. The
LogLevel class in domain also had a default list of levels (the same ones). The presence of this
CustomLogLevels config suggests that ultimately the allowed levels list comes from
configuration (which is better for extensibility).
If you add a new level via CustomLogLevels, ensure that any code using log levels (like
LoggingFacade’s methods or external systems) is aware of the new level string.
```
**Summary of Config Defaults:** Out of the box, the module’s config is set to: - Default log level: **info**. -
Default log channel: **application**. - Allowed log levels: **debug, info, notice, warning, error, critical,
alert, emergency** (standard 8 levels). - Default mask token for sensitive data: "[MASKED]" (and a
special "[SANITIZED_BY_CHANNEL]" token used internally for marking entire messages, though by
default it doesn’t appear in output). - Sensitive keys: includes common terms like "password" and a few
others (the exact list can be extended). - Sensitive patterns: none or a few examples (should be
extended per project needs). - Max message length: 2000 chars; max context value length: 256 chars;
max context depth: 8. - Forbidden characters: Control chars in any input are not allowed. - Directory
traversal is prevented (.. not allowed in paths).

All these can be adjusted by editing the configuration classes, ensuring the logging behavior can be
tailored as needed.

### Public Contracts (Interfaces)

The **PublicContracts\Logging** namespace defines interfaces that formalize the module’s API and allow
for substitutions or mocking in tests:

```
LoggingFacadeInterface: Defines the methods that the LoggingFacade must implement (as
described earlier). This interface extends no external interface directly, but it includes all the
PSR-3 level methods and the additional logInput method. By coding to this interface, the rest
of your application can remain unaware of the concrete LoggingFacade class. In theory, you
could swap in a different implementation of LoggingFacadeInterface (for example, a stub for
testing, or another logging system) without changing your code that uses it.
LoggingKernelInterface: Defines the contract for the LoggingKernel. It includes at least
logger(): LoggingFacadeInterface and psrLogger(): PsrLoggerInterface. This
allows the kernel to be mocked or replaced if needed. It also helps if you want to integrate the
kernel differently; as long as it provides those two methods, the rest of the system can use any
kernel that implements the interface.
PsrLoggerInterface: This interface mirrors the PSR-3 LoggerInterface (with methods
emergency through debug and log ). The PsrLoggerAdapter class implements this
interface. We provide our own interface here likely to avoid a direct dependency on the psr/
log package, but it’s fully compatible. Code expecting a Psr\Log\LoggerInterface can use
an instance of PsrLoggerInterface from this module, since it has the same methods. (If
needed, one could even have PsrLoggerInterface extend Psr\Log\LoggerInterface for full
interop, but that’s an internal detail.)
LoggerInterface (Logging\Application\Contract\LoggerInterface): This interface (not to
confuse with PSR) defines the log(LogEntryInterface $entry) method for our internal
```
#### • • • • • • •


```
Logger service. The Logging\Infrastructure\Logger class implements it. By using an
interface, the LoggingFacade and Kernel only depend on the interface, allowing the actual
logging mechanism to be swapped. For example, if one wanted to direct logs to a different sink
(say a database or an API), one could implement a new LoggerInterface that sends logs there,
and then configure the kernel to use that instead of the file-based Logger. (Currently, the kernel
always uses the file Logger, but the abstraction is in place.)
LogEntryAssemblerInterface (Logging\Application\Contract\LogEntryAssemblerInterface):
Defines the assembler’s behavior, likely with a method like
assembleFromInput(LoggableInputInterface $input): LogEntryInterface. The
LogEntryAssembler implements this. This interface could allow alternate assembly logic or
facilitate testing by mocking the assembler.
LoggableInputInterface, LogEntryInterface: Domain contracts for the value objects. These
just ensure that the value objects expose certain methods (e.g., LogEntryInterface likely
has getters like getLevel(), getMessage(), getContext(), getChannel(),
getTimestamp()). The rest of the system (formatter, resolver) uses these interfaces to interact
with log entries without needing the concrete class (improving decoupling).
Config Interfaces: LoggingConfigInterface, AssemblerConfigInterface,
ValidationConfigInterface, SanitizationConfigInterface mirror the methods of
the config classes described. These allow you to provide different config implementations to the
kernel. For instance, one could create a LoggingConfig that reads settings from a PHP file or
environment variables but still implements LoggingConfigInterface so that LoggingKernel
accepts it. In our module, the provided classes already implement these interfaces.
```
In summary, the PublicContracts layer ensures that each major component has an interface type. This
not only guides the structure (developers know what methods are available) but also makes the module
more modular and testable. One can swap implementations if needed or use these interfaces to mock
behaviors in unit tests (e.g., mocking LoggerInterface to verify that a log entry would be written,
without actually writing a file).

## Logical Flow and Functioning

To understand how all these pieces work together, consider a typical scenario: **logging a message via
the facade**. Below is the step-by-step flow for a central use case – recording a log entry – with emphasis
on the sequence of interactions, validations, and outcomes:

```
Initiating a Log: The client code (your application) invokes a logging call. For example, in your
code you might do:
```
```
$logger = $loggingKernel->logger(); // Obtain LoggingFacadeInterface
from the kernel
$logger->info("User login successful", ["user" => "tester"]);
```
```
This call is requesting to log a message "User login successful" at the info level with a context
containing the user name. At this point, the LoggingFacade (through info() method) is
engaged.
Facade Handling: The LoggingFacade receives the call to info($message, $context).
Inside the info method (defined by LoggingFacadeInterface), it will typically call the more
generic method with the appropriate parameters, something akin to:
```
#### •

#### •

#### •

#### 1.

#### 2.


```
$this->logInput($message, "info", null, $context);
```
```
Here, logInput is used with level="info" and channel=null (meaning no specific
channel was given, so default will be used). All level-specific methods (info, error, etc.) likely
funnel into logInput or log.
Creating Loggable Input: Within LoggingFacade::logInput(), the code creates a new
LoggableInput object to encapsulate this log request. For example:
```
```
$input = new LoggableInput(
message: "User login successful",
level: "info",
context: ["user" => "tester"],
channel: null, // no channel specified by user
timestamp: null // timestamp not provided, will default
);
```
```
The LoggableInput constructor runs its validation:
It checks the message is not empty (it’s not, so OK).
It sees a level "info" is provided, likely validates that (if level were null, it would substitute default
"info"; here it's already "info", which is allowed).
It takes the context array (not null, so uses as is; it might ensure it's an array – it is).
Channel is null: LoggableInput will substitute the default channel. It calls its
validateChannel(null) which returns "application" (the default channel name). So
now the LoggableInput’s channel property is set to "application".
Timestamp is null: the constructor sets it to new DateTimeImmutable() with current time.
The result is a LoggableInput with: message = "User login successful", level = "info",
context = ["user" => "tester"], channel = "application", timestamp = (current time).
If any of these validations had failed (e.g., empty message), an exception would be thrown here
and the logging process would abort with an error.
Assembling LogEntry: The facade then passes this LoggableInput to the assembler:
```
```
$entry = $this->assembler->assembleFromInput($input);
```
```
Now inside LogEntryAssembler :
It receives the LoggableInput. It retrieves from it the raw values (level string "info", message
string, context array, channel string "application", timestamp object).
It begins constructing domain value objects with the help of LogSecurity :
Creates a LogLevel object: calls new LogLevel("info", $logSecurity,
$customLevelsArray). The LogLevel constructor checks "info" against allowed levels.
Allowed levels are the default ones plus any custom from config. "info" is recognized, so
it’s valid. It likely stores it in lowercase. (If "info" wasn’t in allowed list, this step would
throw InvalidLogLevelException).
Creates a LogMessage object: calls new LogMessage("User login successful",
$logSecurity). Inside LogMessage, it asks LogSecurity’s Sanitizer to sanitize the
message if needed (perhaps removing control characters or trimming trailing
punctuation). Given this message is simple text, sanitization probably returns it
unchanged. Then LogMessage validates it’s not empty (and maybe length < 2000, which it
```
#### 3.

#### 4.

#### 5.

#### 6.

#### 7.

#### 8.

#### 9.

#### 10.

#### 11.

#### ◦

#### ◦


```
is). It stores the message. (If the message were empty or too long, an
InvalidLogMessageException could be thrown here).
Creates a LogContext object: calls new LogContext(["user" => "tester"],
$logSecurity). The LogContext will iterate over the context array and for each key/
value:
Validate the key "user" (e.g., ensure it's a string, not too long – it's fine).
Validate the value "tester" (e.g., if it’s a string, ensure length < 256 – it is).
Use the Sanitizer to check if the key is sensitive or value matches a pattern. "user" is likely
not in the sensitive keys list by default, and "tester" probably doesn't match a sensitive
pattern. So the context might remain ["user" => "tester"] with no masking. If
there were sensitive data (say the key was "password"), the Sanitizer would replace the
actual value with "[MASKED]". The LogContext would then contain ["password" =>
"[MASKED]"].
If any context key/value failed validation (too long, or key has forbidden chars), an
InvalidLogContextException would be thrown.
Otherwise, the sanitized context is stored in the LogContext object.
Creates a LogChannel object: calls new LogChannel("application",
$logSecurity). The LogChannel validates the channel string "application" (should be
fine: it’s not empty, likely all alphabetic). If someone had passed a weird channel name
with slashes or .., this would throw InvalidLogChannelException, but "application"
is safe. It might also sanitize the channel (e.g., trim whitespace).
Retrieves the timestamp from input (already a DateTimeImmutable, which likely doesn’t
need validation beyond maybe ensuring it’s not in the future or something – generally not
checked, since future logs are not invalid).
If all the above succeed, the assembler now calls
new LogEntry($logLevel, $logMessage, $logContext, $logChannel,
$timestamp). The LogEntry’s constructor simply assigns these to its properties (it trusts they
are already validated by their own classes). The LogEntry is now an immutable representation of
this event.
The assembler returns the LogEntry object. If any exception was thrown in the process, the
assembler would either propagate it or wrap it in a LogEntryAssemblyException
(depending on implementation). In our module, an assembly failure likely throws
LogEntryAssemblyException with the original error as cause, indicating that something in
input was invalid despite LoggableInput’s initial check.
Logging (Writing) the Entry: Back in the facade, we now have a LogEntry object. The
LoggingFacade proceeds to hand this off to the Logger service:
```
```
$this->logger->log($entry);
```
```
Inside Logger::log(LogEntry $entry):
It calls the LogFilePathResolver :
```
```
$filepath = $this->pathResolver->resolve($entry);
```
```
The resolver takes the entry’s channel and level. The entry’s channel is "application", level is
"info". With the base path (say, /var/log/myapp provided in config), it constructs:
/var/log/myapp/application/info.log. It returns that path as a string. (If directory or file
doesn’t exist yet, that’s fine – resolver doesn’t check).
It calls the LogLineFormatter :
```
#### ◦ ◦ ◦ ◦ ◦ ◦ ◦ ◦

#### 12.

#### 13.

#### 14.

#### 15.

#### 16.


```
$line = $this->formatter->format($entry);
```
```
The formatter retrieves the timestamp from the entry, formatting it as an ISO8601 string (e.g.,
2025-06-20T16:42:05-03:00). It gets the channel ("application"), level ("info" –
likely uppercased to "INFO"), message ("User login successful"), and context array
(["user"=>"tester"]). It then produces a single line string. Following our assumed format,
that line might look like:
```
```
[2025-06-20T16:42:05-03:00] application.INFO: User login successful
{"user":"tester"}
```
```
(There might be a newline character appended at the end of the line, depending on
implementation, to ensure each log entry is on its own line in the file.)
Next, it calls the LogFileWriter to actually write this line to the file:
```
```
$this->writer->write($filepath, $line);
```
```
The writer sees $filepath = "/var/log/myapp/application/info.log". It checks if /
var/log/myapp/application directory exists. If not, it attempts to create it. Suppose this is
the first time we log anything; it will create /var/log/myapp (if not existing) and then /var/
log/myapp/application. On success, it then opens/creates info.log in append mode and
writes the formatted line to it, followed by a newline. If writing succeeds, great – the log entry is
now persisted. If it fails (for example, if the directory cannot be created due to permissions), the
LogFileWriter will throw a LogWriteException. The Logger does not catch this, so it bubbles
up to the LoggingFacade call. The facade also does not explicitly catch exceptions in this design,
so ultimately the info() call in user code would throw. In a well-set environment, this should
not happen; but if it does, the exception message plus the fact that error_log was called would
help diagnose the issue (like "could not create directory" or "could not write file").
If no exception, the Logger returns void, and the LoggingFacade’s logInput method
completes.
Completion: The logging call from the client returns (implicitly, since info() is void). The
message has been logged. On disk, you will find a new file at .../application/info.log
with a line containing the timestamp, channel, level, message, and context. If multiple log
entries were written, they would appear on separate lines in that file.
Subsequent calls: If later another call is made, e.g. $logger->error("File not found",
["file" => "abc.txt"]), the same process repeats:
Possibly a different channel or level directs it to a different file (for "error", it would target .../
application/error.log in the "application" channel).
The context ["file"=>"abc.txt"] would be included in the log line. If "file" were considered
sensitive (not by default), it would be masked. But it's not sensitive by default, so it prints as
"file":"abc.txt" in context.
The module ensures even error messages are sanitized for control characters or forbidden
content.
The file writer will create the error.log file if it doesn’t exist.
Channel usage: If you explicitly log to a different channel via logInput or a custom method,
e.g.:
```
#### 17.

#### 18.

#### 19.

#### 20.

#### 21.

#### 22.

#### 23.

#### 24.

#### 25.


```
$logger->logInput("Admin password changed", "notice", "security",
["admin_user" => "alice"]);
```
```
Then:
The default channel is overridden with "security".
The path resolver will put this in .../security/notice.log.
If "admin_user" isn’t in sensitive keys, its value "alice" remains. If the message or context had
something sensitive (say the message contained "password"), depending on config it might or
might not be masked (the word "password" in the message text might not be automatically
masked unless explicitly coded, typically the sanitizer focuses on context values and maybe full
message if needed).
The rest of the flow is identical. This way, channels can separate log output (perhaps for different
subsystems).
```
```
The mask token "[SANITIZED_BY_CHANNEL]" suggests that if entire messages need to be
hidden based on channel, one could implement such logic. For example, one might decide that
any log in channel "security" should mask certain details in the message, and use that token. By
default, no such automatic per-channel masking is implemented beyond context values.
```
```
Error handling & exceptions: Throughout the flow:
```
```
Domain exceptions (invalid input) are thrown early, preventing bad data from reaching files. This
is intentional: better to not log than to log incorrect or unsafe data. The caller should catch these
if they anticipate possible invalid inputs.
The module does not catch exceptions except where it can add context (like wrapping an
assembly exception). It assumes the application may either handle them or they’ll be noticed
during development. In production, such exceptions would indicate a misuse or environment
problem (and should be fixed).
One built-in fallback is the use of error_log() in the writer. If the file system is not writable, it
uses PHP’s own logging to at least record that it tried to log something but failed. This prevents
silent failures.
```
```
The module does not swallow exceptions about logging; it propagates them, because failing to
log might be critical for auditing. However, in some cases, you might choose to catch
LogWriteException around your logging calls if you want to handle logging failures
gracefully (e.g., notify an admin or switch to an alternative logging mechanism temporarily).
```
```
Test verification (as seen in LoggingTest): After such logging calls, one could inspect the
filesystem:
```
```
The expected file (e.g., application/info.log) should exist.
Its contents should contain the message, context keys, and a timestamp. For instance,
the test checked that the year (from the current timestamp) appears in the log line, to
ensure the timestamp was written. It also checked that the message substring and
context key were present.
Each call to log at a different level produces or appends to the corresponding file
(info.log, error.log, etc.), thanks to the path resolution scheme.
```
#### 26.

#### 27.

#### 28.

#### 29.

#### 30.

#### 31.

#### 32.

#### 33.

#### 34.

#### 35.

#### 36.

#### ◦

#### ◦

#### ◦


This flow illustrates how the module ensures that a log entry goes through multiple stages (validation,
sanitization, assembly, formatting, writing) seamlessly. Each stage has specific responsibilities and error
handling, making the overall process robust and the code easier to maintain.

## Requirements and Dependencies

**Environment Requirements:**

```
PHP Version: The module requires PHP 8.1 or higher. It makes use of PHP 8+ features such as
readonly properties in value objects, typed properties , union types (e.g., string|
Stringable type hints in the interface), and Enums for config default values. PHP 8.1
introduced the enum construct and readonly properties, hence 8.1 is the minimum version for
full compatibility.
PHP Extensions: No special extensions are needed for basic functionality aside from standard
PHP extensions. However, the module uses the Normalizer class from the PHP Intl
(Internationalization) extension in the Sanitizer (for Unicode normalization of strings). In most
PHP distributions, Intl is enabled by default. Ensure that the intl extension (which provides
Normalizer) is available. If you plan to use the sanitization features on Unicode text (e.g., user
names with accents), the normalizer will help unify character representations.
File System Access: The module writes log files to disk. It assumes the PHP process has write
permissions to the configured base log directory. Before using the module in production,
ensure that the directory you specify (and its parent directories) are writable by the web server
or CLI user. For example, if logs go to /var/log/myapp, set proper ownership or permissions
for that path.
No External Libraries: This is a pure PHP solution. It does not require any external composer
packages. (Even PSR-3 compliance is handled internally without needing psr/log). Therefore,
you can use it without additional installs. It’s self-contained as long as your autoloader can load
its classes.
```
**Dependencies Between Components:**

```
The module is self-contained; its components depend on each other as described (Facade
depends on Assembler and Logger, Logger on Writer etc.), but no outside library integration is
required out of the box.
If integrated into a larger project, it will depend on that project’s autoloading mechanism (PSR-4
autoload for the namespaces Logging\*, PublicContracts\Logging\*, and
Config\Modules\Logging\*).
PSR-3 Interop: If you do use the psr/log package or type hints from it, note that
PublicContracts\Logging\PsrLoggerInterface is functionally equivalent. You may cast
or wrap accordingly. The module’s PsrLoggerAdapter can be used wherever a
Psr\Log\LoggerInterface is expected.
```
**Performance Considerations:**

```
Writing logs is I/O bound. For high-frequency logging, ensure the storage medium can handle
frequent small writes. The module appends to files; if extremely high log volume is expected,
consider using a RAM disk for logs or batching log writes (not currently built-in).
The default validation and sanitization checks (string lengths, regex) are lightweight and will not
significantly impact performance for normal log message sizes. The most expensive operations
might be the regex replacements for sanitization and the JSON encoding for context, but these
are negligible for small context data.
```
#### • • • • • • • • •


```
The module does not implement log rotation. If you run a long-running application, log files will
grow indefinitely. It’s recommended to use external log rotation tools (e.g., logrotate on Linux or
custom scripts) to rotate or archive logs periodically. The Logging Module will continue
appending to whatever file name is configured; if an external rotation renames the file and
creates a new empty file with the same name, the module will seamlessly start writing to the
new file.
```
In summary, ensure PHP 8.1+, proper file permissions, and optionally Intl extension for full
functionality. There are no other software dependencies, making the module easy to drop into many
environments.

## Installation

Installing the Logging Module in your project can be done in a couple of ways. Since it’s a standalone
module without external dependencies, you can integrate it manually or via Composer.

**1. Using Composer (if available as a package):** If the module is packaged for Composer (check
packagist or repository), you would run something like:

```
composer require your-vendor/logging-module
```
This would install the module and its classes. You’d then include Composer’s autoloader and be ready to
use the module’s classes. The Logging\, PublicContracts\Logging\, and
Config\Modules\Logging\ namespaces should be autoloaded by Composer.

_(If the module is not on packagist, skip to manual installation.)_

**2. Manual Integration:** You can also add the module to your project manually: - Download or copy the
module’s source code, preserving the folder structure: - The Logging/ directory containing Domain,
Application, Infrastructure subfolders. - The PublicContracts/Logging/ directory for interfaces. -
The Config/Modules/Logging/ directory for configuration classes. - Place these directories in an
appropriate location in your project (for example, under a src/ or modules/ directory in your
project). - Set up PSR-4 autoloading for these namespaces. For instance, if you placed the Logging
folder under src/modules/, your composer autoload section might look like:

#### {

```
"autoload": {
"psr-4": {
"Logging\\":"src/modules/Logging/",
"PublicContracts\\": "src/modules/PublicContracts/",
"Config\\Modules\\Logging\\": "src/modules/Config/Modules/Logging/"
}
}
}
```
Then run composer dump-autoload to update the autoloader. (Adjust the paths according to where
you put the files.) - Alternatively, if not using Composer’s autoloader, you can manually include the files
or write your own simple autoloader. The key is that when you use a class like

#### •


Logging\Infrastructure\LoggingKernel, PHP knows how to load the file. Following PSR-4,
LoggingKernel would be in Logging/Infrastructure/LoggingKernel.php. - **Verify Autoload:**
Test by writing a small script that creates a LoggingConfig and LoggingKernel (see Usage
examples below). If no “class not found” errors occur, your autoload is set up correctly.

Once the classes are available to your application, proceed to configuration and initialization.

**Directory Permissions:** Before first use, decide where your log files will reside. Common practice is a
dedicated logs/ directory in your project or a system log directory. Ensure this path is writable. For
example, if using Linux and your web server runs as user www-data, you might:

```
mkdir/var/www/myapp/logs
chownwww-data:www-data/var/www/myapp/logs
```
and then use /var/www/myapp/logs as the base path in LoggingConfig.

**Integration in Application:** Typically, you will create the LoggingKernel early in your application’s
initialization (for example, in a bootstrap file or a service container configuration). You might register
the LoggingFacade in a central place (so other components can request a logger). The usage section
provides a concrete code example of this.

After installation, you don’t need to perform any additional setup beyond creating a LoggingKernel with
appropriate config. The module doesn’t require any database migrations or external service setups.

## Configuration

Configuring the Logging Module is straightforward, as most defaults are sensible. The primary
configuration you must provide is the **log directory path**. Additional configuration is optional and
involves tweaking the default values through code.

**Basic Configuration via LoggingConfig:**

To configure, instantiate the LoggingConfig class with your chosen log directory. For example:

```
use Config\Modules\Logging\LoggingConfig;
use Logging\Infrastructure\LoggingKernel;
```
```
$logDir = __DIR__. '/logs'; // directory where log files will be
stored
$config = new LoggingConfig($logDir);
$kernel = new LoggingKernel($config);
$logger = $kernel->logger();
```
In this snippet: - $logDir is set to a “logs” directory in the current directory. You can change this to
any path (absolute or relative). If relative, it will be resolved relative to the current working directory of
the script. - new LoggingConfig($logDir) creates the config. It will throw an exception if
$logDir is an empty string. If $logDir does not exist on disk, that’s okay at this point; it will be


created later by the writer as needed. - new LoggingKernel($config) boots the module using that
configuration. The kernel now knows about the base path and uses default values for everything else.

After these steps, $logger (the LoggingFacade) is ready to use. This is essentially the minimal
configuration needed: just the log directory.

**Customizing Validation and Sanitization:**

If you want to customize the behavior: - **Add/Remove Log Levels:** Edit
Config\Modules\Logging\CustomLogLevels::list(). For example, to add a "verbose" level, you
could modify it:

```
public static function list(): array {
return [
'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert',
'emergency',
'verbose' // added custom level
];
}
```
Now the Validator will accept "verbose" as a valid level. You should also handle how to call it (e.g., via
$logger->log('verbose', '...') since there's no dedicated method for it). - **Sensitive Keys:**
Update CustomSensitiveKeys::list() to include any context keys your application considers
sensitive. For instance:

```
public static function list(): array {
return ['password', 'passwd', 'secret', 'api_key', 'ssn', 'credit_card'];
}
```
Now if any context is logged with keys "ssn" or "credit_card", their values will be masked. -
**Sensitive Patterns:** Update CustomSensitivePatterns::list() with regex patterns for sensitive
info. Example:

```
public static function list(): array {
return [
'/\d{3}-\d{2}-\d{4}/' // pattern for US SSN (###-##-#### format)
];
}
```
If any context value matches that pattern, it will be replaced by the mask token. - **Mask Token:** You can
change the mask token globally by editing the default in
DefaultSanitizationValues::MASK_TOKEN. For example, you might prefer "***" instead of
"[MASKED]". Change the enum case return:

```
self::MASK_TOKEN => '***',
```

Similarly, the Assembler’s mask token (for entire messages) could be changed via
AssemblerDefaultValues::DEFAULT_MASK_TOKEN. If you don’t plan to use channel-specific mask
notes, you might ignore that. - **Length Limits and Regex:** If 2000 chars is too short or long for log
messages, adjust DefaultValidationValues::LOG_MESSAGE_MAX_LENGTH. If you want to allow
newline characters in log messages (currently disallowed by the control char regex), you could modify
STRING_FORBIDDEN_CHARS_REGEX (though caution: multi-line log entries can break the one-line-per-
entry format). - **Time Zone:** The timestamp uses the server’s default timezone when formatting with
DateTimeInterface::ATOM. Ensure your PHP timezone configuration is correct (date.timezone
in php.ini) to get accurate local times in logs. Alternatively, you could modify LogLineFormatter to use
UTC or another timezone explicitly if required by your logging policies.

**Environment-based Config:**

You might use different log directories or settings for different environments (development, production,
testing): - For example, in development you might set the log directory to __DIR__.'/logs/dev' and
in production to /var/log/myapp. - You might also choose to lower the log level threshold in dev
(logging debug messages) but not in prod. This module doesn’t have an internal threshold filter (it logs
whatever you call it with). To implement a threshold, you would need to conditionally call logging
methods based on environment, or extend the Logger to drop entries below a certain level.

**Integration with Frameworks:**

If integrating with a framework (Laravel, Symfony, etc.), you could wrap this module in a service
provider: - In Laravel, for instance, you might bind LoggingFacadeInterface in the service
container to an instance created as shown above. - In Symfony, you might configure a service and inject
the path from parameters.

But outside of a framework, using the LoggingKernel directly as shown is perfectly fine.

**Testing Configuration:**

If running in a test environment, you might want logs to go to a temporary directory or memory: - Set
the log directory to something like /tmp/myapp_test_logs for tests, so you can easily clean it up
between runs. - The LoggingConfig could be reused across tests, or you instantiate a fresh kernel for
each test class.

Remember that aside from the base path and the static config classes, there isn’t a lot of dynamic
config. If more complex configuration needs arise (like toggling sanitization off, or using a different
format), you’d have to implement those changes in code (for example, by subclassing components or
altering config classes). The structure is flexible to allow that, but not via .ini or .xml files — it’s all PHP
code configuration.

## Examples of Use

Below are some practical examples demonstrating how to use the Logging Module in a PHP application.
These include initialization, basic logging calls, using context data, and leveraging different channels.

**Example 1: Basic Setup and Logging**
This example shows the typical setup in an application and logs messages of various levels:


```
<?php
useConfig\Modules\Logging\LoggingConfig;
useLogging\Infrastructure\LoggingKernel;
```
```
// 1. Configuration: specify the directory for log files.
$logDir= __DIR__. '/logs'; // ensure this directory is writable
$config= newLoggingConfig($logDir);
```
```
// 2. Boot the logging kernel with the configuration.
$kernel= newLoggingKernel($config);
```
```
// 3. Get the logging facade (implements LoggingFacadeInterface).
$logger= $kernel->logger();
```
```
// 4. Log messages at various levels with context data.
$logger->info("User logged in", ["user" =>"alice"]);
$logger->warning("Disk space low", ["disk" =>"/dev/sda1","free_percent"=>
5]);
$logger->error("File not found", ["file" =>"/path/to/file.txt", "error_code"
=> 404]);
```
```
// 5. Use the generic log method for a custom level (if allowed in config).
$logger->log("notice", "User profile updated", ["user" =>"alice"]);
```
```
// 6. Logging with a different channel (using logInput to specify channel).
$logger->logInput("Admin privileges granted", "critical", "security",
["admin"=> "bob"]);
```
**What happens in the above code:**

- After setup, calling $logger->info(...) writes an entry to logs/application/info.log by
default. The line will include a timestamp, application.INFO, the message "User logged in", and the
context {"user":"alice"}. If "alice" were sensitive (not in this case), it would be masked. - The
    warning call goes to logs/application/warning.log. Context shows which disk and free space.
- The error call goes to logs/application/error.log. It logs an error message with a file path
and code. If the file path contained forbidden chars, the module might sanitize them (though file paths
usually just get logged verbatim). - The log("notice", ...) call demonstrates using the generic
method with a string level. It will result in an entry in logs/application/notice.log. If "notice"
wasn’t an allowed level, this call would throw an exception – but "notice" is standard, so it works. - The
    logInput(..., "security", ...) call logs to channel "security". So instead of the default
application channel, it goes to logs/security/critical.log. The context notes which admin was
granted privileges. If "admin" were a key in sensitive keys list (it’s not by default), "bob" would be
masked as "[MASKED]".

After running this, your logs directory would have subfolders application/ and security/,
each containing log files for the levels used (info.log, warning.log, error.log, notice.log in application,
and critical.log in security).

**Example 2: Using the PSR-3 Adapter**
If you need to supply a logger to a library that expects a PSR-3 logger:


```
use Logging\Infrastructure\LoggingKernel;
use Psr\Log\LoggerInterface; // assuming you have psr/log in your project
for type hints
```
```
$kernel = new LoggingKernel(new LoggingConfig('/var/log/myapp'));
$psrLogger = $kernel->psrLogger(); // This implements PsrLoggerInterface,
which mirrors Psr\Log\LoggerInterface
```
```
// Now $psrLogger can be used wherever a PSR-3 LoggerInterface is required.
SomeLibrary::setLogger($psrLogger);
```
```
// For demonstration, using psrLogger directly:
$psrLogger->error("Payment failed", ["orderId" => 1001, "amount" => 50.00]);
$psrLogger->debug("Payment response", ["responsePayload" => "<xml>...</
xml>"]);
```
Here, SomeLibrary::setLogger() could be any function that expects a
Psr\Log\LoggerInterface. Our $psrLogger qualifies. When the library logs through it, the
adapter will internally create log entries and write to files just as our facade would. The calls in the
example would produce: - An entry in logs/application/error.log for the payment failed (with
context showing order and amount). - A debug entry in logs/application/debug.log for the
response payload. (If the payload contains lots of data or sensitive info, note that the Sanitizer will strip
control characters but won’t automatically remove XML tags unless they match a pattern. If this is a
concern, consider logging a summarized payload or adding a pattern to mask certain XML content.)

**Example 3: Handling Exceptions from Logging**
Generally, you can assume logging will succeed and not wrap it in try-catch every time. However, for
completeness, if you wanted to handle a potential exception (say you want to avoid your app crashing if
logs can’t be written):

```
try {
$logger->critical("Service outage", ["service" => "database", "duration"
=> "5m"]);
} catch (\Logging\Infrastructure\Exception\LogWriteException $e) {
// If writing to file failed (disk full or permission issue), handle
gracefully
error_log("Failed to write to application logs: ". $e->getMessage());
// We could also fallback to an alternate logging mechanism here
}
```
In this snippet, if the log cannot be written to disk, it catches the exception and at least logs the error to
PHP’s system logger (which might go to web server error log). In most cases, this won’t be necessary if
the environment is set up correctly. But it’s an example of using the exception classes for error
handling.

**Example 4: Unit Testing a Component with Logging (using the interface):**
Suppose you have a class in your app that uses a LoggingFacadeInterface to log events. For
testing that class without actually writing logs, you might mock the interface:


```
use PublicContracts\Logging\LoggingFacadeInterface;
```
```
class OrderService {
private LoggingFacadeInterface $logger;
public function __construct(LoggingFacadeInterface $logger) {
$this->logger = $logger;
}
public function placeOrder($orderData) {
// ... order placement logic ...
$this->logger->info("Order placed", ["orderId" => $orderData->id]);
}
}
```
```
// In a PHPUnit test for OrderService:
$loggerMock = $this->createMock(LoggingFacadeInterface::class);
$loggerMock->expects($this->once())
->method('info')
->with(
$this->stringContains("Order placed"),
$this->arrayHasKey("orderId")
);
```
```
$service = new OrderService($loggerMock);
$service->placeOrder($dummyOrder);
```
This shows how the LoggingFacadeInterface allows injection of a mock logger. The test can verify that
info("Order placed", ...) was called exactly once with the expected parameters, without
needing to actually create files. This demonstrates the advantage of coding against the interface for
high-level code.

These examples cover typical usage patterns. In practice, you will mostly initialize the logger once (as a
singleton or a shared service) and then use $logger->level() methods throughout your app
wherever you need to log. The module ensures all such calls result in consistent, well-formatted log
entries in the designated files.

## Tests

The Logging Module is designed with testability in mind. Each layer’s components can be tested in
isolation, and an integration test can verify the end-to-end logging flow.

**Included Tests (if any):** The structure of the module suggests an integration test class (e.g.,
LoggingTest) was used during development to validate functionality. This test likely created a
LoggingKernel with a temporary directory, then ran through creating LoggableInputs, assembling
entries, and writing logs, asserting that files were created and contain expected content. While that test
class might not be distributed with the production module, it serves as a reference for how to
programmatically verify each component.

**Running Module Tests:** If the module is accompanied by a PHPUnit test suite: - Make sure you’ve
installed PHPUnit (composer require --dev phpunit/phpunit if it’s not already in your project). -


The test files would be under a Tests namespace (for example,
Tests\Persistence\LoggingTest as seen in context). Ensure the test files are autoloadable (they
might be in a separate tests/ directory). - Run phpunit targeting the Logging module’s tests. For
instance:

```
vendor/bin/phpunit --testdox tests/LoggingTest.php
```
This would execute the test case. - The tests will create and delete temporary files (the integration test
uses a tmp directory under its folder to write logs and then cleans up).

If no test suite is included, you can manually test the module with a script: - **Domain Tests:** Instantiate
domain objects with known inputs to ensure they behave:

```
new LogMessage("Test", $logSecurity); // should succeed
new LogMessage("", $logSecurity); // should throw
InvalidLogMessageException
new LogLevel("INFO", $logSecurity, $customLevels); // case insensitive
check, should succeed if allowed
new LogLevel("INVALID", $logSecurity, $customLevels);// should throw
InvalidLogLevelException
```
Similarly, test LogContext with a variety of contexts (nested arrays, including a sensitive key to see if it
masks, etc.). - **Security Tests:** Test Validator methods directly if needed:

```
$validator->validateMessage("Hello"); // returns "Hello"
$validator->validateMessage(""); // throws exception
$validator->validateLevel("info", ["info","error"]); // returns "info"
$validator->validateLevel("verbose", ["info","error"]); // exception
```
And Sanitizer:

```
$sanitizer->sanitize(["password" => "secret"]); // returns ["password" =>
"[MASKED]"]
$sanitizer->sanitize(["note" => "hello"]); // returns ["note" =>
"hello"]
```
- **Integration Test:** Simulate a full log write:

```
$config = new LoggingConfig(sys_get_temp_dir(). "/logtest");
$kernel = new LoggingKernel($config);
$logger = $kernel->logger();
$logger->info("Integration test message", ["foo" => "bar"]);
// Now check that file exists:
$logFile = sys_get_temp_dir(). "/logtest/application/info.log";
assert(file_exists($logFile));
$contents = file_get_contents($logFile);
assert(strpos($contents, "Integration test message") !== false);
```

```
assert(strpos($contents, "foo") !== false && strpos($contents, "bar") !==
false);
```
This will write a log and verify its content contains expected substrings. Clean up by removing the
created file and directory. - **Concurrency Test (optional):** If you want to test the module under
concurrent writes (though PHP scripts usually run sequentially), you could simulate with multiple
threads or processes writing to the log at once. The file locking behavior of file_put_contents in
append mode should handle atomicity of writes to some extent (each call is atomic, but interleaving
lines can occur if not using locks; however, for log lines which are relatively short, usually it writes one
line fully then the next). Typically, this is not tested via unit tests but worth noting in a stress test
scenario.

**Testing Considerations:** - When testing file outputs, always clean up the files to avoid cluttering the
system (the provided LoggingTest’s cleanupLogs() method shows an approach to remove the test
log directory). - Use dependency injection and interface mocking for unit tests of any class that uses the
logger, to avoid touching the filesystem during those tests. - The module’s separation of concerns
means you can test domain logic without involving file writes, and test the file writing with known
strings rather than constructing a whole facade call (for example, directly call LogFileWriter with a path
to see if it creates a file).

By following these testing practices, you can ensure the Logging Module works correctly in your
environment and continues to do so as you upgrade or modify it. The clear contracts and pure functions
of the domain layer make unit testing straightforward, whereas the integration tests give confidence
that everything works together as expected.

## Best Practices and Limitations

To make the most of the Logging Module and avoid common pitfalls, consider the following best
practices and be aware of the module’s limitations:

**Best Practices:**

```
Use the Facade or PSR Interface: Always prefer using LoggingFacadeInterface (via the
facade instance from the kernel) or the provided PSR logger interface over directly instantiating
lower-level components. The facade ensures the proper sequence of operations (assemble then
log) and maintains the integrity of the log entries. Directly using LogEntryAssembler or
Logger in client code is possible but not recommended, as it bypasses the simplicity and safety
checks the facade provides.
Log Sufficient Context: Take advantage of the context parameter to include relevant data with
each log. Instead of concatenating variables into the message, pass them in the context array.
For example:
```
```
$logger->error("Payment failed", ["orderId" => $order->id, "amount" =>
$order->amount]);
```
```
This produces structured context in the log, which is easier to parse later. The module will handle
formatting it as JSON. Avoid logging extremely large context data (like a whole image in binary
or a huge text blob); log references or summaries instead.
```
#### •

#### •


```
Avoid Sensitive Data in Messages: Try to put sensitive info in context values rather than the
message. The Sanitizer primarily looks at context. If you log a password or personal info directly
in the message string, the module currently will not mask it (it doesn’t scan message content
against patterns by default). If you must log something sensitive, put it in context with an
appropriate key (so it gets masked), or improve the sanitization patterns to scan messages too.
For example:
```
```
// Not recommended:
$logger->warning("User entered password: $pass"); // password in
message, won't be masked by default
// Better:
$logger->warning("User entered password", ["password" => $pass]); //
'password' value will be masked
```
```
Review and Update Sensitive Patterns: Ensure the default sensitive keys and patterns align
with your application’s data. Add entries as needed (API keys, tokens, etc.). This prevents
accidental leakage of secrets in logs. It’s easier to add them upfront than to find out later that
something was logged in plain text.
Manage Log File Size: Since the module writes to files and doesn’t rotate them, plan for log
maintenance:
Use an external log rotation tool (like Linux’s logrotate) to rotate logs daily or when they
reach a certain size. Make sure to copy/truncate instead of move if you do so, or else the module
might continue writing to a moved file handle (in our case, since we open fresh each time,
moving the file between writes would actually result in a new file being created next write –
which is fine).
Alternatively, implement a simple check in the LogFileWriter to rotate (not provided by default,
but you could subclass or modify it to, say, start a new file when size > X).
Clean up old logs regularly if retention isn’t needed. This prevents disk space issues.
Thread Safety / Parallel Usage: In standard PHP web SAPI usage, each request gets its own
instances, so no issues. If using in a multi-threaded CLI or ReactPHP (event loop) scenario, the
module should still behave (file writes are atomic per call). However, if two processes log at the
exact same time to the same file, their writes might intermix at a byte level (one could start
writing, then another, resulting in interwoven text) because file_put_contents with
FILE_APPEND is not explicitly locking. This is usually rare and small interleaving (within one disk
block). For critical logs, consider using file locks (flock) in LogFileWriter when appending, to
serialize writes. This is a possible enhancement if needed.
Exception Handling: Decide how you want to handle exceptions thrown by the logger in
production. You may wrap logging calls in a try-catch if the logging itself should not break the
application. On the other hand, an exception in logging often signals something serious (invalid
data or disk problem), so you might let it bubble up during development to fix the root cause.
Extend with Caution: If you need to extend the module (e.g., override how formatting works or
integrate with a cloud logging API), prefer composition over modification:
You could write a new class implementing LoggerInterface that sends logs to an API instead of
file, and then either swap it in the kernel (by modifying LoggingKernel or subclassing it to
instantiate your logger) or use LoggingFacade to call both (file and API).
If modifying core classes, ensure you maintain the validation and sanitization steps; those are
crucial for security.
Security of Log Files: Logs can contain sensitive information (even if masked, they might still
contain some user data). Protect the log directory:
Set proper filesystem permissions (no public read access on a web server, for instance).
```
#### • • • • • • • • • • • • •


```
If logs are in a web-accessible directory, ensure there’s no direct access (e.g., use an .htaccess to
deny access, or place logs outside the web root).
Regularly review logged data to ensure sanitization is working as intended.
```
**Known Limitations:**

```
No Log Level Filtering: The module does not have a built-in mechanism to ignore logs below a
certain level. If you call $logger->debug() it will always log. In some systems, you might
want to turn off debug or info logs in production. With this module, you’d need to implement
that logic in your usage (for example, only call debug when in dev environment). Alternatively,
you could extend LoggingFacade to check a config setting before actually logging.
No Built-in Rotation/Archiving: As mentioned, logs will grow indefinitely. This module focuses
on writing, not managing log lifecycle. Plan external solutions for log rotation/cleanup.
Synchronous I/O: Logging happens in-line with your code execution. For web apps under heavy
load, writing to disk can become a bottleneck (especially if the disk is slow). There is no
asynchronous or buffering mechanism in place. In extremely performance-sensitive contexts,
consider pointing the module to a tmpfs (in-memory filesystem) or modifying it to queue logs
and flush in background (which would be a significant custom change).
Single Destination (Files): The current design logs to local files only. If you need to log to
multiple destinations (file + remote, or multiple files for the same message), you would have to
extend the Logger or add additional calls. There’s no multi-handler support like Monolog
provides. That said, you can instantiate multiple kernels with different directories if you wanted
separate sets of logs, but you’d have to call both loggers in code to duplicate messages.
Formatting is Fixed: The log line format can’t be configured at runtime. If your organization
requires a different log format (say, CSV or a specific text layout), you must modify
LogLineFormatter accordingly. The current format is a reasonable default but may not suit
everyone.
Context Serialization Simplicity: The context is JSON-encoded via json_encode in the
formatter. This means objects in context that are not JSON-serializable will be output as {} or
might trigger a notice. Also, large arrays will produce large JSON strings. If you have binary data
in context, it won’t be human-readable (and could bloat the log file). The system doesn’t attempt
to pretty-print or limit context size beyond the validation limits. A custom formatter or
preprocessing of context in your application might be needed for complex data.
Internationalization of Logs: The module doesn’t provide multiple language support for log
messages (that’s usually not needed – log messages are typically in English or the developer’s
language). It also doesn’t format timestamps in localized formats (sticking to ISO8601, which is
generally good practice for logs).
Memory usage: The module has a small memory footprint. Even so, be mindful when logging
extremely large data (the context length limits help mitigate this). Logging huge data structures
could temporarily use a lot of memory to format to JSON or to copy arrays in Sanitizer.
Upgradability: Because this is a custom module, upgrading it means merging custom changes
if you made any. If you treat it as a vendor library, try not to edit its core files; instead extend via
subclassing, so that if a new version of the module is released, you can drop it in and adjust your
subclass if needed. (This point is more relevant if the module were maintained by a third party –
in a private context, it may not apply.)
```
By adhering to these practices and understanding the limitations, you can use the Logging Module
effectively and reliably. It’s a powerful foundation for logging in a PHP application, and with mindful
usage, it will greatly aid in monitoring and debugging your system without introducing security risks
from sensitive data exposure.

#### • • • • • • • • • • •


## Contributing

Contributions to the Logging Module are welcome! Whether you want to fix a bug, add a new feature,
or improve documentation, here are some guidelines to follow:

```
Development Standards: The code follows modern PHP standards (PSR-12 coding style, PSR-4
autoloading). Please keep code style consistent. Use strict types
(declare(strict_types=1); at the top of PHP files) as seen in module files, and add type
hints and return types for all functions/methods.
Branch and Commit: If the project is on a version control repository (e.g., GitHub):
Fork the repository and clone your fork.
Create a new branch for your feature/fix:
```
```
git checkout -b feature/add-rotation
```
```
Make your changes on that branch.
Write clear commit messages explaining the why and what of the changes.
Write Tests: If you add a feature or fix a bug, please add corresponding tests. For example, if
you implement log rotation, add tests to simulate log files reaching a size and ensure rotation
logic works. Running ./vendor/bin/phpunit should pass all tests before and after your
changes.
Documentation: Update the README (or separate docs) if your change affects usage. For
instance, if a new configuration option is introduced, document it under Configuration section.
Pull Request: Push your branch to your fork and open a Pull Request to the main repository.
Provide a descriptive title and description. Explain why the change is needed (e.g., what problem
it solves) and summarize how your implementation works. If it’s a large change, the maintainers
might discuss it with you for clarity or request adjustments.
Code Review: Be open to feedback. Project maintainers may suggest improvements or edits.
This is part of the review process to maintain code quality and consistency.
Issue Reporting: If you’re not coding a change but want to report a bug or request a feature,
open an issue on the repository’s issue tracker. Provide as much detail as possible: steps to
reproduce the bug, environment (PHP version, OS), and any relevant log output or error
messages. For feature requests, explain your use-case and why the feature would be beneficial.
Community Conduct: Interact respectfully with other contributors and maintainers. Use clear,
professional language in discussions. We aim for a collaborative environment where ideas can
be exchanged constructively.
Scope: Try to keep each pull request focused on one topic. This makes it easier to review and
increases the likelihood of quick merging. For example, avoid bundling an unrelated bug fix with
a new feature in the same PR.
Testing Your Changes: Before submitting, test the module in a real scenario if possible.
Integrate your modified module into a sample project and ensure everything works as expected
(this is in addition to automated tests).
```
By following these guidelines, you help ensure that contributions are handled smoothly and the module
remains reliable. We appreciate the time and effort of contributors in improving the Logging Module.

#### • • • • • • • • • • • • • •


## Contact and Support

If you need help with the Logging Module or have questions:

```
Project Repository: Check the repository’s README or wiki for additional documentation. The
issue tracker is a good place to search for known issues or to ask questions.
Issues and Bug Reports: If you encounter a bug, please open an issue on the repository
providing details (as described in the Contributing section). Maintainers will respond as soon as
possible.
Discussion: For general questions or troubleshooting, you might find a discussion forum or a
chat channel (if the project has one, e.g., Slack or Discord) where developers discuss the module.
Refer to the project documentation for any such community channels.
Email: If a direct support contact is provided by the maintainers, you can reach out via email. For
example, the maintainers might have a support email like support@loggingmodule.dev (this
is an example; use the actual contact if given).
Professional Support: If this module is used in a company project and there are internal
maintainers, reach out to the designated team or individual within your organization responsible
for the logging system.
FAQ: Look out for a FAQ section in the documentation or wiki. It may already address common
questions (like "How do I log to a database?" or "How to change log format?").
Stack Overflow: If the project is open-source and widely used, questions tagged for it might
appear on Stack Overflow. You can search there for "php logging-module" or similar terms. Be
mindful to refer only to authoritative answers or the official docs, as implementations can vary.
```
When seeking support, provide context: - The version of the module you are using (if versioned) or the
date of the commit. - Environment details (PHP version, platform, how you integrated it). - Relevant
code snippets of how you initialize and call the logger. - The exact issue or error message you’re facing.

The maintainers aim to help users integrate and use the Logging Module effectively. Response times
may vary depending on the community activity, but the goal is to ensure any user of the module can
resolve issues and fully utilize the features.

## License

The Logging Module is open-source software, released under the **MIT License**. This means you are free
to use, modify, and distribute the module in your own projects, including commercial applications, as
long as you include the license notice.

The MIT License is a permissive license with limited restrictions – primarily requiring preservation of
copyright notices and the license text in any redistribution.

For the full license text, see the LICENSE file distributed with the module. In summary, the module is
provided "as is" without warranty of any kind, express or implied. The authors or copyright holders are
not liable for any claims or damages arising from its use.

By using this module, you agree to the terms of the MIT License.

_End of README_

#### • • • • • • •


(Note: This README is intended to provide comprehensive documentation for the Logging Module. For
any further details or updates, refer to the official repository or contact the maintainers as described
above.)


