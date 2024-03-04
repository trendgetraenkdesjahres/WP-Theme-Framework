<?php

namespace WP_Framework;

use WP_Framework\AdminPanel\Screen;
use WP_Framework\CLI\CLI;
use WP_Framework\Database\Database;
use WP_Framework\Model\AbstractModel;
use WP_Framework\Model\DataModel;

class Framework
{
    private static $instance;

    public readonly Database $database;
    public readonly false|CLI $cli;

    protected array $models = [];
    protected array $screens = [];

    # Private constructor to prevent direct instantiation
    private function __construct()
    {
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance
                ->register_class_autoload()
                ->init()
                ->register_buildin_models();
        }
        return self::$instance;
    }

    private function init(): Framework
    {
        /**
         * Adds the Database singleton.
         */
        $this->database = Database::get_instance();

        /**
         * Adds the Command Line Interface singleton.
         */
        $this->cli = CLI::get_instance();

        /**
         * Registers Commands to the Interface.
         */
        if ($this->cli) {
            $this->cli->register_command('migrate_all', [$this->database, 'migrate_registered_models']);
        }
        return $this;
    }

    public function register_screen(Screen $admin_screen): Framework
    {
        $this->screens[$admin_screen->name] = $admin_screen;
        return $this;
    }

    public function unregister_screen(string $admin_screen_name): Framework
    {
        unset($this->screens[$admin_screen_name]);
        return $this;
    }

    public function is_registered_screen(string $admin_screen_name): bool
    {
        return isset($this->screens[$admin_screen_name]);
    }

    public function get_screen(string $admin_screen_name): Screen
    {
        if (!isset($this->screens[$admin_screen_name])) {
            throw new \Error("An admin-screen named '$admin_screen_name' is not registered");
        }
        return $this->screens[$admin_screen_name];
    }

    /**
     * Method register_model
     *
     * @param string $model the name of a model-class representing a build-in Model or a DataModel-Instance to create a new model.
     *
     * @return Framework
     */
    public function register_model(string|DataModel $model): Framework
    {
        #  register a custom Model
        if (!$model instanceof DataModel) {
            $model = self::create_buildin_model($model);
        }
        $this->models[$model->name] = $model;
        return $this;
    }

    private function register_buildin_models(): Framework
    {
        $this->register_model('PostModel');
        $this->register_model('TermModel');
        $this->register_model('UserModel');
        $this->register_model('CommentModel');

        /**
         * Custom Post Types
         * Adds the custom Post Types, define in the post-types/ folder.
         * put json of the args there, with properties as in the link
         * the slug for the post type goes by it's file name
         * @link https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/
         * */
        $this->models['post']->register_types_from_folder();

        /**
         * Custom Taxonomies
         * Adds the custom taxonomies, define in the taxonomies/ folder.
         * put json there, with properties as in the link
         * the slug for the taxonomy type goes by it's file name
         * @link https://developer.wordpress.org/reference/functions/register_taxonomy/
         * */
        $this->models['term']->register_types_from_folder();

        return $this;
    }

    # maybe move this function to a better location??
    private static function create_buildin_model(string $model_name): AbstractModel
    {
        # Can't register DataModel statically. registration only possible with an DataModel instance.
        if ($model_name == 'DataModel') {
            throw new \Error("DataModel needs to be implemented by an actual Object of Datamodel. Not by it's class");
        }
        # check the model string, if it's actually a Model implementation.
        $full_model_name = "WP_Framework\Model\\" . $model_name;
        if (!is_subclass_of($full_model_name, 'WP_Framework\Model\ModelInterface')) {
            throw new \Error("'$model_name' is not implementing WP_Framework\Model\ModelInterface");
        }
        return new $full_model_name();
    }

    /**
     * Method get_model
     *
     * @param string $name [explicite description]
     *
     * @return AbstractModel
     */
    public function get_model(string $model_name): AbstractModel
    {
        if (!isset($this->models[$model_name])) {
            throw new \Error("A model named '$model_name' is not registered");
        }
        return $this->models[$model_name];
    }

    public function get_models(bool $include_build_ins = false): array
    {
        if ($include_build_ins) {
            return $this->models;
        }

        $models = [];
        foreach ($this->models as $model) {
            if ($model instanceof DataModel) {
                array_push($models, $model);
            }
        }
        return $models;
    }

    private function register_class_autoload(): Framework
    {
        /**
         * Register the function to autoload classes from the 'WP_Framework' namespace
         */
        spl_autoload_register(function ($class) {
            $class_name_array = explode("\\", $class);
            if (array_shift($class_name_array) == 'WP_Framework') {
                $class_name = implode("/", $class_name_array);
                include FRAMEWORK_DIR . "classes/$class_name.php";
            }
        });
        return $this;
    }
}
