<?php

use App\Models\Page;
use App\Support\PageStructureLinkResolver;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('fills link url from page id and leaves page id unchanged', function () {
    $target = Page::query()->create([
        'title' => 'Target',
        'slug' => '/target',
        'is_published' => true,
        'published_at' => now(),
        'structure' => Page::defaultStructure(),
    ]);

    $structure = [
        'id' => 'root',
        'type' => 'root',
        'children' => [
            [
                'id' => 'p1',
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Go',
                        'link' => ['pageId' => $target->id],
                    ],
                ],
            ],
        ],
    ];

    $out = PageStructureLinkResolver::resolve($structure);

    expect($out['children'][0]['content'][0]['link']['url'])->toBe('/target');
    expect($out['children'][0]['content'][0]['link']['pageId'])->toBe($target->id);
    expect($structure['children'][0]['content'][0]['link'])->not->toHaveKey('url');
});

test('does not overwrite existing non empty url', function () {
    $target = Page::query()->create([
        'title' => 'Target',
        'slug' => '/target',
        'is_published' => true,
        'published_at' => now(),
        'structure' => Page::defaultStructure(),
    ]);

    $structure = [
        'id' => 'root',
        'type' => 'root',
        'children' => [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Go',
                        'link' => [
                            'pageId' => $target->id,
                            'url' => 'https://example.com',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $out = PageStructureLinkResolver::resolve($structure);

    expect($out['children'][0]['content'][0]['link']['url'])->toBe('https://example.com');
});

test('resolves links nested under container children and heading content', function () {
    $target = Page::query()->create([
        'title' => 'Inner',
        'slug' => '/nested/page',
        'is_published' => true,
        'published_at' => now(),
        'structure' => Page::defaultStructure(),
    ]);

    $structure = [
        'id' => 'root',
        'type' => 'root',
        'children' => [
            [
                'type' => 'container',
                'children' => [
                    [
                        'type' => 'heading',
                        'config' => ['level' => 2],
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'H',
                                'link' => ['pageId' => $target->id],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $out = PageStructureLinkResolver::resolve($structure);

    expect($out['children'][0]['children'][0]['content'][0]['link']['url'])->toBe('/nested/page');
});

test('does not set url when page id is missing from database', function () {
    $structure = [
        'id' => 'root',
        'type' => 'root',
        'children' => [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Go',
                        'link' => ['pageId' => 999_999],
                    ],
                ],
            ],
        ],
    ];

    $out = PageStructureLinkResolver::resolve($structure);

    expect($out['children'][0]['content'][0]['link'])->not->toHaveKey('url');
    expect($out['children'][0]['content'][0]['link']['pageId'])->toBe(999_999);
});
