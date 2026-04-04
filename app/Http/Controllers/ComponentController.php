<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComponentRequest;
use App\Http\Requests\UpdateComponentRequest;
use App\Models\Component;
use App\Models\Page;
use App\Support\PageStructureComponentResolver;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ComponentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $components = Component::query()->orderBy('id', 'asc')->get();

        return Inertia::render('components/Index', [
            'components' => $components,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreComponentRequest $request): RedirectResponse
    {
        Component::query()->create([
            'name' => $request->validated('name'),
        ]);

        return redirect()->route('components.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Component $component): Response
    {
        return Inertia::render('components/Edit', [
            'component' => $component,
            'structure' => PageStructureComponentResolver::resolve($component->structure ?? []),
            'updateUrl' => route('components.update', $component),
            'linkablePages' => Page::query()
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn (Page $p): array => ['id' => $p->id, 'title' => $p->title])
                ->values()
                ->all(),
            'libraryComponents' => Component::query()
                ->whereKeyNot($component->getKey())
                ->orderBy('name')
                ->get(['id', 'name', 'structure'])
                ->map(fn (Component $c): array => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'structure' => json_encode($c->structure),
                ])
                ->values()
                ->all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateComponentRequest $request, Component $component): RedirectResponse
    {
        $data = [];

        if ($request->has('name')) {
            $data['name'] = $request->validated('name');
        }
        if ($request->has('structure')) {
            $data['structure'] = $request->validated('structure');
        }

        $component->update($data);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Component $component): RedirectResponse
    {
        $component->delete();

        return redirect()->route('components.index');
    }
}
