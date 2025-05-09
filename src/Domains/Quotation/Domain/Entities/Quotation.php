<?php 

namespace App\Domains\Quotation\Domain\Entities;

use App\Shared\Traits\PriceCalcTrait;
use DateTime;
use Exception;

class Quotation {
    use PriceCalcTrait;
    // Propriedades
    private ?int $id = null;
    private string $name;
    private ?string $description = null;
    private ?int $clientId = null;
    private ?DateTime $creationDate = null;
    private array $items = [];
    private float $price = 0;

    // Método Construtor
    public function __construct(string $name, ?string $description = null, ?int $clientId = null) {
        $this->setName($name);
        $this->description = $description;
        $this->setClientId($clientId);
    }

    // Métodos Setters
    public function setId(int $id): self 
    {
        if ($id < 0 || isset($this->id)) {
            throw new Exception("O ID é inválido ou já foi definido e não pode ser alterado.");
        }
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): self {
        if (empty($name)) {
            throw new Exception("O nome deve ser preenchido.");
        }
        $this->name = $name;
        return $this;
    }

    public function setDescription(?string $description = null): self 
    {
        $this->description = $description;
        return $this;
    }

    public function setClientId(?int $clientId = null): self {
        $this->clientId = $this->validateId($clientId);
        return $this;
    }

    public function setCreationDate(string $timeStamp): self
    {
        $this->creationDate = new DateTime($timeStamp);
        return $this;
    }

    // Métodos Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getClientId(): ?int {
        return $this->clientId;
    }

    public function getItems(): array {
        return $this->items;
    }

    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }
    
    // Métodos de adição de itens
    public function addItem(QuotationItem $item): void {
        $this->items[] = $item;
    }

    // Métodos de remoção de itens
    public function removeAllItems(): void {
        if (empty($this->items)) {
            throw new Exception("Não contém itens");
        }
        $this->items = [];
    }
    
    public function removeItem($item): void {
        if (empty($this->items)) {
            throw new Exception("Não contém itens");
        }
        foreach ($this->items as $key => $storedItem) {
            if ($storedItem === $item) {
                unset($this->items[$key]);
                $this->items = array_values($this->items);
                return;
            }
        }
        throw new Exception("O item não foi encontrado.");
    }

    // Métodos de cálculo
    protected function calculateItemsTotalPrice(): float {
        $itemsTotalPrice = 0;
        if (empty($this->items)) {
            throw new Exception("O orçamento não contém itens");
        }
        foreach ($this->items as $key => $item) {

        }

        return $itemsTotalPrice;
    }

    // Métodos de validação
    protected function validateId(?int $id): ?int
    {
        if ($id !== null && $id < 0) {
            throw new Exception("O ID inserido é inválido.");
        }

        return ($id === 0) ? null : $id;
    }
}

?>