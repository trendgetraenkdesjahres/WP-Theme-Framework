<?php

namespace WP_Framework\Model;

use WP_Framework\Database\Database;
use WP_Framework\Database\SQL\ThinSkinnedSyntaxCheck as SyntaxCheck;
use WP_Framework\Debug\Debug;
use WP_Framework\Model\Type\AbstractType;
use WP_Framework\Model\Type\BuildinType;

/**
 * Trait to integrate Custom model and model type attributes in WP Style,
 * aligning with WordPress documentation conventions.
 *
 * The trait applies to both base models (e.g., USER, TERM, POST, COMMENT) and the base model type layer
 * (e.g., PAGE, CATEGORY), providing a unified mechanism for handling WordPress-specific attributes.
 *
 * Note: The term 'type' here might cause confusion as it also refers to the base model type. Similarly,
 * 'property' is used to describe characteristics, acknowledging that base models have attributes such as
 * 'author' and 'date'.
 *
 * @package YourPackage
 */
trait WP_ModelTrait
{
    public string $name;
    public string $singular_name;
    public string $plural_name;

    public string $table_name;

    private static $property_keys = [
        'description',
        'public',
        'exclude_from_search',
        'publicly_queryable',
        'show_ui',
        'show_in_menu',
        'show_in_nav_menus',
        'show_in_admin_bar',
        'capability_type',
        'object_type',
        'menu_position',
        'menu_icon',
        'map_meta_cap',
        'hierarchical',
        'has_archive',
        'rewrite'
    ];

    protected array $attributes = [];

    /**
     * Set the names properties
     *
     * @param string      $name        The name of the data model.
     * @param string|null $plural_name The plural form of the name (optional).
     *
     * @return self The modified instance.
     * @throws \Error If the table name is illegal.
     */
    private function set_names(string $name, ?string $plural_name = null): static
    {
        $this->name = sanitize_key(str_replace(' ', '_', $name));

        $this->singular_name = $name;
        $this->set_label_attribute('singular_name', $name);

        $this->plural_name = $plural_name ? $plural_name : $name . 's';
        $this->set_label_attribute('name', $this->plural_name);

        if ($this instanceof BuildinType || $this instanceof BuildinModel) {
            $this->table_name = "wp_" . $this->model_name . 's';
        } else {
            $this->table_name = Database::$table_prefix . "_" . $this->name . 's';
            SyntaxCheck::is_table_name($this->table_name);
        }

        return $this;
    }

