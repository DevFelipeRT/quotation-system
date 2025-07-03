<?php

declare(strict_types=1);

namespace Routing\Domain\Contract;

use Routing\Domain\ValueObject\Definition\Verb;
use Routing\Domain\ValueObject\Uri\Path;

/**
 * Describes a generic, protocol-agnostic request.
 *
 * This contract provides the essential information needed by the router to
 * perform a match. It is designed to be decoupled from any specific protocol.
 *
 * Compatibility with standards like PSR-7 is achieved at the infrastructure
 * layer via an adapter that implements this interface.
 */
interface RequestInterface
{
    /**
     * Retrieves the request's path component.
     *
     * @return Path
     */
    public function getPath(): Path;

    /**
     * Retrieves the request's generic verb.
     *
     * An adapter would typically generate this Verb object by calling getMethod()
     * on a PSR-7 request and wrapping the resulting string.
     *
     * @return Verb
     */
    public function getVerb(): Verb;
}