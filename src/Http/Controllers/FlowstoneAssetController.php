<?php

namespace CleaniqueCoders\Flowstone\Http\Controllers;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FlowstoneAssetController
{
    /**
     * Serve Flowstone assets (JS, CSS, source maps)
     */
    public function serve(string $asset): BinaryFileResponse
    {
        // Whitelist allowed assets for security
        $allowedAssets = [
            'flowstone-ui.js',
            'flowstone-ui.js.map',
            'flowstone-ui.css',
            'flowstone-ui.css.map',
        ];

        if (! in_array($asset, $allowedAssets)) {
            abort(404, 'Asset not found.');
        }

        $path = $this->resolveAssetPath($asset);

        if (! File::exists($path)) {
            abort(404, 'Asset file does not exist.');
        }

        return response()->file($path, [
            'Content-Type' => $this->getMimeType($asset),
            'Cache-Control' => $this->getCacheControl(),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Resolve the full path to the asset
     */
    protected function resolveAssetPath(string $asset): string
    {
        // Check public directory first (for development with build --watch)
        $publicPath = __DIR__.'/../../../public/'.$asset;

        if (File::exists($publicPath)) {
            return $publicPath;
        }

        // Fallback to dist directory (for production builds)
        return __DIR__.'/../../../dist/'.$asset;
    }

    /**
     * Get the MIME type for the asset
     */
    protected function getMimeType(string $asset): string
    {
        $extension = File::extension($asset);

        return match ($extension) {
            'js' => 'application/javascript',
            'css' => 'text/css',
            'map' => 'application/json',
            default => 'application/octet-stream',
        };
    }

    /**
     * Get cache control header
     */
    protected function getCacheControl(): string
    {
        // In production, cache assets for 1 year
        // In development, no cache for easier debugging
        return app()->environment('production')
            ? 'public, max-age=31536000, immutable'
            : 'no-cache, no-store, must-revalidate';
    }
}
