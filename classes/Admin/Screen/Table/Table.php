<?php

namespace WP_Framework\Admin\Screen\Table;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

use WP_Framework\Admin\Screen\WP_ScreenTrait;
use WP_Framework\Database\Table\AbstractTable as AbstractDBTable;
use WP_Framework\Debug\Debug;

# inspired by https://supporthost.com/wp-list-table-tutorial/
class Table extends \WP_List_Table
{
    use WP_ScreenTrait;

    private array $columns;
    private array $table_data;

    protected int $max_rows;
    protected bool $search_box;

    protected ?AbstractDBTable $database_table = null;

    public function __construct(string $item_name, ?string $items_name = null, int $max_rows = 10, bool $ajax = false, ?string $screen = null)
    {
        if (!$items_name) {
            $items_name = "{$item_name}s";
        }
        $this->max_rows = $max_rows;

        parent::__construct([
            'singular' => $item_name,
            'plural' => $items_name,
            'ajax' => $ajax,
            'screen' => $screen
        ]);
    }

    public function set_max_rows(int $rows): self
    {
        $this->max_rows = $rows;
        return $this;
    }

    public function get_max_rows(): int
    {
        return $this->get_items_per_page('per_page', $this->max_rows);
    }

    public function add_column(string $name, string $title, string $type = 'string', bool $sortable = false, bool $visible = true): self
    {
        $this->columns[$name] = [
            'title' => $title,
            'sortable' => $sortable,
            'hidden' => !$visible,
            'type' => $type
        ];
        return $this;
    }

    public function get_column_names(): array
    {
        return array_keys($this->columns);
    }

    public function set_database_table(AbstractDBTable $table): self
    {
        $this->database_table = $table;
        return $this;
    }

    private function set_table_data(): self
    {
        if (!$this->database_table) {
            throw new \Error("'private function get_table_data()' must be implemented, or method 'set_database_table' must be called.");
        }
        if (isset($_POST['s'])) {
            $this->table_data = $this->database_table
                ->get_rows_by_search($_POST['s'], ...$this->get_column_names());
        } else {
            $this->table_data = $this->database_table
                ->get_rows();
        }
        return $this;
    }

    public function get_columns(): array
    {
        $columns = [];
        foreach ($this->columns as $key => $column_info) {
            $columns[$key] = $column_info['title'];
        }
        return $columns;
    }

    public function get_sortable_columns(): array
    {
        $columns = [];
        foreach ($this->columns as $key => $column_info) {
            if ($column_info['sortable']) {
                $columns[$key] = [$key];
            }
        }
        return $columns;
    }

    protected function get_sorting_callback(): callable
    {
        return function ($a, $b) {
            $first_column_key = array_keys($this->columns)[0];

            // If no sort, default to first column
            $orderby = $_GET['orderby'] ?? $first_column_key;

            // If no order, default to asc
            $order = $_GET['order'] ?? 'asc';

            // Determine sort order
            $result = strcmp($a[$orderby], $b[$orderby]);

            // Send final sort direction to usort
            return ($order === 'asc') ? $result : -$result;
        };
    }

    public function get_hidden_columns(): array
    {
        $columns = get_user_meta(
            user_id: get_current_user_id(),
            key: "{$this->_args['plural']}_table_hidden_columns",
            single: true
        );
        if (!is_array($columns)) {
            $columns = [];
            foreach ($this->columns as $key => $column_info) {
                if ($column_info['hidden']) {
                    $columns[$key] = [$key];
                }
            }
        }
        return $columns;
    }

    /**
     * Prepare items and column_headers for the table.
     */
    public function prepare_items(): self
    {
        $this
            ->prepare_table_headers()
            ->set_table_data()
            ->sort_table_data()
            ->slice_table_data()
            ->set_pagination();

        $this->items = $this->table_data;
        $this->search_box('search', 'search_id');
        return $this;
    }

    protected function sort_table_data(): self
    {
        # sort the data
        usort($this->table_data, $this->get_sorting_callback());
        return $this;
    }

    protected function prepare_table_headers(): self
    {
        $primary  = 'appointment_type';
        $columns = $this->get_columns();
        $hidden_columns = $this->get_hidden_columns();
        $sortable_columns = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden_columns, $sortable_columns, $primary);
        return $this;
    }

    protected function slice_table_data(): self
    {
        $items_per_page = $this->get_max_rows();
        $first_item = ($this->get_pagenum() - 1) * $items_per_page;

        $this->table_data = array_slice($this->table_data, $first_item, $items_per_page);
        return $this;
    }

    protected function set_pagination(): self
    {
        $total_items = count($this->table_data);
        $items_per_page = $this->get_max_rows();

        $this->set_pagination_args([
            'total_items' => $total_items, // total number of items
            'per_page'    => $items_per_page, // items to show on a page
            'total_pages' => ceil($total_items / $items_per_page) // use ceil to round up
        ]);
        return $this;
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
        switch ($this->columns[$column_name]['type']) {
            case 'datetime':
                return $item[$column_name];
                break;
            case 'string':
            default:
                return $item[$column_name];
                break;
        }
    }

    public function __toString()
    {
        $this->prepare_items();
        ob_start();
        $this->display();
        return ob_get_clean();
    }
}
