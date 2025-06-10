<?php

declare(strict_types=1);

namespace Persistence\Domain\Contract;

/**
 * QueryInterface defines the contract for a value object representing
 * a parametrized SQL query for execution by a database service.
 *
 * Implementations should be immutable and guarantee safe parameter binding.
 *
 * Typical usage:
 *   $query = new SelectQuery('SELECT * FROM users WHERE id = :id', [':id' => 1]);
 *   $sql = $query->getSql();
 *   $params = $query->getBindings();
 *
 * @author
 */
interface QueryInterface
{
    /**
     * Returns the parametrized SQL statement ready for execution.
     *
     * @return string
     */
    public function getSql(): string;

    /**
     * Returns an array of named parameter bindings for the query.
     *
     * @return array<string, mixed>
     */
    public function getBindings(): array;
}
