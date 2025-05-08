<?php 

namespace App\Domains\Quotation\Presentation\Http\Controllers;

use Exception;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../Config/config.php';

class QuotationController extends Controller
{
    private QuotationModel $quotationModel;

    public function __construct(?Session $session = null)
    {
        $this->quotationModel = new QuotationModel;
        parent::__construct($this->quotationModel, $session);
    }

    // Métodos CRUD de Orçamento
    
    // Método para adicionar novo orçamento
    public function addQuotation(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $clientId = $_POST['clientId'] ?? null;

            try {
                // Instanciando um objeto Quotation
                $quotation = new Quotation($name, $description, $clientId);

                // Adicionando o orçamento através do model
                $this->quotationModel->addQuotation($quotation);

                // Obtendo e definindo o ID
                $quotation->setId($this->quotationModel->getLastInsertedId());

                // Registrar sucesso no log
                $this->logger->info("Orçamento '{$quotation->getName()}' com Id: '{$quotation->getId()}' adicionado com sucesso.");

            } catch (Exception $e) {
                $this->logger->error('Erro ao adicionar orçamento: ' . $e->getMessage());
                echo 'Erro ao adicionar orçamento.';
                return;
            }
        }
    }

    // Método para obter lista de todos orçamentos
    protected function getAllQuotations(): array
    {
        $quotationsList = $this->quotationModel->getAllQuotations();
        $this->logger->info('Lista de orçamentos obtida.');
        return $quotationsList;
    }

    // Método para instanciar lista de orçamentos em objetos
    public function getAllQuotationsObjects(): array
    {
        $quotationsList = $this->getAllQuotations();

        $quotationsObjects = [];
        foreach ($quotationsList as $key => $quotation) {
            $quotationsObjects[$key] = new Quotation($quotation[QUOTATION_NAME_COLUMN], $quotation[QUOTATION_DESCRIPTION_COLUMN], $quotation[QUOTATION_CLIENT_ID_COLUMN]);
            $quotationsObjects[$key]->setId($quotation[QUOTATION_ID_COLUMN]);
            $quotationsObjects[$key]->setCreationDate($quotation[QUOTATION_ITEM_CREATED_AT_COLUMN]);
        }

        return $quotationsObjects;
    }

    // Método para atualizar um orçamento
    public function updateQuotation(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $clientId = (int) $_POST['clientId'];

            $quotationObjects = $this->session->getData('quotationObjects');
            $quotation = $quotationObjects[$id]['object'];

            try {
                // Atualizando dados do objeto orçamento
                $quotation->setName($name)->setDescription($description)->setClientId($clientId);

                // Atualizando o orçamento através do model
                $this->quotationModel->updateQuotation($quotation);

                // Registrar sucesso no log
                $this->logger->info("Orçamento com Id: '{$quotation->getId()}' atualizado com sucesso para '{$quotation->getName()}' com a descrição: '{$quotation->getDescription()}' e com cliente '{$quotation->getClientId()}'.");

            } catch (Exception $e) {
                $this->logger->error('Erro ao atualizar orçamento: ' . $e->getMessage());
                echo 'Erro ao atualizar orçamento.';
                return;
            }
        }
    }

    // Método para remover um orçamento
    public function deleteQuotation(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];

            $quotationObjects = $this->session->getData('quotationObjects');
            $quotation = $quotationObjects[$id]['object'];

            try {
                $this->quotationModel->deleteQuotation($quotation);
    
                $this->logger->info("Orçamento com Id: $id removido com sucesso.");
    
            } catch (Exception $e) {
                $this->logger->error("Erro ao remover orçamento com Id $id: " . $e->getMessage());
    
                echo 'Erro ao remover orçamento.';
                return;
            }
        }
    }
}

?>