<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitBasecoatUi\Tool;

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Tool\Tool;
use CarmeloSantana\PHPAgents\Tool\ToolResult;
use CarmeloSantana\PHPAgents\Tool\Parameter\ArrayParameter;
use CarmeloSantana\PHPAgents\Tool\Parameter\EnumParameter;
use CarmeloSantana\PHPAgents\Tool\Parameter\StringParameter;
use CarmeloSantana\CoquiToolkitBasecoatUi\ComponentRegistry;

/**
 * Generates a complete HTML page using Basecoat UI components (v0-style).
 *
 * Accepts a natural-language description and a list of component slugs,
 * then assembles a full page with the requested components composed together.
 *
 * Returns HTML string — does NOT write to the filesystem.
 */
final readonly class DesignPageTool
{
    public function __construct(
        private ComponentRegistry $registry,
    ) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'basecoat_design_page',
            description: 'Generate a complete HTML page using Basecoat UI components. Provide a page description and specify which components to include. Returns the full HTML — use code-edit tools to write it to a file. This is designed for v0-style page generation.',
            parameters: [
                new StringParameter('description', 'Natural language description of the page (e.g. "A settings page with a form for user profile, a sidebar navigation, and toast notifications").', required: true),
                new ArrayParameter(
                    'components',
                    'List of component slugs to include (e.g. ["card", "form", "button", "toast"]). Use basecoat_list_components to see available slugs.',
                    required: true,
                    items: new StringParameter('slug', 'Component slug'),
                ),
                new StringParameter('title', 'Page title.', required: false),
                new EnumParameter(
                    'theme',
                    'Default color theme.',
                    values: ['light', 'dark'],
                    required: false,
                ),
            ],
            callback: fn(array $input): ToolResult => $this->execute($input),
        );
    }

    /**
     * @param array<string, mixed> $input
     */
    private function execute(array $input): ToolResult
    {
        $description = trim((string) ($input['description'] ?? ''));
        $components = (array) ($input['components'] ?? []);
        $title = trim((string) ($input['title'] ?? 'Basecoat UI Page'));
        $theme = trim((string) ($input['theme'] ?? 'light'));

        if ($description === '') {
            return ToolResult::error('The "description" parameter is required.');
        }

        if ($components === []) {
            return ToolResult::error('The "components" parameter is required. Provide at least one component slug.');
        }

        // Validate all slugs, collect component data, and track JS requirements
        $componentData = [];
        $jsFiles = [];
        $unknown = [];

        foreach ($components as $slug) {
            $slug = trim((string) $slug);
            $component = $this->registry->get($slug);
            if ($component === null) {
                $unknown[] = $slug;
                continue;
            }
            $componentData[] = $component;
            if ($component['requires_js']) {
                $jsFiles[$slug] = true;
            }
        }

        if ($unknown !== []) {
            return ToolResult::error(sprintf(
                'Unknown component slug(s): %s. Use basecoat_list_components to see available components.',
                implode(', ', $unknown),
            ));
        }

        $cdn = $this->registry->cdn();

        // Build JS script tags for components that need them
        $jsScripts = '';
        foreach (array_keys($jsFiles) as $slug) {
            $jsScripts .= sprintf("\n    <script src=\"%s/js/%s.min.js\" defer></script>", $cdn, $slug);
        }

        // Build component reference section
        $componentSections = [];
        foreach ($componentData as $c) {
            $defaultVariant = $c['variants'][0] ?? null;
            if ($defaultVariant === null) {
                continue;
            }

            $cssNote = '';
            if ($c['css_classes'] !== []) {
                $cssNote = sprintf(' | CSS: %s', implode(', ', $c['css_classes']));
            }

            $jsNote = $c['requires_js'] ? ' | Requires JS' : '';

            $componentSections[] = sprintf(
                "<!-- %s (%s%s%s) -->\n%s",
                $c['name'],
                $c['slug'],
                $cssNote,
                $jsNote,
                $defaultVariant['html'],
            );
        }

        $componentsHtml = implode("\n\n", $componentSections);

        $hasToast = isset($jsFiles['toast']);
        $toasterHtml = $hasToast ? "\n\n    <!-- Toaster container -->\n    <div id=\"toaster\" class=\"toaster\"></div>" : '';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en" data-theme="{$theme}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="stylesheet" href="{$cdn}/dist/basecoat.min.css">
    <script src="{$cdn}/js/basecoat.min.js" defer></script>{$jsScripts}
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-background text-foreground">
    <!--
    Page Description: {$description}

    Components used: {$this->formatComponentList($componentData)}

    This page was generated with Basecoat UI v{$this->registry->version()}.
    Modify the layout and components below to match your design.
    -->

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">{$title}</h1>

        {$componentsHtml}
    </main>{$toasterHtml}

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
HTML;

        return ToolResult::success($html);
    }

    /**
     * @param list<array<string, mixed>> $components
     */
    private function formatComponentList(array $components): string
    {
        return implode(', ', array_map(
            static fn(array $c): string => $c['name'],
            $components,
        ));
    }
}
