<?php 

namespace App\Models;

use App\Database\ExecuteQuery;
use App\Utils\Logger;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../Config/config.php';

class Model {
    protected ExecuteQuery $executeQuery;
    protected Logger $logger;

    public function __construct()
    {
        $this->executeQuery = new ExecuteQuery();
        $this->logger = new Logger(MODELS_LOG_PATH);
    }

    // Obter o Id do último id adicionado
    public function getLastInsertedId(): int {
        return $this->executeQuery->getLastInsertedId();
    }

    // Encerrar conexão com o banco de dados
    public function endDatabaseConnection(): void
    {
        $this->executeQuery->endConnection();
    }
}

?>