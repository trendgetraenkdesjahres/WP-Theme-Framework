<?php

namespace WP_Framework\Database\SQL\Statement\Clause;

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

    public function values(string ...$value): self
    {
        if ($this->values_clause_completed) {
            throw new \Error("Can not append more values to complete VALUES clause.");
        }
        foreach ($value as $value) {
            # TODO insert checking
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
