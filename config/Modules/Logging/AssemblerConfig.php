<?php

declare(strict_types=1);

namespace Config\Modules\Logging;

use PublicContracts\Logging\Config\AssemblerConfigInterface;

/**
 * AssemblerConfig
 *
 * Implementation of AssemblerConfigInterface using AssemblerDefaultValues enum
 * and CustomLogLevels provider.
 */
final class AssemblerConfig implements AssemblerConfigInterface
{
    private readonly ?string $defaultLevel;
    private readonly ?array  $defaultContext;
    private readonly ?string $defaultChannel;
    private readonly ?string $maskToken;
    private readonly ?array  $customLogLevels;

    public function __construct()
    {
        $this->defaultLevel    = AssemblerDefaultValues::DEFAULT_LEVEL->getValue();
        $this->defaultContext  = AssemblerDefaultValues::DEFAULT_CONTEXT->getValue();
        $this->defaultChannel  = AssemblerDefaultValues::DEFAULT_CHANNEL->getValue();
        $this->maskToken       = AssemblerDefaultValues::DEFAULT_MASK_TOKEN->getValue();
        $this->customLogLevels = CustomLogLevels::list();
    }

    public function defaultLevel(): ?string
    {
        return $this->defaultLevel;
    }

    public function defaultContext(): ?array
    {
        return $this->defaultContext;
    }

    public function defaultChannel(): ?string
    {
        return $this->defaultChannel;
    }

    public function customLogLevels(): ?array
    {
        return $this->customLogLevels;
    }

    public function maskToken(): ?string
    {
        return $this->maskToken;
    }
}
