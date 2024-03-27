<?php

namespace WP_Framework\Element;

use WP_Framework\Debug\Debug;

/**
 * Class AbstractElement
 * @package WP_Framework\Element
 *
 * Represents an abstract element based on PHP DOMDocument for easier handling.
 */
abstract class AbstractElement
{
    /**
     * @var \DOMDocument The DOMDocument instance.
     */
    protected \DOMDocument $dom;

    /**
     * @var \DOMNode The DOMNode instance.
     */
    protected $node;

    /**
     * Appends sub-elements or strings to the current node.
     *
     * @param array $content An array of content, including sub-elements or strings.
     * @return AbstractElement The current instance of AbstractElement.
     */
    protected function append_elements_to_node(array $content): AbstractElement
    {
        # append content, juggle sub-elements to strings.
        foreach ($content as $content) {
            if ($content instanceof AbstractElement) {
                $child_element = $this->dom->importNode($content->node, true);
                $this->node->appendChild($child_element);
            } elseif (is_string($content)) {
                $text_node = $this->dom->createTextNode($content);
                $this->node->appendChild($text_node);
            }
        }
        return $this;
    }

    public function append_child(AbstractElement ...$child): AbstractElement
    {
        foreach ($child as $child) {
            $child_element = $this->dom->importNode($child->node, true);
            $this->node->appendChild($child_element);
        }
        return $this;
    }

    /**
     * Converts the element to a string representation.
     *
     * @return string The string representation of the element.
     */
    public function __toString(): string
    {
        # return as string
        return $this->dom->saveXML($this->node);
    }
}
