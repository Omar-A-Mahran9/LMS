<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class TrashController extends Controller
{
    /**
     * Models that can be restored from the "exists but in the trash" validation link,
     * with the permission needed to restore each one.
     */
    private const RESTORABLE = [
        'Admin'               => ['model' => \App\Models\Admin::class, 'ability' => 'delete_admins'],
        'Category'            => ['model' => \App\Models\Category::class, 'ability' => 'delete_categories'],
        'CategorySubCategory' => ['model' => \App\Models\CategorySubCategory::class, 'ability' => 'delete_categories'],
        'Government'          => ['model' => \App\Models\Government::class, 'ability' => 'delete_governments'],
        'CommonQuestion'      => ['model' => \App\Models\CommonQuestion::class, 'ability' => 'delete_CommonQuestion'],
    ];

    public function index()
    {
        // There is no recycle bin page in the menu; go back to the dashboard.
        return redirect()->route('dashboard.index');
    }

    public function restore($modelName, $id)
    {
        abort_unless(isset(self::RESTORABLE[$modelName]), 404);

        $this->authorize(self::RESTORABLE[$modelName]['ability']);

        $model = self::RESTORABLE[$modelName]['model'];
        $model::onlyTrashed()->withoutGlobalScope('onlyParents')->findOrFail($id)->restore();

        return response(['message' => __('Item has been restored successfully')]);
    }
}
