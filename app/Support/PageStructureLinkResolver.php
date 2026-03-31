<?php

namespace App\Support;

use App\Models\Page;

class PageStructureLinkResolver
{
    /**
     * Deep-clone structure and populate `link.url` from `link.pageId` using stored page slugs.
     * Existing non-empty `url` values are left unchanged so external links are not overwritten.
     *
     * @param  array<string, mixed>  $structure
     * @return array<string, mixed>
     */
    public static function resolve(array $structure): array
    {
        $resolved = json_decode(json_encode($structure, JSON_THROW_ON_ERROR), true);
        $pageIds = self::collectPageIds($resolved);

        if ($pageIds === []) {
            return $resolved;
        }

        /** @var array<int, string> $slugById */
        $slugById = Page::query()
            ->whereIn('id', $pageIds)
            ->pluck('slug', 'id')
            ->all();

        self::applyUrls($resolved, $slugById);

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function visitBlock(array &$block, callable $onTextNode): void
    {
        if (isset($block['content']) && is_array($block['content'])) {
            foreach ($block['content'] as &$item) {
                if (! is_array($item)) {
                    continue;
                }
                if (($item['type'] ?? null) === 'text') {
                    $onTextNode($item);
                }
            }
            unset($item);
        }

        if (isset($block['children']) && is_array($block['children'])) {
            foreach ($block['children'] as &$child) {
                if (is_array($child)) {
                    self::visitBlock($child, $onTextNode);
                }
            }
            unset($child);
        }
    }

    /**
     * @param  array<string, mixed>  $root
     * @return list<int>
     */
    private static function collectPageIds(array $root): array
    {
        $ids = [];
        self::visitBlock($root, function (array &$node) use (&$ids): void {
            $link = $node['link'] ?? null;
            if (! is_array($link) || ! array_key_exists('pageId', $link)) {
                return;
            }

            $pid = self::normalizePageId($link['pageId']);
            if ($pid === null) {
                return;
            }

            $ids[] = $pid;
        });

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<int, string>  $slugById
     */
    private static function applyUrls(array &$root, array $slugById): void
    {
        self::visitBlock($root, function (array &$node) use ($slugById): void {
            if (! isset($node['link']) || ! is_array($node['link'])) {
                return;
            }

            $link = &$node['link'];

            if (! array_key_exists('pageId', $link)) {
                return;
            }

            $pid = self::normalizePageId($link['pageId']);
            if ($pid === null) {
                return;
            }

            if (trim((string) ($link['url'] ?? '')) !== '') {
                return;
            }

            if (! isset($slugById[$pid])) {
                return;
            }

            $link['url'] = self::slugToPublicPath((string) $slugById[$pid]);
        });
    }

    private static function slugToPublicPath(string $slug): string
    {
        $slug = trim($slug);
        if ($slug === '' || $slug === '/') {
            return '/';
        }

        return '/'.ltrim($slug, '/');
    }

    private static function normalizePageId(mixed $pageId): ?int
    {
        if (is_int($pageId)) {
            return $pageId >= 1 ? $pageId : null;
        }

        if (is_string($pageId) && ctype_digit($pageId)) {
            $asInt = (int) $pageId;

            return $asInt >= 1 ? $asInt : null;
        }

        return null;
    }
}