    /**
     * Get a WordPress trait attribute of this model/type.
     *
     * @param string $key   The attribute key.
     * @param bool $throw_error   Throwing an error when unable to find attribute
     *
     * @return mixed The attribute's value, if throwing errors is of, the method will return 'null' if no value found.
     * @throws \Error If attribute is not set.
     */
    public function get_attribute(string $key, bool $throw_error = true): mixed
    {
        if ($throw_error && !isset($this->attributes[$key])) {
            throw new \Error("'$key' is not an attribute of this trait.");
        }
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

    /**
     * Get a WordPress capability trait attribute of this model/type.
     *
     * @param string $key   The capability attribute key (shortform is supported)
     * @param bool $throw_error   Throwing an error when unable to find attribute
     *
     * @return mixed The attribute's value, if throwing errors is of, the method will return 'null' if no value found.
     * @throws \Error If attribute is not set.
     */
    public function get_capability_attribute(string $key, bool $throw_error = true): mixed
    {
        if ($throw_error && !isset($this->attributes['capabilities'][$key])) {
            throw new \Error("'$key' is not an attribute of this trait.");
        }
        return isset($this->attributes['capabilities'][$key]) ? $this->attributes['capabilities'][$key] : null;
    }

    /**
     * Get all trait attributes of this model/type.
     *
     * @return array The attributes
     */
    public function get_attributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set a WordPress trait attribute for the model/type.
     *
     * @param string $key   The attribute key.
     * @param string $value The attribute value.
     *
     * @return self $this
     * @throws \Error If key is not supported.
     */
    public function set_attribute(string $key, bool|int|string $value): self
    {
        if (!in_array($key, self::$property_keys)) {
            throw new \Error("'$key' is not a supported value of this trait.");
        }
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Set a WordPress trait attribute for the model/type in the DANGEROUS way.
     *
     * @param string $key   The attribute key.
     * @param string $value The attribute value.
     *
     * @return self $this
     */
    protected function _set_attribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Set a private WordPress label attribute for the model/type.
     *
     * @param string $key   The label key.
     * @param string $value The label value.
     *
     * @return self $this
     */
    protected function set_label_attribute(string $key, string $value): self
    {
        if (!isset($this->attributes['label'])) {
            $this->attributes['label'] = [];
        }
        # implement key checking
        $this->attributes['label'][$key] = $value;
        return $this;
    }

    /**
     * Set the labels for the model/label.
     *
     * @param string|null $add_new           Label for adding a new model.
     * @param string|null $add_new_item      Label for adding a new model item.
     * @param string|null $edit_item         Label for editing a model item.
     * @param string|null $new_item          Label for a new model item.
     * @param string|null $view_item         Label for viewing a model item.
     * @param string|null $view_items        Label for viewing multiple model items.
     * @param string|null $search_items      Label for searching model items.
     * @param string|null $not_found         Label for not finding model items.
     * @param string|null $not_found_in_trash Label for not finding model items in the trash.
     *
     * @return self $this
     */
    public function set_labels(
        ?string $add_new = null,
        ?string $add_new_item = null,
        ?string $edit_item = null,
        ?string $new_item = null,
        ?string $view_item = null,
        ?string $view_items = null,
        ?string $search_items = null,
        ?string $not_found = null,
        ?string $not_found_in_trash = null
    ): self {
        if ($add_new) {
            $this->set_label_attribute('add_new', $add_new);
        }
        if ($add_new_item) {
            $this->set_label_attribute('add_new_item', $add_new_item);
        }
        if ($edit_item) {
            $this->set_label_attribute('edit_item', $edit_item);
        }
        if ($new_item) {
            $this->set_label_attribute('new_item', $new_item);
        }
        if ($view_item) {
            $this->set_label_attribute('view_item', $view_item);
        }
        if ($view_items) {
            $this->set_label_attribute('view_items', $view_items);
        }
        if ($search_items) {
            $this->set_label_attribute('search_items', $search_items);
        }
        if ($not_found) {
            $this->set_label_attribute('not_found', $not_found);
        }
        if ($not_found_in_trash) {
            $this->set_label_attribute('not_found_in_trash', $not_found_in_trash);
        }
        return $this;
    }

    /**
     * Set visibility options for the model/type.
     *
     * @param bool|null $public               Whether the model is public.
     * @param bool|null $exclude_from_search  Whether to exclude the model from search results.
     * @param bool|null $publicly_queryable   Whether the model can be queried publicly.
     * @param bool|null $show_ui              Whether to display the model in the admin UI.
     * @param bool|null $show_in_menu         Whether to display the model in the admin menu.
     * @param bool|null $show_in_nav_menus    Whether to include the model in navigation menus.
     * @param bool|null $show_in_admin_bar    Whether to show the model in the admin bar.
     * @param int|null  $menu_position        The position in the admin menu.
     * @param string|null $menu_icon          The icon for the admin menu.
     *
     * @return self $this
     */
    public function set_visibility(
        ?bool $public = null,
        ?bool $exclude_from_search = null,
        ?bool $publicly_queryable = null,
        ?bool $show_ui = null,
        ?bool $show_in_menu = null,
        ?bool $show_in_nav_menus = null,
        ?bool $show_in_admin_bar = null,
        ?int $menu_position = null,
        ?string $menu_icon = null
    ): self {
        if ($public) {
            $this->set_attribute('public', $public);
        }
        if ($exclude_from_search) {
            $this->set_attribute('exclude_from_search', $exclude_from_search);
        }
        if ($publicly_queryable) {
            $this->set_attribute('publicly_queryable', $publicly_queryable);
        }
        if ($show_ui) {
            $this->set_attribute('show_ui', $show_ui);
        }
        if ($show_in_menu) {
            $this->set_attribute('show_in_menu', $show_in_menu);
        }
        if ($show_in_nav_menus) {
            $this->set_attribute('show_in_nav_menus', $show_in_nav_menus);
        }
        if ($show_in_admin_bar) {
            $this->set_attribute('show_in_admin_bar', $show_in_admin_bar);
        }
        if ($menu_position) {
            $this->set_attribute('menu_position', $menu_position);
        }
        if ($menu_icon) {
            $this->set_attribute('menu_icon', $menu_icon);
        }
        return $this;
    }

    /**
     * Add support for certain features to the model/type.
     *
     * @param string ...$feature The features to add support for.
     *
     * @return self $this
     */
    public function add_support_of(string ...$feature): self
    {
        foreach ($feature as $i => $feature) {
            if ($feature == 'meta' && !$this->meta) {
                unset($feature[$i]);
                $this->meta = [];
            }
            # just add type-feature if this is not already a type
            if ($feature == 'types' && !$this->types && !$this instanceof AbstractType) {
                unset($feature[$i]);
                $this->types = [];
            }
        }
        return $this->_set_attribute('supports', $feature);
    }

    /**
     * Remove support for certain features from the model/type.
     *
     * @param string ...$feature The features to remove support for.
     *
     * @return self $this
     */
    public function remove_support_of(string ...$feature): self
    {
        foreach ($feature as $i => $feature) {
            if ($feature == 'meta' && $this->meta) {
                unset($feature[$i]);
                $this->meta = null;
            }
            if ($feature == 'types' && $this->types && !$this instanceof AbstractType) {
                unset($feature[$i]);
                $this->types = null;
            }
            if ($feature == 'status' && isset($this->properties["{$this->name}_status"])) {
                unset($feature[$i]);
                unset($this->properties["{$this->name}_status"]);
            }
            if (($key = array_search($feature, $this->attributes['supports'])) !== false) {
                unset($this->attributes['supports'][$key]);
            }
        }
        return $this;
    }

    public function is_supporting(string $feature): bool
    {
        if ($feature == 'meta' && is_array($this->meta)) {
            return true;
        }
        if ($feature == 'types' && !$this instanceof AbstractType && is_array($this->types)) {
            return true;
        }
        if ($feature == 'status' && isset($this->properties["{$this->name}_status"])) {
            return true;
        }
        if (array_search($feature, $this->attributes['supports']) !== false) {
            return true;
        }
        return false;
    }


    /**
     * Set taxonomies for the model/type.
     *
     * @param string ...$taxonomy The taxonomies to associate with the model.
     *
     * @return self $this
     */
    public function set_taxonomies(string ...$taxonomy): self
    {
        return $this->_set_attribute('taxonomies', $taxonomy);
    }

    /**
     * Execute me before end of constructor.
     *
     * @param string $sanitized_model_name The name of the model.
     *
     * @return self $this
     */
    protected function _init(string $sanitized_model_name): self
    {
        return $this
            ->set_attribute('object_type', $sanitized_model_name)
            ->create_capability_attributes($sanitized_model_name)
            ->fill_attributes_with_defaults();
    }

    /**
     * capability_type
     * Create capability-related attributes for the model/type.
     *
     * @param string $sanitized_model_name The name of the model.
     *
     * @return self $this
     */
    private function create_capability_attributes(string $sanitized_model_name): self
    {
        if (!isset($this->attributes['capabilities'])) {
            $this->attributes['capabilities'] = [];
        }
        return $this
            ->set_attribute('capability_type', $sanitized_model_name)
            ->_set_attribute('capabilities', [
                "edit_{$sanitized_model_name}" => "edit_{$sanitized_model_name}",
                "read_{$sanitized_model_name}" => "read_{$sanitized_model_name}",
                "delete_{$sanitized_model_name}" => "delete_{$sanitized_model_name}",
                "edit_{$sanitized_model_name}s" => "edit_{$sanitized_model_name}s",
                "edit_others_{$sanitized_model_name}s" => "edit_others_{$sanitized_model_name}s",
                "publish_{$sanitized_model_name}s" => "publish_{$sanitized_model_name}s",
                "read_private_{$sanitized_model_name}s" => "read_private_{$sanitized_model_name}s"
            ]);
    }

    /**
     * Fill attributes with default values before usage.
     *
     * @return self $this
     */
    private function fill_attributes_with_defaults(): self
    {
        $this
            ->fill_label_attributes_with_defaults()
            ->fill_visibility_attributes_with_defaults();
        $default_attributes = [
            'hierarchical' => false,
            'taxonomies' => [],
            'has_archive' => false,
            'rewrite' => true,
            'query_var' => true,
            'can_export' => true,
            'show_in_rest' => true,
            'rest_base' => '',
            'rest_controller_class' => ''
        ];

        $this->attributes = array_merge($default_attributes, $this->attributes);
        return $this;
    }

    /**
     * Fill label attributes with default values.
     *
     * @return self $this
     */
    private function fill_label_attributes_with_defaults(): self
    {
        if (!isset($this->attributes['label'])) {
            $this->attributes['label'] = [];
        }
        $label_defaults = [
            'add_new' => 'Add New',
            'add_new_item' => 'Add New ' . $this->attributes['label']['singular_name'],
            'edit_item' => 'Edit ' . $this->attributes['label']['singular_name'],
            'new_item' => 'New ' . $this->attributes['label']['singular_name'],
            'view_item' => 'View ' . $this->attributes['label']['singular_name'],
            'view_items' => 'View ' . $this->attributes['label']['name'],
            'search_items' => 'Search ' . $this->attributes['label']['name'],
            'not_found' => 'No ' . $this->attributes['label']['name'] . ' found',
            'not_found_in_trash' => 'No ' . $this->attributes['label']['name'] . ' found in trash',
            'parent_item_colon' => 'Parent ' . $this->attributes['label']['singular_name'] . ':',
        ];
        $this->attributes['label'] = array_merge($label_defaults, $this->attributes['label']);
        return $this;
    }

    /**
     * Fill visibility attributes with default values.
     *
     * @return self $this
     */
    private function fill_visibility_attributes_with_defaults(): self
    {
        $visibility_defaults = [
            "public" => true,
            "exclude_from_search" => false,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "show_in_admin_bar" => true,
            "menu_position" => 25,
            "menu_icon" => null,
        ];
        $this->attributes = array_merge($visibility_defaults, $this->attributes);
        return $this;
    }
}
