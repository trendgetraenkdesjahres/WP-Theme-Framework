<?php

namespace WP_ThemeFramework\AssetFile;

interface AssetFileInterface
{
    /**
     * Registers the asset.
     *
     * @return AssetFileInterface
     */
    public function register(): AssetFileInterface;

    /**
     * Enqueues the asset.
     *
     * @return AssetFileInterface
     */
    public function enqueue(): AssetFileInterface;
}
