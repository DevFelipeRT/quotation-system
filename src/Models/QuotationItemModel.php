<?php

namespace App\Models;

use App\Entities\QuotationItem;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../Config/config.php';

class QuotationItemModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Métodos CRUD de Item Orçamento

    // Adicionar item de orçamento
    public function addQuotationItem(QuotationItem $quotationItem): bool
    {
        $queryData = [
            QUOTATION_ITEM_QUOTATION_ID_COLUMN => $quotationItem->getQuotationId(),
            QUOTATION_ITEM_ITEM_ID_COLUMN => $quotationItem->getItemId(),
            QUOTATION_ITEM_TYPE_ID_COLUMN => $quotationItem->getTypeId(),
            QUOTATION_ITEM_QUANTITY_COLUMN => $quotationItem->getQuantity(),
            QUOTATION_ITEM_DISCOUNT_COLUMN => $quotationItem->getTotalDiscount(),
            QUOTATION_ITEM_FEE_COLUMN => $quotationItem->getTotalFee()
        ];

        return $this->executeQuery->insert(QUOTATION_ITEM_TABLE, $queryData);
    }

    // Obter um item de orçamento
    public function getQuotationItemById(int $id): array
    {
        $where = QUOTATION_ITEM_ID_COLUMN . ' = ?';
        $params = [$id]; 
        return $this->executeQuery->select(QUOTATION_ITEM_TABLE, ['*'], $where, $params);
    }

    // Obter todos itens de um orçamento
    public function getQuotationItems(int $quotationId): array
    {
        $where = QUOTATION_ITEM_QUOTATION_ID_COLUMN . ' = ?';
        $params = [$quotationId]; 
        return $this->executeQuery->select(QUOTATION_ITEM_TABLE, ['*'], $where, $params);
    }

    // Adicionar item de orçamento
    public function updateQuotationItem(QuotationItem $quotationItem): bool
    {
        $queryData = [
            QUOTATION_ITEM_QUOTATION_ID_COLUMN => $quotationItem->getQuotationId(),
            QUOTATION_ITEM_ITEM_ID_COLUMN => $quotationItem->getItemId(),
            QUOTATION_ITEM_TYPE_ID_COLUMN => $quotationItem->getTypeId(),
            QUOTATION_ITEM_QUANTITY_COLUMN => $quotationItem->getQuantity(),
            QUOTATION_ITEM_DISCOUNT_COLUMN => $quotationItem->getTotalDiscount(),
            QUOTATION_ITEM_FEE_COLUMN => $quotationItem->getTotalFee()
        ];

        $where = QUOTATION_ITEM_ID_COLUMN . ' = ?';
        $params = [$quotationItem[QUOTATION_ITEM_ID_COLUMN]]; 

        return $this->executeQuery->update(QUOTATION_ITEM_TABLE, $queryData, $where, $params);
    }

    // Remover item de produto do orçamento
    public function deleteQuotationItem(int $id): bool
    {
        $where = QUOTATION_ITEM_ID_COLUMN . ' = ?';
        $params = [$id];

        return $this->executeQuery->delete(QUOTATION_ITEM_TABLE, $where, $params);
    }

    // Métodos CRUD de Types

    // Adicionar novo tipo
    public function addType(string $name, string $description): bool
    {
        $queryData = [
            TYPES_NAME_COLUMN => $name,
            TYPES_DESCRIPTION_COLUMN => $description
        ];

        return $this->executeQuery->insert(TYPES_TABLE, $queryData);
    }

    // Obter tipos
    public function getQuotationItemTypes(): array
    {
        return $this->executeQuery->select(TYPES_TABLE, ['*']);
    }

    // Atualizar um tipo existente
    public function updateType(array $type): bool
    {
        $queryData = [
            TYPES_NAME_COLUMN => $type[TYPES_NAME_COLUMN],
            TYPES_DESCRIPTION_COLUMN => $type[TYPES_DESCRIPTION_COLUMN]
        ];

        $where = TYPES_ID_COLUMN . ' = ?';
        $params = [$type[TYPES_ID_COLUMN]];

        return $this->executeQuery->update(TYPES_TABLE, $queryData, $where, $params);
    }

    // Remover um tipo
    public function deleteType(int $id): bool
    {
        $where = TYPES_ID_COLUMN . ' = ?';
        $params = [$id];

        return $this->executeQuery->delete(TYPES_TABLE, $where, $params);
    }
}

?>