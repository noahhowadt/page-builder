<?php

use App\Models\Component;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests cannot view components index', function () {
    /** @var TestCase $this */
    $response = $this->get(route('components.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view components index', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('components.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('components/Index')
        ->has('components'));
});

test('guests cannot create a component', function () {
    /** @var TestCase $this */
    $response = $this->post(route('components.store'), [
        'name' => 'Hero',
    ]);

    $response->assertRedirect(route('login'));
    expect(Component::query()->count())->toBe(0);
});

test('authenticated users can create a component', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('components.store'), [
        'name' => 'Hero section',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('components.index'));

    $component = Component::query()->first();
    expect($component)->not->toBeNull();
    expect($component->name)->toBe('Hero section');
});

test('new components get default structure with root block', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('components.store'), [
        'name' => 'With structure',
    ]);

    $component = Component::query()->first();
    expect($component->structure)->toBeArray();
    expect($component->structure)->toHaveKeys(['id', 'type', 'children']);
    expect($component->structure['type'])->toBe('root');
    expect($component->structure['children'])->toBe([]);
    expect($component->structure['id'])->toBeString();
    expect(strlen($component->structure['id']))->toBeGreaterThan(0);
});

test('component creation requires name', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('components.store'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
    expect(Component::query()->count())->toBe(0);
});

test('authenticated users can update component name', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    /** @var Component $component */
    $component = Component::factory()->create(['name' => 'Old']);

    $response = $this->put(route('components.update', $component), [
        'name' => 'New name',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect();
    $component->refresh();
    expect($component->name)->toBe('New name');
});

test('authenticated users can delete a component', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    /** @var Component $component */
    $component = Component::factory()->create();

    $response = $this->delete(route('components.destroy', $component));

    $response->assertRedirect(route('components.index'));
    expect(Component::query()->find($component->id))->toBeNull();
});

test('guests cannot delete a component', function () {
    /** @var TestCase $this */
    /** @var Component $component */
    $component = Component::factory()->create();

    $response = $this->delete(route('components.destroy', $component));

    $response->assertRedirect(route('login'));
    expect(Component::query()->find($component->id))->not->toBeNull();
});

test('authenticated users can view component editor', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    /** @var Component $component */
    $component = Component::factory()->create();

    $response = $this->get(route('components.edit', $component));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('components/Edit')
        ->has('component')
        ->has('structure')
        ->has('updateUrl')
        ->has('linkablePages')
        ->has('libraryComponents'));
});

test('update can accept structure only for component builder', function () {
    /** @var TestCase $this */
    /** @var User $user */
    $user = User::factory()->create();
    $this->actingAs($user);

    /** @var Component $component */
    $component = Component::factory()->create(['name' => 'Builder']);
    $originalName = $component->name;
    $newStructure = ['id' => 'custom-id', 'type' => 'root', 'children' => []];

    $response = $this->put(route('components.update', $component), [
        'structure' => $newStructure,
    ]);

    $response->assertSessionHasNoErrors();
    $component->refresh();
    expect($component->name)->toBe($originalName);
    expect($component->structure)->toBe($newStructure);
});
