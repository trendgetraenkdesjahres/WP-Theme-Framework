<?php

namespace WP_Framework\Model\Type;

use WP_Framework\Model\AbstractModel;
use WP_Framework\Model\Meta\AbstractMeta;
use WP_Framework\Model\ModelIntegrationTrait;

/**
 * Handles a model type. A model type is a variation of a model.
 * It has the same properties (table columns) as a model, but not the same values, not the same meta, and can be displayed differently.
 * For example, 'page' and 'attachment' of post type, 'category' and 'tag' of term type/taxonomy are WordPress built-in models.
 */
abstract class AbstractType extends AbstractModel
{
    use ModelIntegrationTrait;
    /**
     * The internal name of the type.
     *
     * @var string
     */
    public string $name;

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
            'object_subtype' => $this->name
        ]);
    }
}
