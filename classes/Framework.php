<?php

namespace WP_Framework;

use WP_Framework\Admin\Option\Option;
use WP_Framework\Admin\Option\OptionsGroup;
use WP_Framework\Admin\Screen\AbstractScreen;
use WP_Framework\Admin\Role\Role;
use WP_Framework\Admin\Screen\SettingsPage\BuildinSettingsScreen;
use WP_Framework\Admin\Screen\SettingsPage\PrimarySettingsScreen;
use WP_Framework\Admin\Screen\SettingsPage\SettingsScreen as SettingsScreen;
use WP_Framework\CLI\CLI;
use WP_Framework\Database\Database;
use WP_Framework\Database\Table\BuildinTable;
use WP_Framework\Database\Table\CustomTable;
use WP_Framework\Debug\Debug;
use WP_Framework\Model\AbstractModel;
use WP_Framework\Model\BuildinModel;
use WP_Framework\Model\CustomModel;
use WP_Framework\Model\Type\BuildinType;

/**
 * Class Framework
 *
 * Represents the WP_Framework, providing functionalities for managing admin screens, models, and more.
 */
class Framework
{
    /**
     * The singleton instance of the WP_Framework class.
     */
    private static $instance;

    /**
     * The Database instance.
     */
    public readonly Database $database;

    /**
     * The Command Line Interface instance, if available.
     */
    public readonly false|CLI $cli;

    /**
     * An array containing registered models.
     */
    protected array $models = [];

    /**
     * An array containing registered admin screens.
     */
    protected array $admin_screens = [];

