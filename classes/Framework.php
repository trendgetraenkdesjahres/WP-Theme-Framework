<?php

namespace WP_Framework;

use WP_Framework\AdminPanel\AbstractPanel;
use WP_Framework\AdminPanel\ModelPanel;
use WP_Framework\CLI\CLI;
use WP_Framework\Database\Database;
use WP_Framework\Model\AbstractModel;
use WP_Framework\Model\BuildinModel;
use WP_Framework\Model\CustomModel;

class Framework
{
    private static $instance;

    public readonly Database $database;
    public readonly false|CLI $cli;

    protected array $models = [];
    protected array $panels = [];

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

    public function register_panel(AbstractPanel $panel): Framework
    {
        $this->panels[$panel->name] = $panel->register();
        return $this;
    }

    public function unregister_panel(string $admin_panel_name): Framework
    {
        $this->panels[$admin_panel_name]->unregister();
        unset($this->panels[$admin_panel_name]);
        return $this;
    }

    public function is_registered_panel(string $admin_panel_name): bool
    {
        return isset($this->panels[$admin_panel_name]);
    }

    public function get_panel(string $admin_panel_name): AbstractPanel
    {
        if (!isset($this->panels[$admin_panel_name])) {
            throw new \Error("An admin-panel named '$admin_panel_name' is not registered");
        }
        return $this->panels[$admin_panel_name];
    }

    /**
     * Method register_model
     *
     * @param AbstractModel $model the name of a model-class representing a build-in Model or a DataModel-Instance to create a new model.
     *
     * @return Framework
     */
    public function register_model(AbstractModel ...$model): Framework
    {
        foreach ($model as $model) {
            $model->register_types_from_folder();
            $this->models[$model->name] = $model;
        }
        return $this;
    }

    private function register_buildin_models(): Framework
    {
        $this->register_model(new BuildinModel('comment'));
        $this->register_model(new BuildinModel('post', 'PostType', 'post-types', true));
        $this->register_model(new BuildinModel('term', 'Taxonomy', 'taxonomies', true));
        $this->register_model(new BuildinModel('user'));
        return $this;
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
            if (!$model instanceof BuildinModel) {
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
                require FRAMEWORK_DIR . "classes/$class_name.php";
            }
        });
        return $this;
    }
}
