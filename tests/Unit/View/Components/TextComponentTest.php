<?php

use App\View\Components\Text;

it('renders plain escaped text', function () {
    $component = new Text([
        'type' => 'text',
        'text' => 'Hello <world>',
        'marks' => [],
    ]);

    expect($component->render())->toBe('Hello &lt;world&gt;');
});

it('wraps bold, italic, and underline in mark order', function () {
    $component = new Text([
        'type' => 'text',
        'text' => 'X',
        'marks' => ['bold', 'italic', 'underline'],
    ]);

    expect($component->render())->toBe('<strong><em><u>X</u></em></strong>');
});

it('supports alternate mark aliases from legacy data', function () {
    $component = new Text([
        'type' => 'text',
        'text' => 'Y',
        'marks' => ['strong', 'em'],
    ]);

    expect($component->render())->toBe('<strong><em>Y</em></strong>');
});

it('wraps content in an anchor for allowed external URLs', function () {
    $component = new Text([
        'type' => 'text',
        'text' => 'Go',
        'marks' => [],
        'link' => ['url' => 'https://example.com/path?q=1'],
    ]);

    expect($component->render())->toBe(
        '<a href="https://example.com/path?q=1" target="_blank" rel="noopener noreferrer">Go</a>'
    );
});

it('allows mailto links', function () {
    $component = new Text([
        'type' => 'text',
        'text' => 'Email',
        'marks' => [],
        'link' => ['url' => 'mailto:test@example.com'],
    ]);

    expect($component->render())->toBe(
        '<a href="mailto:test@example.com" target="_blank" rel="noopener noreferrer">Email</a>'
    );
});

it('renders internal path URLs as same-site links without target blank', function () {
    $component = new Text([
        'type' => 'text',
        'text' => 'Go',
        'marks' => [],
        'link' => ['url' => '/some/page'],
    ]);

    expect($component->render())->toBe('<a href="/some/page">Go</a>');
});

it('does not treat protocol-relative URLs as internal paths', function () {
    $component = new Text([
        'type' => 'text',
        'text' => 'Go',
        'marks' => [],
        'link' => ['url' => '//evil.example'],
    ]);

    expect($component->render())->toBe('Go');
});

it('does not wrap page-only links until href is resolved', function () {
    $component = new Text([
        'type' => 'text',
        'text' => 'Home',
        'marks' => [],
        'link' => ['pageId' => 42],
    ]);

    expect($component->render())->toBe('Home');
});

it('escapes quotes in href', function () {
    $component = new Text([
        'type' => 'text',
        'text' => 'x',
        'marks' => [],
        'link' => ['url' => 'https://example.com/?a=1&b=2'],
    ]);

    expect($component->render())->toContain('https://example.com/?a=1&amp;b=2');
});
