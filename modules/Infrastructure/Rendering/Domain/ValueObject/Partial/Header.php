<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject\Partial;

/**
 * Immutable value object representing a header partial view component.
 *
 * This class extends the PartialView to provide a reusable header component
 * that encapsulates its template file reference, rendering data, and nested partial components.
 * It follows the Value Object pattern, ensuring immutability and data integrity through
 * validation at instantiation time.
 *
 * The header component can contain nested partial views, enabling compositional rendering
 * architectures where headers may include sub-components like navigation menus, breadcrumbs,
 * or metadata sections.
 */
final class Header extends PartialView
{
}
