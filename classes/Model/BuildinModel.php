<?php

namespace WP_Framework\Model;

use WP_Framework\Model\Type\AbstractType;
use WP_Framework\Model\Type\BuildinType;

/**
 * Handles the BuildinModels in WordPress.
 */
class BuildinModel extends AbstractModel
{
    /**
     * Array to store types in. Null if build-in Model does not support types.
     */
    public ?array $types = null;

    public function __construct(public string $name, bool $has_meta = false)
    {
        if ($has_meta) {
            $this->meta = [];
        }
    }

    public function get_instance(int $object_id): object
    {
        switch ($this->name) {
            case 'post':
                return \WP_Post::get_instance($object_id);
            case 'comment':
                return \WP_Comment::get_instance($object_id);
            case 'term':
                return \WP_Term::get_instance($object_id);
            case 'user':
                return new \WP_User($object_id);
            default:
                throw new \Error("'$this->name' has no 'get_buildin_object'-method.");
        }
    }

    /**
     * Register Custom Post Types, Taxonomies from json files...
     *
     * Adds the custom Post Types, taxonomies ..., define in the post-types/ folder.
     * put json of the args there, with properties as in the link
     * the slug for the post type goes by it's file name
     * @link https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
     * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
     *
     * @param string $type_class (like "PostType", "Taxonomy", ...)
     * @param string $json_folder
     *
     * @return AbstractModel
     */
    public function register_types_from_folder(string $folder): AbstractModel
    {
        # check for json files.
        if (!$model_type_files = glob(THEME_DIR . "$folder/*.json")) {
            throw new \Error("'" . THEME_DIR . "$folder' does not exist or is contains no json");
        };

        foreach ($model_type_files as $model_type_file) {
            if (str_starts_with(
                haystack: basename($model_type_file),
                needle: '.example'
            )) continue;
            $this->register_type(
                BuildinType::create_from_json($model_type_file)
            );
        }
        return $this;
    }

    public function register_type(BuildinType $type): BuildinModel
    {
        if ($this->types === null) {
            throw new \Error("This Model '$this->name' does not support types.");
        }
        add_action('init', function () use ($type) {
            call_user_func(
                "register_{$this->name}_type",
                $type->name,
                $type->props
            );
        });
        $this->types[$type->name] = $type;
        return $this;
    }

    public function unregister_type(string|BuildinType $type): BuildinModel
    {
        if (!is_string($type)) {
            $type = $type->name;
        }
        add_action('init', function () use ($type) {
            call_user_func(
                "unregister_{$this->name}_type",
                $type
            );
        });
        unset($this->types[$type]);
        return $this;
    }

    public function get_type(string $name): AbstractType
    {
        if (!$this->types || !isset($this->types[$name])) {
            throw new \Error("A {$this->name}-type named '$name' is not registered");
        }
        return $this->types[$name];
    }
}
