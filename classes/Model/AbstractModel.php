<?php

namespace WP_Framework\Model;

/**
 * Interface for object type (post, term, user, comment, ...) fields in WordPress.
 */
interface ObjectInterface
{
    public function get_buildin();
    public function get_type();
}


/**
 * Handles a object ('post', 'term', 'user', ...) in WordPress.
 */
abstract class AbstractObject
{
    public function __construct(public readonly string $name)
    {
    }
}
