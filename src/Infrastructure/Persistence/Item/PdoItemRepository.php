<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Item;

use App\Domain\Entities\Item;
use App\Domain\Repositories\ItemRepositoryInterface;
use App\Infrastructure\Database\Connection\DatabaseConnectionInterface;
use App\Infrastructure\Database\Exceptions\QueryExecutionException;
use App\Logging\Domain\LogEntry;
use App\Logging\Domain\LogLevelEnum;
use App\Logging\LoggerInterface;
use Config\Database\DatabaseSchemaConfig;
use PDO;
use PDOException;

/**
 * PdoItemRepository
 *
 * Repository for managing item persistence using PDO, abstracted
 * from physical database structure via DatabaseSchemaConfig.
 */
final class PdoItemRepository implements ItemRepositoryInterface
{
    private const LOG_CHANNEL = 'repositorio.item';

    private PDO $pdo;
    private LoggerInterface $logger;

    public function __construct(
        DatabaseConnectionInterface $connection,
        LoggerInterface $logger
    ) {
        $this->pdo = $connection->connect();
        $this->logger = $logger;
    }

    public function findAll(): array
    {
        $sql = sprintf(
            'SELECT 
                %s AS id, 
                %s AS name, 
                %s AS description, 
                %s AS price, 
                %s AS category_id
             FROM %s',
            DatabaseSchemaConfig::ITEM_ID,
            DatabaseSchemaConfig::ITEM_NAME,
            DatabaseSchemaConfig::ITEM_DESCRIPTION,
            DatabaseSchemaConfig::ITEM_PRICE,
            DatabaseSchemaConfig::ITEM_CATEGORY_ID,
            DatabaseSchemaConfig::ITEMS_TABLE_NAME
        );

        try {
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll();

            $this->log(LogLevelEnum::INFO, 'Itens recuperados com sucesso.', [
                'quantidade' => count($rows)
            ]);

            return array_map([$this, 'mapRowToItem'], $rows);
        } catch (PDOException $e) {
            $this->log(LogLevelEnum::ERROR, 'Erro ao recuperar itens.', [
                'erro' => $e->getMessage()
            ]);

            throw new QueryExecutionException('Não foi possível recuperar os itens.', 0, [], $e);
        }
    }

    public function save(Item $item): void
    {
        $sql = sprintf(
            'INSERT INTO %s (%s, %s, %s, %s) VALUES (:name, :description, :price, :category_id)',
            DatabaseSchemaConfig::ITEMS_TABLE_NAME,
            DatabaseSchemaConfig::ITEM_NAME,
            DatabaseSchemaConfig::ITEM_DESCRIPTION,
            DatabaseSchemaConfig::ITEM_PRICE,
            DatabaseSchemaConfig::ITEM_CATEGORY_ID
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'name'        => $item->getName(),
                'description' => $item->getDescription(),
                'price'       => $item->getPrice(),
                'category_id' => $item->getCategoryId()
            ]);

            $id = $this->pdo->lastInsertId();
            if (!$id) {
                throw new QueryExecutionException('Falha ao obter o ID do item inserido.');
            }

            $item->defineId((int)$id);

            $this->log(LogLevelEnum::INFO, 'Item inserido com sucesso.', [
                'id' => $id,
                'nome' => $item->getName()
            ]);
        } catch (PDOException $e) {
            $this->log(LogLevelEnum::ERROR, 'Erro ao salvar item.', [
                'nome' => $item->getName(),
                'erro' => $e->getMessage()
            ]);

            throw new QueryExecutionException('Erro ao salvar item.', 0, [], $e);
        }
    }

    public function update(Item $item): void
    {
        if ($item->getId() === null) {
            throw new \InvalidArgumentException('Item sem ID não pode ser atualizado.');
        }

        $sql = sprintf(
            'UPDATE %s 
             SET %s = :name, %s = :description, %s = :price, %s = :category_id 
             WHERE %s = :id',
            DatabaseSchemaConfig::ITEMS_TABLE_NAME,
            DatabaseSchemaConfig::ITEM_NAME,
            DatabaseSchemaConfig::ITEM_DESCRIPTION,
            DatabaseSchemaConfig::ITEM_PRICE,
            DatabaseSchemaConfig::ITEM_CATEGORY_ID,
            DatabaseSchemaConfig::ITEM_ID
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id'          => $item->getId(),
                'name'        => $item->getName(),
                'description' => $item->getDescription(),
                'price'       => $item->getPrice(),
                'category_id' => $item->getCategoryId()
            ]);

            $this->log(LogLevelEnum::INFO, 'Item atualizado com sucesso.', [
                'id' => $item->getId()
            ]);
        } catch (PDOException $e) {
            $this->log(LogLevelEnum::ERROR, 'Erro ao atualizar item.', [
                'id'   => $item->getId(),
                'erro' => $e->getMessage()
            ]);

            throw new QueryExecutionException('Erro ao atualizar item.', 0, [], $e);
        }
    }

    public function delete(int $id): void
    {
        $sql = sprintf(
            'DELETE FROM %s WHERE %s = :id',
            DatabaseSchemaConfig::ITEMS_TABLE_NAME,
            DatabaseSchemaConfig::ITEM_ID
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);

            $this->log(LogLevelEnum::INFO, 'Item removido com sucesso.', ['id' => $id]);
        } catch (PDOException $e) {
            $this->log(LogLevelEnum::ERROR, 'Erro ao remover item.', [
                'id'   => $id,
                'erro' => $e->getMessage()
            ]);

            throw new QueryExecutionException('Erro ao remover item.', 0, [], $e);
        }
    }

    public function findById(int $id): ?Item
    {
        $sql = sprintf(
            'SELECT 
                %s AS id, 
                %s AS name, 
                %s AS description, 
                %s AS price, 
                %s AS category_id 
             FROM %s 
             WHERE %s = :id',
            DatabaseSchemaConfig::ITEM_ID,
            DatabaseSchemaConfig::ITEM_NAME,
            DatabaseSchemaConfig::ITEM_DESCRIPTION,
            DatabaseSchemaConfig::ITEM_PRICE,
            DatabaseSchemaConfig::ITEM_CATEGORY_ID,
            DatabaseSchemaConfig::ITEMS_TABLE_NAME,
            DatabaseSchemaConfig::ITEM_ID
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();

            if (!$row) {
                $this->log(LogLevelEnum::WARNING, 'Item não encontrado.', ['id' => $id]);
                return null;
            }

            $this->log(LogLevelEnum::INFO, 'Item encontrado com sucesso.', ['id' => $id]);
            return $this->mapRowToItem($row);
        } catch (PDOException $e) {
            $this->log(LogLevelEnum::ERROR, 'Erro ao buscar item.', [
                'id'   => $id,
                'erro' => $e->getMessage()
            ]);

            throw new QueryExecutionException('Erro ao buscar item.', 0, [], $e);
        }
    }

    /**
     * Converts a database row to a domain Item entity.
     *
     * @param array<string, mixed> $row
     * @return Item
     */
    private function mapRowToItem(array $row): Item
    {
        return new Item(
            $row['name'],
            (float)$row['price'],
            (int)$row['category_id'],
            $row['description'] ?? '' // fallback necessário
        );
    }

    /**
     * Structured logging helper.
     *
     * @param LogLevelEnum $level
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    private function log(LogLevelEnum $level, string $message, array $context = []): void
    {
        $this->logger->log(new LogEntry(
            level: $level,
            message: $message,
            context: $context,
            channel: self::LOG_CHANNEL
        ));
    }
}
