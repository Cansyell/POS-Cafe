<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends Controller
{
    /**
     * Display a listing of order items.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $orderItems = OrderItem::with(['order', 'product'])->get();
        return response()->json([
            'status' => true,
            'data' => $orderItems
        ]);
    }

    /**
     * Store a newly created order item in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $orderItem = OrderItem::create($request->all());
        
        return response()->json([
            'status' => true,
            'message' => 'Order item created successfully',
            'data' => $orderItem
        ], 201);
    }

    /**
     * Display the specified order item.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $orderItem = OrderItem::with(['order', 'product'])->find($id);
        
        if (!$orderItem) {
            return response()->json([
                'status' => false,
                'message' => 'Order item not found'
            ], 404);
        }
        
        return response()->json([
            'status' => true,
            'data' => $orderItem
        ]);
    }

    /**
     * Update the specified order item in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $orderItem = OrderItem::find($id);
        
        if (!$orderItem) {
            return response()->json([
                'status' => false,
                'message' => 'Order item not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'order_id' => 'exists:orders,id',
            'product_id' => 'exists:products,id',
            'quantity' => 'integer|min:1',
            'unit_price' => 'numeric|min:0',
            'subtotal' => 'numeric|min:0',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $orderItem->update($request->all());
        
        return response()->json([
            'status' => true,
            'message' => 'Order item updated successfully',
            'data' => $orderItem
        ]);
    }

    /**
     * Remove the specified order item from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $orderItem = OrderItem::find($id);
        
        if (!$orderItem) {
            return response()->json([
                'status' => false,
                'message' => 'Order item not found'
            ], 404);
        }
        
        // Check if the order is in a status that allows item modifications
        $order = Order::find($orderItem->order_id);
        if ($order && in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot remove items from completed or cancelled orders'
            ], 400);
        }
        
        $orderItem->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Order item deleted successfully'
        ]);
    }

    /**
     * Get order items by order ID.
     *
     * @param int $orderId
     * @return JsonResponse
     */
    public function getByOrder($orderId): JsonResponse
    {
        $order = Order::find($orderId);
        
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        $orderItems = OrderItem::where('order_id', $orderId)
            ->with('product')
            ->get();
        
        return response()->json([
            'status' => true,
            'data' => $orderItems
        ]);
    }

    /**
     * Update quantity of a specific order item.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateQuantity(Request $request, $id): JsonResponse
    {
        $orderItem = OrderItem::find($id);
        
        if (!$orderItem) {
            return response()->json([
                'status' => false,
                'message' => 'Order item not found'
            ], 404);
        }
        
        // Check if the order is in a status that allows item modifications
        $order = Order::find($orderItem->order_id);
        if ($order && in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot modify items in completed or cancelled orders'
            ], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $orderItem->quantity = $request->quantity;
        $orderItem->subtotal = $orderItem->unit_price * $request->quantity;
        $orderItem->save();
        
        return response()->json([
            'status' => true,
            'message' => 'Order item quantity updated successfully',
            'data' => $orderItem
        ]);
    }

    /**
     * Add multiple items to an order at once.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkAdd(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($request->order_id);
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot add items to completed or cancelled orders'
            ], 400);
        }

        $addedItems = [];
        
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $request->order_id;
                $orderItem->product_id = $item['product_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->unit_price = $product->price;
                $orderItem->subtotal = $product->price * $item['quantity'];
                $orderItem->notes = $item['notes'] ?? null;
                $orderItem->save();
                
                $addedItems[] = $orderItem;
            }
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Order items added successfully',
            'data' => $addedItems
        ], 201);
    }
}