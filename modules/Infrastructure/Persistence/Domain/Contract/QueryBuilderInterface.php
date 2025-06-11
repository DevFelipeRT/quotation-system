<?php

declare(strict_types=1);

namespace Persistence\Domain\Contract;

use Persistence\Domain\ValueObject\Query;
use InvalidArgumentException;
use LogicException;

/**
 * QueryBuilderInterface defines a fluent, immutable interface for building secure SQL queries.
 * It supports SELECT, INSERT, UPDATE, DELETE, JOINs, conditions, grouping, ordering, limits,
 * subqueries (via raw), and secure parameter binding.
 */
interface QueryBuilderInterface
{
    /**
     * Sets the table name for the query.
     *
     * @param string $table
     * @return self
     */
    public function table(string $table): self;

    /**
     * Sets the columns to select. Defaults to ['*'].
     *
     * @param array<int, string> $columns
     * @return self
     */
    public function select(array $columns = ['*']): self;

    /**
     * Prepares an INSERT operation with the provided data.
     *
     * @param array<string, mixed> $data
     * @return self
     * @throws InvalidArgumentException if $data is empty
     */
    public function insert(array $data): self;

    /**
     * Prepares an UPDATE operation with the provided data.
     *
     * @param array<string, mixed> $data
     * @return self
     * @throws InvalidArgumentException if $data is empty
     */
    public function update(array $data): self;

    /**
     * Prepares a DELETE operation.
     *
     * @return self
     */
    public function delete(): self;

    /**
     * Adds a JOIN clause.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @param string $type
     * @return self
     */
    public function join(
        string $table,
        string $first,
        string $operator,
        string $second,
        string $type = 'INNER'
    ): self;

    /**
     * Adds a LEFT JOIN clause.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self;

    /**
     * Adds a RIGHT JOIN clause.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return self
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self;

    /**
     * Adds a FULL JOIN clause.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return self
     */
    public function fullJoin(string $table, string $first, string $operator, string $second): self;

    /**
     * Adds a WHERE condition.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return self
     */
    public function where(string $column, string $operator, mixed $value): self;

    /**
     * Adds an OR WHERE condition.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return self
     */
    public function orWhere(string $column, string $operator, mixed $value): self;

    /**
     * Adds a GROUP BY clause.
     *
     * @param string|array<int, string> $columns
     * @return self
     */
    public function groupBy(string|array $columns): self;

    /**
     * Adds a HAVING condition.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return self
     */
    public function having(string $column, string $operator, mixed $value): self;

    /**
     * Adds an ORDER BY clause.
     *
     * @param string $column
     * @param string $direction
     * @return self
     * @throws InvalidArgumentException if $direction is invalid
     */
    public function orderBy(string $column, string $direction = 'ASC'): self;

    /**
     * Sets the LIMIT clause.
     *
     * @param int $limit
     * @return self
     * @throws InvalidArgumentException if $limit < 1
     */
    public function limit(int $limit): self;

    /**
     * Sets the OFFSET clause.
     *
     * @param int $offset
     * @return self
     * @throws InvalidArgumentException if $offset < 0
     */
    public function offset(int $offset): self;

    /**
     * Sets a raw SQL fragment for SELECT columns or subqueries (use with care).
     *
     * @param string $rawSql
     * @return self
     */
    public function selectRaw(string $rawSql): self;

    /**
     * Builds and returns an immutable Query Value Object for execution.
     *
     * @return Query
     * @throws LogicException if configuration is invalid
     */
    public function build(): Query;

    /**
     * Resets the builder to its initial state.
     *
     * @return self
     */
    public function reset(): self;
}
