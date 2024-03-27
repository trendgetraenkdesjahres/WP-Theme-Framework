<?php

namespace WP_Framework\Element;

use DOMDocument;
use Throwable;

/**
 * Class Element
 * @package WP_Framework\Element
 *
 * Represents an HTML element based on PHP DOMDocument for easier handling.
 */
class Element extends AbstractElement
{
    /**
     * @var \DOMElement The DOMElement instance.
     */
    protected $node;

    /**
     * Element constructor.
     *
     * @param string $name The name of the HTML element.
     * @param array $attributes An array of attributes for the HTML element.
     * @param string|Element ...$content The content, including sub-elements or strings.
     */
    public function __construct(private string $name, private array $attributes = [], Element|string ...$content)
    {
        # init
        $this->dom = new DOMDocument();

        # create element
        $this->node = $this->dom->createElement($this->name);

        # set attributes
        foreach ($this->attributes as $attribute => $value) {
            if (isset($value)) $this->node->setAttribute($attribute, $value);
        }

        # add sub elements and strings
        $this->append_elements_to_node($content);
    }

    protected function set_attribute(string $attribute, ?string $value): self
    {
        $this->node->setAttribute($attribute, $value);
        return $this;
    }

    public static function from_string(string $html): static
    {
        $dom = new DOMDocument();
        $dom->loadXML($html);
        $dom_node = $dom->importNode($dom->documentElement, true);
        $element = new Element($dom_node->tagName);
        $element->node = $dom_node;
        return $element;
    }
}
