<?php

declare(strict_types=1);

namespace Tests;

trait TestAssertionTools
{
    use IntegrationTestPrinter;

    /**
     * Asserts that a value is not null.
     */
    public function assertNotNull($actual, string $message = ''): string
    {
        $description = $message !== '' ? $message : 'Assert value is not null.';
        $success = $actual !== null;
        $this->saveResult($description, $success);
        return $success
            ? "{$description} Success."
            : "{$description} Failed: value is null.";
    }

    /**
     * Asserts that a value is true.
     */
    public function assertTrue($actual, string $message = ''): string
    {
        $description = $message !== '' ? $message : 'Assert value is true.';
        $success = $actual === true;
        $this->saveResult($description, $success);
        return $success
            ? "{$description} Success."
            : "{$description} Failed: value is not true.";
    }

    /**
     * Asserts that a value is false.
     */
    public function assertFalse($actual, string $message = ''): string
    {
        $description = $message !== '' ? $message : 'Assert value is false.';
        $success = $actual === false;
        $this->saveResult($description, $success);
        return $success
            ? "{$description} Success."
            : "{$description} Failed: value is not false.";
    }

    /**
     * Asserts that two values are equal (==).
     */
    public function assertEquals($expected, $actual, string $message = ''): string
    {
        $description = $message !== '' ? $message : 'Assert equality.';
        $success = $expected == $actual;
        $this->saveResult($description, $success);
        return $success
            ? "{$description} Success."
            : "{$description} Failed: {$actual} is not equal to {$expected}.";
    }

    /**
     * Asserts that a string contains a substring.
     */
    public function assertStringContains(string $needle, string $haystack, string $message = ''): string
    {
        $description = $message !== '' ? $message : "Assert string contains '{$needle}'.";
        $success = strpos($haystack, $needle) !== false;
        $this->saveResult($description, $success);
        return $success
            ? "{$description} Success."
            : "{$description} Failed: '{$needle}' not found in string.";
    }

    /**
     * Asserts that a variable is an instance of the given class/interface.
     */
    public function assertInstanceOf(string $expected, $actual, string $message = ''): string
    {
        $description = $message !== '' ? $message : "Assert instance of {$expected}.";
        $success = $actual instanceof $expected;
        $this->saveResult($description, $success);
        return $success
            ? "{$description} Success."
            : "{$description} Failed: value is not instance of {$expected}.";
    }

    /**
     * Asserts that a condition is true (alias for assertTrue).
     */
    public function assert($condition, string $message = ''): string
    {
        return $this->assertTrue($condition, $message);
    }

    /**
     * Asserts that a value is a string.
     */
    public function assertIsString($actual, string $message = ''): string
    {
        $description = $message !== '' ? $message : 'Assert value is a string.';
        $success = is_string($actual);
        $this->saveResult($description, $success);
        return $success
            ? "{$description} Success."
            : "{$description} Failed: value is not a string.";
    }

}
