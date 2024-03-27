<?php

namespace WP_Framework\Model;

use WP_Framework\Database\Database;
use WP_Framework\Database\Table\BuildinTable;
use WP_Framework\Model\Type\BuildinType;

use function WP_CLI\Utils\pluralize;

/**
 * Handles the Built-in Models in WordPress.
 */
class BuildinModel extends AbstractModel
{
    /**
     * Array to store types. Null if built-in Model does not support types.
     *
     * @var array|null
     */
    private ?array $types = null;

    /**
     * BuildinModel constructor.
     *
     * @param string $name The internal name of the built-in model.
     * @param bool   $supports_meta Indicates if the model supports meta.
     * @param bool   $supports_types Indicates if the model supports types.
     */
    public function __construct(public string $name, bool $supports_meta = true, bool $supports_types = false)
    {
        if ($supports_meta) {
            $this->meta = [];
        }
        if ($supports_types) {
            $this->types = [];
        }
    }

    /**
     * Get an instance of the specified built-in object.
     *
     * @param int $object_id The ID of the object.
     *
     * @return object The built-in object instance.
     * @throws \Error If the model has no corresponding 'get_buildin_object'-method.
     */
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
     * Get a registered type by name.
     *
     * @param string $name The name of the type.
     *
     * @return BuildinType The AbstractType instance.
     * @throws \Error If the type is not registered.
     */
    public function get_type(string $name): BuildinType
    {
        if (!$this->types || !isset($this->types[$name])) {
            throw new \Error("A {$this->name}-type named '$name' is not registered");
        }
        return $this->types[$name];
    }

    /**
     * Register custom post types, taxonomies, etc. from JSON files.
     *
     * @link https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
     * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
     *
     * @param string $folder The folder containing JSON files with model type definitions.
     *
     * @return BuildinModel The BuildinModel instance.
     * @throws \Error If the specified folder does not exist or contains no JSON files.
     */
    public function register_types_from_folder(string $folder): BuildinModel
    {
        # check for json files.
        if (!$model_type_files = glob(THEME_DIR . "$folder/*.json")) {
            throw new \Error("'" . THEME_DIR . "$folder' does not exist or is contains no json");
        };

        foreach ($model_type_files as $model_type_file) {
            # skip example
            if (str_starts_with(basename($model_type_file), '.example')) {
                continue;
            }
            $this->register_type(
                BuildinType::create_from_json($model_type_file, $this->name)
            );
        }
        return $this;
    }

    /**
     * Register a type for built-in Model.
     *
     * @param BuildinType $type The BuildinType instance to register.
     *
     * @return BuildinModel The modified BuildinModel instance.
     * @throws \Error If the model does not support types.
     */
    public function register_type(BuildinType ...$type): BuildinModel
    {
        foreach ($type as $type) {
            if ($this->types === null) {
                throw new \Error("This Model '$this->name' does not support types.");
            }
            $this
                ->hook_type_register_actions($type)
                ->add_type($type);
        }
        return $this;
    }

    /**
     * Unregister a type of a built-in Model.
     *
     * @param BuildinType $type The BuildinType instance to unregister.
     *
     * @return BuildinModel The modified BuildinModel instance.
     */
    public function unregister_type(BuildinType $type): BuildinModel
    {
        return $this
            ->hook_type_unregister_actions($type)
            ->remove_type($type);
    }

    /**
     * Hook actions for type registration.
     *
     * @param BuildinType $type The type to register.
     *
     * @return BuildinModel The modified BuildinModel instance.
     */
    protected function hook_type_register_actions(BuildinType $type): BuildinModel
    {
        add_action('init', function () use ($type) {
            if ($this->name == 'term') {
                register_taxonomy(
                    $type->name,
                    $type->get_attribute('object_type'),
                    $type->get_attributes()
                );
            } else {

                call_user_func(
                    "register_{$this->name}_type",
                    $type->name,
                    $type->get_attributes()
                );
            }
        });
        return $this;
    }

    /**
     * Hook actions for type unregistration.
     *
     * @param BuildinType $type The type to unregister.
     *
     * @return BuildinModel The modified BuildinModel instance.
     */
    protected function hook_type_unregister_actions(BuildinType $type): BuildinModel
    {
        add_action('init', function () use ($type) {
            if ($this->name == 'term') {
                unregister_taxonomy($type->name);
            } else {
                call_user_func("unregister_{$this->name}_type", $type->name);
            }
        });
        return $this;
    }

    /**
     * Register a built-in type for built-in Model.
     *
     * @param BuildinType $type The BuildinType instance to register.
     *
     * @return BuildinModel The modified BuildinModel instance.
     * @throws \Error If the model does not support types.
     */
    public function register_buildin_type(BuildinType ...$type): BuildinModel
    {
        foreach ($type as $type) {
            if ($this->types === null) {
                throw new \Error("This Model '$this->name' does not support types.");
            }
            $this
                ->add_type($type);
        }
        return $this;
    }

    /**
     * Unregister a build-in type of a build-in model.
     *
     * @param BuildinType $type The BuildinType instance to unregister.
     *
     * @return BuildinModel The modified BuildinModel instance.
     */
    public function unregister_buildin_type(BuildinType $type): BuildinModel
    {
        return $this
            ->remove_type($type);
    }

    /**
     * Add a type object to the list of registered type objects.
     *
     * @param BuildinType $meta The meta object.
     * @return BuildinModel The modified AbstractModel instance.
     */
    protected function add_type(BuildinType $type): BuildinModel
    {
        $this->types[$type->name] = $type;
        return $this;
    }

    /**
     * Remove a type object from the list of registered type objects.
     *
     * @param BuildinType $meta The meta object.
     * @return BuildinModel The modified AbstractModel instance.
     */
    protected function remove_type(string|BuildinType $type): BuildinModel
    {
        if (!is_string($type)) {
            $type = $type->name;
        }
        unset($this->types[$type]);
        return $this;
    }

    /**
     * Get the database table name for the model.
     *
     * @return string The database table name.
     */
    public function get_table_name(): string
    {
        return "wp_{$this->name}s";
    }
}
