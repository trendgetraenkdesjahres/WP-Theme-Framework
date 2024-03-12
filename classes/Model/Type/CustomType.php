<?php

namespace WP_Framework\Model\Type;

use WP_Framework\Utils\JsonFile;

/**
 * Handles a custom model's type.
 */
class CustomType extends AbstractType
{
    public function __construct(public string $name, public string $singular_name, public string $plural_name, string $description = '', string ...$taxonomy)
    {
        $this
            ->set_attribute('description', $description)
            ->set_taxonomies(...$taxonomy)
            ->set_label_attribute('name', $plural_name)
            ->set_label_attribute('singular_name', $singular_name);
    }

    /**
     * Creates a CustomType instance from a JSON file.
     *
     * @param string $path The path to the JSON file defining the type.
     *
     * @return CustomType The created WpTypeTrait instance.
     */
    public static function create_from_json(string $path): CustomType
    {
        $name = basename($path, '.json');
        $props = JsonFile::to_array($path);
        $type = new CustomType(
            name: $name,
            singular_name: $props['singular_name'],
            plural_name: $props['name']
        );
        foreach ($props as $key => $prop) {
            $type->_set_attribute($key, $prop);
        }
        return $type;
    }
}
