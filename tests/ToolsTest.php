<?php

declare(strict_types=1);

use CarmeloSantana\CoquiToolkitBasecoatUi\ComponentRegistry;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\ListComponentsTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\GetComponentTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\SearchComponentsTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\ScaffoldProjectTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\DesignPageTool;
use CarmeloSantana\CoquiToolkitBasecoatUi\Tool\GetThemeTool;
use CarmeloSantana\PHPAgents\Enum\ToolResultStatus;
use CarmeloSantana\PHPAgents\Tool\ToolResult;

$registry = new ComponentRegistry();

// --- ListComponentsTool ---

test('list components returns all components', function () use ($registry) {
    $tool = (new ListComponentsTool($registry))->build();
    $result = $tool->execute([]);

    expect($result)->toBeInstanceOf(ToolResult::class)
        ->and($result->content)->toContain('Basecoat UI v')
        ->and($result->content)->toContain('Button')
        ->and($result->content)->toContain('Card');
});

test('list components filters by category', function () use ($registry) {
    $tool = (new ListComponentsTool($registry))->build();
    $result = $tool->execute(['category' => 'overlay']);

    expect($result->content)
        ->toContain('OVERLAY')
        ->toContain('Dialog')
        ->toContain('Toast');
});

// --- GetComponentTool ---

test('get component returns full details', function () use ($registry) {
    $tool = (new GetComponentTool($registry))->build();
    $result = $tool->execute(['slug' => 'button']);

    expect($result->content)
        ->toContain('# Button')
        ->toContain('btn')
        ->toContain('```html')
        ->toContain('Variants');
});

test('get component returns specific variant', function () use ($registry) {
    $tool = (new GetComponentTool($registry))->build();
    $result = $tool->execute(['slug' => 'button', 'variant' => 'outline']);

    expect($result->content)
        ->toContain('outline variant')
        ->toContain('btn-outline');
});

test('get component error for unknown slug', function () use ($registry) {
    $tool = (new GetComponentTool($registry))->build();
    $result = $tool->execute(['slug' => 'nonexistent']);

    expect($result->status === ToolResultStatus::Error)->toBeTrue()
        ->and($result->content)->toContain('not found');
});

test('get component suggests close match', function () use ($registry) {
    $tool = (new GetComponentTool($registry))->build();
    $result = $tool->execute(['slug' => 'buttom']);

    expect($result->status === ToolResultStatus::Error)->toBeTrue()
        ->and($result->content)->toContain('Did you mean "button"');
});

test('get component error for unknown variant', function () use ($registry) {
    $tool = (new GetComponentTool($registry))->build();
    $result = $tool->execute(['slug' => 'button', 'variant' => 'nonexistent']);

    expect($result->status === ToolResultStatus::Error)->toBeTrue()
        ->and($result->content)->toContain('Available variants');
});

test('get component error for empty slug', function () use ($registry) {
    $tool = (new GetComponentTool($registry))->build();
    $result = $tool->execute(['slug' => '']);

    expect($result->status === ToolResultStatus::Error)->toBeTrue();
});

// --- SearchComponentsTool ---

test('search finds components by keyword', function () use ($registry) {
    $tool = (new SearchComponentsTool($registry))->build();
    $result = $tool->execute(['query' => 'modal']);

    expect($result->content)
        ->toContain('Found')
        ->toContain('dialog');
});

test('search error for no matches', function () use ($registry) {
    $tool = (new SearchComponentsTool($registry))->build();
    $result = $tool->execute(['query' => 'zzzznotfoundzzz']);

    expect($result->status === ToolResultStatus::Error)->toBeTrue();
});

test('search error for empty query', function () use ($registry) {
    $tool = (new SearchComponentsTool($registry))->build();
    $result = $tool->execute(['query' => '']);

    expect($result->status === ToolResultStatus::Error)->toBeTrue();
});

// --- ScaffoldProjectTool ---

test('scaffold returns valid HTML', function () use ($registry) {
    $tool = (new ScaffoldProjectTool($registry))->build();
    $result = $tool->execute([]);

    expect($result->content)
        ->toContain('<!DOCTYPE html>')
        ->toContain('basecoat.min.css')
        ->toContain('basecoat.min.js')
        ->toContain('data-theme="light"');
});

test('scaffold respects custom title and theme', function () use ($registry) {
    $tool = (new ScaffoldProjectTool($registry))->build();
    $result = $tool->execute(['title' => 'My App', 'theme' => 'dark']);

    expect($result->content)
        ->toContain('<title>My App</title>')
        ->toContain('data-theme="dark"')
        ->toContain('My App');
});

test('scaffold includes lucide by default', function () use ($registry) {
    $tool = (new ScaffoldProjectTool($registry))->build();
    $result = $tool->execute([]);

    expect($result->content)->toContain('lucide');
});

test('scaffold excludes lucide when none', function () use ($registry) {
    $tool = (new ScaffoldProjectTool($registry))->build();
    $result = $tool->execute(['icon_library' => 'none']);

    expect($result->content)->not->toContain('lucide');
});

// --- DesignPageTool ---

test('design page returns composed HTML', function () use ($registry) {
    $tool = (new DesignPageTool($registry))->build();
    $result = $tool->execute([
        'description' => 'A login page with a card and form',
        'components' => ['card', 'button', 'input'],
        'title' => 'Login',
    ]);

    expect($result->content)
        ->toContain('<!DOCTYPE html>')
        ->toContain('Login')
        ->toContain('class="card"')
        ->toContain('class="btn"')
        ->toContain('class="input"');
});

test('design page includes JS scripts for components that need them', function () use ($registry) {
    $tool = (new DesignPageTool($registry))->build();
    $result = $tool->execute([
        'description' => 'A page with tabs and toast',
        'components' => ['tabs', 'toast', 'button'],
    ]);

    expect($result->content)
        ->toContain('tabs.min.js')
        ->toContain('toast.min.js')
        ->toContain('toaster');
});

test('design page error for unknown component', function () use ($registry) {
    $tool = (new DesignPageTool($registry))->build();
    $result = $tool->execute([
        'description' => 'Test page',
        'components' => ['button', 'nonexistent'],
    ]);

    expect($result->status === ToolResultStatus::Error)->toBeTrue()
        ->and($result->content)->toContain('nonexistent');
});

test('design page error for empty description', function () use ($registry) {
    $tool = (new DesignPageTool($registry))->build();
    $result = $tool->execute(['description' => '', 'components' => ['button']]);

    expect($result->status === ToolResultStatus::Error)->toBeTrue();
});

test('design page error for empty components', function () use ($registry) {
    $tool = (new DesignPageTool($registry))->build();
    $result = $tool->execute(['description' => 'A page', 'components' => []]);

    expect($result->status === ToolResultStatus::Error)->toBeTrue();
});

// --- GetThemeTool ---

test('get theme returns theming information', function () use ($registry) {
    $tool = (new GetThemeTool($registry))->build();
    $result = $tool->execute([]);

    expect($result->content)
        ->toContain('Basecoat UI Theming')
        ->toContain('data-theme')
        ->toContain('--primary')
        ->toContain('--background')
        ->toContain('basecoat.min.css')
        ->toContain('shadcn');
});
