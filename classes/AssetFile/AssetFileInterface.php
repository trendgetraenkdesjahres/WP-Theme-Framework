<?php

namespace WP_Framework\AssetFile;

interface AssetFileInterface
{
    /**
     * Registers the asset.
     *
     * @return self
     */
    public function register(): self;

    /**
     * Enqueues the asset.
     *
     * @return self
     */
    public function enqueue(): self;
}
