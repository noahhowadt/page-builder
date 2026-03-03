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
    expect($page->slug)->toBe('/about-us');
    expect($page->is_published)->toBeFalse();
});

test('page creation requires title', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('pages.store'), [
        'title' => '',
        'slug' => '',
    ]);

    $response->assertSessionHasErrors('title');
    expect(Page::query()->count())->toBe(0);
});

test('empty slug is normalized to root path', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('pages.store'), [
        'title' => 'Home',
        'slug' => '',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('pages.index'));
    $page = Page::query()->first();
    expect($page->slug)->toBe('/');
});

test('slug without leading slash gets leading slash added', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('pages.store'), [
        'title' => 'Nested',
        'slug' => 'test/nested',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('pages.index'));
    $page = Page::query()->where('title', 'Nested')->first();
    expect($page->slug)->toBe('/test/nested');
});

test('page slug must be unique', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    Page::query()->create([
        'title' => 'Existing',
        'slug' => '/existing-page',
    ]);

    $response = $this->post(route('pages.store'), [
        'title' => 'Another',
        'slug' => '/existing-page',
    ]);

    $response->assertSessionHasErrors('slug');
    expect(Page::query()->count())->toBe(1);
});
