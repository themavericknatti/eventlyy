<?php

namespace Ace;

use PDO;
use Exception;
use Ace\Exceptions\ModelNotFoundException;

class QueryBuilder
{
    protected string $modelClass;
    protected string $table;
    protected array $wheres = [];
    protected array $orWheres = [];
    protected array $params = [];
    protected ?string $orderBy = null;
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected string $select = '*';

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->table = $modelClass::tableName();
    }

    /**
     * Add a basic where clause to the query (hardened against SQL injection)
     */
    public function where(string $column, mixed $value, string $operator = '='): self
    {
        // Whitelist safe SQL operators
        $allowedOperators = ['=', '!=', '<>', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE', 'ILIKE', 'IS', 'IS NOT'];
        $cleanOperator = in_array(strtoupper(trim($operator)), $allowedOperators, true) ? strtoupper(trim($operator)) : '=';

        // Sanitize column name to allow only valid SQL identifiers
        $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);

        // Parameter placeholder name
        $paramName = 'p_' . count($this->params) . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $cleanColumn);

        // Safely wrap identifier
        if (str_contains($cleanColumn, '.')) {
            [$tbl, $col] = explode('.', $cleanColumn, 2);
            $this->wheres[] = "`$tbl`.`$col` $cleanOperator :$paramName";
        } else {
            $this->wheres[] = "`$cleanColumn` $cleanOperator :$paramName";
        }

        $this->params[$paramName] = $value;
        return $this;
    }

    /**
     * Add an order by clause (hardened against SQL injection)
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper(trim($direction)) === 'DESC' ? 'DESC' : 'ASC';
        $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);

        if (str_contains($cleanColumn, '.')) {
            [$tbl, $col] = explode('.', $cleanColumn, 2);
            $this->orderBy = "`$tbl`.`$col` $direction";
        } else {
            $this->orderBy = "`$cleanColumn` $direction";
        }

        return $this;
    }

    /**
     * Add an OR where clause
     */
    public function orWhere(string $column, mixed $value, string $operator = '='): self
    {
        $allowedOperators = ['=', '!=', '<>', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE', 'IS', 'IS NOT'];
        $cleanOperator = in_array(strtoupper(trim($operator)), $allowedOperators, true) ? strtoupper(trim($operator)) : '=';
        $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);
        $paramName = 'p_' . count($this->params) . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $cleanColumn);

        if (str_contains($cleanColumn, '.')) {
            [$tbl, $col] = explode('.', $cleanColumn, 2);
            $this->orWheres[] = "`$tbl`.`$col` $cleanOperator :$paramName";
        } else {
            $this->orWheres[] = "`$cleanColumn` $cleanOperator :$paramName";
        }

        $this->params[$paramName] = $value;
        return $this;
    }

    /**
     * Add a WHERE IN clause
     */
    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            // Impossible condition — no results
            $this->wheres[] = '0 = 1';
            return $this;
        }

        $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);
        $placeholders = [];
        foreach ($values as $i => $val) {
            $paramName = 'in_' . count($this->params) . '_' . $i;
            $placeholders[] = ":$paramName";
            $this->params[$paramName] = $val;
        }

        $colRef = str_contains($cleanColumn, '.')
            ? '`' . implode('`.`', explode('.', $cleanColumn, 2)) . '`'
            : "`$cleanColumn`";

        $this->wheres[] = "$colRef IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    /**
     * Add a WHERE column IS NULL clause
     */
    public function whereNull(string $column): self
    {
        $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);
        $colRef = str_contains($cleanColumn, '.')
            ? '`' . implode('`.`', explode('.', $cleanColumn, 2)) . '`'
            : "`$cleanColumn`";

        $this->wheres[] = "$colRef IS NULL";
        return $this;
    }

    /**
     * Add a WHERE column IS NOT NULL clause
     */
    public function whereNotNull(string $column): self
    {
        $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);
        $colRef = str_contains($cleanColumn, '.')
            ? '`' . implode('`.`', explode('.', $cleanColumn, 2)) . '`'
            : "`$cleanColumn`";

        $this->wheres[] = "$colRef IS NOT NULL";
        return $this;
    }

    /**
     * Add a WHERE BETWEEN clause
     */
    public function whereBetween(string $column, mixed $min, mixed $max): self
    {
        $cleanColumn = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);
        $paramMin = 'btwn_' . count($this->params) . '_min';
        $paramMax = 'btwn_' . count($this->params) . '_max';

        $colRef = str_contains($cleanColumn, '.')
            ? '`' . implode('`.`', explode('.', $cleanColumn, 2)) . '`'
            : "`$cleanColumn`";

        $this->wheres[] = "$colRef BETWEEN :$paramMin AND :$paramMax";
        $this->params[$paramMin] = $min;
        $this->params[$paramMax] = $max;
        return $this;
    }

    /**
     * Set query limit
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set query offset
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Set specific columns to select
     */
    public function select(string ...$columns): self
    {
        $cleaned = array_map(fn($c) => '`' . preg_replace('/[^a-zA-Z0-9_.]/', '', $c) . '`', $columns);
        $this->select = implode(', ', $cleaned);
        return $this;
    }

    /**
     * Compile and execute select statement, returning array of Model instances
     */
    public function get(): array
    {
        $db = Application::$app->db;
        if (!$db) {
            return [];
        }

        $sql = "SELECT {$this->select} FROM `{$this->table}`";

        // Build WHERE clause with AND + OR support
        $whereParts = [];
        if (!empty($this->wheres)) {
            $whereParts[] = '(' . implode(' AND ', $this->wheres) . ')';
        }
        foreach ($this->orWheres as $orWhere) {
            $whereParts[] = $orWhere;
        }
        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(' OR ', $whereParts);
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        $statement = $db->prepare($sql);
        foreach ($this->params as $key => $value) {
            $statement->bindValue(":$key", $value);
        }

        $statement->execute();
        $records = $statement->fetchAll();

        $models = [];
        foreach ($records as $record) {
            $model = new $this->modelClass();
            $model->loadData($record);
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Get first matching record or null
     */
    public function first(): ?Model
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Get the first record matching the query or throw a ModelNotFoundException.
     *
     * @throws ModelNotFoundException
     */
    public function firstOrFail(): Model
    {
        $record = $this->first();

        if (!$record) {
            throw new ModelNotFoundException($this->modelClass);
        }

        return $record;
    }

    /**
     * Find a record by primary key, or throw a ModelNotFoundException.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(mixed $id): Model
    {
        $tempModel = new $this->modelClass();
        $primaryKey = $tempModel->primaryKey();

        $record = $this->where($primaryKey, $id)->first();

        if (!$record) {
            throw new ModelNotFoundException($this->modelClass, $id);
        }

        return $record;
    }

    /**
     * Get total count matching query criteria
     */
    public function count(): int
    {
        $db = Application::$app->db;
        if (!$db) {
            return 0;
        }

        $sql = "SELECT COUNT(*) FROM `{$this->table}`";

        $whereParts = [];
        if (!empty($this->wheres)) {
            $whereParts[] = '(' . implode(' AND ', $this->wheres) . ')';
        }
        foreach ($this->orWheres as $orWhere) {
            $whereParts[] = $orWhere;
        }
        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(' OR ', $whereParts);
        }

        $statement = $db->prepare($sql);
        foreach ($this->params as $key => $value) {
            $statement->bindValue(":$key", $value);
        }

        $statement->execute();
        $return = (int)$statement->fetchColumn();
        return $return;
    }
}

