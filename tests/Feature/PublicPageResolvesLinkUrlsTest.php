<?php

use App\Models\Page;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('published public page html includes href resolved from link page id', function () {
    $target = Page::query()->create([
        'title' => 'About',
        'slug' => '/about',
        'is_published' => true,
        'published_at' => now(),
        'structure' => Page::defaultStructure(),
    ]);

    Page::query()->create([
        'title' => 'Home',
        'slug' => '/link-test-home',
        'is_published' => true,
        'published_at' => now(),
        'structure' => [
            'id' => 'root',
            'type' => 'root',
            'children' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'About us',
                            'link' => ['pageId' => $target->id],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->get('/link-test-home')
        ->assertOk()
        ->assertSee('href="/about"', false);
});
