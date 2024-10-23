<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
/**
 * @OA\Info(title="Order Management API", version="1.0")
 * @OA\Tag(name="Order", description="Order management operations")
 */

/**
 * @OA\Schema(
 *     schema="OrderItem",
 *     required={"id", "user_id", "cart_items", "total_price", "address", "phone"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="cart_items", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="total_price", type="number", format="float"),
 *     @OA\Property(property="address", type="string"),
 *     @OA\Property(property="phone", type="string")
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Order"},
     *     summary="Create a new order",
     *     description="Place a new order for the items in the cart",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cart_id"},
     *             @OA\Property(property="cart_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order created successfully."),
     *             @OA\Property(property="order", ref="#/components/schemas/OrderItem")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Existing order found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have an existing order. Please complete or cancel it before placing a new order.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cart is empty",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart is empty.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'cart_id' => 'required|exists:carts,id',
        ]);

        $user = $request->user();
        $existingOrder = Order::where('user_id', $user->id)->first();
        if ($existingOrder) {
            return response()->json(['message' => 'You have an existing order. Please complete or cancel it before placing a new order.'], 400);
        }
        // Retrieve the cart items for the user
        $cartItems = Cart::where('user_id', $user->id)->get(['product_id', 'quantity','price']);
        
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 404);
        }

        // Calculate the total amount
        $totalAmount = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity; // Assuming price is in the cart
        });

        $order = Order::create([
            'user_id' => $user->id,
            'cart_items' => $cartItems, // You may want to convert this to JSON or another format
            'total_price' => $totalAmount,
            'address' => $user->address,
            'phone' => $user->phone,
        ]);

        return response()->json([
            'message' => 'Order created successfully.',
            'order' => $order,
        ], 201);
    }
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Order"},
     *     summary="Get user orders",
     *     description="Retrieve all orders placed by the user",
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/OrderItem")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No orders found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No orders found.")
     *         )
     *     )
     * )
     */
    public function getUserOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        // Retrieve all orders for the user
        $orders = Order::where('user_id', $user->id)->get();

        return response()->json([
            'orders' => $orders,
            'message' => 'Orders retrieved successfully.',
        ], 200);
    }
}
