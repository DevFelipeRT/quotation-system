<?php

namespace App\Domains\Quotation\Domain\Entities;

use Exception;

class QuotationItem
{
    use PriceCalcTrait;
    // Propriedades Estáticas
    protected static ?array $types = [];
    // Propriedades Privadas
    private ?int $id = null;
    private Quotation $quotation;
    private Item $item;
    private ?float $price = null;
    // Propriedades Compartilhadas
    protected ?int $typeId = null;
    protected int $quantity = 1;

    // Métodos estáticos
    public static function setTypes(array $types): void {
        static::validateTypes($types);
        static::$types = $types;
    }

    public static function getTypes(): array {
        if (empty(static::$types)) {
            throw new Exception('Não contém tipos.');
        }
        return static::$types;
    }

    public static function getTypeById(int $id): array {
        if (static::validateTypeId($id)) {
            foreach (static::$types as $type) {
                if ($type[TYPES_ID_COLUMN] === $id) {
                    return $type;
                }
            }
            throw new Exception('Tipo não encontrado.');
        }
        throw new Exception('O id inserido é inválido.');
    }

    public static function validateTypeId(int $id): bool {
        if (empty(static::$types)) {
            throw new Exception('Não contém tipos.');
        }
        foreach (static::$types as $type) {
            if ($type[TYPES_ID_COLUMN] === $id) {
                return true;
            }
        }
        return false;
    }

    protected static function validateTypes(array $types): array {
        foreach ($types as $type) {
            if (!is_array($type)) {
                throw new Exception('Deve conter arrays.');
            }
            if (!isset($type[TYPES_ID_COLUMN]) || !isset($type[TYPES_NAME_COLUMN])) {
                throw new Exception('Contém arrays inválidos.');
            }
        }
        return $types;
    }

    // Método Construtor
    public function __construct(Quotation $quotation, Item $item, ?int $quantity = null) {
        $this->quotation = $quotation;
        $this->item = $item;
        $this->setQuantity($quantity);
        $this->price = $item->getPrice();
    }

    // Métodos Setters
    public function setId(int $id): self {
        if (isset($this->id)) {
            if ($this->id === $id) {
                return $this;
            }
            throw new Exception("O ID já foi definido e não pode ser alterado.");
        }
        $this->id = $this::validateId($id);
        return $this;
    }

    public function setQuantity(?int $quantity): self {
       $this->quantity = $this->validateQuantity($quantity);
       return $this;
    }

    public function setTypeId(?int $typeId): self
    {
        $this->validateId($typeId);
        if (!static::validateTypeId($typeId)) {
            throw new Exception("O ID inserido é inválido.");
        }
        $this->typeId = $typeId;
        return $this;
    }

    // Métodos Getters
    public function getId(): ?int 
    {
        return $this->id;
    }

    public function getQuotation(): Quotation 
    {
        return $this->quotation;
    }

    public function getQuotationId(): int 
    {
        return $this->quotation->getId();
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getItemId(): int
    {
        return $this->item->getId();
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function getQuantity(): int 
    {
        return $this->quantity;
    }

    public function getPrice(): float 
    {
        return $this->price;
    }

    // Métodos de Validação
    protected function validateQuantity(?int $quantity = null): ?int {
        switch ($quantity) {
            case $quantity === null:
                return 0;
                break;
            case $quantity < 0:
                throw new Exception("A quantidade inserida é inválida.");
                break;
            default:
                return $quantity;
        }
    }
}

?>
