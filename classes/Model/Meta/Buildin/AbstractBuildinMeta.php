<?php

namespace WP_Framework\Model\Meta\Buildin;

use WP_Framework\Model\Meta\AbstractMeta;


/**
 * An abstract class for built-in meta fields in WordPress models.
 */
abstract class AbstractBuildinMeta extends AbstractMeta
{
    public function get_save_callback(string $model): callable
    {
        return function ($instance_id) use ($model) {

            if (!$this->is_saving_safe_and_secure($instance_id, $model)) {
                return $instance_id;
            }
            call_user_func(
                "update_{$model}_meta",
                $instance_id,
                $this->key,
                $this->cast_to_string($_POST[$this->key])

            );
            return $instance_id;
        };
    }
}
