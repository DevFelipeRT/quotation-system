<?php

declare(strict_types=1);

namespace Tests\Decorators;

use App\Infrastructure\Session\Domain\Contracts\SessionDataInterface;
use App\Infrastructure\Session\Domain\ValueObjects\AbstractControllerSessionData;
use App\Infrastructure\Session\Domain\ValueObjects\SessionContext;

/**
 * CustomSessionDataDecorator
 *
 * Session value object decorator for attaching arbitrary custom fields.
 * Suitable for use by controllers or modules that require extra, strongly-typed,
 * session-coupled data in addition to the base session structure.
 *
 * Fully compatible with SessionDataFactory for (de)serialization.
 */
final class CustomSessionDataDecorator extends AbstractControllerSessionData
{
    private SessionDataInterface $baseSession;
    private array $customFields;

    /**
     * @param SessionDataInterface $baseSession The original session value object being decorated.
     * @param array<string, mixed> $customFields Arbitrary extra fields to be stored with this session.
     */
    public function __construct(SessionDataInterface $baseSession, array $customFields)
    {
        parent::__construct($baseSession->getContext());
        $this->baseSession = $baseSession;
        $this->customFields = $customFields;
    }

    /**
     * Returns a unique key identifying this controller-specific session data class.
     *
     * @return string
     */
    public function controllerKey(): string
    {
        return static::class;
    }

    /**
     * Returns an associative array of custom fields to be merged in serialization.
     *
     * @return array<string, mixed>
     */
    protected function controllerPayload(): array
    {
        return ['custom_fields' => $this->customFields];
    }

    /**
     * Retrieves a custom field value, or a default if not present.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getCustomField(string $key, mixed $default = null): mixed
    {
        return $this->customFields[$key] ?? $default;
    }

    /**
     * Returns all custom fields associated with this decorator.
     *
     * @return array<string, mixed>
     */
    public function getAllCustomFields(): array
    {
        return $this->customFields;
    }

    /**
     * Indicates whether the session is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->baseSession->isAuthenticated();
    }

    /**
     * Returns the session locale.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->baseSession->getLocale();
    }

    /**
     * Serializes the decorator for session storage.
     * Includes all fields from the base session, controller key, and custom fields.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(
            $this->baseSession->toArray(),
            [
                'controller'    => static::class,
                'custom_fields' => $this->customFields,
            ]
        );
    }

    /**
     * Reconstructs this decorator from an associative array (called by SessionDataFactory).
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        // Rebuild base session by stripping decorator-only keys for safety
        $baseArray = $data;
        unset($baseArray['controller'], $baseArray['custom_fields']);
        $base = \App\Infrastructure\Session\Domain\Factories\SessionDataFactory::fromArray($baseArray);

        $fields = $data['custom_fields'] ?? [];
        return new self($base, $fields);
    }
}
