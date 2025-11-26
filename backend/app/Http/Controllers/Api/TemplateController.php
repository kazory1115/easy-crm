<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    /**
     * 取得範本列表
     */
    public function index(Request $request)
    {
        $query = Template::with(['fields', 'creator']);

        // 搜尋
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // 類型篩選
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // 分類篩選
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // 只取得啟用的
        if ($request->get('status') === 'active') {
            $query->active();
        }

        // 排序
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 判斷是否需要分頁
        if ($request->get('paginate', true)) {
            $perPage = $request->get('per_page', 15);
            $templates = $query->paginate($perPage);
        } else {
            $templates = $query->get();
        }

        return response()->json($templates);
    }

    /**
     * 取得單一範本
     */
    public function show($id)
    {
        $template = Template::with(['fields', 'creator', 'updater'])
            ->findOrFail($id);

        return response()->json(['data' => $template]);
    }

    /**
     * 建立範本
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'type' => 'required|in:quote,invoice,general',
            'status' => 'nullable|in:active,inactive',
            'fields' => 'nullable|array',
            'fields.*.field_key' => 'required_with:fields|string|max:255',
            'fields.*.field_label' => 'required_with:fields|string|max:255',
            'fields.*.field_type' => 'required_with:fields|in:text,number,date,select,textarea',
            'fields.*.field_value' => 'nullable|string',
            'fields.*.field_options' => 'nullable|array',
            'fields.*.is_required' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // 建立範本
            $template = Template::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? null,
                'type' => $validated['type'],
                'status' => $validated['status'] ?? 'active',
                'created_by' => auth()->id(),
            ]);

            // 建立範本欄位
            if (isset($validated['fields'])) {
                foreach ($validated['fields'] as $index => $fieldData) {
                    TemplateField::create([
                        'template_id' => $template->id,
                        'field_key' => $fieldData['field_key'],
                        'field_label' => $fieldData['field_label'],
                        'field_type' => $fieldData['field_type'],
                        'field_value' => $fieldData['field_value'] ?? null,
                        'field_options' => $fieldData['field_options'] ?? null,
                        'sort_order' => $index + 1,
                        'is_required' => $fieldData['is_required'] ?? false,
                    ]);
                }
            }

            DB::commit();

            // 重新載入關聯資料
            $template->load('fields');

            return response()->json([
                'message' => '範本建立成功',
                'data' => $template,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '範本建立失敗',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 更新範本
     */
    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'type' => 'sometimes|required|in:quote,invoice,general',
            'status' => 'nullable|in:active,inactive',
            'fields' => 'nullable|array',
            'fields.*.field_key' => 'required_with:fields|string|max:255',
            'fields.*.field_label' => 'required_with:fields|string|max:255',
            'fields.*.field_type' => 'required_with:fields|in:text,number,date,select,textarea',
            'fields.*.field_value' => 'nullable|string',
            'fields.*.field_options' => 'nullable|array',
            'fields.*.is_required' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // 更新範本基本資料
            $template->fill(array_filter($validated, fn($key) => $key !== 'fields', ARRAY_FILTER_USE_KEY));
            $template->updated_by = auth()->id();
            $template->save();

            // 如果有傳入欄位，則更新欄位
            if (isset($validated['fields'])) {
                // 刪除舊的欄位
                $template->fields()->delete();

                // 建立新的欄位
                foreach ($validated['fields'] as $index => $fieldData) {
                    TemplateField::create([
                        'template_id' => $template->id,
                        'field_key' => $fieldData['field_key'],
                        'field_label' => $fieldData['field_label'],
                        'field_type' => $fieldData['field_type'],
                        'field_value' => $fieldData['field_value'] ?? null,
                        'field_options' => $fieldData['field_options'] ?? null,
                        'sort_order' => $index + 1,
                        'is_required' => $fieldData['is_required'] ?? false,
                    ]);
                }
            }

            DB::commit();

            // 重新載入關聯資料
            $template->load('fields');

            return response()->json([
                'message' => '範本更新成功',
                'data' => $template,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '範本更新失敗',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 刪除範本
     */
    public function destroy($id)
    {
        $template = Template::findOrFail($id);
        $template->delete();

        return response()->json([
            'message' => '範本刪除成功',
        ]);
    }
}
