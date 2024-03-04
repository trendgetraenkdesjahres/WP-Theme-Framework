<?php

namespace WP_Framework\Database;

use WP_Framework\Model\DataModel;

class QueryString
{
    /**
     * Generates the migration SQL query to create a query which represents the DataModel with it's added properties.
     *
     * @return string The SQL query for migration.
     */
    public static function create_table(DataModel $model): string
    {
        $meta_table_name = Database::$table_prefix . $model->name . "s";

        # go
        $query = "CREATE TABLE $meta_table_name (";

        # add primary key
        $query .= "{$model->name}_id bigint(20) unsigned NOT NULL auto_increment, PRIMARY KEY ({$model->name}_id),";

        # iterate through the properties and add
        foreach ($model->properties as $propery) {
            $query .= "{$propery['key']} {$propery['type']} {$propery['nullable']} {$propery['default_value']},";
        }

        # add type...
        if ($model->has_types) {
            array_push($model->composite_index_properties, 'type');
            $query .= "{$model->name}_type varchar(20) NOT NULL default '{$model->name}',";
        }

        # add hirarchie key
        if ($model->is_hierarchical) {
            $query .= "{$model->name}_parent bigint(20) unsigned NOT NULL default '0', KEY {$model->name}_parent ({$model->name}_parent),";
        }

        # add owner (like 'author' or 'user')
        if (is_string($model->owner_type)) {
            $query .= "{$model->name}_{$model->owner_type} bigint(20) unsigned NOT NULL default '0', KEY {$model->name}_{$model->owner_type} ({$model->name}_{$model->owner_type}),";
        }

        # add composite index
        if ($model->composite_index_properties) {
            $query .= self::create_composite_index($model->composite_index_properties, $model->name);
        }

        # remove ','
        $query = rtrim($query, ',');

        # done
        $query .= ") " . Database::$charset_collate . ";\n";

        return $query;
    }

    public static function create_meta_table(string $model_name): string
    {
        $max_index_length = 19; # is defined in wp_get_db_schema
        $meta_table_name = Database::$table_prefix . $model_name . "meta";

        # go
        $query = "CREATE TABLE $meta_table_name (";
        $query .= "meta_id bigint(20) unsigned NOT NULL auto_increment,";
        $query .= "{$model_name}_id bigint(20) unsigned NOT NULL default '0',";
        $query .= "meta_key varchar(255) default NULL,";
        $query .= "meta_value longtext,";
        $query .= "PRIMARY KEY (meta_id),";
        $query .= "KEY {$model_name}_id ({$model_name}_id),";
        $query .= "KEY meta_key (meta_key($max_index_length))";
        $query .= ") " . Database::$charset_collate . ";\n";
        return $query;
    }

    private static function create_composite_index(array $properties, string $model_name): string
    {
        $key_name = implode('_', $properties);
        $key_list = '';
        foreach ($properties as $key) {
            $key_list .= "{$model_name}_{$key},";
        }
        $key_list = rtrim($key_list, ',');
        return "KEY {$key_name} ($key_list),";
    }
}
