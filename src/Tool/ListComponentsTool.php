<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitBasecoatUi\Tool;

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Tool\Tool;
use CarmeloSantana\PHPAgents\Tool\ToolResult;
use CarmeloSantana\PHPAgents\Tool\Parameter\EnumParameter;
use CarmeloSantana\CoquiToolkitBasecoatUi\ComponentRegistry;

/**
 * Lists all Basecoat UI components or filters by category.
 */
final readonly class ListComponentsTool
{
    public function __construct(
        private ComponentRegistry $registry,
    ) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'basecoat_list_components',
            description: 'List all available Basecoat UI components. Optionally filter by category. Returns compact summaries (slug, name, category, has_dedicated_class, requires_js).',
            parameters: [
                new EnumParameter(
                    'category',
                    'Filter components by category.',
                    values: ['data-display', 'feedback', 'form', 'layout', 'navigation', 'overlay'],
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
        $category = isset($input['category']) ? trim((string) $input['category']) : null;

        if ($category !== null && $category === '') {
            $category = null;
        }

        $summaries = $this->registry->summaries($category);

        if ($summaries === []) {
            return ToolResult::error(sprintf(
                'No components found%s.',
                $category !== null ? " in category '{$category}'" : '',
            ));
        }

        $grouped = [];
        foreach ($summaries as $s) {
            $grouped[$s['category']][] = $s;
        }

        ksort($grouped);

        $lines = [sprintf('Basecoat UI v%s — %d components', $this->registry->version(), count($summaries))];
        $lines[] = '';

        foreach ($grouped as $cat => $items) {
            $lines[] = strtoupper($cat);
            foreach ($items as $item) {
                $flags = [];
                if ($item['has_dedicated_class']) {
                    $flags[] = 'css-class';
                }
                if ($item['requires_js']) {
                    $flags[] = 'js';
                }
                $flagStr = $flags !== [] ? ' [' . implode(', ', $flags) . ']' : '';
                $lines[] = sprintf('  • %s (%s)%s', $item['name'], $item['slug'], $flagStr);
            }
            $lines[] = '';
        }

        $lines[] = 'Use basecoat_get_component to get full HTML markup, CSS classes, and variants for a specific component.';

        return ToolResult::success(implode("\n", $lines));
    }
}
