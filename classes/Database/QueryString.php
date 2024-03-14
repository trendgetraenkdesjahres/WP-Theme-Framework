<?php

namespace WP_Framework\Database;

use WP_Framework\Model\CustomModel;

class QueryString
{
    /**
     * Generates the migration SQL query to create a query which represents the DataModel with it's added properties.
     *
     * @return string The SQL query for migration.
     */
    public static function create_table(CustomModel $model): string
    {
        $meta_table_name = Database::craete_model_table_name($model->name);

        # go
        $query = "CREATE TABLE $meta_table_name (";

        # add primary key
        $query .= "{$model->name}_id bigint(20) unsigned NOT NULL auto_increment,PRIMARY KEY ({$model->name}_id),";

        # iterate through the properties and add
        foreach ($model->properties as $property) {
            $query .= "{$model->name}_{$property->key} {$property->sql_type} {$property->nullable} {$property->default_value},";
        }

        # add hirarchie key
        if ($model->get_attribute('hierarchical', false)) {
            $query .= "{$model->name}_parent bigint(20) unsigned NOT NULL default '0',KEY {$model->name}_parent ({$model->name}_parent),";
        }

        # add owner (like 'author' or 'user')
        if (is_string($model->owner_type)) {
            $query .= "{$model->name}_{$model->owner_type} bigint(20) unsigned NOT NULL default '0',
            KEY {$model->name}_{$model->owner_type} ({$model->name}_{$model->owner_type}),";
        }

        # add composite index of all indexable properties
        $query .= self::create_composite_index($model->get_properties(just_indexables: true), $model->name);

        # remove ','
        $query = rtrim($query, ',');

        # done
        $query .= ") " . Database::$charset_collate . ";\n";

        return $query;
    }

    public static function create_meta_table(CustomModel $model): string
    {
        $max_index_length = 19; # is defined in wp_get_db_schema
        $meta_table_name = Database::craete_model_meta_table_name($model->name);

        # go
        $query = "CREATE TABLE $meta_table_name (";
        $query .= "meta_id bigint(20) unsigned NOT NULL auto_increment,";
        $query .= "{$model->name}_id bigint(20) unsigned NOT NULL default '0',";
        $query .= "meta_key varchar(255) default NULL,";
        $query .= "meta_value longtext,";
        $query .= "PRIMARY KEY (meta_id),";
        $query .= "KEY {$model->name}_id ({$model->name}_id),";
        $query .= "KEY meta_key (meta_key($max_index_length))";
        $query .= ") " . Database::$charset_collate . ";\n";
        return $query;
    }

    private static function create_composite_index(array $properties, string $model_name): string
    {
        if (!$properties) {
            return '';
        }
        $key_name = implode('_', $properties);
        $key_list = '';
        foreach ($properties as $property) {
            $key_list .= "{$model_name}_{$property->key},";
        }
        $key_list = rtrim($key_list, ',');
        return "KEY {$key_name} ($key_list),";
    }
}
