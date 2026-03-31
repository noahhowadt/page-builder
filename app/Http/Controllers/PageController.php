<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = Page::query()->orderBy('id', 'asc')->get();

        return Inertia::render('pages/Index', [
            'pages' => $pages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePageRequest $request): RedirectResponse
    {
        Page::query()->create([
            'title' => $request->validated('title'),
            'slug' => $request->validated('slug'),
        ]);

        return redirect()->route('pages.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page)
    {
        return Inertia::render('pages/Edit', [
            'page' => $page,
            'structure' => $page->structure,
            'updateUrl' => route('pages.update', $page),
            'linkablePages' => Page::query()
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn (Page $p): array => ['id' => $p->id, 'title' => $p->title])
                ->values()
                ->all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $data = [];

        if ($request->has('title')) {
            $data['title'] = $request->validated('title');
        }
        if ($request->has('slug')) {
            $data['slug'] = $request->validated('slug');
        }
        if ($request->has('is_published')) {
            $isPublished = $request->boolean('is_published');
            $data['is_published'] = $isPublished;
            $data['published_at'] = $isPublished ? now() : null;
        }
        if ($request->has('structure')) {
            Log::info('structure', ['structure' => $request->getContent()]);
            $data['structure'] = $request->validated('structure');
        }

        $page->update($data);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('pages.index');
    }
}
