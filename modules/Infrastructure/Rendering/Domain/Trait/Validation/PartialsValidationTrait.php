<?php 

declare(strict_types=1);

namespace Rendering\Domain\Trait\Validation;

use InvalidArgumentException;
use Rendering\Domain\Contract\Partial\PartialViewInterface;

trait PartialsValidationTrait
{
    /**
     * Validates that the provided array contains only valid PartialViewInterface instances.
     *
     * @param array<string, mixed> $partials Associative array of partials to validate
     * @throws InvalidArgumentException When any item in the array is not a valid PartialViewInterface instance
     */
    public static function validatePartials(array $partials): void
    {
        foreach ($partials as $identifier => $partial) {
            if (!is_string($identifier) || trim($identifier) === '') {
                throw new InvalidArgumentException('Partial identifier must be a non-empty string.');
            }
            if (!($partial instanceof PartialViewInterface)) {
                $type = is_object($partial) ? get_class($partial) : gettype($partial);
                throw new InvalidArgumentException(
                    "Partial with identifier '{$identifier}' must be an instance of PartialViewInterface, but {$type} was given."
                );
            }
        }
    }
}