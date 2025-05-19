<?php

namespace App\Domains\Quotation\Presentation\Http\Controllers;

use App\Domain\Quotation\Domain\Entities\Quotation;
use App\Domain\Quotation\Domain\Entities\QuotationItem;
use App\Models\ItemModel;
use App\Models\QuotationItemModel;
use App\Presentation\Http\Controllers\AbstractController;
use Exception;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../Config/config.php';


class QuotationItemController extends AbstractController
{
    private QuotationItemModel $quotationItemModel;
    private ItemModel $itemModel;

    public function __construct()
    {
        $this->quotationItemModel = new QuotationItemModel();
        parent::__construct($this->quotationItemModel);
    }

    //Métodos CRUD de Item de Orçamento

    // Método para adicionar itens de orçamento
    public function addQuotationItem(Quotation $quotation): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $items = (array) $_POST['items'];

            try {
                // Atualizando Types da classe QuotationItem
                $this->setQuotationItemTypes();

                // Instanciando objetos de QuotationItem e adicionando ao banco de dados
                foreach ($items as $item) {
                    // Instanciando objetos de QuotationItem
                    $quotationItem = new QuotationItem($quotation, $item['item'], $item['quantity']);

                    // Definindo o typeId de QuotationItem
                    $quotationItem->setTypeId($item['typeId']);

                    // Salvando QuotationItem no banco de dados 
                    $this->quotationItemModel->addQuotationItem($quotationItem);

                    // Obtendo Id de QuotationItem
                    $id = $this->quotationItemModel->getLastInsertedId();

                    // Definindo Id de QuotationItem
                    $quotationItem->setId($id);

                    // Registrando log de sucesso
                    $this->logger->info("Objeto de item de orçamento '{$quotationItem->getItem()->getName()}' instanciado e salvo com sucesso com Id: '{$quotationItem->getId()}'.");
                }
                
            } catch (Exception $e) {
                $this->logger->error("Erro ao adicionar item de orçamento: " . $e->getMessage());

                echo 'Erro ao adicionar item de orçamento.';
                return;
            }
        }
    }

    // Método para obter lista de itens de um orçamento
    protected function getQuotationItems(Quotation $quotation)
    {
        $quotationId = $quotation->getId();
        try {
            $quotationItems = $this->quotationItemModel->getQuotationItems($quotationId);
            return $quotationItems;
        } catch (Exception $e) {
            $this->logger->error('Erro ao obter lista de itens de orçamento: ' . $e->getMessage());
            echo 'Erro ao obter lista de itens de orçamento.';
        }
    }

    // Método para obter objetos de itens de um orçamento
    public function getQuotationItemsObjects(Quotation $quotation): array
    {
        $quotationItems = $this->getQuotationItems($quotation);

        $quotationItemsObjects = [];
        foreach ($quotationItems as $key => $quotationItem){
            $item = $this->getItemObjectById($quotationItem[QUOTATION_ITEM_ITEM_ID_COLUMN]);

            $quotationItemsObjects[$key] = new QuotationItem($quotation, $item, $quotationItem[QUOTATION_ITEM_QUANTITY_COLUMN]);
        }

        return $quotationItemsObjects;
    }

    // Método para atualizar um item de orçamento
    public function updateQuotationItem(QuotationItem $quotationItem): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $quantity = (int) $_POST['quantity'];
            $typeId = (int) $_POST['typeId'];

            try {
                // Atualizando Types da classe QuotationItem
                $this->setQuotationItemTypes();

                // Atualizando objeto de QuotationItem
                $quotationItem->setQuantity($quantity)->setTypeId($typeId);
                
                // Atualizando registro no banco de dados
                $this->quotationItemModel->updateQuotationItem($quotationItem);

                // Registrando log de sucesso
                $this->logger->info("Item de orçamento '{$quotationItem->getItem()->getName()}' com Id: '{$quotationItem->getId()}' atualizado com sucesso para a quantidade: '{$quotationItem->getQuantity()}' e typeId: '{$quotationItem->getTypeId()}'.");
                
            } catch (Exception $e) {
                $this->logger->error("Erro ao atualizar item de orçamento: " . $e->getMessage());

                echo 'Erro ao atualizar item de orçamento.';
                return;
            }
        }
    }

    // Método para remover um item de orçamento
    public function deleteQuotationItem(QuotationItem $quotationItem): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Salvando Id
                $id = $quotationItem->getId();
                
                // Destruindo objeto
                unset($quotationItem);
                
                // Removendo do banco de dados
                $this->quotationItemModel->deleteQuotationItem($id);

                // Registrando log de sucesso
                $this->logger->info("Item de orçamento com Id: '{$id}' removido com sucesso.");
                
            } catch (Exception $e) {
                $this->logger->error("Erro ao remover item de orçamento: " . $e->getMessage());

                echo 'Erro ao remover item de orçamento.';
                return;
            }
        }
    }

    // Métodos CRUD de Tipos de itens de orçamento

    // Método para adicionar tipo de item de orçameto
    public function addQuotiationItemType(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = (string) $_POST['name'];
            $description = (string) $_POST['description'];

            try {
                $this->quotationItemModel->addType($name, $description);
                $id = $this->quotationItemModel->getLastInsertedId();
                $this->setQuotationItemTypes();
                $type = QuotationItem::getTypeById($id);

                $this->logger->info("Tipo de item de orçamento '{$type[TYPES_NAME_COLUMN]}' com Id: '{$type[TYPES_ID_COLUMN]}' adicionado com sucesso.");
            } catch (Exception $e) {
                $this->logger->error("Erro ao adicionar tipo de item de orçamento: " . $e->getMessage());
                echo ('Erro ao adicionar tipo de item de orçamento.');
                return;
            }

        }
    }

    // Método para obter tipos de item de orçamento
    public function getQuotiationItemTypes(): array
    {
        try {
            $types = $this->quotationItemModel->getQuotationItemTypes();
            $this->logger->info("Sucesso ao obter tipos de itens de orçamento");
            return $types;
        } catch (Exception $e) {
            $this->logger->error("Erro ao obter tipos de itens de orçamento: " . $e->getMessage());
            echo "Erro ao obter tipos de itens de orçamento.";
            return [];
        }
    }

    // Método para atualizar tipo de item de orçamento
    public function updateQuotationItemType(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) $_POST['id'];
            $name = (string) $_POST['name'];
            $description = (string) $_POST['description'];

            $type = [];
            $type[TYPES_ID_COLUMN] = $id;
            $type[TYPES_NAME_COLUMN] = $name;
            $type[TYPES_DESCRIPTION_COLUMN] = $description;

            try {
                $this->quotationItemModel->updateType($type);
                $this->setQuotationItemTypes();
                $type = QuotationItem::getTypeById($id);

                $this->logger->info("Tipo de item de orçamento com Id: '{$type[TYPES_ID_COLUMN]}' atualizado com sucesso com Nome: '{$type[TYPES_NAME_COLUMN]}' e Descriçao: '{$type[TYPES_DESCRIPTION_COLUMN]}'.");
            } catch (Exception $e) {
                $this->logger->error("Erro ao atualizar tipo de item de orçamento: " . $e->getMessage());
                echo ('Erro ao atualizar tipo de item de orçamento.');
                return;
            }
        }
    }

    // Método para remover tipo de item de orçamento
    public function deleteQuotationItemType(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) $_POST['id'];

            try {
                $this->quotationItemModel->deleteType($id);
                $this->logger->info("Tipo de item de orçamento com Id: '{$id}' removido com sucesso.");
            } catch (Exception $e) {
                $this->logger->error("Erro ao remover tipo de item de orçamento: " . $e->getMessage());

                echo 'Erro ao remover tipo de item de orçamento.';
                return;
            }
        }
    }

    // Métodos Getters

    // Método para obter objeto de Item por Id
    protected function getItemObjectById(int $id): Item
    {
        try {
            $item = $this->itemModel->getItemById($id);
        } catch (Exception $e) {
            $this->logger->error("Erro ao obter item com Id '{$id}': " . $e->getMessage());
        }

        $itemObject = new Item($item[ITEMS_NAME_COLUMN], $item[ITEMS_PRICE_COLUMN], $item[ITEMS_CATEGORY_ID_COLUMN], $item[ITEMS_DESCRIPTION_COLUMN]);
        $itemObject->setId($item[ITEMS_ID_COLUMN]);

        return $itemObject;
    }

    // Métodos Setters

    // Método para definir Types da classe QuotationItem recuperados do banco de dados
    public function setQuotationItemTypes(): self
    {
        try {
            $types = $this->quotationItemModel->getQuotationItemTypes();
            QuotationItem::setTypes($types);
            return $this;
        } catch (Exception $e) {
            $this->logger->error("Erro ao definir ou obter tipos do banco de dados: " . $e->getMessage());
            echo "Erro ao definir ou obter tipos do banco de dados.";
            return $this;
        }
    }
}

?>