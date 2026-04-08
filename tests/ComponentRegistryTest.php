<?php

declare(strict_types=1);

use CarmeloSantana\CoquiToolkitBasecoatUi\ComponentRegistry;

$registry = new ComponentRegistry();

test('loads all components from registry', function () use ($registry) {
    expect($registry->count())->toBeGreaterThan(30);
});

test('returns version string', function () use ($registry) {
    expect($registry->version())->toBe('0.3.11');
});

test('returns CDN url', function () use ($registry) {
    expect($registry->cdn())->toContain('basecoat-css');
});

test('gets a component by slug', function () use ($registry) {
    $button = $registry->get('button');

    expect($button)
        ->not->toBeNull()
        ->and($button['name'])->toBe('Button')
        ->and($button['category'])->toBe('form')
        ->and($button['has_dedicated_class'])->toBeTrue()
        ->and($button['requires_js'])->toBeFalse()
        ->and($button['css_classes'])->toContain('btn')
        ->and($button['variants'])->not->toBeEmpty();
});

test('returns null for unknown slug', function () use ($registry) {
    expect($registry->get('nonexistent'))->toBeNull();
});

test('lists all components', function () use ($registry) {
    $all = $registry->list();

    expect($all)->not->toBeEmpty()
        ->and(count($all))->toBe($registry->count());
});

test('filters components by category', function () use ($registry) {
    $formComponents = $registry->list('form');

    expect($formComponents)->not->toBeEmpty();

    foreach ($formComponents as $component) {
        expect($component['category'])->toBe('form');
    }
});

test('returns empty array for unknown category', function () use ($registry) {
    expect($registry->list('nonexistent'))->toBeEmpty();
});

test('returns all categories', function () use ($registry) {
    $categories = $registry->categories();

    expect($categories)
        ->toContain('form')
        ->toContain('layout')
        ->toContain('navigation')
        ->toContain('overlay')
        ->toContain('feedback')
        ->toContain('data-display');
});

test('searches components by name', function () use ($registry) {
    $results = $registry->search('button');

    expect($results)->not->toBeEmpty();

    $slugs = array_column($results, 'slug');
    expect($slugs)->toContain('button');
});

test('searches components by tag', function () use ($registry) {
    $results = $registry->search('modal');

    expect($results)->not->toBeEmpty();

    $slugs = array_column($results, 'slug');
    expect($slugs)->toContain('dialog');
});

test('search is case insensitive', function () use ($registry) {
    $lower = $registry->search('dialog');
    $upper = $registry->search('DIALOG');

    expect(count($lower))->toBe(count($upper));
});

test('returns empty search for no matches', function () use ($registry) {
    expect($registry->search('zzzznonexistentzzzz'))->toBeEmpty();
});

test('lists components requiring JS', function () use ($registry) {
    $jsComponents = $registry->requiresJs();

    expect($jsComponents)->not->toBeEmpty();

    foreach ($jsComponents as $component) {
        expect($component['requires_js'])->toBeTrue();
    }

    $slugs = array_column($jsComponents, 'slug');
    expect($slugs)
        ->toContain('dropdown-menu')
        ->toContain('tabs')
        ->toContain('toast');
});

test('returns all slugs', function () use ($registry) {
    $slugs = $registry->slugs();

    expect($slugs)
        ->toContain('button')
        ->toContain('card')
        ->toContain('dialog')
        ->toContain('input')
        ->and(count($slugs))->toBe($registry->count());
});

test('returns compact summary for a component', function () use ($registry) {
    $summary = $registry->summary('card');

    expect($summary)
        ->not->toBeNull()
        ->toHaveKeys(['slug', 'name', 'category', 'has_dedicated_class', 'requires_js'])
        ->and($summary['slug'])->toBe('card')
        ->and($summary['name'])->toBe('Card');
});

test('returns null summary for unknown slug', function () use ($registry) {
    expect($registry->summary('nonexistent'))->toBeNull();
});

test('returns summaries for all components', function () use ($registry) {
    $summaries = $registry->summaries();

    expect(count($summaries))->toBe($registry->count());

    foreach ($summaries as $s) {
        expect($s)->toHaveKeys(['slug', 'name', 'category', 'has_dedicated_class', 'requires_js']);
    }
});

test('returns summaries filtered by category', function () use ($registry) {
    $overlays = $registry->summaries('overlay');

    expect($overlays)->not->toBeEmpty();

    foreach ($overlays as $s) {
        expect($s['category'])->toBe('overlay');
    }
});
