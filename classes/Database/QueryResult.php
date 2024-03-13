<?php

namespace WP_Framework\Database;

class QueryResult
{
    private array $data;
    public function __construct(array $array_a)
    {
        $this->data = $array_a;
    }

    public function get_data(): array
    {
        return $this->data;
    }
}
