<?php

namespace WP_Framework;

use WP_Framework\Admin\Panel\AbstractPanel;
use WP_Framework\Admin\Role\Role;
use WP_Framework\CLI\CLI;
use WP_Framework\Database\Database;
use WP_Framework\Database\Table\BuildinTable;
use WP_Framework\Database\Table\CustomTable;
use WP_Framework\Debug\Debug;
use WP_Framework\Model\AbstractModel;
use WP_Framework\Model\BuildinModel;
use WP_Framework\Model\CustomModel;

class Framework
{
    private static $instance;

    public readonly Database $database;
    public readonly false|CLI $cli;

    protected array $models = [];
    protected array $admin_panels = [];

    # Private constructor to prevent direct instantiation
    private function __construct()
    {
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance
                ->init()
                ->register_buildin_models();
        }
        return self::$instance;
    }

    private function init(): self
    {
        $this->register_class_autoload();

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

        # wrap this into WB_DEBUG const
        if (true) {
            Debug::init();
        }
        return $this;
    }

    public function register_panel(AbstractPanel ...$panel): self
    {
        foreach ($panel as $panel) {
            $this
                ->add_panel($panel)
                ->hook_panel_actions($panel);
        }
        return $this;
    }

    public function unregister_panel(AbstractPanel $panel): self
    {
        return $this
            ->remove_panel($panel)
            ->unhook_panel_actions($panel);
    }

    private function hook_panel_actions(AbstractPanel $panel): self
    {
        # add panel to menu
        add_action($panel->get_menu_hook(), $panel->get_menu_callback());
        return $this;
    }

    private function unhook_panel_actions(AbstractPanel $panel): self
    {
        # remove panel from menu
        remove_action($panel->get_menu_hook(), $panel->get_menu_callback());
        return $this;
    }

    private function add_panel(AbstractPanel $panel): self
    {
        $this->admin_panels[$panel->name] = $panel;
        return $this;
    }

    private function remove_panel(string|AbstractPanel $panel): self
    {
        if (!is_string($panel)) {
            $panel = $panel->name;
        }
        unset($this->admin_panels[$panel]);
        return $this;
    }

    public function get_panel(string $admin_panel_name): AbstractPanel
    {
        if (!isset($this->admin_panels[$admin_panel_name])) {
            throw new \Error("An admin-panel named '$admin_panel_name' is not registered");
        }
        return $this->admin_panels[$admin_panel_name];
    }

    public function get_role(string $name)
    {
        return Role::get_role($name);
    }

    /**
     * Method register_model
     *
     * @param AbstractModel $model the name of a model-class representing a build-in Model or a DataModel-Instance to create a new model.
     *
     * @return self
     */
    public function register_model(AbstractModel ...$model): self
    {
        foreach ($model as $model) {
            # register data table of the custom model for interaction
            if ($model instanceof CustomModel) {
                $table = new CustomTable($model->get_table_name());
            } elseif ($model instanceof BuildinModel) {
                $table = new BuildinTable($model->get_table_name());
            } else {
                throw new \Error('no table implemented for this model class.');
            }
            $this->database->register_table($table);

            # add any model to the list
            $this->models[$model->name] = $model;
        }
        return $this;
    }

    private function register_buildin_models(): self
    {
        $comment_model = new BuildinModel('comment');

        $user_model = new BuildinModel('user');

        $post_model = new BuildinModel('post', supports_types: true);
        $post_model->register_types_from_folder('post-types');

        $term_model = new BuildinModel('term', supports_types: true);
        $term_model->register_types_from_folder('taxonomies');

        $this->register_model($comment_model, $user_model, $post_model, $term_model);

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

    private function register_class_autoload(): self
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
