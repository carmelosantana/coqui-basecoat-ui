<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitBasecoatUi\Tool;

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Tool\Tool;
use CarmeloSantana\PHPAgents\Tool\ToolResult;
use CarmeloSantana\CoquiToolkitBasecoatUi\ComponentRegistry;

/**
 * Returns Basecoat UI theming information — CSS custom properties,
 * theme switching, and color scheme details.
 */
final readonly class GetThemeTool
{
    public function __construct(
        private ComponentRegistry $registry,
    ) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'basecoat_get_theme',
            description: 'Get Basecoat UI theming information: CSS custom properties, dark/light mode setup, and how to customize the color palette. Useful when setting up a project or customizing colors.',
            parameters: [],
            callback: fn(array $input): ToolResult => $this->execute(),
        );
    }

    private function execute(): ToolResult
    {
        $cdn = $this->registry->cdn();
        $version = $this->registry->version();

        $info = <<<INFO
# Basecoat UI Theming (v{$version})

## Theme Switching

Set the `data-theme` attribute on `<html>`:

```html
<html lang="en" data-theme="light">  <!-- or "dark" -->
```

Toggle via JavaScript:
```js
document.documentElement.dataset.theme =
  document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
```

## Color System

Basecoat uses CSS custom properties (HSL) compatible with shadcn/ui themes. Key variables:

### Background & Foreground
- `--background` / `--foreground` — page background and default text
- `--card` / `--card-foreground` — card surfaces
- `--popover` / `--popover-foreground` — popover surfaces

### Brand Colors
- `--primary` / `--primary-foreground` — primary actions (buttons, links)
- `--secondary` / `--secondary-foreground` — secondary actions
- `--accent` / `--accent-foreground` — subtle highlights, hover states
- `--destructive` / `--destructive-foreground` — danger/error states

### Utility Colors
- `--muted` / `--muted-foreground` — subdued backgrounds and text
- `--border` — border color
- `--input` — form input borders
- `--ring` — focus ring color

### Semantic CSS Classes
These Tailwind utility classes map to the custom properties:
- `bg-background`, `text-foreground`
- `bg-primary`, `text-primary`, `text-primary-foreground`
- `bg-secondary`, `text-secondary`
- `bg-destructive`, `text-destructive`
- `bg-muted`, `text-muted-foreground`
- `bg-accent`, `text-accent-foreground`
- `border-border`, `ring-ring`

## CDN Setup

```html
<link rel="stylesheet" href="{$cdn}/dist/basecoat.min.css">
<script src="{$cdn}/js/basecoat.min.js" defer></script>
```

## NPM Setup

```bash
npm install basecoat-css
```

```js
import 'basecoat-css/dist/basecoat.min.css';
```

## CLI Setup

```bash
npx basecoat-cli init
npx basecoat-cli add button card dialog
```

## shadcn/ui Theme Compatibility

Basecoat is compatible with shadcn/ui themes. You can use any shadcn/ui theme generator to create a palette, then apply it to Basecoat by setting the CSS custom properties in your stylesheet.

## Component Styling Convention

Basecoat uses composable CSS class names:
- Base class: `btn`, `card`, `input`, `badge`, etc.
- Variant suffix: `btn-primary`, `btn-outline`, `badge-destructive`
- Size suffix: `btn-sm`, `btn-lg`
- Combined: `btn-lg-destructive`, `btn-sm-icon-outline`

Components without dedicated classes (Avatar, Breadcrumb, Progress, Skeleton, Spinner, Empty) use Tailwind utility composition.
INFO;

        return ToolResult::success($info);
    }
}