    /**
     * An array containing option groups.
     */
    protected array $option_groups = [];

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Retrieves the singleton instance of the Framework class.
     *
     * @return self The singleton instance of the Framework class.
     */
    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance
                ->init()
                ->register_buildin_models()
                ->register_admin_option_screen();
        }
        return self::$instance;
    }

    /**
     * Initializes the Framework instance by registering class autoload, initializing the Database and CLI singletons,
     * and registering commands to the CLI interface.
     *
     * @return self The current instance of the Framework class.
     */
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

    /**
     * Retrieves the option group by name.
     *
     * @param string $name The name of the option group to retrieve.
     * @return OptionsGroup The option group array.
     * @throws \Error If the option group with the specified name does not exist.
     */
    public function get_option_group(string $name, bool $throw_errors = true): OptionsGroup
    {
        if (!$this->has_collection_with_member('option_groups', $name, $throw_errors)) {
            $this->option_groups[$name] = new OptionsGroup($name);
        }
        return $this->option_groups[$name];
    }

    /**
     * Retrieves an option by name and group.
     *
     * @param string $name The name of the option group to retrieve. If it contains a '/', the first part will be interpreted as group-name and the second as option-name.
     * @param null|OptionsGroup $group The group of the option (keep empty for standart wp options).
     * @return Option The option group array.
     * @throws \Error If the option group with the specified name does not exist.
     */
    public function get_option(string $name, ?OptionsGroup &$group = null): Option
    {
        $name_parts = explode('/', $name, 2);
        if (count($name_parts) > 1) {
            return $this->get_option_by_strings($name_parts[0], $name_parts[1]);
        }
        if (!$group) {
            return new Option($name);
        }
        $this->add_option_group_conditionally($group->name);
        return $group->get_option($name);
    }

    protected function get_option_by_strings(string $group_name, string $option_name): Option
    {
        $this->add_option_group_conditionally($group_name);
        return $this->option_groups[$group_name]->get_option($option_name);
    }

    protected function add_option_group_conditionally(string $group_name = '', ?OptionsGroup &$group = null): static
    {
        if ($group) {
            if (!$this->has_collection_with_member('option_groups', $group->name)) {
                $this->option_groups[$group->name] = $group;
            }
            return $this;
        }
        if ($group_name) {
            $group_name = sanitize_title($group_name);
            if (!$this->has_collection_with_member('option_groups', $group_name)) {
                $this->option_groups[$group_name] = new OptionsGroup($group_name);
            }
            return $this;
        }
        throw new \Error("\$group_name and \$group are emtpy.");
    }

    /**
     * Retrieves the value of an option by name and group.
     *
     * @param string $name The name of the option group to retrieve. If it contains a '/', the first part will be interpreted as group-name and the second as option-name.
     * @param null|OptionsGroup $group The group of the option (keep empty for standart wp options).
     * @return mixed The options value.
     * @throws \Error If the option group with the specified name does not exist.
     */
    public function get_option_value(string $name, ?OptionsGroup &$group = null): mixed
    {
        return $this->get_option($name, $group)->get_value();
    }

    /**
     * Registers one or more admin screens.
     *
     * @param AbstractScreen ...$panel One or more admin screen instances.
     * @return self The current instance of Framework.
     */
    public function register_admin_screen(AbstractScreen ...$panel): self
    {
        foreach ($panel as $panel) {
            if ($panel instanceof SettingsScreen) {
                register_setting($panel->name, $panel->get_option_name());
            }
            $this
                ->add_admin_screen($panel)
                ->hook_admin_screen_actions($panel);
        }
        return $this;
    }

    /**
     * Registers the built-in admin option screen 'options-general.php'.
     *
     * @return self The current instance of Framework.
     */
    private function register_admin_option_screen(): self
    {
        $options_page = new BuildinSettingsScreen('Option');
        $options_page->name = 'options-general.php';
        $build_in_option_pages = ['general', 'reading', 'writing', 'discussion', 'media'];
        foreach ($build_in_option_pages as $build_in_option_page) {
            $options_page->register_sub_screen(new BuildinSettingsScreen($build_in_option_page));
        }
        return $this->register_admin_screen($options_page);
    }

    /**
     * Unregisters an admin screen.
     *
     * @param AbstractScreen $panel The admin screen to unregister.
     * @return self The current instance of Framework.
     */
    public function unregister_admin_screen(AbstractScreen $panel): self
    {
        return $this
            ->remove_admin_screen($panel)
            ->unhook_admin_screen_actions($panel);
    }

    /**
     * Hooks actions for the admin screen.
     *
     * @param AbstractScreen $panel The admin screen for which actions are being hooked.
     * @return self The current instance of Framework.
     */
    private function hook_admin_screen_actions(AbstractScreen $panel): self
    {
        # add panel to menu
        add_action('admin_menu', $panel->get_register_callback());
        return $this;
    }

    /**
     * Unhooks actions for the admin screen.
     *
     * @param AbstractScreen $panel The admin screen for which actions are being unhooked.
     * @return self The current instance of Framework.
     */
    private function unhook_admin_screen_actions(AbstractScreen $panel): self
    {
        # remove panel from menu
        remove_action('admin_menu', $panel->get_register_callback());
        return $this;
    }

    private function add_admin_screen(AbstractScreen $panel): self
    {
        $this->admin_screens[$panel->name] = $panel;
        return $this;
    }

    /**
     * Adds an admin screen.
     *
     * @param AbstractScreen $panel The admin screen to add.
     * @return self The current instance of Framework.
     */
    private function remove_admin_screen(string|AbstractScreen $panel): self
    {
        if (!is_string($panel)) {
            $panel = $panel->name;
        }
        unset($this->admin_screens[$panel]);
        return $this;
    }

    /**
     * Retrieves the admin options screen 'options-general.php'.
     *
     * @return BuildinSettingsScreen The admin options screen.
     */
    public function get_admin_options_screen(): BuildinSettingsScreen
    {
        return $this->admin_screens['options-general.php'];
    }

    /**
     * Retrieves the admin screen based on the specified name.
     *
     * @param string $admin_panel_name The name of the admin panel.
     * @return PrimarySettingsScreen The admin screen corresponding to the specified name.
     */
    public function get_admin_screen(string $admin_panel_name): PrimarySettingsScreen
    {
        $this->has_collection_with_member('admin_screens', $admin_panel_name, true);
        return $this->admin_screens[$admin_panel_name];
    }

    /**
     * Retrieves all registered admin screens.
     *
     * @return array An array containing all registered admin screens.
     */
    public function get_admin_screens(): array
    {
        return $this->admin_screens;
    }

    /**
     * Retrieves a role object by its name.
     *
     * @param string $name The name of the role.
     * @return Role|null The role object if found, null otherwise.
     */
    public function get_role(string $name)
    {
        return Role::get_role($name);
    }

    /**
     * Registers one or more models into the framework.
     *
     * @param AbstractModel ...$model One or more model instances to register.
     * @return self The Framework instance.
     * @throws \Error When no table is implemented for a model class.
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

    /**
     * Registers built-in models such as 'comment', 'user', 'post', and 'term' into the framework.
     *
     * @return self The Framework instance.
     */
    private function register_buildin_models(): self
    {
        $comment_model = new BuildinModel('comment');

        $user_model = new BuildinModel('user');

        $post_model = new BuildinModel('post', supports_types: true);
        foreach (get_post_types(output: 'objects') as $wp_post_type) {
            $type = BuildinType::from_wp_type_object($wp_post_type);
            $post_model->register_buildin_type($type);
        }
        $post_model->register_types_from_folder('post-types');

        $term_model = new BuildinModel('term', supports_types: true);
        foreach (get_taxonomies(output: 'objects') as $wp_taxonomy) {
            $type = BuildinType::from_wp_type_object($wp_taxonomy);
            $term_model->register_buildin_type($type);
        }
        $term_model->register_types_from_folder('taxonomies');

        $this->register_model($comment_model, $user_model, $post_model, $term_model);

        return $this;
    }

    /**
     * Retrieves the model object by its name.
     *
     * @param string $model_name The name of the model.
     * @return AbstractModel The model object.
     */
    public function get_model(string $model_name): AbstractModel
    {
        $this->has_collection_with_member('models', $model_name, true);
        return $this->models[$model_name];
    }

    /**
     * Retrieves the built-in model object by its name.
     *
     * @param string $model_name The name of the built-in model.
     * @return BuildinModel The built-in model object.
     * @throws \Error If the model is not built-in.
     */
    public function get_buildin_model(string $model_name): BuildinModel
    {
        $model = $this->get_model($model_name);
        if (!$model instanceof BuildinModel) {
            throw new \Error("Model '$model_name' is not buildin");
        }
        return $model;
    }

    /**
     * Retrieves the built-in post type object by its name.
     *
     * @param string $type The name of the post type.
     * @return BuildinType The built-in post type object.
     */
    public function get_post_type(string $type): BuildinType
    {
        return $this->get_buildin_model('post')->get_type($type);
    }

    /**
     * Retrieves the built-in taxonomy object by its name.
     *
     * @param string $type The name of the taxonomy.
     * @return BuildinType The built-in taxonomy object.
     */
    public function get_taxonomy(string $type): BuildinType
    {
        return $this->get_buildin_model('term')->get_type($type);
    }

    /**
     * Retrieves an array of models optionally including built-in models.
     *
     * @param bool $include_build_ins Whether to include built-in models or not. Defaults to false.
     * @return array An array of models.
     */
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

    /**
     * Checks if a collection property contains a specific member.
     *
     * @param string $collection_property The name of the collection property to check.
     * @param string $member_key The key of the member to check for.
     * @param bool $throw_errors Whether to throw errors if the property or member is not found. Defaults to false.
     * @return bool True if the collection property contains the member, false otherwise.
     * @throws \Error If $throw_errors is true and the property or member is not found.
     */
    private function has_collection_with_member(string $collection_property, string $member_key, bool $throw_errors = false): bool
    {
        if (!property_exists($this, $collection_property)) {
            if ($throw_errors) {
                throw new \Error("A property named '$collection_property' does not exist.");
            }
            return false;
        }
        if (!is_array($this->$collection_property)) {
            if ($throw_errors) {
                throw new \Error("The property named '$collection_property' is not a collection");
            }
            return false;
        }
        if (!isset($this->$collection_property[$member_key])) {
            if ($throw_errors) {
                throw new \Error("The collection '$collection_property' has no member named '$member_key'.");
            }
            return false;
        }
        return true;
    }

    /**
     * Registers an autoloader function to load classes from the 'WP_Framework' namespace.
     *
     * @return self
     */
    private function register_class_autoload(): self
    {
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
