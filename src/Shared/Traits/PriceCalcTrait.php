<?php 

declare(strict_types=1);

namespace App\Shared\Traits;

use Exception;
use InvalidArgumentException;

trait PriceCalcTrait {
    // Propriedades Estáticas
    protected static array $calculationTypes = [
        ['id' => 1, 'name' => 'fixed'],
        ['id' => 2, 'name' => 'percentage']
    ];
    // Propriedades de Intância
    protected array $discounts = [];
    protected array $fees = [];
    protected float $finalPrice;

    // Métodos Estáticos Públicos
    public static function setCalculationTypes(array $types): void {
        self::validateCalculationTypes($types);
        self::$calculationTypes = [];
        foreach ($types as $type) {
            self::$calculationTypes[] = $type;
        }
    }

    public static function getCalculationTypes(): array {
        if (empty(self::$calculationTypes)) {
            throw new Exception('Não contém tipos.');
        }
        return self::$calculationTypes;
    }

    public static function getCalculationTypeById(int $calculationTypeId): array {
        $calculationTypes = self::getCalculationTypes();
        foreach ($calculationTypes as $calculationType) {
            if (isset($calculationType['id']) && $calculationType['id'] === $calculationTypeId) {
                return $calculationType;
            }
        }
        throw new Exception("Tipo não encontrado.");
    }

    public static function validateCalculationTypeId(int $calculationTypeId): bool {
        $calculationTypes = self::getCalculationTypes();
        foreach ($calculationTypes as $calculationType) {
            if (isset($calculationType['id']) && $calculationType['id'] === $calculationTypeId) {
                return true;
            }
        }
        return false;
    }

    // Métodos Setters
    protected function setFinalPrice(): void {
        $this->finalPrice = $this->calculateFinalPrice($this->price);
    }

    // Métodos Getters
    public function getDiscounts(): array 
    {
        return $this->discounts;
    }

    public function getTotalDiscount(): ?float {
        if ($this->price <= 0) {
            return null;
        }
        return $this->calculateTotalDiscount($this->price);
    }

    public function getFees(): array 
    {
        return $this->fees;
    }

    public function getTotalFee(): ?float {
        if ($this->price <= 0) {
            return null;
        }
        return $this->calculateTotalFee($this->price);
    }

    public function getFinalPrice(): ?float 
    {
        if ($this->price <= 0) {
            return null;
        }
        if (!isset($this->finalPrice)) {
            $this->setFinalPrice($this->price);
        }
        return $this->finalPrice;
    }

    // Métodos de adição de descontos e taxas
    public function addDiscount(float $value, ?string $description, ?int $calculationTypeId = 1): void {
        if (self::validateCalculationTypeId($calculationTypeId)) {
            $this->discounts[] = [
                'value' => $value,
                'description' => $description,
                'calculationTypeId' => $calculationTypeId
            ];
        }
    }

    public function addFee(string $description, float $value, int $calculationTypeId): void {
        if (self::validateCalculationTypeId($calculationTypeId)) {
            $this->discounts[] = [
                'description' => $description,
                'value' => $value,
                'calculationTypeId' => $calculationTypeId
            ];
        }
    }

    // Métodos de remoção de descontos e taxas
    public function removeAllDiscounts(): void {
        if (empty($this->discounts)) {
            throw new Exception("Não contém descontos");
        }
        $this->discounts = [];
    }

    public function removeDiscount(string $description, float $value): void {
        if (empty($this->discounts)) {
            throw new Exception("Não contém descontos");
        }
        $discount = ['description' => $description, 'value' => $value];
        foreach ($this->discounts as $key => $storedDiscount) {
            if ($storedDiscount === $discount) {
                unset($this->discounts[$key]);
                $this->discounts = array_values($this->discounts);
                return;
            }
        }
        throw new Exception("O desconto não foi encontrado.");
    }

    public function removeAllFees(): void {
        if (empty($this->fees)) {
            throw new Exception("Não contém taxas");
        }
        $this->fees = [];
    }

    public function removeFee(string $description, float $value): void {
        if (empty($this->fees)) {
            throw new Exception("Não contém taxas");
        }
        $fee = ['description' => $description, 'value' => $value];
        foreach ($this->fees as $key => $storedFee) {
            if ($storedFee === $fee) {
                unset($this->fees[$key]);
                $this->fees = array_values($this->fees);
                return;
            }
        }
        throw new Exception("A taxa não foi encontrada.");
    }

    // Métodos de cálculos
    protected function calculateTotalDiscount(?float $price = null): float {
        if (empty($this->discounts)) {
            return 0;
        }

        $totalDiscount = 0;
        foreach ($this->discounts as $discount) {
            if ($discount['calculationTypeId'] === 2 && $price = null) {
                throw new Exception("Preço inválido.");
            }

            $value = $discount['value'];
            $calculationTypeId = $discount['calculationTypeId'];
    
            if ($calculationTypeId === 1) {
                $totalDiscount += $value;
            } elseif ($calculationTypeId === 2) {
                $totalDiscount += ($price * ($value / 100));
            }
        }
    
        return $totalDiscount;
    }

    protected function calculateTotalFee(?float $price = null): float {
        if (empty($this->fees)) {
            return 0;
        }
    
        $totalFee = 0;
        foreach ($this->fees as $fee) {
            if ($fee['calculationTypeId'] === 2 && $price === null) {
                throw new Exception("Preço inválido.");
            }
    
            $value = $fee['value'];
            $calculationTypeId = $fee['calculationTypeId'];
    
            if ($calculationTypeId === 1) {
                $totalFee += $value;
            }
            elseif ($calculationTypeId === 2 && $price !== null) {
                $totalFee += ($price * ($value / 100));
            }
        }
    
        return $totalFee;
    }
    
    protected function calculateFinalPrice(float $basePrice): float {
        $finalPrice = $basePrice;

        if (!empty($this->discounts)) {
            $finalPrice -= $this->calculateTotalDiscount($basePrice);
        }

        if (!empty($this->fees)) {
            $finalPrice += $this->calculateTotalFee($basePrice);
        }
    
        return $finalPrice;
    }
    
    // Métodos de Validação
    protected function validateId(?int $id): ?int {
        if ($id !== null && $id < 0) {
            throw new Exception("O ID inserido é inválido.");
        }
        return $id;
    }

    private static function validateName(?string $name): void {
        if (empty($name)) {
            throw new Exception("O Nome inserido é inválido.");
        }
    }

    private static function validateCalculationTypes(array $types): void {
        foreach($types as $type) {
            if (is_array($type)) {
                if (array_key_exists('id', $type) && array_key_exists('name', $type)) {
                    self::validateId($type['id']);
                    self::validateName($type['name']);
                } else {
                    throw new InvalidArgumentException('Chaves inválidas. Deve conter: [\'id\' => $id, \'name\' => $name].');
                }
            } else {
                throw new InvalidArgumentException('Cada tipo deve ser um array contendo: [\'id\' => $id, \'name\' => $name].');
            }
        }
    }

}

?>