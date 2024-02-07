<?php

namespace WP_ThemeFramework\AssetFile;

interface AssetFileInterface
{
    /**
     * Registers the asset.
     *
     * @return AssetInterface
     */
    public function register(): AssetFileInterface;

    /**
     * Sets tag attributes for the asset.
     *
     * @param array $key_value_pairs Array of key-value pairs for attributes.
     * @return AssetInterface
     */
    public function set_tag_attributes(array $key_value_pairs): AssetFileInterface;

    /**
     * Enqueues the asset.
     *
     * @return AssetInterface
     */
    public function enqueue(): AssetFileInterface;
}