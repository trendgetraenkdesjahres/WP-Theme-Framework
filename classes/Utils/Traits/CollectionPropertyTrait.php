<?php

namespace WP_Framework\Utils\Traits;

trait CollectionPropertyTrait
{
    protected function has_collection_with_member(string $collection_property, string $member_key, bool $throw_errors = false): bool
    {
        if (!property_exists($this, $collection_property)) {
            if ($throw_errors) {
                throw new \Error("A property named '$collection_property' does not exist.");
            }
            return false;
        }
        if (!is_array($this->$collection_property)) {
            if ($throw_errors) {
                throw new \Error("The property named '$collection_property' is not a collection");
            }
            return false;
        }
        if (!isset($this->$collection_property[$member_key])) {
            if ($throw_errors) {
                throw new \Error("The collection '$collection_property' has no member named '$member_key'.");
            }
            return false;
        }
        return true;
    }
}
