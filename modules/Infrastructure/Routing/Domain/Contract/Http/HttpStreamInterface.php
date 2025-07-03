<?php

declare(strict_types=1);

namespace Routing\Domain\Contract\Http;

/**
 * Describes a data stream for an HTTP message body.
 *
 * This contract provides a wrapper around a stream resource, allowing for
 * efficient reading and writing of data. Its design is compatible with the
 * principles defined in PSR-7 for stream objects.
 */
interface HttpStreamInterface
{
    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close(): void;

    /**
     * Separates any underlying resources from the stream.
     *
     * @return resource|null Underlying PHP stream, if any.
     */
    public function detach();

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize(): ?int;

    /**
     * Returns the current position of the file read/write pointer.
     *
     * @return int Position of the file pointer.
     * @throws \RuntimeException on error.
     */
    public function tell(): int;

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool;

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool;

    /**
     * Seek to a position in the stream.
     *
     * @param int $offset Stream offset.
     * @param int $whence Specifies how the cursor position will be calculated.
     * Uses a value identical to the built-in PHP fseek() function.
     * @throws \RuntimeException on failure.
     */
    public function seek(int $offset, int $whence = SEEK_SET): void;

    /**
     * Seek to the beginning of the stream.
     *
     * @throws \RuntimeException on failure.
     */
    public function rewind(): void;

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable(): bool;

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write(string $string): int;

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return them.
     * @return string Returns the data read from the stream, or an empty string if unavailable.
     * @throws \RuntimeException if an error occurs.
     */
    public function read(int $length): string;

    /**
     * Returns the remaining contents of the stream as a string.
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs.
     */
    public function getContents(): string;

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * @param string|null $key Specific metadata to retrieve.
     * @return array<string, mixed>|mixed|null Returns an associative array if no key is provided,
     * a specific value if a key is provided, or null if the key is not found.
     */
    public function getMetadata(?string $key = null);
}