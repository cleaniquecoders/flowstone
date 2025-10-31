# Flowstone UI (Admin) — Build & Assets

Flowstone ships an optional admin UI (Telescope-like) to visualize workflows using React Flow.

This page covers building the UI bundle, publishing assets, and configuring the layout.

## Prerequisites

- Node.js 18+ (for local builds)
- PHP/Laravel app where Flowstone is installed
- Livewire is optional (UI will render a message if not present)

## Build the UI bundle

The UI is built with Vite as a UMD library exposing `window.FlowstoneUI.mount(el, graph)`.

```bash
# From the package root
npm ci
npm run build
```

This generates the bundle under `dist/`:

- `dist/flowstone-ui.js` — main UI bundle (expects React and ReactDOM globals)
- `dist/flowstone-ui.css` — styles (includes React Flow styles)

## Publish assets

Publish the built assets to your host app's `public/vendor/flowstone` directory.

```bash
php artisan vendor:publish --tag=flowstone-ui-assets
```

Alternatively, use the convenience command:

```bash
php artisan flowstone:publish-assets
```

## Layout configuration

The Flowstone layout loads assets from `config('flowstone.ui.asset_url')` (default: `/vendor/flowstone`).

It also loads React and ReactDOM as externals to keep the bundle small:

```html
<script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="/vendor/flowstone/flowstone-ui.css" />
<script src="/vendor/flowstone/flowstone-ui.js" defer></script>
```

If you prefer to self-host React and ReactDOM, copy them to your own public folder and update the script tags accordingly.

## Using the UI

- Navigate to `/flowstone` (configurable via `flowstone.ui.path`).
- Open a workflow detail page to see the React Flow graph.
- In the graph sidebar, click "Refresh graph" to re-fetch and re-render the canvas.

## CI build (optional)

Use the provided GitHub Action workflow to build and attach `dist/` artifacts to a release. This lets users publish assets without local Node setup.

- On GitHub, create a new release (tag). The workflow will:
  - Check out the repository
  - Install Node dependencies
  - Run `npm run build`
  - Upload `dist/` to the release assets

See `.github/workflows/release-build.yml`.
