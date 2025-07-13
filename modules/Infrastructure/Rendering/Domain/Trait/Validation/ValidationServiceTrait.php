<?php 

declare(strict_types=1);

namespace Rendering\Domain\Trait\Validation;

trait ValidationServiceTrait
{
    use PartialsValidationTrait;
    use DirectoryValidationTrait;
    use TemplateFileValidationTrait;
}