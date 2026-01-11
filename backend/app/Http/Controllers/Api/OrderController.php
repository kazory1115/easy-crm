<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService; // Added OrderService
use App\Models\Order; // Added Order model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'creator', 'items', 'quote']);

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                    ->orWhere('customer_name', 'like', "%{$request->search}%")
                    ->orWhere('project_name', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     * This method is primarily for direct order creation (not from quote conversion).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:order_date',
            'project_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:1',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->orderService->createOrder(
                array_filter($validated, fn($key) => $key !== 'items', ARRAY_FILTER_USE_KEY),
                $validated['items'],
                auth()->id()
            );

            $order->load(['items', 'customer']);

            return response()->json([
                'message' => '訂單建立成功',
                'data' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => '訂單建立失敗', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'items', 'creator', 'updater', 'quote', 'logs.user']);
        return response()->json(['data' => $order]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'order_date' => 'sometimes|required|date',
            'due_date' => 'nullable|date|after_or_equal:order_date',
            'project_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:1',
            'status' => ['sometimes', 'required', Rule::in(['pending', 'confirmed', 'shipped', 'completed', 'cancelled'])],
            'payment_status' => ['sometimes', 'required', Rule::in(['unpaid', 'partially_paid', 'paid'])],
            'items' => 'sometimes|required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ]);

        try {
            $order = $this->orderService->updateOrder(
                $order,
                array_filter($validated, fn($key) => $key !== 'items', ARRAY_FILTER_USE_KEY),
                $validated['items'] ?? [],
                auth()->id()
            );

            $order->load(['items', 'customer']);

            return response()->json([
                'message' => '訂單更新成功',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => '訂單更新失敗', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        try {
            $order->delete();
            return response()->json(['message' => '訂單刪除成功']);
        } catch (\Exception $e) {
            return response()->json(['message' => '訂單刪除失敗', 'error' => $e->getMessage()], 500);
        }
    }
}