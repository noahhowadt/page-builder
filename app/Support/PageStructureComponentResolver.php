<?php

namespace App\Support;

use App\Models\Component;

class PageStructureComponentResolver
{
    /**
     * Recursively resolve component references in a page/component structure.
     * Root blocks with a `componentId` have their children replaced with the
     * actual component structure fetched from the database.
     *
     * @param  array<string, mixed>  $structure
     * @param  list<int>  $seen  Guard against circular references
     * @return array<string, mixed>
     */
    public static function resolve(array $structure, array $seen = []): array
    {
        if (! isset($structure['children']) || ! is_array($structure['children'])) {
            return $structure;
        }

        $structure['children'] = array_map(
            fn (array $child): array => self::resolveBlock($child, $seen),
            $structure['children'],
        );

        return $structure;
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<int>  $seen
     * @return array<string, mixed>
     */
    private static function resolveBlock(array $block, array $seen): array
    {
        $type = $block['type'] ?? null;
        $componentId = $block['componentId'] ?? null;

        if ($type === 'root' && $componentId !== null) {
            return self::resolveComponentRoot($block, (int) $componentId, $seen);
        }

        if (isset($block['children']) && is_array($block['children'])) {
            $block['children'] = array_map(
                fn (array $child): array => self::resolveBlock($child, $seen),
                $block['children'],
            );
        }

        return $block;
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<int>  $seen
     * @return array<string, mixed>
     */
    private static function resolveComponentRoot(array $block, int $componentId, array $seen): array
    {
        if (in_array($componentId, $seen, true)) {
            return $block;
        }

        $component = Component::query()->find($componentId);
        if (! $component instanceof Component) {
            return $block;
        }

        $componentStructure = $component->structure;
        if (! is_array($componentStructure)) {
            return $block;
        }

        $seen[] = $componentId;
        $resolved = self::resolve($componentStructure, $seen);

        return [
            ...$block,
            'children' => $resolved['children'] ?? [],
        ];
    }
}
