<?php

use App\Models\Page;
use App\Models\User;
use Tests\TestCase;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests cannot create a page', function () {
    /** @var TestCase $this */
    $response = $this->post(route('pages.store'), [
        'title' => 'Test Page',
        'slug' => 'test-page',
    ]);

    $response->assertRedirect(route('login'));
    expect(Page::query()->count())->toBe(0);
});

test('authenticated users can create a page', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('pages.store'), [
        'title' => 'About Us',
        'slug' => 'about-us',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('pages.index'));

    $page = Page::query()->first();
    expect($page)->not->toBeNull();
    expect($page->title)->toBe('About Us');
    expect($page->slug)->toBe('about-us');
    expect($page->is_published)->toBeFalse();
});

test('page creation requires title and slug', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('pages.store'), [
        'title' => '',
        'slug' => '',
    ]);

    $response->assertSessionHasErrors(['title', 'slug']);
    expect(Page::query()->count())->toBe(0);
});

test('page slug must be unique', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    Page::query()->create([
        'title' => 'Existing',
        'slug' => 'existing-page',
    ]);

    $response = $this->post(route('pages.store'), [
        'title' => 'Another',
        'slug' => 'existing-page',
    ]);

    $response->assertSessionHasErrors('slug');
    expect(Page::query()->count())->toBe(1);
});
