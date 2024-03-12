<?php

namespace WP_Framework\Model\Type;

use WP_Framework\Database\SQLSyntax;
use WP_Framework\Utils\JsonFile;

/**
 * Handles a built-in model type (e.g., 'post-type' of posts or 'taxonomy' of terms) in WordPress.
 */
class BuildinType extends AbstractType
{
    /**
     * BuildinType constructor.
     *
     * @param string $name   The internal name of the type.
     * @param array  $attributes  Attributes in WP Style for the type.
     *
     * @throws \Error If the provided type name is not a valid type name.
     */
    public function __construct(string $name, array $attributes = [])
    {
        if (!SQLSyntax::is_field_name($name)) {
            throw new \Error("'$name' is not a valid type-name");
        }
        $this->name = $name;
        $this->attributes = $attributes;
    }

    /**
     * Creates a BuildinType instance from a JSON file.
     *
     * @param string $path The path to the JSON file defining the type.
     *
     * @return BuildinType The created BuildinType instance.
     */
    public static function create_from_json(string $path): BuildinType
    {
        $name = basename($path, '.json');
        return new BuildinType(
            name: $name,
            attributes: JsonFile::to_array($path)
        );
    }

    /**
     * Hide the post type from the UI and search results.
     *
     * @return BuildinType The modified BuildinType instance.
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
