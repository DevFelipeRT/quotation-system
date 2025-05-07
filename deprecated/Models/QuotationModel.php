<?php 

namespace App\Models;

use App\Entities\Quotation;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../Config/config.php';

class QuotationModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Métodos CRUD de Quotation

    // Adicionar orçamento
    public function addQuotation(Quotation $quotation): bool
    {
        $queryData = [
            QUOTATION_NAME_COLUMN => $quotation->getName(),
            QUOTATION_DESCRIPTION_COLUMN => $quotation->getDescription(),
            QUOTATION_CLIENT_ID_COLUMN => $quotation->getClientId()
        ];

        return $this->executeQuery->insert(QUOTATION_TABLE, $queryData);
    }

    // Obter todos os orçamentos
    public function getAllQuotations(): array
    {
        return $this->executeQuery->select(QUOTATION_TABLE, ['*']);
    }

    // Obter orçamento por ID
    public function getQuotationById(int $id): array
    {
        $where = QUOTATION_ID_COLUMN . ' = ?';
        $params = [$id];

        return $this->executeQuery->select(QUOTATION_TABLE, ['*'], $where, $params);
    }

    // Atualizar orçamento
    public function updateQuotation(Quotation $quotation): bool
    {
        $queryData = [
            QUOTATION_NAME_COLUMN => $quotation->getName(),
            QUOTATION_DESCRIPTION_COLUMN => $quotation->getDescription(),
            QUOTATION_CLIENT_ID_COLUMN => $quotation->getClientId()
        ];

        $where = QUOTATION_ID_COLUMN . ' = ?';
        $params = [$quotation->getId()];

        return $this->executeQuery->update(QUOTATION_TABLE, $queryData, $where, $params);
    }

    // Remover orçamento
    public function deleteQuotation(Quotation $quotation): bool
    {
        $where = QUOTATION_ID_COLUMN . ' = ?';
        $params = [$quotation->getId()];

        return $this->executeQuery->delete(QUOTATION_TABLE, $where, $params);
    }

    // Métodos CRUD de descontos
    public function addDiscount(Quotation $quotation): bool
    {
        $queryData = [
            QUOTATION_DISCOUNT_COLUMN => $quotation->getTotalDiscount()
        ];

        $where = QUOTATION_ID_COLUMN . ' = ?';
        $params = [$quotation->getId()];

        return $this->executeQuery->update(QUOTATION_TABLE,$queryData, $where, $params);
    }

    public function getDiscount(Quotation $quotation): array
    {
        $where = QUOTATION_ID_COLUMN . ' = ?';
        $params = [$quotation->getId()];

        return $this->executeQuery->select(QUOTATION_TABLE, [QUOTATION_DISCOUNT_COLUMN], $where, $params);
    }

    public function updateDiscount(Quotation $quotation): bool
    {
        return $this->addDiscount($quotation);
    }

    public function deleteDiscount(Quotation $quotation): bool
    {
        $queryData = [
            QUOTATION_DISCOUNT_COLUMN => null
        ];

        $where = QUOTATION_ID_COLUMN . ' = ?';
        $params = [$quotation->getId()];

        return $this->executeQuery->update(QUOTATION_TABLE,$queryData, $where, $params);
    }

    // Métodos CRUD de taxas
    public function addFee(Quotation $quotation): bool
    {
        $queryData = [
            QUOTATION_FEE_COLUMN => $quotation->getTotalFee()
        ];

        $where = QUOTATION_ID_COLUMN . ' = ?';
        $params = [$quotation->getId()];

        return $this->executeQuery->update(QUOTATION_TABLE, $queryData, $where, $params);
    }

    public function getFee(Quotation $quotation): array
    {
        $where = QUOTATION_ID_COLUMN . ' = ?';
        $params = [$quotation->getId()];

        return $this->executeQuery->select(QUOTATION_TABLE, [QUOTATION_FEE_COLUMN], $where, $params);
    }

    public function updateFee(Quotation $quotation): bool
    {
        return $this->addFee($quotation);
    }

    public function deleteFee(Quotation $quotation): bool
    {
        $queryData = [
            QUOTATION_FEE_COLUMN => null
        ];

        $where = QUOTATION_ID_COLUMN . ' = ?';
        $params = [$quotation->getId()];

        return $this->executeQuery->update(QUOTATION_TABLE, $queryData, $where, $params);
    }

}

?>