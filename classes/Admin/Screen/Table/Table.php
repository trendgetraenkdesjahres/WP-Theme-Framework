<?php

namespace WP_Framework\Admin\Screen\Table;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

use WP_Framework\Admin\Screen\WP_ScreenTrait;
use WP_Framework\Database\Table\AbstractTable as AbstractDBTable;

/**
 * Class Table
 * Represents a custom table (extending WP_List_Table).
 * Inspired by https://supporthost.com/wp-list-table-tutorial/
 */
class Table extends \WP_List_Table
{
    use WP_ScreenTrait;

    /**
     * @var array The columns of the table.
     **/
    private array $columns;

    protected string $primary_column;

    /**
     * @var array The data of the table.
     **/
    private array $table_data;

    /**
     * @var int The maximum number of rows per page.
     **/
    protected int $max_rows;

    /**
     * @var bool Whether to display a search box.
     **/
    protected bool $search_box;

    /**
     * @var AbstractDBTable|null The database table associated with this table.
     **/
    protected ?AbstractDBTable $database_table = null;

    /**
     * Constructor for the Table class.
     *
     * @param string $item_name The singular name of the items in the table.
     * @param string|null $items_name The plural name of the items in the table.
     * @param int $max_rows The maximum number of rows per page.
     * @param bool $ajax Whether the table supports AJAX.
     * @param string|null $screen The screen ID where the table should be displayed.
     */
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

    /**
     * Set the maximum number of rows per page.
     *
     * @param int $rows The maximum number of rows per page.
     * @return self
     */
    public function set_max_rows(int $rows): static
    {
        $this->max_rows = $rows;
        return $this;
    }

    /**
     * Get the maximum number of rows per page, considering the WordPress user "per page" setting.
     *
     * @return int The maximum number of rows per page.
     */
    public function get_max_rows(): int
    {
        return $this->get_items_per_page('per_page', $this->max_rows);
    }

    /**
     * Add a column to the table.
     *
     * This method adds a new column to the table with the specified name, title, and options.
     * By default, the column type is set to 'string', and it is not sortable or visible.
     *
     * @param string $name The name (key) of the column.
     * @param string $title The title of the column.
     * @param string $type The type of data in the column (e.g., 'string', 'datetime').
     * @param bool $sortable Whether the column should be sortable.
     * @param bool $visible Whether the column should be visible.
     * @return static
     */
    public function add_column(string $name, string $title, string $type = 'string', bool $sortable = false, bool $visible = true): static
    {
        $this->columns[$name] = [
            'title' => $title,
            'sortable' => $sortable,
            'hidden' => !$visible,
            'type' => $type
        ];
        return $this;
    }

    /**
     * Get the names of all columns in the table.
     *
     * @return array An array containing the names of all columns in the table.
     */
    public function get_column_names(): array
    {
        return array_keys($this->columns);
    }

    /**
     * Set the database table associated with this table.
     *
     * @param AbstractDBTable $table The database table object to associate.
     * @return static
     */
    public function set_database_table(AbstractDBTable $table): static
    {
        $this->database_table = $table;
        return $this;
    }

    /**
     * Get the column titles.
     *
     * @return array An associative array where keys are column names and values are column titles.
     */
    public function get_columns(): array
    {
        $columns = [];
        foreach ($this->columns as $key => $column_info) {
            $columns[$key] = $column_info['title'];
        }
        return $columns;
    }

    /**
     * Get the sortable columns.
     *
     * @return array An associative array where keys are sortable column names and values are arrays containing the column name.
     */
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

    /**
     * Get the hidden columns based on the user's settings.
     *
     * @return array The hidden columns.
     */
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
     * Prepares the items, column headers, and pagination for the table.
     *
     * @return self
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

    /**
     * Renders a column when no specific method exists for that column.
     *
     * When WP_List_Table attempts to render columns within single_row_columns(), it first checks for a column-specific method.
     * If none exists, it defaults to this method instead.
     *
     * @param array $item The data for the current row.
     * @param string $column_name The name of the column to render.
     * @return mixed The rendered column content.
     */
    public function column_default($item, $column_name)
    {
        if (!isset($item[$column_name])) {
            return "<pre>0</pre>";
        }
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

    /**
     * Returns the table content as a string when the object is treated as a string.
     *
     * Prepares the table items, starts output buffering, displays the table, and returns the buffered content.
     *
     * @return string The table content as a string.
     */
    public function __toString()
    {
        $this->prepare_items();
        ob_start();
        $this->display();
        return ob_get_clean();
    }

    /**
     * Sorts the table data based on the defined sorting callback function.
     *
     * @return self
     */
    protected function sort_table_data(): self
    {
        # sort the data
        usort($this->table_data, $this->get_sorting_callback());
        return $this;
    }

    /**
     * Returns the sorting callback function used to sort the table data.
     *
     * @return callable The sorting callback function.
     */
    protected function get_sorting_callback(): callable
    {
        return function ($a, $b) {
            $first_column_key = array_keys($this->columns)[0];

            // If no sort, default to first column
            $orderby = $_GET['orderby'] ?? $first_column_key;

            if (!isset($a[$orderby]) || !isset($b[$orderby])) {
                return 0;
            }

            // If no order, default to asc
            $order = $_GET['order'] ?? 'asc';

            // Determine sort order
            $result = strcmp($a[$orderby], $b[$orderby]);

            // Send final sort direction to usort
            return ($order === 'asc') ? $result : -$result;
        };
    }

    /**
     * Prepares the column headers for the table.
     *
     * @return self
     */
    protected function prepare_table_headers(): self
    {
        $primary = isset($this->primary_column) ? $this->primary_column : array_key_first($this->get_columns());
        $columns = $this->get_columns();
        $hidden_columns = $this->get_hidden_columns();
        $sortable_columns = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden_columns, $sortable_columns, $primary);
        return $this;
    }

    /**
     * Slices the table data to display only the items for the current page.
     *
     * @return self
     */
    protected function slice_table_data(): self
    {
        $items_per_page = $this->get_max_rows();
        $first_item = ($this->get_pagenum() - 1) * $items_per_page;

        $this->table_data = array_slice($this->table_data, $first_item, $items_per_page);
        return $this;
    }

    /**
     * Sets up pagination parameters for the table.
     *
     * @return static
     */
    protected function set_pagination(): static
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
     * Sets the table data based on search parameters, if provided.
     * If no search parameters are provided, retrieves all rows from the database table.
     *
     * @return static
     * @throws \Error If the database table is not set.
     */
    private function set_table_data(): static
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
}
