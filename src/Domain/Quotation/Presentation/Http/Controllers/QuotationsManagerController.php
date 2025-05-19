<?php 

namespace App\Domain\Quotation\Presentation\Http\Controllers;

use App\Domais\Item\Presentation\Http\Controllers\Controller as ItemController;
use App\Presentation\Http\Controllers\AbstractController;
use SessionHandler;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../Config/config.php';

class QuotationsManagerController extends AbstractController
{
    public QuotationController $quotationController;
    public ItemController $itemController;
    public QuotationItemController $quotationItemController;

    public function __construct(SessionHandler $session)
    {
        $this->quotationController = new QuotationController($session);
        $this->itemController = new ItemController();
        $this->quotationItemController = new QuotationItemController();
        parent::__construct(null, $session);
    }

    // Método para gerar a página
    public function index(?string $headerTitle = 'Meus Orçamentos', ?string $fileName = 'quotations_manager', ?string $viewName = 'test', ?array $viewData = []): void {
        $headerData = [
            'headerTitle' => $headerTitle,
            'fileName' => $fileName
        ];

        $viewData = $this->getViewData($fileName);

        $this->render->renderTemplate('header_template', $headerData);
        $this->render->renderView($viewName, $viewData);
        $this->render->renderTemplate('footer_template');
        $this->endDatabaseConnection();
        $this->logger->info('Página de gerenciamento de itens acessada.');
    }

    // Métodos Setters

    // Método para definir itens de cada objeto orçamento
    protected function setQuotationsItems(array $quotations): void
    {
        foreach ($quotations as $quotation) {
            $items = $this->quotationItemController->getQuotationItemsObjects($quotation);
            foreach ($items as $item) {
                $quotation->addItem($item);
            }
        }
    }

    // Métodos Getters

    // Método para retornar dados ao método index
    public function getViewData(string $fileName): array
    {
        $viewData = $this->getQuotationsManagerData();
        $viewData ['actionFileName'] = $fileName . '.php';
        $viewData ['session'] = $this->session;
        foreach ($this->itemController->getItemsManagerData() as $key => $value) {
            $viewData[$key] = $value;
        }

        return $viewData;
    }

    // Método para retornar dados ao genrenciador de orçamentos
    public function getQuotationsManagerData(): array
    {
        $quotations = $this->quotationController->getAllQuotationsObjects();
        $itemTypes = $this->quotationItemController->setQuotationItemTypes()->getQuotiationItemTypes();
        $this->setQuotationsItems($quotations);

        $data = [
            'quotations' => $quotations,
            'itemTypes' => $itemTypes
        ];

        return $data;
    }
}

?>