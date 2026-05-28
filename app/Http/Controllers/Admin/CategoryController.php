<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'sort_order' => $category->sort_order,
            ]),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $slug = UniqueSlug::forModel(new Category, $request->string('name')->toString());

        Category::create([
            'name' => $request->string('name')->toString(),
            'slug' => $slug,
            'description' => $request->input('description'),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        return back()->with('success', 'Category created.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $name = $request->string('name')->toString();
        $slug = $category->slug;

        if ($name !== $category->name) {
            $slug = UniqueSlug::forModel(new Category, $name);
        }

        $category->update([
            'name' => $name,
            'slug' => $slug,
            'description' => $request->input('description'),
            'sort_order' => (int) $request->input('sort_order', $category->sort_order),
        ]);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->courses()->exists()) {
            return back()->with('error', 'Cannot delete a category that has courses.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
