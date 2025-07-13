<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\Partial;

/**
 * Immutable value object representing a footer partial view component.
 *
 * This class extends PartialView to provide a reusable footer component
 * that encapsulates its template file reference, rendering data (including a copyright notice),
 * and nested partial components. It follows the Value Object pattern, ensuring immutability
 * and data integrity through validation at instantiation time.
 */
final class Footer extends PartialView
{
}
