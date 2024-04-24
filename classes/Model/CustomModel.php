<?php

namespace WP_Framework\Model;

use WP_Framework\Database\Database;
use WP_Framework\Model\Instance\CustomInstance;
use WP_Framework\Model\Instance\CustomInstanceWithMeta;
use WP_Framework\Model\Property\ForeignInstance;
use WP_Framework\Model\Property\Property;
use WP_Framework\Model\Property\Status;
use WP_Framework\Model\Type\CustomType;

/**
 * DataModel is the class to implement custom models.
 *
 * @package WP_Framework\Model
 */
class CustomModel extends AbstractModel
{
    use WP_ModelTrait;

    /**
     * Array to store types. Null if custom Model does not support types.
     *
     * @var array|null
     */
    private ?array $types = null;

    /**
     * @var array An array to store properties of the model.
     */
    public array $properties = [];

    public string $primary_propery;

    /**
     *
     * @param string             $name               The name of the data model.
     * @param string|null        $plural_name        The plural form of the name (optional).
     * @param bool               $supports_meta      Indicates whether the model has meta data.
     * @param bool               $supports_types     Indicates whether the model has types.
     * @param bool               $supports_hierarchy Indicates whether the model is hierarchical (objects have parents).
     * @param AbstractModel|null $owner_model        The type of owner (e.g., 'user' or a custom model).
     */
    public  function __construct(string $name, ?string $plural_name = null, bool|array $possible_status = false, bool $supports_meta = false, bool $supports_types = false, bool $supports_hierarchy = false, ?AbstractModel $owner_model = null)
    {
        $this->set_names($name, $plural_name);

        $this->model_name = $this->name;

        if ($possible_status) {
            $this->initialize_status($possible_status);
        }

        if ($supports_meta) {
            $this->meta = [];
        }

        if ($supports_types) {
            $this->initialize_types();
        }

        if ($supports_hierarchy) {
            $this->set_attribute('hierarchical', true);
        }

        if ($owner_model) {
            $this->initialize_owner($owner_model);
        }
        $this->_init($this->name);
    }

    /**
     * Get an instance of the custom model.
     *
     * @param int $object_id The ID of the object.
     *
     * @return ?CustomInstance The CustomInstance instance or null on fail.
     */
    public function get_instance(int $id): ?CustomInstance
    {
        if ($this->meta !== null) {
            return CustomInstanceWithMeta::get_instance($this, $id);
        }
        return CustomInstance::get_instance($this, $id);
    }

    /**
     * Get a CustomType instance by name.
     *
     * @param string $name The name of the CustomType.
     *
     * @return CustomType The CustomType instance.
     * @throws \Error If the CustomType is not registered.
     */
    public function get_type(string $name): CustomType
    {
        if (!$this->types || !isset($this->types[$name])) {
            throw new \Error("A {$this->name}-type named '$name' is not registered");
        }
        return $this->types[$name];
    }

    /**
     * Register a CustomType for the model.
     *
     * @param CustomType $type The CustomType instance to register.
     *
     * @return CustomModel The modified CustomModel instance.
     * @throws \Error If the model does not support types.
     */
    public function register_type(CustomType ...$type): CustomModel
    {
        foreach ($type as $type) {
            if ($this->types === null) {
                throw new \Error("This Model '$this->name' does not support types.");
            }
            $this->add_type($type);
        }
        return $this;
    }

    /**
     * Unregister a CustomType from the model.
     *
     * @param string|CustomType $type The CustomType instance or name to unregister.
     *
     * @return CustomModel The modified CustomModel instance.
     */
    public function unregister_type(string|CustomType $type): CustomModel
    {
        return $this->remove_type($type);
    }

    /**
     * Add a custom type.
     *
     * @param CustomType $type The custom type to add.
     *
     * @return CustomModel The modified CustomModel instance.
     */
    private function add_type(CustomType $type): CustomModel
    {
        $this->types[$type->name] = $type;
        return $this;
    }

