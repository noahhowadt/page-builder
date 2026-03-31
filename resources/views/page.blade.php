@php
    $root = $structure ?? $page->structure;
    $blocks = ($root['type'] ?? null) === 'root'
        ? ($root['children'] ?? [])
        : (is_array($root) && array_is_list($root) ? $root : [$root]);
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $page->title }}</title>

    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
</head>

<body>
    @foreach ($blocks as $block)
        <x-dynamic-component :component="ucfirst($block['type'])" :block="$block" />
    @endforeach
</body>

</html>
