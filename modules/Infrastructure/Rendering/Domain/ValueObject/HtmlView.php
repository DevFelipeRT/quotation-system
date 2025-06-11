<?php

declare(strict_types=1);

namespace Rendering\Domain\ValueObject;

use Rendering\Domain\Contract\ViewInterface;
use Rendering\Domain\Contract\ViewDataInterface;
use InvalidArgumentException;

/**
 * HtmlView
 *
 * Value Object representing an immutable HTML view with its rendering data and
 * an associated JavaScript file. Encapsulates the template file name, the
 * JavaScript file name, and the view data.
 *
 * @package Rendering/Domain/ValueObjects
 */
final class HtmlView implements ViewInterface
{
    /** @var string */
    private readonly string $fileName;

    /** @var string */
    private readonly string $jsFileName;

    /** @var ViewDataInterface */
    private readonly ViewDataInterface $data;

    /**
     * Initializes the HtmlView.
     *
     * @param string            $fileName   Name or path of the HTML template.
     * @param string            $jsFileName Name or path of the related JavaScript file.
     * @param ViewDataInterface $data       Encapsulated data for the view.
     * 
     * @throws InvalidArgumentException If file names are empty.
     */
    public function __construct(string $fileName, string $jsFileName, ViewDataInterface $data)
    {
        $fileName = trim($fileName);
        $jsFileName = trim($jsFileName);

        if ($fileName === '') {
            throw new InvalidArgumentException('The view file name cannot be empty.');
        }

        if ($jsFileName === '') {
            throw new InvalidArgumentException('The JavaScript file name cannot be empty.');
        }

        $this->fileName = $fileName;
        $this->jsFileName = $jsFileName;
        $this->data = $data;
    }

    /**
     * Returns the HTML template file name.
     *
     * @return string
     */
    public function fileName(): string
    {
        return $this->fileName;
    }

    /**
     * Returns the associated JavaScript file name.
     *
     * @return string
     */
    public function jsFileName(): string
    {
        return $this->jsFileName;
    }

    /**
     * Returns the encapsulated view data.
     *
     * @return ViewDataInterface
     */
    public function data(): ViewDataInterface
    {
        return $this->data;
    }

    /**
     * Exports the object as an array for serialization or debugging.
     *
     * @return array {fileName: string, jsFileName: string, data: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'fileName'   => $this->fileName,
            'jsFileName' => $this->jsFileName,
            'data'       => $this->data->toArray(),
        ];
    }
}
