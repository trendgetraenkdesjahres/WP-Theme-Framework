<?php

namespace WP_Framework\AdminPanel;

use WP_List_Table;

class Table extends WP_List_Table
{
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

    public function get_columns(): array
    {
        return [
            'column1' => 'Column 1',
            'column2' => 'Column 2',
            // Add more columns as needed
        ];
    }

    /**
     * Prepare items for the table.
     */
    public function prepare_items()
    {
        // Implement data preparation logic here
        $data = []; // Your data array

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $data;

        // Set pagination args
        $per_page = $this->get_items_per_page('items_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    /**
     * Display the table.
     */
    public function display()
    {
        // Output table content
        echo '<div class="wrap">';
        echo '<h2>My List Table</h2>';
        $this->display_tablenav('top');
        $this->screen->render_screen_reader_content('heading');
        echo '<form method="post">';
        $this->search_box('Search', 'search_id');
        $this->display();
        echo '</form>';
        echo '</div>';
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
    }
}
