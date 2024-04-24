<?php

namespace WP_Framework\Model\Property;

use WP_Framework\Debug\Debug;

class Status extends Property
{
    private array $possible_status;
    # default status is first element of $possible_status
    public function __construct(string $model_singular_name, array $possible_status = ['public', 'draft', 'trash'])
    {
        parent::__construct(
            key: 'status',
            sql_type: 'varchar(20)',
            singular_name: "{$model_singular_name} Status",
            plural_name: "{$model_singular_name} Status",
            is_indexable: true,
            default_value: $possible_status[0]
        );
        $this->possible_status = $possible_status;
    }

    public function add_possible_status(string ...$status): static
    {
        array_push($this->possible_status, ...$status);
        return $this;
    }

    public function remove_possible_status(string $status)
    {
        if (($key = array_search($status, $this->possible_status)) !== false) {
            unset($this->possible_status[$key]);
        }
        return $this;
    }

    public function set_default_status(string $status): static
    {
        if (!in_array($status, $this->possible_status)) {
            throw new \Error("\$status '{$status}' needs to be one of \$this->possible_status.");
        }
        Debug::var('hallo', $this);
        $this->default_value = $status;
        return $this;
    }
}
