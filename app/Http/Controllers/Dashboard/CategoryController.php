<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CategorySubCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreCategoryRequest;
use App\Http\Requests\Dashboard\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_categories');
        $params = request()->all();

        if ($request->ajax()) {
            if ($request['type'] == 'parent') {
                $data = getModelData(model: new Category());
            } else {
                $data = getModelData(model: new CategorySubCategory(), relations: ['categories' => ['id', 'name_ar', 'name_en', 'description_ar', 'description_en', 'created_at']]);
            }
            return response()->json($data);
        }

        return view('dashboard.categories.index');
    }

public function store(StoreCategoryRequest $request)
{
    $this->authorize('create_categories');

    // Start with all relevant data except 'parent_id', 'category_type', and 'type'
    $data = $request->except('parent_id', 'category_type', 'type');

    // Upload image if exists and add to data
    if ($request->hasFile('image')) {
        $data['image'] = uploadImageToDirectory($request->file('image'), "Categories");
    }

    if ($request->type === 'parent') {
        // Create as a parent category
        Category::create($data);
    } else {
        // If subcategory, also set the parent_id from the request
        $data['parent_id'] = $request->parent_id;
        CategorySubCategory::create($data);
    }

    return response(["Category created successfully"]);
}


public function update(UpdateCategoryRequest $request, $id)
{
    $this->authorize('update_categories');

    $type = $request->input('type');
    $data = $request->except('parent_id', 'category_type', 'type');

    if ($request->has('image')) {
        $data['image'] = uploadImageToDirectory($request->file('image'), "Categories");
    }

    if ($type === 'category') {
        $category = Category::findOrFail($id);
        $category->update($data);
    } else {
        $subCategory = CategorySubCategory::findOrFail($id);
        $subCategory->update($data);
        $subCategory->categories()->sync($request['parent_id']);
    }

    return response(["Category updated successfully"]);
}


public function destroy(Request $request, $id)
{
    dd($request);

    $this->authorize('delete_categories');

    $category = Category::withTrashed()->find($id);
dd($category);
    if (!$category) {
        return response()->json(['message' => 'Category not found'], 404);
    }

    if (is_null($category->parent_id)) {
        // Delete subcategories if main category
        $category->children()->delete();
    }

    $category->delete();

    return response()->json(['message' => 'Category deleted successfully']);
}





    public function parentCategories()
    {
        $parentCategories = Category::whereNull('parent_id')->get();

        return response()->json([
            'parentCategories' => $parentCategories
        ]);
    }

    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_categories');

        CategorySubCategory::whereIn('id', $request->selected_items_ids)->delete();

        return response(["selected categories deleted successfully"]);
    }

    public function restoreSelected(Request $request)
    {
        $this->authorize('delete_categories');

        CategorySubCategory::withTrashed()->whereIn('id', $request->selected_items_ids)->restore();

        return response(["selected categories restored successfully"]);
    }
}
