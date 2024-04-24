<?php

namespace WP_Framework\Element;

use DOMDocument;
use WP_Framework\Debug\Debug;

/**
 * Class Fragment
 * @package WP_Framework\Element
 *
 * Represents an HTML DocumentFragment based on PHP DOMDocument for easier handling.
 */
class Fragment extends AbstractElement
{
    /**
     * Fragment constructor.
     *
     * @param string|Element ...$content The content, including sub-elements or strings.
     */
    public function __construct(null|string|Element ...$content)
    {
        # init
        $this->dom = new DOMDocument();

        # create fragment
        $this->node = $this->dom->createDocumentFragment();

        # add sub elements and strings
        $this->append_elements_to_node($content);
    }
}
