<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Building;

use Rendering\Infrastructure\Contract\Building\RenderableBuilderInterface;
use InvalidArgumentException;
use Rendering\Infrastructure\Building\Factory\PartialFactory;
use Rendering\Infrastructure\Building\Factory\RenderableDataFactory;
use Rendering\Domain\Contract\RenderableInterface;
use Rendering\Domain\Contract\RenderableDataInterface;
use Rendering\Domain\Contract\Partial\PartialViewInterface;
use Rendering\Domain\ValueObject\Shared\PartialsCollection;
use Rendering\Domain\ValueObject\Shared\Renderable;

abstract class AbstractRenderableBuilder implements RenderableBuilderInterface
{
    protected PartialFactory $partialFactory;
    protected RenderableDataFactory $dataFactory;
    protected string $templateFile = '';
    protected array $data = [];
    protected array $partials = [];

    /**
     * A flag to track if the builder has been configured by the client.
     * @var bool
     */
    protected bool $isConfigured = false;

    /**
     * Constructor to initialize the builder with necessary factories.
     *
     * @param PartialFactory $partialFactory Factory for creating partials.
     * @param RenderableDataFactory $dataFactory Factory for creating renderable data.
     */
    public function __construct(
        PartialFactory $partialFactory, 
        RenderableDataFactory $dataFactory
    ) {
        $this->partialFactory = $partialFactory;
        $this->dataFactory = $dataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplateFile(string $templateFile): self
    {
        if (trim($templateFile) === '') {
            throw new InvalidArgumentException('Template file name cannot be empty.');
        }
        $this->templateFile = $templateFile;
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        $this->isConfigured = true;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function addPartial(string $key, PartialViewInterface|array $partial): self
    {
        $this->partials[$key] = $partial;
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPartials(array $partials): self
    {
        $this->partials = $partials;
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isReady(): bool
    {
        // A builder is considered "ready" if it has been explicitly configured
        // in any way and has a valid template file associated with it.
        return $this->isConfigured && !empty($this->templateFile);
    }

    /**
     * {@inheritdoc}
     */
    public function build(): RenderableInterface
    {
        if (!$this->isReady()) {
            throw new \LogicException('Builder is not in a ready state to build. Ensure a template file has been set.');
        }
        
        return new Renderable(
            $this->templateFile,
            $this->buildDataFromArray($this->data),
            $this->buildPartialsCollection($this->partials)
        );
    }

    /**
     * Initializes the default template file without marking the builder as configured.
     * This method is intended for use within the constructors of child builders.
     *
     * @param string $templateFile The default template file path.
     */
    protected function initializeTemplateFile(string $templateFile): void
    {
        if (trim($templateFile) === '') {
            throw new InvalidArgumentException('Default template file name cannot be empty.');
        }
        $this->templateFile = $templateFile;
    }

    /**
     * Hydrates an array of partials into a PartialsCollection.
     *
     * @param array $partials The array of partial definitions.
     * @return PartialsCollection|null A collection of hydrated PartialView objects or null if the input is empty.
     */
    protected function buildPartialsCollection(array $partials): ?PartialsCollection
    {
        if (empty($partials)) {
            return null;
        }
        return $this->partialFactory->createPartialsCollection($partials);
    }

    /**
     * Converts an associative array into a RenderableData object.
     *
     * @param array $data The associative array to convert.
     * @return RenderableDataInterface|null The RenderableData object or null if the array is empty.
     */
    protected function buildDataFromArray(array $data): ?RenderableDataInterface
    {
        if (empty($data)) {
            return null;
        }
        return $this->dataFactory->createRenderableDataFromArray($data);
    }
}
