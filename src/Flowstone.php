<?php

namespace CleaniqueCoders\Flowstone;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use RuntimeException;

class Flowstone
{
    /**
     * Get the CSS for the Flowstone dashboard.
     *
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public static function css()
    {
        // Skip if using Vite dev server (it injects styles automatically)
        if (static::isViteDevMode()) {
            return new HtmlString('');
        }

        if (static::shouldInlineAssets()) {
            $styles = @file_get_contents(__DIR__.'/../dist/flowstone-ui.css');

            return new HtmlString(<<<HTML
                <style>{$styles}</style>
            HTML);
        }

        $url = route('flowstone.asset', ['asset' => 'flowstone-ui.css']);

        return new HtmlString(<<<HTML
            <link rel="stylesheet" href="{$url}">
        HTML);
    }

    /**
     * Get the JS for the flowstone dashboard.
     *
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public static function js()
    {
        // Skip if using Vite dev server (handled by @vite directive)
        if (static::isViteDevMode()) {
            return new HtmlString('');
        }

        $flowstone = Js::from(static::scriptVariables());

        if (static::shouldInlineAssets()) {
            if (($js = @file_get_contents(__DIR__.'/../dist/flowstone-ui.js')) === false) {
                throw new RuntimeException('Unable to load the flowstone dashboard JavaScript.');
            }

            $sourceMapUrl = route('flowstone.asset', ['asset' => 'flowstone-ui.js.map']);

            return new HtmlString(<<<HTML
                <script type="module">
                    window.flowstone = {$flowstone};
                    {$js}
                    //# sourceMappingURL={$sourceMapUrl}
                </script>
            HTML);
        }

        $jsUrl = route('flowstone.asset', ['asset' => 'flowstone-ui.js']);

        return new HtmlString(<<<HTML
            <script>
                window.flowstone = {$flowstone};
            </script>
            <script type="module" src="{$jsUrl}"></script>
        HTML);
    }

    /**
     * Check if Vite dev server is enabled
     */
    protected static function isViteDevMode(): bool
    {
        return app()->environment('local')
            && config('flowstone.vite_dev_server', false);
    }

    /**
     * Determine if assets should be inlined.
     */
    protected static function shouldInlineAssets(): bool
    {
        return config('flowstone.inline_assets', true);
    }

    /**
     * Get the default JavaScript variables for flowstone.
     */
    public static function scriptVariables(): array
    {
        return [
            'path' => config('flowstone.ui.path'),
            'domain' => config('flowstone.ui.domain'),
            'asset_url' => config('flowstone.ui.asset_url'),
            'api_url' => url(config('flowstone.ui.path').'/api'),
            'csrf_token' => csrf_token(),
        ];
    }
}
