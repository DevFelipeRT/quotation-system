<?php

namespace App\Models;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../Config/config.php';

use App\Entities\Item;

class ItemModel extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Métodos CRUD de Category

    // Adicionar uma nova categoria
    public function addCategory(string $name, string $description): bool
    {
        $queryData = [
            CATEGORIES_NAME_COLUMN => $name,
            CATEGORIES_DESCRIPTION_COLUMN => $description
        ];

        return $this->executeQuery->insert(CATEGORIES_TABLE, $queryData);
    }

    // Obter categorias
    public function getItemCategories(): array
    {
        return $this->executeQuery->select(CATEGORIES_TABLE, ['*']);
    }

    // Atualizar uma categoria existente
    public function updateCategory(array $category): bool
    {
        $queryData = [
            CATEGORIES_NAME_COLUMN => $category[CATEGORIES_NAME_COLUMN],
            CATEGORIES_DESCRIPTION_COLUMN => $category[CATEGORIES_DESCRIPTION_COLUMN]
        ];

        $where = CATEGORIES_ID_COLUMN . ' = ?';
        $params = [$category[CATEGORIES_ID_COLUMN]];

        return $this->executeQuery->update(CATEGORIES_TABLE, $queryData, $where, $params);
    }

    // Remover uma categoria
    public function deleteCategory(int $id): bool
    {
        $where = CATEGORIES_ID_COLUMN . ' = ?';
        $params = [$id];

        return $this->executeQuery->delete(CATEGORIES_TABLE, $where, $params);
    }

    // Métodos CRUD de Item

    // Adicionar um novo item
    public function addItem(Item $item): bool 
    {
        $itemData = $item->getData();
        $queryData = [
            ITEMS_NAME_COLUMN => $itemData['name'],
            ITEMS_DESCRIPTION_COLUMN => $itemData['description'],
            ITEMS_CATEGORY_ID_COLUMN => $itemData['categoryId'],
            ITEMS_PRICE_COLUMN => $itemData['price']
        ];

        return $this->executeQuery->insert(ITEMS_TABLE, $queryData);
    }

    // Obter todos os itens
    public function getAllItems(): array
    {
        return $this->executeQuery->select(ITEMS_TABLE, ['*']);
    }

    // Obter um item por ID
    public function getItemById(int $id): ?array
    {
        $result = $this->executeQuery->select(ITEMS_TABLE, ['*'], ITEMS_ID_COLUMN . ' = ?', [$id]);

        return $result ? $result[0] : null;
    }

    // Atualizar um item existente
    public function updateItem(Item $item): bool
    {
        $itemData = $item->getData();
        $queryData = [
            ITEMS_NAME_COLUMN => $itemData['name'],
            ITEMS_DESCRIPTION_COLUMN => $itemData['description'],
            ITEMS_CATEGORY_ID_COLUMN => $itemData['categoryId'],
            ITEMS_PRICE_COLUMN => $itemData['price']
        ];

        $where = ITEMS_ID_COLUMN . ' = ?';
        $params = [$itemData['id']];

        return $this->executeQuery->update(ITEMS_TABLE, $queryData, $where, $params);
    }

    // Remover um item
    public function deleteItem(int $id): bool
    {
        $where = ITEMS_ID_COLUMN . ' = ?';
        $params = [$id];

        return $this->executeQuery->delete(ITEMS_TABLE, $where, $params);
    }
}

?>
