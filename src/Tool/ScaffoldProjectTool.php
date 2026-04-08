<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitBasecoatUi\Tool;

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Tool\Tool;
use CarmeloSantana\PHPAgents\Tool\ToolResult;
use CarmeloSantana\PHPAgents\Tool\Parameter\EnumParameter;
use CarmeloSantana\PHPAgents\Tool\Parameter\StringParameter;
use CarmeloSantana\CoquiToolkitBasecoatUi\ComponentRegistry;

/**
 * Generates an HTML project scaffold with Basecoat UI pre-configured.
 *
 * Returns the complete HTML — does NOT write to the filesystem.
 * Use code-edit or claude-code toolkits to persist the output.
 */
final readonly class ScaffoldProjectTool
{
    public function __construct(
        private ComponentRegistry $registry,
    ) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'basecoat_scaffold_project',
            description: 'Generate a starter HTML file with Basecoat UI pre-configured (CDN link, charset, viewport, dark/light theme toggle). Returns the complete HTML string — use code-edit tools to write it to a file.',
            parameters: [
                new StringParameter('title', 'Page title for the <title> tag.', required: false),
                new EnumParameter(
                    'theme',
                    'Default color theme.',
                    values: ['light', 'dark'],
                    required: false,
                ),
                new EnumParameter(
                    'icon_library',
                    'Include an icon library CDN link.',
                    values: ['lucide', 'none'],
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
        $title = trim((string) ($input['title'] ?? 'Basecoat UI Project'));
        $theme = trim((string) ($input['theme'] ?? 'light'));
        $iconLibrary = trim((string) ($input['icon_library'] ?? 'lucide'));

        $cdn = $this->registry->cdn();

        $iconTag = '';
        if ($iconLibrary === 'lucide') {
            $iconTag = "\n    <script src=\"https://unpkg.com/lucide@latest\"></script>";
        }

        $basecoatJs = sprintf('%s/js/basecoat.min.js', $cdn);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en" data-theme="{$theme}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="stylesheet" href="{$cdn}/dist/basecoat.min.css">
    <script src="{$basecoatJs}" defer></script>{$iconTag}
</head>
<body class="min-h-screen bg-background text-foreground">
    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">{$title}</h1>
        <!-- Add your Basecoat UI components here -->
    </main>

    <!-- Toaster container (for toast notifications) -->
    <div id="toaster" class="toaster"></div>
</body>
</html>
HTML;

        return ToolResult::success($html);
    }
}
