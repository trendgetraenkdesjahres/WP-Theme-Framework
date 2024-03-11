<?php

namespace WP_Framework\Model\Type;

use WP_Framework\Utils\JsonFile;

/**
 * Handles a (custom) buildin model type (eg. 'post-type' of posts or 'taxonomy' of terms) in WordPress.
 */
class BuildinType extends AbstractType
{

    public static function create_from_json(string $path): AbstractType
    {
        $name = basename($path, '.json');
        $class = get_called_class();
        return new $class(
            name: $name,
            props: JsonFile::to_array($path)
        );
    }

    /**
     * Hide the post type from the UI and search results.
     *
     * @return PostType The modified PostType instance.
     */
    public function hide(): BuildinType
    {
        add_filter("register_{$this->name}_post_type_args", function () {
            return [
                'public' => false,
                'show_ui' => false,
                'show_in_menu' => false,
                'show_in_admin_bar' => false,
                'show_in_nav_menus' => false,
                'can_export' => false,
                'has_archive' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_in_rest' => false
            ];
        });
        return $this;
    }
}
