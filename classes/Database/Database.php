<?php

namespace WP_Framework\Database;

use WP_Framework\Database\SQL\QueryString;
use WP_Framework\Database\Table\AbstractTable;
use WP_Framework\Model\CustomModel;

class Database
{
    private static $instance;
    public static string $charset_collate;
    public static string $table_prefix = 'fw';
    private array $tables = [];

    # Private constructor to prevent direct instantiation
    private function __construct()
    {
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            global $wpdb;
            self::$instance::$charset_collate = $wpdb->get_charset_collate();
        }
        return self::$instance;
    }

    # is not checking for safe execution!! but's ok. the function calling this method should prepare the query...
    public static function get_result(string $sql_query): array
    {
        global $wpdb;
        $result_array = $wpdb->get_results(
            query: $sql_query,
            output: ARRAY_A
        );
        return $result_array;
    }

    public static function get_table(string $name): AbstractTable
    {
        return self::get_instance()->tables[$name];
    }

    public function register_table(AbstractTable ...$table): Database
    {
        foreach ($table as $table) {
            $this->tables[$table->name] = $table;
        }
        return self::$instance;
    }

    public function unregister_table(string|AbstractTable $table): Database
    {
        if (!is_string($table)) {
            $table = $table->name;
        }
        unset($this->tables[$table]);
        return $this;
    }

    public static function create_model_table_name(string $model_name): string
    {
        return self::$table_prefix . "_" . $model_name . "s";
    }

    public static function create_model_meta_table_name(string $model_name): string
    {
        return self::$table_prefix . "_" . $model_name . "meta";
    }

    /**
     * Performs the migration of the registred DataModel.
     * Creates tables in the database.
     * WILL DEFINETLY BREAK STUFF
     *
     * @careful BE CAREFUL
     * @return array sql-results
     */
    public static function create_model_tables(CustomModel ...$models): array
    {
        $query = '';
        foreach ($models as $model) {
            # get the model's table query
            $query .= QueryString::create_table($model);

            # get the model's meta-table query
            if ($model->meta) {
                $query .= QueryString::create_meta_table($model);
            }
        }
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        return dbDelta($query);
    }

    /**
     * Drops tables that belonged to framework but are not registred now.
     * the tables are identified by framework prefix.
     * WILL DEFINETLY BREAK STUFF
     *
     * @careful BE CAREFUL
     * @return array sql-results
     */
    public static function drop_orphaned_tables(array $registred_models): bool
    {
        global $wpdb;

        # get current tables
        $current_tables = array_map('current', $wpdb->get_results("SHOW TABLES", ARRAY_N));

        # reduce to tables with framework-prefix
        $current_tables = array_filter($current_tables, function ($element) {
            return str_starts_with($element, self::$table_prefix . "");
        });

        # create array of tables to keep
        $keep_tables = [];
        foreach ($registred_models as $model) {
            if (!$model instanceof CustomModel) {
                throw new \Error("\$registred_models can only contain DataModel objects.");
            }
            array_push($keep_tables, $model->table_name);
        }

        # remove keep_tables from list of all current tables to have an array of tables to be dropped.
        $drop_tables = array_diff($current_tables, $keep_tables);

        # drop them
        foreach ($drop_tables as $drop_table) {
            if (!$wpdb->query("DROP TABLE IF EXISTS {$drop_table}")) {
                throw new \Error("Query 'DROP TABLE IF EXISTS {$drop_table}' did not work.");
            }
        }
        return true;
    }
}
