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
        return function ($object_id) use ($model) {
            if (!$this->is_saving_safe_and_secure($object_id, $model)) {
                return $object_id;
            }
            call_user_func(
                "update_{$model}_meta",
                $object_id,
                $this->key,
                $this->cast_to_data_type($_POST[$this->key])
            );
            return $object_id;
        };
    }
}
