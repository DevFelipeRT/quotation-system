<?php

declare(strict_types=1);

namespace Routing\Domain\Contract\Http;

use Routing\Domain\Contract\Http\MessageInterface;

/**
 * Represents an outgoing, server-side HTTP response.
 *
 * This contract extends the generic MessageInterface and adds methods specific
 * to an HTTP response, such as the status code and reason phrase. Its design
 * is compatible with the principles of PSR-7.
 */
interface HttpResponseInterface extends MessageInterface
{
    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int;

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * If no reason phrase is available, this method should return an empty
     * string.
     *
     * @return string Reason phrase; must be empty if none is provided.
     */
    public function getReasonPhrase(): string;
}