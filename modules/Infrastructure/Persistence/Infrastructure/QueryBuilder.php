<?php

declare(strict_types=1);

namespace Persistence\Infrastructure;

use InvalidArgumentException;
use LogicException;
use Persistence\Domain\ValueObject\Query;

/**
 * QueryBuilder provides a secure, fluent interface for constructing all major SQL queries.
 * It produces an immutable Query Value Object, encapsulating SQL and safe parameter bindings.
 *
 * Supported operations:
 *  - SELECT (with JOIN, WHERE, GROUP BY, HAVING, ORDER BY, LIMIT, OFFSET)
 *  - INSERT
 *  - UPDATE
 *  - DELETE
 *  - Various WHERE/OR/AND clauses
 *  - Subqueries via raw SQL (if required)
 *  - Secure parameterization to prevent SQL injection
 *
 * Example:
 *   $query = (new QueryBuilder)
 *      ->table('users')
 *      ->select(['id', 'name'])
 *      ->where('status', '=', 'active')
 *      ->orderBy('created_at', 'DESC')
 *      ->limit(10)
 *      ->build();
 *
 * @author
 */
final class QueryBuilder
{
    private string $table = '';
    private array $columns = ['*'];
    private array $joins = [];
    private array $conditions = [];
    private array $orConditions = [];
    private array $groupBys = [];
    private array $orderBys = [];
    private array $havings = [];
    private ?int $limit = null;
    private ?int $offset = null;
    /** @var array<string, mixed> */
    private array $bindings = [];
    /** @var array<string, mixed> */
    private array $insertData = [];
    /** @var array<string, mixed> */
    private array $updateData = [];
    /** @var string */
    private string $operation = 'select';

    // ----------------- Table & Columns -------------------

    public function table(string $table): self
    {
        $clone = clone $this;
        $clone->table = $table;
        return $clone;
    }

    public function select(array $columns = ['*']): self
    {
        $clone = clone $this;
        $clone->operation = 'select';
        $clone->columns = $columns;
        return $clone;
    }

