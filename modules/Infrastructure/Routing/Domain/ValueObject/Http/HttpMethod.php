<?php

declare(strict_types=1);

namespace Routing\Domain\ValueObject\Http;

/**
 * Represents an HTTP request method as a typesafe enum.
 *
 * This ensures that within the HTTP context, only valid, standardized
 * methods can be represented.
 */
enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case OPTIONS = 'OPTIONS';
    case HEAD = 'HEAD';
}