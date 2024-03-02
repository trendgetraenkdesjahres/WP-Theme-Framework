<?php

namespace WP_Framework\AssetFile;

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
