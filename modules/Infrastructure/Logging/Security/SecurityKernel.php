<?php

declare(strict_types=1);

namespace Logging\Security;

use PublicContracts\Logging\Config\SanitizationConfigInterface;
use PublicContracts\Logging\Config\ValidationConfigInterface;

use Logging\Domain\Security\Contract\SanitizerInterface;
use Logging\Domain\Security\Contract\ValidatorInterface;

use Logging\Security\Sanitizing\Contract\ArraySanitizerInterface;
use Logging\Security\Sanitizing\Contract\ObjectSanitizerInterface;
use Logging\Security\Sanitizing\Contract\SensitiveKeyDetectorInterface;
use Logging\Security\Sanitizing\Contract\StringSanitizerInterface;
use Logging\Security\Sanitizing\Detector\CircularReferenceDetector;
use Logging\Security\Sanitizing\Detector\SensitiveKeyDetector;
use Logging\Security\Sanitizing\Detector\SensitivePatternDetector;
use Logging\Security\Sanitizing\Service\CredentialPhraseSanitizer;
use Logging\Security\Sanitizing\Service\ArraySanitizer;
use Logging\Security\Sanitizing\Service\ObjectSanitizer;
use Logging\Security\Sanitizing\Service\SensitivePatternSanitizer;
use Logging\Security\Sanitizing\Service\StringSanitizer;
use Logging\Security\Sanitizing\Service\SanitizingService;
use Logging\Security\Sanitizing\Tools\MaskTokenValidator;
use Logging\Security\Sanitizing\Tools\UnicodeNormalizer;
use Logging\Security\Validation\Services\ChannelValidator;
use Logging\Security\Validation\Services\ContextValidator;
use Logging\Security\Validation\Services\DirectoryValidator;
use Logging\Security\Validation\Services\LevelValidator;
use Logging\Security\Validation\Services\MessageValidator;
use Logging\Security\Validation\Services\TimestampValidator;
use Logging\Security\Validation\ValidationFacade;

/**
 * SecurityKernel
 *
 * Centralizes the initialization and wiring of all sanitization services and collaborators.
 * Uses private methods to assemble and organize the construction of each dependency.
 */
final class SecurityKernel
{
    private array  $sensitiveKeys;
    private array  $sensitivePatterns;
    private array  $credentialPhraseSeparators;
    private int    $maxDepth;
    private string $maskToken;
    private string $maskTokenForbiddenPattern;

    private SanitizerInterface $sanitizingService;
    private ValidatorInterface $validationFacade;

    public function __construct(
        SanitizationConfigInterface $sanitizationConfig,
        ValidationConfigInterface   $validationConfig
    )
    {
        $this->initParameters($sanitizationConfig);
        $this->bootComponents($validationConfig);
    }

    public function sanitizer(): SanitizerInterface
    {
        return $this->sanitizingService;
    }

    public function validator(): ValidatorInterface
    {
        return $this->validationFacade;
    }

    /**
     * Loads all saniti$sanitizationConfiguration parameters into the class properties.
     *
     * @param SanitizationConfigInterface $sanitizationConfig
     */
    private function initParameters(SanitizationConfigInterface $sanitizationConfig): void
    {
        $this->sensitiveKeys               = $sanitizationConfig->sensitiveKeys();
        $this->sensitivePatterns           = $sanitizationConfig->sensitivePatterns();
        $this->credentialPhraseSeparators  = [];
        $this->maxDepth                    = $sanitizationConfig->maxDepth();
        $this->maskToken                   = $sanitizationConfig->maskToken();
        $this->maskTokenForbiddenPattern   = $sanitizationConfig->maskTokenForbiddenPattern();
    }

    /**
     * Initializes all security services.
     *
     * @return void
     */
    private function bootComponents(ValidationConfigInterface $validationConfig): void
    {
        $this->bootSanitizer();
        $this->bootValidator($validationConfig);
    }

    /**
     * Initializes all detectors and sanitizers and wires the main sanitizattion service.
     *
     * @return void
     */
    private function bootSanitizer(): void
    {
        $patternDetector   = new SensitivePatternDetector($this->sensitivePatterns);
        $keyDetector       = new SensitiveKeyDetector($this->sensitiveKeys);
        $circularDetector  = new CircularReferenceDetector();

        $tokenValidator    = new MaskTokenValidator($this->maskTokenForbiddenPattern);
        $unicodeNormalizer = new UnicodeNormalizer();

        $patternSanitizer  = new SensitivePatternSanitizer($patternDetector, $unicodeNormalizer);
        $phraseSanitizer   = new CredentialPhraseSanitizer($keyDetector, $this->credentialPhraseSeparators);
        $stringSanitizer   = new StringSanitizer($patternSanitizer, $phraseSanitizer, $unicodeNormalizer);

        $objectSanitizer = $this->createObjectSanitizer(
            $circularDetector, 
            $stringSanitizer, 
            $keyDetector
        );
        $arraySanitizer = $this->createArraySanitizer(
            $stringSanitizer, 
            $keyDetector, 
            $objectSanitizer, 
            $circularDetector
        );
        
        $this->sanitizingService = new SanitizingService(
            $arraySanitizer,
            $objectSanitizer,
            $stringSanitizer,
            $patternDetector,
            $keyDetector,
            $tokenValidator,
            $this->maskToken
        );
    }

    private function createArraySanitizer(
        StringSanitizerInterface      $stringSanitizer,
        SensitiveKeyDetectorInterface $keyDetector,
        ObjectSanitizerInterface      $objectSanitizer,
        CircularReferenceDetector     $circularDetector
    ): ArraySanitizerInterface {
        return new ArraySanitizer(
            $stringSanitizer,
            $keyDetector,
            $objectSanitizer,
            $circularDetector,
            $this->maskToken,
            $this->maxDepth
        );
    }

    private function createObjectSanitizer(
        CircularReferenceDetector     $circularDetector,
        StringSanitizerInterface      $stringSanitizer,
        SensitiveKeyDetectorInterface $keyDetector
    ): ObjectSanitizerInterface {
        return new ObjectSanitizer(
            $circularDetector,
            $keyDetector,
            $stringSanitizer,
            $this->maskToken,
            $this->maxDepth
        );
    }

    /**
     * Initializes all validation services and wires the main facade.
     *
     * @return void
     */
    private function bootValidator(ValidationConfigInterface $validationConfig): void
    {
        $channelValidator   = new ChannelValidator($validationConfig);
        $contextValidator   = new ContextValidator($validationConfig);
        $directoryValidator = new DirectoryValidator($validationConfig);
        $levelValidator     = new LevelValidator();
        $messageValidator   = new MessageValidator($validationConfig);
        $timestampValidator = new TimestampValidator();

        $this->validationFacade = new ValidationFacade(
            $channelValidator,
            $contextValidator,
            $directoryValidator,
            $levelValidator,
            $messageValidator,
            $timestampValidator
        );
    }
}
