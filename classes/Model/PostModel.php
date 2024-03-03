<?php

namespace WP_Framework\Model;

use WP_Post;

/**
 * Handles the PostModel in WordPress.
 */
class PostModel extends AbstractModel implements ModelInterface
{
    public string $name = 'post';
    protected ?string $type_class = 'PostType';
    protected ?string $types_json_folder = 'post-types';
    protected bool $has_types = true;

    public function get_buildin_object(int $object_id): object
    {
        return WP_Post::get_instance($object_id);
    }
}
