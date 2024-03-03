<?php

namespace WP_Framework\Model;

use WP_Comment;

/**
 * Handles the UserModel in WordPress.
 */
class CommentModel extends AbstractModel implements ModelInterface
{
    public string $name = 'comment';
    protected ?string $type_class = null;
    protected ?string $types_json_folder = null;

    public function get_buildin_object(int $object_id): object
    {
        return WP_Comment::get_instance($object_id);
    }
}
