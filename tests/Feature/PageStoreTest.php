<?php

use App\Models\Component;
use App\Models\Page;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
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

test('new pages get default structure with root block', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('pages.store'), [
        'title' => 'With Default Structure',
        'slug' => '/with-default-structure',
    ]);

    $page = Page::query()->first();
    expect($page->structure)->toBeArray();
    expect($page->structure)->toHaveKeys(['id', 'type', 'children']);
    expect($page->structure['type'])->toBe('root');
    expect($page->structure['children'])->toBe([]);
    expect($page->structure['id'])->toBeString();
    expect(strlen($page->structure['id']))->toBeGreaterThan(0);
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

test('authenticated users can update page title slug and published status', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::query()->create([
        'title' => 'Draft',
        'slug' => '/draft',
        'is_published' => false,
        'published_at' => null,
    ]);

    $response = $this->put(route('pages.update', $page), [
        'title' => 'Updated Title',
        'slug' => '/updated-slug',
        'is_published' => true,
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect();
    $page->refresh();
    expect($page->title)->toBe('Updated Title');
    expect($page->slug)->toBe('/updated-slug');
    expect($page->is_published)->toBeTrue();
    expect($page->published_at)->not->toBeNull();
});

test('publishing a page sets published_at automatically', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::query()->create([
        'title' => 'Draft',
        'slug' => '/draft',
        'is_published' => false,
        'published_at' => null,
    ]);

    $this->put(route('pages.update', $page), [
        'is_published' => true,
    ]);

    $page->refresh();
    expect($page->is_published)->toBeTrue();
    expect($page->published_at)->not->toBeNull();
});

test('unpublishing a page clears published_at', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::query()->create([
        'title' => 'Published',
        'slug' => '/published',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->put(route('pages.update', $page), [
        'is_published' => false,
    ]);

    $page->refresh();
    expect($page->is_published)->toBeFalse();
    expect($page->published_at)->toBeNull();
});

test('update slug must be unique except for current page', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    Page::query()->create(['title' => 'Taken', 'slug' => '/taken']);
    $page = Page::query()->create(['title' => 'Mine', 'slug' => '/mine']);

    $response = $this->put(route('pages.update', $page), [
        'slug' => '/taken',
    ]);

    $response->assertSessionHasErrors('slug');
    expect($page->fresh()->slug)->toBe('/mine');
});

test('update can keep same slug for current page', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::query()->create(['title' => 'My Page', 'slug' => '/my-page']);

    $response = $this->put(route('pages.update', $page), [
        'title' => 'New Title',
        'slug' => '/my-page',
    ]);

    $response->assertSessionHasNoErrors();
    $page->refresh();
    expect($page->title)->toBe('New Title');
    expect($page->slug)->toBe('/my-page');
});

test('update can accept structure only for page builder', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::query()->create(['title' => 'Builder Page', 'slug' => '/builder']);
    $originalTitle = $page->title;
    $newStructure = ['id' => 'custom-id', 'type' => 'root', 'children' => []];

    $response = $this->put(route('pages.update', $page), [
        'structure' => $newStructure,
    ]);

    $response->assertSessionHasNoErrors();
    $page->refresh();
    expect($page->title)->toBe($originalTitle);
    expect($page->structure)->toBe($newStructure);
});

test('authenticated users can delete a page', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $page = Page::query()->create(['title' => 'To Delete', 'slug' => '/to-delete']);

    $response = $this->delete(route('pages.destroy', $page));

    $response->assertRedirect(route('pages.index'));
    expect(Page::query()->find($page->id))->toBeNull();
});

test('guests cannot delete a page', function () {
    /** @var TestCase $this */
    $page = Page::query()->create(['title' => 'Protected', 'slug' => '/protected']);

    $response = $this->delete(route('pages.destroy', $page));

    $response->assertRedirect(route('login'));
    expect(Page::query()->find($page->id))->not->toBeNull();
});

test('page editor includes library components for sidebar', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    Component::factory()->create(['name' => 'Hero']);

    $page = Page::query()->create([
        'title' => 'About',
        'slug' => '/about',
    ]);

    $response = $this->get(route('pages.edit', $page));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('pages/Edit')
        ->has('libraryComponents', 1)
        ->where('libraryComponents.0.name', 'Hero'));
});
