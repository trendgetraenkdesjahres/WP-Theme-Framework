<?php

namespace WP_Framework\Database\SQL\Statement\Clause;

use WP_Framework\Database\SQL\ThinSkinnedSyntaxCheck as SyntaxCheck;

/**
 * Values WhereClauseTrait
 *
 * Provides methods for constructing SQL VALUE clauses dynamically.
 *
 * @package WP_Framework\Database\Statement
 */
trait ValuesClauseTrait
{
    /**
     * The constructed VALUES clause
     * @var string
     **/
    private string $values_clause = '';

    /**
     * Flag indicating if the VALUES clause has been completed
     * @var bool
     **/
    private bool $values_clause_completed = false;

    public function values(string|int ...$value): self
    {
        if ($this->values_clause_completed) {
            throw new \Error("Can not append more values to complete VALUES clause.");
        }
        foreach ($value as $value) {
            SyntaxCheck::is_safe_value($value);
            if (is_string($value)) {
                $value = "'{$value}'";
            }
            $this->values_clause .= "{$value}, ";
        }
        $this->values_clause = "VALUES (" . rtrim($this->values_clause, ', ') . ")";
        $this->values_clause_completed = true;
        return $this;
    }

    /**
     * Gets the constructed VALUES clause.
     *
     * @return string The constructed VALUES clause.
     * @throws \Error If the VALUES clause is incomplete.
     */
    protected function get_values_clause(): string
    {
        if (!$this->values_clause_completed) {
            throw new \Error("VALUES Clause incomplete.");
        }
        return $this->values_clause;
    }
}
