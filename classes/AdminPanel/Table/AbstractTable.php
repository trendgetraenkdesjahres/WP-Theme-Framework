<?php

namespace WP_Framework\AdminPanel\Table;

use WP_Framework\Database\SQLSyntax;
use WP_List_Table;

abstract class AbstractTable extends WP_List_Table
{

    protected array $columns;
    private array $table_data;

    protected ?string $database_table = null;

    public function __construct(string $item_name, ?string $items_name = null, bool $ajax = false, ?string $screen = null)
    {
        if (!$items_name) {
            $items_name = "{$item_name}s";
        }
        parent::__construct([
            'singular' => $item_name,
            'plural' => $items_name,
            'ajax' => $ajax,
            'screen' => $screen
        ]);
    }

    public function add_column(string $name, string $title, bool $sortable = false, bool $visible = true): WP_List_Table
    {
        $this->columns[$name] = [
            'title' => $title,
            'sortable' => $sortable,
            'hidden' => !$visible
        ];
        return $this;
    }

    public function bind_to_database_table(string $table_name): AbstractTable
    {
        if (!SQLSyntax::field_name($table_name)) {
            throw new \Error("'$table_name' is not a sql table name.");
        }
        $this->database_table = $table_name;
        return $this;
    }

    private function set_table_data(): AbstractTable
    {
        if (!$this->database_table) {
            throw new \Error("'private function get_table_data()' must be implemented, or method 'bind_to_database_table' must be called.");
        }
        global $wpdb;
        $this->table_data = $wpdb->get_results(
            "SELECT * from {$this->database_table}",
            ARRAY_A
        );
        return $this;
    }


    # wordpress needs those:

    public function get_columns(): array
    {
        $columns = [];
        foreach ($this->columns as $column) {
            if (!$column['sortable'] && !$column['hidden']) {
                array_push($columns, $column['title']);
            }
        }
        return $columns;
    }

    public function get_sortable_columns(): array
    {
        $columns = [];
        foreach ($this->columns as $column) {
            if ($column['sortable']) {
                array_push($columns, $column['title']);
            }
        }
        return $columns;
    }

    public function get_hidden_columns(): array
    {
        $columns = [];
        foreach ($this->columns as $column) {
            if ($column['hidden']) {
                array_push($columns, $column['title']);
            }
        }
        return $columns;
    }

    /**
     * Prepare items and column_headers for the table.
     */
    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden_columns = $this->get_hidden_columns();
        $sortable_columns = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden_columns, $sortable_columns);

        $this->set_table_data();
        $this->items = $this->table_data;
    }

    /**
     * This is method that is used to render a column when no other specific method exists for that column.
     * When WP_List_Tables attempts to render your columns (within single_row_columns()), it first checks for a column-specific method.
     * If none exists, it defaults to this method instead.
     * This method accepts two arguments, a single $item array and the $column_name (as a slug).
     *
     * @param $item $item [explicite description]
     * @param $column_name $column_name [explicite description]
     *
     * @return void
     */
    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }
}
