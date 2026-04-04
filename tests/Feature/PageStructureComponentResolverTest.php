<?php

use App\Models\Component;
use App\Support\PageStructureComponentResolver;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('resolves a simple component reference', function () {
    /** @var Component $hero */
    $hero = Component::factory()->create([
        'name' => 'Hero',
        'structure' => [
            'id' => 'comp-root',
            'type' => 'root',
            'children' => [
                ['id' => 'h1', 'type' => 'heading', 'config' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Hello']]],
            ],
        ],
    ]);

    $pageStructure = [
        'id' => 'page-root',
        'type' => 'root',
        'children' => [
            ['id' => 'ref-1', 'type' => 'root', 'componentId' => $hero->id, 'children' => []],
        ],
    ];

    $resolved = PageStructureComponentResolver::resolve($pageStructure);

    expect($resolved['children'][0]['componentId'])->toBe($hero->id);
    expect($resolved['children'][0]['children'])->toHaveCount(1);
    expect($resolved['children'][0]['children'][0]['type'])->toBe('heading');
    expect($resolved['children'][0]['children'][0]['content'][0]['text'])->toBe('Hello');
});

test('resolves nested component references', function () {
    /** @var Component $inner */
    $inner = Component::factory()->create([
        'name' => 'Inner',
        'structure' => [
            'id' => 'inner-root',
            'type' => 'root',
            'children' => [
                ['id' => 'p1', 'type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Nested']]],
            ],
        ],
    ]);

    /** @var Component $outer */
    $outer = Component::factory()->create([
        'name' => 'Outer',
        'structure' => [
            'id' => 'outer-root',
            'type' => 'root',
            'children' => [
                ['id' => 'inner-ref', 'type' => 'root', 'componentId' => $inner->id, 'children' => []],
            ],
        ],
    ]);

    $pageStructure = [
        'id' => 'page-root',
        'type' => 'root',
        'children' => [
            ['id' => 'outer-ref', 'type' => 'root', 'componentId' => $outer->id, 'children' => []],
        ],
    ];

    $resolved = PageStructureComponentResolver::resolve($pageStructure);

    $outerBlock = $resolved['children'][0];
    expect($outerBlock['children'])->toHaveCount(1);

    $innerBlock = $outerBlock['children'][0];
    expect($innerBlock['componentId'])->toBe($inner->id);
    expect($innerBlock['children'])->toHaveCount(1);
    expect($innerBlock['children'][0]['type'])->toBe('paragraph');
    expect($innerBlock['children'][0]['content'][0]['text'])->toBe('Nested');
});

test('prevents circular component references', function () {
    /** @var Component $compA */
    $compA = Component::factory()->create(['name' => 'A']);
    /** @var Component $compB */
    $compB = Component::factory()->create(['name' => 'B']);

    $compA->update([
        'structure' => [
            'id' => 'a-root',
            'type' => 'root',
            'children' => [
                ['id' => 'ref-b', 'type' => 'root', 'componentId' => $compB->id, 'children' => []],
            ],
        ],
    ]);

    $compB->update([
        'structure' => [
            'id' => 'b-root',
            'type' => 'root',
            'children' => [
                ['id' => 'ref-a', 'type' => 'root', 'componentId' => $compA->id, 'children' => []],
            ],
        ],
    ]);

    $pageStructure = [
        'id' => 'page-root',
        'type' => 'root',
        'children' => [
            ['id' => 'top-ref', 'type' => 'root', 'componentId' => $compA->id, 'children' => []],
        ],
    ];

    $resolved = PageStructureComponentResolver::resolve($pageStructure);

    $aBlock = $resolved['children'][0];
    expect($aBlock['children'])->toHaveCount(1);

    $bBlock = $aBlock['children'][0];
    expect($bBlock['children'])->toHaveCount(1);

    $circularRef = $bBlock['children'][0];
    expect($circularRef['componentId'])->toBe($compA->id);
    expect($circularRef['children'])->toBe([]);
});

test('leaves structure unchanged when no component references exist', function () {
    $pageStructure = [
        'id' => 'page-root',
        'type' => 'root',
        'children' => [
            ['id' => 'h1', 'type' => 'heading', 'config' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Title']]],
            ['id' => 'p1', 'type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Body']]],
        ],
    ];

    $resolved = PageStructureComponentResolver::resolve($pageStructure);

    expect($resolved)->toBe($pageStructure);
});

test('handles missing component gracefully', function () {
    $pageStructure = [
        'id' => 'page-root',
        'type' => 'root',
        'children' => [
            ['id' => 'ref-1', 'type' => 'root', 'componentId' => 99999, 'children' => []],
        ],
    ];

    $resolved = PageStructureComponentResolver::resolve($pageStructure);

    expect($resolved['children'][0]['componentId'])->toBe(99999);
    expect($resolved['children'][0]['children'])->toBe([]);
});

test('resolves components inside containers', function () {
    /** @var Component $card */
    $card = Component::factory()->create([
        'name' => 'Card',
        'structure' => [
            'id' => 'card-root',
            'type' => 'root',
            'children' => [
                ['id' => 'card-h', 'type' => 'heading', 'config' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Card']]],
            ],
        ],
    ]);

    $pageStructure = [
        'id' => 'page-root',
        'type' => 'root',
        'children' => [
            [
                'id' => 'container-1',
                'type' => 'container',
                'config' => ['direction' => 'column', 'gap' => 0, 'padding' => 20],
                'children' => [
                    ['id' => 'card-ref', 'type' => 'root', 'componentId' => $card->id, 'children' => []],
                ],
            ],
        ],
    ];

    $resolved = PageStructureComponentResolver::resolve($pageStructure);

    $container = $resolved['children'][0];
    expect($container['type'])->toBe('container');

    $cardBlock = $container['children'][0];
    expect($cardBlock['componentId'])->toBe($card->id);
    expect($cardBlock['children'])->toHaveCount(1);
    expect($cardBlock['children'][0]['type'])->toBe('heading');
});
