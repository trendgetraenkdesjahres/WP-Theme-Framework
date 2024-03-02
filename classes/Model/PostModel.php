<?php

namespace WP_Framework\Model;

use WP_Post;

/**
 * Handles the PostModel in WordPress.
 */
class PostModel extends AbstractModel implements ObjectInterface
{
    protected string $type_class = 'PostType';
    protected string $types_json_folder = 'post-types';
    protected string $name = 'post';

    public function get_buildin_object(int $object_id): object
    {
        return WP_Post::get_instance($object_id);
    }
}
