<?php

namespace WP_Framework\Model;

use WP_Framework\Model\Meta\MetaInterface;
use WP_Framework\Model\Type\AbstractType;
use WP_Framework\Model\Type\TypeInterface;

/**
 * Handles a object ('post', 'term', 'user', ...) in WordPress.
 */
abstract class AbstractModel
{
    /**
     * The internal name of this model.
     */
    public string $name;

    /**
     * This model supports sub types.
     */
    public bool $has_types = false;

    /**
     * Array to store types in.
     */
    protected array $types = [];

    /**
     * The type-class name for this model. null = disable types for this model.
     */
    public ?string $type_class = null;

    /**
     * The folder with json-files. null =  disabled types for this model.
     */
    public ?string $types_json_folder = null;

    /**
     * This model supports meta values for it's instances.
     */
    public bool $has_meta = true;

    /**
     * Array to store meta fields in.
     */
    protected array $meta = [];

    public function register_type(TypeInterface $type): AbstractModel
    {
        # check if the model accepts types
        if (!$this->type_class) {
            throw new \Error("Model '$this->name' has no Type class");
        }
        # check if the types name matches the expectation.
        if (!$type->name === $this->type_class) {
            throw new \Error("'$type->name' is not a type for '$this->name'");
        }
        $this->types[$type->name] = $type->register();
        return $this;
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
    public function register_types_from_folder(): AbstractModel
    {
        # disable this function by an empty types-json-folder
        if (!$this->types_json_folder) {
            return $this;
        }

        # check for json files.
        if (!$model_type_files = glob(THEME_DIR . "$this->types_json_folder/*.json")) {
            throw new \Error("'" . THEME_DIR . "$this->types_json_folder' does not exist or is contains no json");
        };

        # register the Model Types (ignore '.example')
        $model_type_name = "WP_Framework\Model\Type\\" . $this->type_class;
        foreach ($model_type_files as $model_type_file) {
            if (str_starts_with(
                haystack: basename($model_type_file),
                needle: '.example'
            )) continue;
            $this->register_type(
                $model_type_name::create_from_json($model_type_file)
            );
        }
        return $this;
    }

    public function unregister_type(TypeInterface $type): AbstractModel
    {
        $type->unregister();
        unset($this->types[$type->name]);
        return $this;
    }

    public function get_type(string $name): AbstractType
    {
        if (!isset($this->types[$name])) {
            throw new \Error("A {$this->name}-type named '$name' is not registered");
        }
        return $this->types[$name];
    }

    /**
     * Register custom meta fields for this model type.
     *
     * @param MetaInterface $meta The WP_Framework Meta object to register.
     * @return AbstractModel The modified AbstractType instance.
     */
    public function register_meta(MetaInterface $meta): AbstractModel
    {
        $this->meta[$meta->name] = $meta->register($this->name);
        return $this;
    }

    /**
     * Unregister custom meta fields for this model type.
     *
     * @param string|MetaInterface $meta The WP_Framework Meta object or a string (builtin Meta) to unregister.
     * @return AbstractModel The modified AbstractType instance.
     */
    public function unregister_meta(string|MetaInterface $meta): AbstractModel
    {
        if (is_string($meta)) {
            unregister_meta_key($this->name, $meta, $this->name);
            return $this;
        }
        $meta->unregister($this->name);
        unset($this->meta[$meta->name]);
        return $this;
    }

    /**
     * Get a meta object of this model type.
     *
     * @param string $name the (serialized) name of the meta.
     * @return AbstractType The modified AbstractType instance.
     */
    public function get_meta(string $name): AbstractModel
    {
        if (!isset($this->meta[$name])) {
            throw new \Error("A {$this->name}-meta named '$name' is not registered");
        }
        return $this->meta[$name];
    }
}
