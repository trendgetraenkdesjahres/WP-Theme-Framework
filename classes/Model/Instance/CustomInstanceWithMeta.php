<?php

namespace WP_Framework\Model\Instance;

use WP_Framework\Database\Database;

class CustomInstanceWithMeta extends CustomInstance
{
    public function get_meta(?string $key = null): mixed
    {
        $meta_table_name = Database::create_model_meta_table_name($this->model_name);

        # try from cache
        $meta = self::get_meta_from_cache($meta_table_name, $this->id);
        if (is_array($meta)) {
            return self::get_meta_value($meta, $key);
        }

        # try to update meta-cache for this instance with data from db with buildin worpress func.
        $meta = self::update_meta_cache($this->name, $this->id);
        if (is_array($meta)) {
            return self::get_meta_value($meta, $key);
        }

        # null on fail
        return null;
    }

    private static function get_meta_from_cache(string $meta_table_name, int $id): ?array
    {
        $meta_value = wp_cache_get(
            key: $id,
            group: $meta_table_name,
            found: $success = false
        );
        if (!$success) {
            return null;
        }
        return $meta_value;
    }

    private static function update_meta_cache(string $model_name, int $id): false|array
    {
        return update_meta_cache(
            meta_type: $model_name,
            object_ids: $id
        );
    }

    private static function get_meta_value(array $meta, ?string $key): mixed
    {
        if (!$key) {
            return $meta;
        }
        if (!isset($meta[$key])) {
            return null;
        }
        return $meta[$key];
    }
}