    public function insert(array $data): self
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Insert data cannot be empty.');
        }
        $clone = clone $this;
        $clone->operation = 'insert';
        $clone->insertData = $data;
        return $clone;
    }

    public function update(array $data): self
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Update data cannot be empty.');
        }
        $clone = clone $this;
        $clone->operation = 'update';
        $clone->updateData = $data;
        return $clone;
    }

    public function delete(): self
    {
        $clone = clone $this;
        $clone->operation = 'delete';
        return $clone;
    }

    // ----------------- JOINs -------------------

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $clone = clone $this;
        $clone->joins[] = strtoupper($type) . " JOIN {$table} ON {$first} {$operator} {$second}";
        return $clone;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function fullJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'FULL');
    }

    // ----------------- WHERE / OR WHERE / AND WHERE -------------------

    public function where(string $column, string $operator, mixed $value): self
    {
        $clone = clone $this;
        $placeholder = ':' . $column . '_w_' . count($clone->bindings);
        $clone->conditions[] = "{$column} {$operator} {$placeholder}";
        $clone->bindings[$placeholder] = $value;
        return $clone;
    }

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $clone = clone $this;
        $placeholder = ':' . $column . '_ow_' . count($clone->bindings);
        $clone->orConditions[] = "{$column} {$operator} {$placeholder}";
        $clone->bindings[$placeholder] = $value;
        return $clone;
    }

    // ----------------- GROUP BY / HAVING -------------------

    public function groupBy(string|array $columns): self
    {
        $clone = clone $this;
        foreach ((array)$columns as $col) {
            $clone->groupBys[] = $col;
        }
        return $clone;
    }

    public function having(string $column, string $operator, mixed $value): self
    {
        $clone = clone $this;
        $placeholder = ':' . $column . '_h_' . count($clone->bindings);
        $clone->havings[] = "{$column} {$operator} {$placeholder}";
        $clone->bindings[$placeholder] = $value;
        return $clone;
    }

    // ----------------- ORDER BY -------------------

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $dir = strtoupper($direction);
        if (!in_array($dir, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException('Order direction must be ASC or DESC.');
        }
        $clone = clone $this;
        $clone->orderBys[] = "{$column} {$dir}";
        return $clone;
    }

    // ----------------- LIMIT / OFFSET -------------------

    public function limit(int $limit): self
    {
        if ($limit < 1) {
            throw new InvalidArgumentException('Limit must be at least 1.');
        }
        $clone = clone $this;
        $clone->limit = $limit;
        return $clone;
    }

    public function offset(int $offset): self
    {
        if ($offset < 0) {
            throw new InvalidArgumentException('Offset cannot be negative.');
        }
        $clone = clone $this;
        $clone->offset = $offset;
        return $clone;
    }

    // ----------------- RAW & SUBQUERY Support -------------------

    /**
     * Allows raw SQL fragment for SELECT columns or subqueries.
     * Use carefully and always with parameter binding if user input is involved.
     *
     * @param string $rawSql
     * @return self
     */
    public function selectRaw(string $rawSql): self
    {
        $clone = clone $this;
        $clone->columns = [$rawSql];
        return $clone;
    }

    // ----------------- BUILD (finalize Query VO) -------------------

    /**
     * Builds and returns an immutable Query VO for execution.
     *
     * @return Query
     * @throws LogicException
     */
    public function build(): Query
    {
        if (empty($this->table)) {
            throw new LogicException('Table name is required.');
        }

        $sql = match ($this->operation) {
            'select' => $this->buildSelect(),
            'insert' => $this->buildInsert(),
            'update' => $this->buildUpdate(),
            'delete' => $this->buildDelete(),
            default  => throw new LogicException("Unknown operation: {$this->operation}"),
        };

        return new Query($sql, $this->bindings);
    }

    // ----------------- Internal SQL Generators -------------------

    private function buildSelect(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . " FROM {$this->table}";

        if ($this->joins) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        $sql .= $this->buildWhere();

        if ($this->groupBys) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBys);
        }
        if ($this->havings) {
            $sql .= ' HAVING ' . implode(' AND ', $this->havings);
        }
        if ($this->orderBys) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBys);
        }
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        return $sql;
    }

    private function buildInsert(): string
    {
        $fields = array_keys($this->insertData);
        $placeholders = [];
        foreach ($fields as $field) {
            $ph = ':' . $field . '_i_' . count($this->bindings);
            $placeholders[] = $ph;
            $this->bindings[$ph] = $this->insertData[$field];
        }
        $fieldsList = implode(', ', $fields);
        $placeholdersList = implode(', ', $placeholders);

        return "INSERT INTO {$this->table} ({$fieldsList}) VALUES ({$placeholdersList})";
    }

    private function buildUpdate(): string
    {
        if (!$this->conditions && !$this->orConditions) {
            throw new LogicException('UPDATE queries must have at least one WHERE condition.');
        }

        $fields = [];
        foreach ($this->updateData as $field => $value) {
            $ph = ':' . $field . '_u_' . count($this->bindings);
            $fields[] = "{$field} = {$ph}";
            $this->bindings[$ph] = $value;
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields);
        $sql .= $this->buildWhere();
        return $sql;
    }

    private function buildDelete(): string
    {
        if (!$this->conditions && !$this->orConditions) {
            throw new LogicException('DELETE queries must have at least one WHERE condition.');
        }
        $sql = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhere();
        return $sql;
    }

    private function buildWhere(): string
    {
        $allConditions = [];
        if ($this->conditions) {
            $allConditions[] = implode(' AND ', $this->conditions);
        }
        if ($this->orConditions) {
            $allConditions[] = implode(' OR ', $this->orConditions);
        }
        return $allConditions ? ' WHERE ' . implode(' AND ', $allConditions) : '';
    }

    // ----------------- RESET (optional utility) -------------------

    /**
     * Resets the builder state, returning a fresh instance.
     */
    public function reset(): self
    {
        $clone = clone $this;
        $clone->table = '';
        $clone->columns = ['*'];
        $clone->joins = [];
        $clone->conditions = [];
        $clone->orConditions = [];
        $clone->groupBys = [];
        $clone->orderBys = [];
        $clone->havings = [];
        $clone->limit = null;
        $clone->offset = null;
        $clone->bindings = [];
        $clone->insertData = [];
        $clone->updateData = [];
        $clone->operation = 'select';
        return $clone;
    }
}
