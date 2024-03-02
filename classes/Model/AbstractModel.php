<?php

namespace WP_Framework\Model;

use WP_Framework\Model\Type\AbstractType;
use WP_Framework\Model\Type\TypeInterface;

/**
 * Interface for object type (post, term, user, comment, ...) fields in WordPress.
 */
interface ObjectInterface
{
    public function get_buildin_object(int $object_id): object;
}


/**
 * Handles a object ('post', 'term', 'user', ...) in WordPress.
 */
abstract class AbstractModel
{
    protected string $name = 'abstract ( :-0 ) abstract';
    protected string $type_class = '';
    protected string $types_json_folder = '';

    public function register_type(TypeInterface $type): AbstractModel
    {
        # check if the types name matches the expectation.
        if (!$type->name === $this->type_class) {
            throw new \Error("'$type->name' is not a type for '$this->name'");
        }
        $type->register();
        return $this;
    }

    /**
     * Method register_types_from_folder
     *
     * @param string $type_class (like "PostType", "Taxonomy", ...)
     * @param string $json_folder
     *
     * @return void
     */
    public function register_types_from_folder()
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
            $model_type = $model_type_name::create_from_json($model_type_file);
            $model_type->register();
        }
        return $this;
    }

    public function unregister_type(TypeInterface $type): AbstractModel
    {
        $type->unregister();
        return $this;
    }

    public function get_type(string $type): AbstractType
    {
        $model_type_name = "WP_Framework\Model\Type\\" . $this->type_class;
        return new $model_type_name($type);
    }
}
