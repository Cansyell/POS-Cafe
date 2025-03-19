<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $orders = Order::with('user')->get();
        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }

    /**
     * Store a newly created order in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'table' => 'nullable|string|max:255',
            'order_type' => 'required|in:dine_in,takeaway,delivery',
            'status' => 'required|in:pending,preparing,ready,completed,cancelled',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount' => 'numeric|min:0',
            'total' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate unique order number
        $orderData = $request->all();
        $orderData['order_number'] = 'ORD-' . strtoupper(Str::random(8));

        $order = Order::create($orderData);
        
        return response()->json([
            'status' => true,
            'message' => 'Order created successfully',
            'data' => $order
        ], 201);
    }

    /**
     * Display the specified order.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $order = Order::with('user')->find($id);
        
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        return response()->json([
            'status' => true,
            'data' => $order
        ]);
    }

    /**
     * Update the specified order in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $order = Order::find($id);
        
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'exists:users,id',
            'table' => 'nullable|string|max:255',
            'order_type' => 'in:dine_in,takeaway,delivery',
            'status' => 'in:pending,preparing,ready,completed,cancelled',
            'subtotal' => 'numeric|min:0',
            'tax' => 'numeric|min:0',
            'discount' => 'numeric|min:0',
            'total' => 'numeric|min:0',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $order->update($request->all());
        
        return response()->json([
            'status' => true,
            'message' => 'Order updated successfully',
            'data' => $order
        ]);
    }

    /**
     * Update the status of the specified order.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $order = Order::find($id);
        
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,preparing,ready,completed,cancelled',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $order->status = $request->status;
        $order->save();
        
        return response()->json([
            'status' => true,
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

    /**
     * Cancel the specified order.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel($id): JsonResponse
    {
        $order = Order::find($id);
        
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        if ($order->status === 'completed') {
            return response()->json([
                'status' => false,
                'message' => 'Completed orders cannot be cancelled'
            ], 400);
        }
        
        $order->status = 'cancelled';
        $order->save();
        
        return response()->json([
            'status' => true,
            'message' => 'Order cancelled successfully',
            'data' => $order
        ]);
    }

    /**
     * Get orders by status.
     *
     * @param string $status
     * @return JsonResponse
     */
    public function getByStatus($status): JsonResponse
    {
        $validator = Validator::make(['status' => $status], [
            'status' => 'required|in:pending,preparing,ready,completed,cancelled',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid status parameter',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $orders = Order::where('status', $status)->with('user')->get();
        
        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }
}