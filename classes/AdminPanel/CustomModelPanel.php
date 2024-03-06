<?php

namespace WP_Framework\AdminPanel;

use WP_Framework\AdminPanel\Table\CustomModelTable;
use WP_Framework\Model\CustomModel;

class CustomModelPanel extends AbstractPanel
{
    public string $required_capabilty = 'edit_themes';
    public CustomModel $model;

    public function __construct(CustomModel $model)
    {
        $this->model = $model;
        parent::__construct(
            singular_name: $model->name,
            plural_name: $model->plural_name
        );
    }

    protected function get_body(): string
    {
        ob_start();
        $table = new CustomModelTable($this->model);
        $table->prepare_items();
        $table->display();
        return ob_get_clean();
    }

    protected function get_editor_body(): string
    {
        ob_start();
        echo "<form name='post' action='comment.php' method='post' id='post'>";
        wp_editor(
            $comment->comment_content,
            'content',
            array(
                'media_buttons' => false,
                'tinymce'       => false,
                'quicktags'     => $quicktags_settings,
            )
        );

        /**
         * Fires when comment-specific meta boxes are added.
         *
         * @param CustomObject $object Custom Object (of a registred model).
         */
        do_action('add_meta_boxes', $this->model->sanitized_name, $object);
        do_meta_boxes(null, 'normal', $comment);

        echo "</form>";
        return ob_get_clean();
    }
}
