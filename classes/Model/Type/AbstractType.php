<?php

namespace WP_Framework\Model\Type;

use WP_Framework\Model\AbstractModel;
use WP_Framework\Model\Meta\AbstractMeta;
use WP_Framework\Model\WP_ModelTrait;
use WP_Framework\Utils\JsonFile;

/**
 * Handles a model type. A model type is a variation of a model.
 * It has the same properties (table columns) as a model, but not the same values, not the same meta, and can be displayed differently.
 * For example, 'page' and 'attachment' of post type, 'category' and 'tag' of term type/taxonomy are WordPress built-in models.
 */
abstract class AbstractType extends AbstractModel
{
    use WP_ModelTrait;
    /**
     * The internal name of the type.
     *
     * @var string
     */

    public function __construct(string $model_name, string $singular_name, string $plural_name, string $description = '', string ...$taxonomy)
    {
        $this
            ->set_names($singular_name, $plural_name)
            ->set_attribute('description', $description)
            ->set_taxonomies(...$taxonomy)
            ->set_label_attribute('name', $plural_name)
            ->set_label_attribute('singular_name', $singular_name)
            ->_init($model_name);
    }

    /**
     * Creates a CustomType instance from a JSON file.
     *
     * @param string $path The path to the JSON file defining the type.
     *
     * @return self The created AbstractType instance.
     */
    public static function create_from_json(string $path, ?string $model_name = null): self
    {
        # get the (sanitized) name from file name.
        $name = basename($path, '.json');

        # get the attributes.
        $attributes = JsonFile::to_array($path);

        # set the model name / object type
        if (!$model_name) {
            if (!isset($attributes['object_type'])) {
                throw new \Error("If the \$model_name parameter is unused,  the 'object_type' field in the json must be set.");
            }
            $object_type = $attributes['object_type'];
        } else {
            $object_type = $model_name;
        }

        # set the fancy names
        $singular_name = isset($attributes['labels']['singular_name']) ?
            $attributes['labels']['singular_name'] : ucfirst($name);
        $plural_name = isset($attributes['labels']['plural_name']) ?
            $attributes['labels']['plural_name'] : ucfirst($name) . 's';

        # get class name of AbstractType implementation for calling constructor.
        $this_class = get_called_class();
        $type = new $this_class(
            model_name: $object_type,
            singular_name: $singular_name,
            plural_name: $plural_name
        );
        foreach ($attributes as $key => $attribute) {
            $type->_set_attribute($key, $attribute);
        }
        return $type;
    }

    /**
     * Creates options for registering meta fields.
     *
     * @param AbstractMeta $meta The WP_Framework Meta object.
     *
     * @return array The merged array of meta options.
     */
    protected function create_meta_options(AbstractMeta $meta): array
    {
        return array_merge($meta->options, [
            'type' => $meta->type,
            'description' => $meta->description,
            'object_subtype' => $this->attributes['object_type']
        ]);
    }

    /**
     * Get the database table name for this types base model.
     *
     * @return string The database table name.
     */
    public function get_table_name(): string
    {
        throw new \Error('needs helps');
        return "wp_{$this->name}s";
    }
}
