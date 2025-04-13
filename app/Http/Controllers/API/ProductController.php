<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $products = Product::with('category')->where('is_active', 1)->where('is_featured', 1)->get();
        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created product in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = new Product();
        $product->category_id = $request->category_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->is_active = $request->has('is_active') ? $request->is_active : true;
        $product->is_featured = $request->has('is_featured') ? $request->is_featured : false;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('products', $imageName, 'public');
            $product->image_path = $path;
        } else {
            // Set default image
            $product->image_path = 'products/default.png';
        }

        $product->save();

        return response()->json([
            'status' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified product.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified product in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update product fields
        if ($request->has('category_id')) $product->category_id = $request->category_id;
        if ($request->has('name')) $product->name = $request->name;
        if ($request->has('description')) $product->description = $request->description;
        if ($request->has('price')) $product->price = $request->price;
        if ($request->has('is_active')) $product->is_active = $request->is_active;
        if ($request->has('is_featured')) $product->is_featured = $request->is_featured;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists and is not the default image
            if ($product->image_path && $product->image_path !== 'products/default.png') {
                Storage::disk('public')->delete($product->image_path);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('products', $imageName, 'public');
            $product->image_path = $path;
        }

        $product->save();

        return response()->json([
            'status' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified product's image and set to default.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function removeImage($id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Delete image if it exists and is not the default image
        if ($product->image_path && $product->image_path !== 'products/default.png') {
            Storage::disk('public')->delete($product->image_path);
        }

        // Set to default image
        $product->image_path = 'products/default.png';
        $product->save();

        return response()->json([
            'status' => true,
            'message' => 'Product image removed successfully',
            'data' => $product
        ]);
    }

    /**
     * Deactivate the specified product by setting is_active to false.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->is_active = false;
        $product->save();

        return response()->json([
            'status' => true,
            'message' => 'Product deactivated successfully'
        ]);
    }

    /**
     * Get featured products.
     *
     * @return JsonResponse
     */
    public function getFeatured(): JsonResponse
    {
        $products = Product::where('is_featured', true)
            ->where('is_active', true)
            ->with('category')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    /**
     * Get products by category.
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getByCategory($categoryId): JsonResponse
    {
        $products = Product::where('category_id', $categoryId)
            ->where('is_active', true)
            ->with('category')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }
}