<?php

declare(strict_types=1);

namespace Rendering\Infrastructure\Contract\Building\Partial;

use Rendering\Domain\Contract\PartialViewInterface;
use Rendering\Domain\Partial\ValueObject\Header;

/**
 * Define o contrato para um construtor de componentes Header.
 *
 * Esta interface fornece uma API fluente para encapsular a lógica de
 * construção de um objeto Header, incluindo o seu título, assets CSS
 * e sub-componentes parciais.
 */
interface HeaderBuilderInterface
{
    /**
     * Define o título principal a ser usado no cabeçalho.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self;

    /**
     * Adiciona um caminho de ficheiro CSS à coleção de assets do cabeçalho.
     *
     * @param string $path
     * @return $this
     */
    public function addCss(string $path): self;

    /**
     * Adiciona um sub-componente parcial nomeado ao cabeçalho.
     *
     * @param string $key O identificador para o parcial (usado com @partial).
     * @param PartialViewInterface $partial O objeto de view parcial a ser adicionado.
     * @return $this
     */
    public function addPartial(string $key, PartialViewInterface $partial): self;

    /**
     * Monta e retorna o objeto Header final e imutável.
     *
     * @return Header
     */
    public function build(): Header;
}
