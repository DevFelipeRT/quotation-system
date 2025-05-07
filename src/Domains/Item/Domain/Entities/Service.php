<?php 

namespace App\Domain\Entities;

use Exception;

class Service extends Item {
    // Propriedades Privadas
    private ?int $id = null;

    // Método Construtor
    public function __construct(string $name, float $price) {
        $this->setName($name);
        $this->setPrice($price);
    }

    // Métodos Setters
    public function setId(int $id): void {
        $this->validateId($id);
        if (isset($this->id)) {
            throw new Exception("O ID já foi definido e não pode ser alterado.");
        }
        $this->id = $id;
    }

    // Métodos Getters
    public function getId(): ?int {
        return $this->id;
    }

}

?>