<?php

declare(strict_types=1);

namespace Persistence\Infrastructure;

use Persistence\Domain\Contract\DatabaseExecutionInterface;
use Persistence\Domain\Contract\QueryBuilderInterface;
use Persistence\Domain\Contract\QueryInterface;
use Persistence\Infrastructure\QueryBuilder;
use PublicContracts\Persistence\PersistenceFacadeInterface;

/**
 * PersistenceFacade unifies query building and execution, abstracting the infrastructure details.
 *
 * This class is the preferred entry point for all persistence operations.
 */
final class PersistenceFacade implements PersistenceFacadeInterface
{
    private DatabaseExecutionInterface $executor;
    private QueryBuilderInterface $builder;

    public function __construct(
        DatabaseExecutionInterface $executor,
        QueryBuilderInterface $builder
    ) {
        $this->executor = $executor;
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function queryBuilder(): QueryBuilder
    {
        return $this->builder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(QueryInterface $query): mixed
    {
        return $this->executor->execute($query);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): void
    {
        $this->executor->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
        $this->executor->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(): void
    {
        $this->executor->rollback();
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId(): int|string
    {
        return $this->executor->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function affectedRows(): int
    {
        return $this->executor->affectedRows();
    }
}