    /**
     * Remove a custom type.
     *
     * @param string|CustomType $type The custom type or its name to remove.
     *
     * @return CustomModel The modified CustomModel instance.
     */
    private function remove_type(string|CustomType $type): CustomModel
    {
        if (!is_string($type)) {
            $type = $type->name;
        }
        unset($this->types[$type]);
        return $this;
    }

    /**
     * Get a property by its name.
     *
     * @param string $property The name of the property.
     *
     * @return Property The property object.
     */
    public function get_property(string $property): Property
    {
        return $this->properties[$property];
    }

    public function get_property_names(): array
    {
        return array_keys($this->properties);
    }

    /**
     * Get properties of the model.
     *
     * @param bool $just_indexables If true, returns only indexable properties.
     *
     * @return array The array of properties.
     */
    public function get_properties(bool $just_indexables = false): array
    {
        # return any
        if (!$just_indexables) {
            return $this->properties;
        }

        # return just indexables
        return array_filter($this->properties, function ($property) {
            return $property->is_indexable;
        });
    }

    /**
     * Register properties for the model.
     *
     * @param Property ...$property The properties to register.
     *
     * @return CustomModel The modified CustomModel instance.
     */
    public function register_property(Property ...$property): CustomModel
    {
        foreach ($property as $property) {
            $this->add_property($property);
        }
        return $this;
    }

    /**
     * Unregister a property from the model.
     *
     * @param Property $property The property to unregister.
     *
     * @return CustomModel The modified CustomModel instance.
     */
    public function unregister_property(Property $property): CustomModel
    {
        return $this->remove_property($property);
    }

    public function set_primary_property(string|Property $property): CustomModel
    {
        if (!is_string($property)) {
            $property = $property->get_property_key($this);
        }
        $this->primary_propery = $property;
        return $this;
    }

    /**
     * Add a property to the model.
     *
     * @param Property $property The property to add.
     *
     * @return CustomModel The modified CustomModel instance.
     */
    private function add_property(Property $property): CustomModel
    {
        # get validated key
        $key = $property->get_property_key($this);
        # append key
        $this->properties[$key] = $property;
        # set primary property
        if (!isset($this->primary_propery)) {
            $this->set_primary_property($property);
        }
        return $this;
    }

    /**
     * Remove a property from the model.
     *
     * @param string|Property $property The Property instance or property key to remove.
     *
     * @return CustomModel The modified CustomModel instance.
     */
    private function remove_property(string|Property $property)
    {
        if (!is_string($property)) {
            $property = $property->get_property_key($this);
        }
        unset($this->properties[$property]);
        return $this;
    }

    /**
     * Get the database table name for the model.
     *
     * @return string The database table name.
     */
    public function get_table_name(): string
    {
        return Database::create_model_table_name($this->name);
    }

    /**
     * Get the meta table name for the model.
     *
     * @return string|null The meta table name or null if meta is not supported.
     */
    public function get_meta_table_name(): string
    {
        if ($this->meta === null) {
            return null;
        }
        return Database::create_model_meta_table_name($this->name);
    }

    private function initialize_status(bool|array $possible_status): static
    {
        if (!is_array($possible_status)) {
            return $this->register_property(new Status(
                model_singular_name: $this->singular_name
            ));
        }
        return $this->register_property(new Status(
            model_singular_name: $this->singular_name,
            possible_status: $possible_status
        ));
    }

    /**
     * Initialize types-array and registers a 'type' property.
     *
     * @return static The modified CustomModel instance.
     */
    private function initialize_types(): static
    {
        $this->types = [];

        # add 'type' property
        return $this->register_property(new Property(
            key: 'type',
            sql_type: 'varchar(20)',
            singular_name: "{$this->singular_name} Type",
            plural_name: "{$this->singular_name} Types",
            is_indexable: true,
            default_value: $this->name
        ));
    }

    /**
     * Registers a 'owner' property.
     *
     * @return static The modified CustomModel instance.
     */
    private function initialize_owner(AbstractModel $owner_model): static
    {
        # add 'owner' property
        return $this->register_property(new ForeignInstance(
            referenced_model: $owner_model
        ));
    }
}
