<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->forUser(auth()->id())
            ->withCount('songs')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $request->user()->categories()->create([
            'name' => $request->validated('name'),
            'slug' => $this->uniqueSlug($request->validated('name'), $request->user()->id),
            'is_system' => false,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('status', 'category-created');
    }

    public function edit(Category $category): View
    {
        $this->authorizeCategory($category);

        return view('categories.edit', compact('category'));
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $category->update([
            'name' => $request->validated('name'),
            'slug' => $this->uniqueSlug($request->validated('name'), $category->user_id, $category->id),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('status', 'category-updated');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('status', 'category-deleted');
    }

    private function authorizeCategory(Category $category): void
    {
        abort_unless($category->isEditableBy(auth()->id()), 403);
    }

    private function uniqueSlug(string $name, int $userId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Category::query()
            ->where('user_id', $userId)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
