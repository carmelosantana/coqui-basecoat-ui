<?php

declare(strict_types=1);

use CarmeloSantana\CoquiToolkitBasecoatUi\BasecoatToolkit;
use CarmeloSantana\PHPAgents\Contract\ToolkitInterface;
use CarmeloSantana\PHPAgents\Contract\ToolInterface;

test('implements ToolkitInterface', function () {
    $toolkit = new BasecoatToolkit();

    expect($toolkit)->toBeInstanceOf(ToolkitInterface::class);
});

test('returns 6 tools', function () {
    $toolkit = new BasecoatToolkit();
    $tools = $toolkit->tools();

    expect($tools)->toHaveCount(6);

    foreach ($tools as $tool) {
        expect($tool)->toBeInstanceOf(ToolInterface::class);
    }
});

test('has expected tool names', function () {
    $toolkit = new BasecoatToolkit();
    $names = array_map(
        static fn(ToolInterface $tool): string => $tool->name(),
        $toolkit->tools(),
    );

    expect($names)
        ->toContain('basecoat_list_components')
        ->toContain('basecoat_get_component')
        ->toContain('basecoat_search_components')
        ->toContain('basecoat_scaffold_project')
        ->toContain('basecoat_design_page')
        ->toContain('basecoat_get_theme');
});

test('guidelines returns non-empty string', function () {
    $toolkit = new BasecoatToolkit();

    expect($toolkit->guidelines())
        ->toBeString()
        ->toContain('BASECOAT-UI-GUIDELINES')
        ->toContain('basecoat_list_components')
        ->toContain('basecoat_get_component');
});
