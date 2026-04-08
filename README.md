# Basecoat UI Toolkit for Coqui Bot

A component library toolkit that brings [Basecoat UI](https://basecoatui.com/) to Coqui Bot. Browse, search, and design with 38 prefetched components — like v0 for any web stack.

## What is Basecoat?

Basecoat is a Tailwind CSS component library that brings the magic of shadcn/ui to traditional web applications — no React required. Clean semantic HTML, accessible components, dark mode, and full shadcn/ui theme compatibility.

## Features

- **Prefetched component registry** — all 38 components with HTML examples, CSS classes, variants, and accessibility notes. Zero network calls.
- **v0-style page design** — describe a page in natural language, get complete basecoat HTML output.
- **Project scaffolding** — generate new projects pre-configured with basecoat (CDN or NPM).
- **Component search** — find components by use case, name, or CSS class.
- **Theme support** — get shadcn/ui-compatible theme variables and customization guides.
- **Composable** — returns markup for code-edit/claude-code toolkits to place into files.

## Tools

| Tool | Description |
|------|-------------|
| `basecoat_list_components` | Browse all 38 components, optionally filtered by category |
| `basecoat_get_component` | Get full HTML examples, CSS classes, and variants for any component |
| `basecoat_search_components` | Search components by keyword, use case, or CSS class |
| `basecoat_scaffold_project` | Generate a new project pre-configured with Basecoat |
| `basecoat_design_page` | Design a complete page from a natural language description |
| `basecoat_get_theme` | Get theme variables, customization guide, and import instructions |

## Installation

```bash
composer require coquibot/coqui-toolkit-basecoat-ui
```

Auto-discovered by Coqui Bot — no configuration needed.

## Usage with Other Toolkits

This toolkit is designed to work in tandem with:

- **code-edit** — use `replace_in_file` / `insert_before` to place basecoat markup into your files
- **claude-code** — delegate complex multi-file changes that involve basecoat components
- **code-search** — find where to insert components in existing projects

Typical workflow:
1. `basecoat_scaffold_project` or identify your target project
2. `basecoat_design_page` or `basecoat_get_component` for markup
3. Use code-edit tools to place the markup into your files
4. `basecoat_get_theme` to customize the look

## Components

Accordion, Alert, Alert Dialog, Avatar, Badge, Breadcrumb, Button, Button Group, Card, Checkbox, Combobox, Command, Dialog, Dropdown Menu, Empty, Field, Form, Input, Input Group, Item, Kbd, Label, Pagination, Popover, Progress, Radio Group, Select, Sidebar, Skeleton, Slider, Spinner, Switch, Table, Tabs, Textarea, Theme Switcher, Toast, Tooltip.

## License

MIT
