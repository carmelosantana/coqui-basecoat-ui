<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitBasecoatUi;

use CarmeloSantana\PHPAgents\Contract\ToolkitInterface;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\DesignPageTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\GetComponentTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\GetThemeTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\ListComponentsTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\ScaffoldProjectTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\SearchComponentsTool;

/**
 * Basecoat UI toolkit for Coqui.
 *
 * Provides 6 tools for working with the Basecoat UI component library:
 * listing, searching, scaffolding, designing pages, and retrieving
 * component details and theme information.
 *
 * All tools return markup — they never write files directly.
 * Use code-edit, claude-code, or filesystem toolkits to persist output.
 *
 * Auto-discovered by Coqui's ToolkitDiscovery when installed via Composer.
 * No credentials required.
 */
final class BasecoatToolkit implements ToolkitInterface
{
    private readonly ComponentRegistry $registry;

    public function __construct(
        ?ComponentRegistry $registry = null,
    ) {
        $this->registry = $registry ?? new ComponentRegistry();
    }

    public function tools(): array
    {
        return [
            (new ListComponentsTool($this->registry))->build(),
            (new GetComponentTool($this->registry))->build(),
            (new SearchComponentsTool($this->registry))->build(),
            (new ScaffoldProjectTool($this->registry))->build(),
            (new DesignPageTool($this->registry))->build(),
            (new GetThemeTool($this->registry))->build(),
        ];
    }

    public function guidelines(): string
    {
        return <<<'GUIDELINES'
            <BASECOAT-UI-GUIDELINES>
            ## Basecoat UI Component Library

            You have 6 tools for working with Basecoat UI — a Tailwind CSS component library with 38 components.

            ### Tool Selection Guide

            | Task | Tool | When to Use |
            |------|------|-------------|
            | Browse components | `basecoat_list_components` | See all components or filter by category |
            | Get component details | `basecoat_get_component` | Get HTML markup, CSS classes, variants, accessibility |
            | Find components | `basecoat_search_components` | Search by keyword (e.g. "modal", "loading", "toggle") |
            | Create starter file | `basecoat_scaffold_project` | Generate an HTML file with Basecoat CDN pre-configured |
            | Design a page | `basecoat_design_page` | Generate a full page with specific components composed together |
            | Theme information | `basecoat_get_theme` | Get theming setup, CSS custom properties, dark/light mode |

            ### Workflow

            1. **Browse or search** — use `basecoat_list_components` or `basecoat_search_components` to find what you need
            2. **Get details** — use `basecoat_get_component` to get exact HTML markup, CSS classes, and accessibility patterns
            3. **Create** — use `basecoat_scaffold_project` for a starter file or `basecoat_design_page` for a composed page
            4. **Persist** — use code-edit or filesystem tools to write the HTML to disk

            ### Key Principles

            - **These tools return markup only** — they never write files. Use code-edit or claude-code toolkits for file I/O.
            - Basecoat uses semantic HTML (native `<dialog>`, `<details>`, `<fieldset>`, `<input type="checkbox" role="switch">`)
            - Components use composable CSS class names: `btn`, `btn-primary`, `btn-lg-destructive`, `btn-icon-ghost`
            - 6 components require JavaScript: Dropdown Menu, Popover, Select (enhanced), Sidebar, Tabs, Toast
            - All components support dark mode via `data-theme` attribute on `<html>`
            - Basecoat is compatible with shadcn/ui themes
            - Always include accessibility attributes shown in component examples

            ### Categories

            - **data-display**: Avatar, Badge, Kbd, Table, Tooltip
            - **feedback**: Alert, Progress, Skeleton, Spinner
            - **form**: Button, Button Group, Checkbox, Combobox, Command, Field, Input, Input Group, Label, Radio Group, Select, Slider, Switch, Textarea
            - **layout**: Card, Empty, Form, Item, Sidebar
            - **navigation**: Accordion, Breadcrumb, Dropdown Menu, Pagination, Tabs, Theme Switcher
            - **overlay**: Alert Dialog, Dialog, Popover, Toast
            </BASECOAT-UI-GUIDELINES>
            GUIDELINES;
    }
}
