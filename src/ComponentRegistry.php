<?php

declare(strict_types=1);

namespace CarmeloSantana\CoquiToolkitBasecoatUi;

/**
 * Lazy-loaded component registry backed by data/registry.json.
 *
 * Provides search, filter, and lookup operations over the full
 * Basecoat UI component catalog.
 */
final class ComponentRegistry
{
    /** @var array<string, array<string, mixed>>|null slug → component data */
    private ?array $components = null;

    private ?string $version = null;

    private ?string $cdn = null;

    public function __construct(
        private readonly string $registryPath = __DIR__ . '/../data/registry.json',
    ) {}

    public function version(): string
    {
        $this->ensureLoaded();

        return $this->version ?? '';
    }

    public function cdn(): string
    {
        $this->ensureLoaded();

        return $this->cdn ?? '';
    }

    /**
     * Get a single component by slug.
     *
     * @return array<string, mixed>|null
     */
    public function get(string $slug): ?array
    {
        $this->ensureLoaded();

        return $this->components[$slug] ?? null;
    }

    /**
     * List all components, optionally filtered by category.
     *
     * @return list<array<string, mixed>>
     */
    public function list(?string $category = null): array
    {
        $this->ensureLoaded();

        if ($category === null) {
            return array_values($this->components ?? []);
        }

        return array_values(array_filter(
            $this->components ?? [],
            static fn(array $c): bool => $c['category'] === $category,
        ));
    }

    /**
     * Get all unique categories.
     *
     * @return list<string>
     */
    public function categories(): array
    {
        $this->ensureLoaded();

        $categories = array_unique(array_map(
            static fn(array $c): string => $c['category'],
            $this->components ?? [],
        ));

        sort($categories);

        return $categories;
    }

    /**
     * Search components by name, description, or tags.
     *
     * @return list<array<string, mixed>>
     */
    public function search(string $query): array
    {
        $this->ensureLoaded();

        $query = mb_strtolower($query);

        return array_values(array_filter(
            $this->components ?? [],
            static function (array $c) use ($query): bool {
                if (str_contains(mb_strtolower($c['name']), $query)) {
                    return true;
                }
                if (str_contains(mb_strtolower($c['description']), $query)) {
                    return true;
                }
                foreach ($c['tags'] as $tag) {
                    if (str_contains(mb_strtolower($tag), $query)) {
                        return true;
                    }
                }

                return false;
            },
        ));
    }

    /**
     * Get components that require JavaScript.
     *
     * @return list<array<string, mixed>>
     */
    public function requiresJs(): array
    {
        $this->ensureLoaded();

        return array_values(array_filter(
            $this->components ?? [],
            static fn(array $c): bool => $c['requires_js'] === true,
        ));
    }

    /**
     * Get all component slugs.
     *
     * @return list<string>
     */
    public function slugs(): array
    {
        $this->ensureLoaded();

        return array_keys($this->components ?? []);
    }

    /**
     * Get total number of registered components.
     */
    public function count(): int
    {
        $this->ensureLoaded();

        return count($this->components ?? []);
    }

    /**
     * Get a compact summary of a component (for list display).
     *
     * @return array{slug: string, name: string, category: string, has_dedicated_class: bool, requires_js: bool}|null
     */
    public function summary(string $slug): ?array
    {
        $component = $this->get($slug);
        if ($component === null) {
            return null;
        }

        return [
            'slug' => $component['slug'],
            'name' => $component['name'],
            'category' => $component['category'],
            'has_dedicated_class' => $component['has_dedicated_class'],
            'requires_js' => $component['requires_js'],
        ];
    }

    /**
     * Get compact summaries for all components.
     *
     * @return list<array{slug: string, name: string, category: string, has_dedicated_class: bool, requires_js: bool}>
     */
    public function summaries(?string $category = null): array
    {
        return array_values(array_filter(array_map(
            fn(array $c): ?array => $this->summary($c['slug']),
            $this->list($category),
        )));
    }

    private function ensureLoaded(): void
    {
        if ($this->components !== null) {
            return;
        }

        $json = file_get_contents($this->registryPath);
        if ($json === false) {
            throw new \RuntimeException(sprintf(
                'Failed to read component registry: %s',
                $this->registryPath,
            ));
        }

        /** @var array{version: string, cdn: string, components: list<array<string, mixed>>} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->version = $data['version'];
        $this->cdn = $data['cdn'];
        $this->components = [];

        foreach ($data['components'] as $component) {
            $this->components[$component['slug']] = $component;
        }
    }
}
