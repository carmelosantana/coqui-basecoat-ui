<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitBasecoatUi\Tool;

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Tool\Tool;
use CarmeloSantana\PHPAgents\Tool\ToolResult;
use CarmeloSantana\PHPAgents\Tool\Parameter\StringParameter;
use CarmeloSantana\CoquiToolkitBasecoatUi\ComponentRegistry;

/**
 * Gets full details for a specific Basecoat UI component.
 *
 * Returns HTML markup, CSS classes, variants, accessibility notes,
 * and JavaScript requirements.
 */
final readonly class GetComponentTool
{
    public function __construct(
        private ComponentRegistry $registry,
    ) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'basecoat_get_component',
            description: 'Get full details for a Basecoat UI component — HTML markup, CSS classes, all variants, accessibility notes, and JS requirements. Use the component slug (e.g. "button", "card", "dialog", "alert").',
            parameters: [
                new StringParameter('slug', 'Component slug (e.g. "button", "card", "dialog", "toast").', required: true),
                new StringParameter('variant', 'Specific variant name to retrieve. If omitted, returns all variants.', required: false),
            ],
            callback: fn(array $input): ToolResult => $this->execute($input),
        );
    }

    /**
     * @param array<string, mixed> $input
     */
    private function execute(array $input): ToolResult
    {
        $slug = trim((string) ($input['slug'] ?? ''));
        $variant = isset($input['variant']) ? trim((string) $input['variant']) : null;

        if ($slug === '') {
            return ToolResult::error('The "slug" parameter is required. Use basecoat_list_components to see all available slugs.');
        }

        $component = $this->registry->get($slug);

        if ($component === null) {
            $suggestion = $this->findClosestSlug($slug);
            $msg = sprintf('Component "%s" not found.', $slug);
            if ($suggestion !== null) {
                $msg .= sprintf(' Did you mean "%s"?', $suggestion);
            }
            $msg .= ' Use basecoat_list_components to see all available components.';

            return ToolResult::error($msg);
        }

        if ($variant !== null && $variant !== '') {
            return $this->formatVariant($component, $variant);
        }

        return $this->formatFull($component);
    }

    /**
     * @param array<string, mixed> $component
     */
    private function formatFull(array $component): ToolResult
    {
        $lines = [];
        $lines[] = sprintf('# %s', $component['name']);
        $lines[] = '';
        $lines[] = $component['description'];
        $lines[] = '';
        $lines[] = sprintf('Category: %s', $component['category']);
        $lines[] = sprintf('Tags: %s', implode(', ', $component['tags']));
        $lines[] = sprintf('Requires JS: %s', $component['requires_js'] ? 'yes' : 'no');
        $lines[] = sprintf('Has dedicated CSS class: %s', $component['has_dedicated_class'] ? 'yes' : 'no');

        if ($component['css_classes'] !== []) {
            $lines[] = sprintf('CSS classes: %s', implode(', ', $component['css_classes']));
        }

        $lines[] = '';
        $lines[] = '## Accessibility';
        $lines[] = $component['accessibility_notes'];

        $lines[] = '';
        $lines[] = '## Variants';

        foreach ($component['variants'] as $v) {
            $lines[] = '';
            $lines[] = sprintf('### %s', $v['name']);
            $lines[] = $v['description'];
            $lines[] = '';
            $lines[] = '```html';
            $lines[] = $v['html'];
            $lines[] = '```';
        }

        return ToolResult::success(implode("\n", $lines));
    }

    /**
     * @param array<string, mixed> $component
     */
    private function formatVariant(array $component, string $variantName): ToolResult
    {
        foreach ($component['variants'] as $v) {
            if ($v['name'] === $variantName) {
                $lines = [];
                $lines[] = sprintf('# %s — %s variant', $component['name'], $v['name']);
                $lines[] = '';
                $lines[] = $v['description'];

                if ($component['css_classes'] !== []) {
                    $lines[] = '';
                    $lines[] = sprintf('CSS classes: %s', implode(', ', $component['css_classes']));
                }

                $lines[] = '';
                $lines[] = '```html';
                $lines[] = $v['html'];
                $lines[] = '```';

                return ToolResult::success(implode("\n", $lines));
            }
        }

        $available = array_map(
            static fn(array $v): string => $v['name'],
            $component['variants'],
        );

        return ToolResult::error(sprintf(
            'Variant "%s" not found for %s. Available variants: %s',
            $variantName,
            $component['name'],
            implode(', ', $available),
        ));
    }

    private function findClosestSlug(string $input): ?string
    {
        $slugs = $this->registry->slugs();
        $best = null;
        $bestDistance = PHP_INT_MAX;

        foreach ($slugs as $slug) {
            $distance = levenshtein($input, $slug);
            if ($distance < $bestDistance && $distance <= 3) {
                $best = $slug;
                $bestDistance = $distance;
            }
        }

        return $best;
    }
}
