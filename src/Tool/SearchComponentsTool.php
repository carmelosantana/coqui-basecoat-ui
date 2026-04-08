<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitBasecoatUi\Tool;

use CarmeloSantana\PHPAgents\Contract\ToolInterface;
use CarmeloSantana\PHPAgents\Tool\Tool;
use CarmeloSantana\PHPAgents\Tool\ToolResult;
use CarmeloSantana\PHPAgents\Tool\Parameter\StringParameter;
use CarmeloSantana\CoquiToolkitBasecoatUi\ComponentRegistry;

/**
 * Searches the Basecoat UI component registry by name, description, or tags.
 */
final readonly class SearchComponentsTool
{
    public function __construct(
        private ComponentRegistry $registry,
    ) {}

    public function build(): ToolInterface
    {
        return new Tool(
            name: 'basecoat_search_components',
            description: 'Search Basecoat UI components by keyword. Matches against component name, description, and tags. Returns matching components with summaries.',
            parameters: [
                new StringParameter('query', 'Search keyword (e.g. "modal", "form", "loading", "toggle").', required: true),
            ],
            callback: fn(array $input): ToolResult => $this->execute($input),
        );
    }

    /**
     * @param array<string, mixed> $input
     */
    private function execute(array $input): ToolResult
    {
        $query = trim((string) ($input['query'] ?? ''));

        if ($query === '') {
            return ToolResult::error('The "query" parameter is required.');
        }

        $results = $this->registry->search($query);

        if ($results === []) {
            return ToolResult::error(sprintf(
                'No components match "%s". Try a broader keyword or use basecoat_list_components to browse all components.',
                $query,
            ));
        }

        $lines = [sprintf('Found %d component(s) matching "%s":', count($results), $query)];
        $lines[] = '';

        foreach ($results as $c) {
            $flags = [];
            if ($c['has_dedicated_class']) {
                $flags[] = 'css: ' . implode(', ', $c['css_classes']);
            }
            if ($c['requires_js']) {
                $flags[] = 'requires JS';
            }
            $flagStr = $flags !== [] ? ' — ' . implode(' | ', $flags) : '';

            $lines[] = sprintf('• %s (%s) [%s]%s', $c['name'], $c['slug'], $c['category'], $flagStr);
            $lines[] = sprintf('  %s', $c['description']);
            $lines[] = '';
        }

        $lines[] = 'Use basecoat_get_component with the slug to get full HTML examples and variants.';

        return ToolResult::success(implode("\n", $lines));
    }
}
