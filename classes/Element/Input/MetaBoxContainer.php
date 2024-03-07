<?php

namespace WP_Framework\Element\Input;

use WP_Framework\Element\Element;

class MetaBoxContainer extends Element
{
    public function __construct(public string $model_name, private ?object $object)
    {
        parent::__construct('div', ['id' => 'meta-box-container']);
    }

    public function append_added_meta_boxes()
    {
        ob_start();
        echo "<div id='root_node'>";
        /** This action is documented in wp-admin/includes/meta-boxes.php */
        do_action('add_meta_boxes', $this->model_name, $this->object);

        /** This action is documented in wp-admin/includes/meta-boxes.php */
        do_action('add_meta_boxes_' . $this->model_name, $this->object);

        echo "</div>";
        $html = ob_get_clean();
        $this->dom->loadXML($html);
        $root_node = $this->dom->getElementById('root_node');
        foreach ($root_node->childNodes as $meta_box_element) {
            $this->append_child($meta_box_element);
        }
    }

    public function get_meta_box_action_hook(): string
    {
        return 'add_meta_boxes_' . $this->model_name;
    }

    public function __toString(): string
    {
        $this->append_added_meta_boxes();

        # return as string
        return $this->dom->saveXML($this->node);
    }
}
