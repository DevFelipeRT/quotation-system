<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Http;

/**
 * Represents a single HTTP header as an immutable value object.
 *
 * This object encapsulates a header's name and its associated value(s),
 * ensuring name validation and case-insensitive normalization upon creation.
 */
final class Header
{
    private readonly string $normalizedName;

    /** @var string[] */
    private readonly array $values;

    /**
     * @param string          $name   The header name.
     * @param string|string[] $values The header value or an array of values.
     * @throws \InvalidArgumentException If the header name or values are invalid.
     */
    public function __construct(
        private readonly string $originalName,
        string|array $values
    ) {
        $this->ensureNameIsValid($originalName);

        $this->values = is_array($values) ? array_values($values) : [$values];
        $this->ensureValuesAreValid($this->values);

        $this->normalizedName = $this->normalizeName($originalName);
    }

    /**
     * Retrieves the original header name with its original casing.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->originalName;
    }

    /**
     * Retrieves the normalized header name (Title-Case).
     *
     * @return string
     */
    public function getNormalizedName(): string
    {
        return $this->normalizedName;
    }

    /**
     * Retrieves the list of values for this header.
     *
     * @return string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Retrieves the first value for this header.
     *
     * @return string
     */
    public function getFirstValue(): string
    {
        return $this->values[0];
    }

    /**
     * Returns a comma-separated string of all header values.
     *
     * @return string
     */
    public function getValueLine(): string
    {
        return implode(', ', $this->values);
    }

    /**
     * Checks if the header name matches a given name (case-insensitively).
     *
     * @param string $name
     * @return bool
     */
    public function matches(string $name): bool
    {
        return $this->normalizedName === $this->normalizeName($name);
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     */
    private function ensureNameIsValid(string $name): void
    {
        if (trim($name) === '' || !preg_match('/^[a-zA-Z0-9\'`#$%&*+.^~|!_-]+$/', $name)) {
            throw new \InvalidArgumentException("Invalid header name: \"{$name}\"");
        }
    }

    /**
     * @param string[] $values
     * @throws \InvalidArgumentException
     */
    private function ensureValuesAreValid(array $values): void
    {
        if (empty($values)) {
            throw new \InvalidArgumentException('Header values cannot be empty.');
        }

        foreach ($values as $value) {
            if (!is_string($value) || preg_match('/[\\r\\n]/', $value)) {
                throw new \InvalidArgumentException('Invalid header value. Contains invalid characters.');
            }
        }
    }

    /**
     * Normalizes a header name to Title-Case format.
     *
     * @param string $name
     * @return string
     */
    private function normalizeName(string $name): string
    {
        return str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))));
    }
}