<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
/**
 * @OA\Info(title="Cart Management API", version="1.0")
 * @OA\Tag(name="Cart", description="Cart management operations")
 */

/**
 * @OA\Schema(
 *     schema="CartItem",
 *     required={"id", "user_id", "product_id", "quantity", "price"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="product_id", type="integer", format="int64"),
 *     @OA\Property(property="quantity", type="integer", format="int32"),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="product", ref="#/components/schemas/Product")
 * )
 */
class CartController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/cart",
     *     tags={"Cart"},
     *     summary="Add item to the cart",
     *     description="Add a product to the user's cart",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product added to cart successfully."),
     *             @OA\Property(property="cart", ref="#/components/schemas/CartItem")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found.")
     *         )
     *     )
     * )
     */
    public function addToCart(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $user = $request->user();
            $product = Product::findOrFail($request->product_id);

            // Check if the product is already in the user's cart
            $existingCartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingCartItem) {
                return response()->json([
                    'message' => 'Product is already in the cart.',
                    'cart' => $existingCartItem,
                ], 500);
            }

            // Create a new cart item
            $cartItem = Cart::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price * $request->quantity, // Set the price based on quantity
            ]);

            return response()->json([
                'message' => 'Product added to cart successfully.',
                'cart' => $cartItem,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/cart",
     *     tags={"Cart"},
     *     summary="View cart",
     *     description="Retrieve the user's cart items",
     *     @OA\Response(
     *         response=200,
     *         description="Cart items retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CartItem")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function viewCart(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

            return response()->json([
                'cart' => $cartItems,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the cart.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * @OA\Put(
     *     path="/api/cart/{cartId}",
     *     tags={"Cart"},
     *     summary="Update cart item quantity",
     *     description="Modify the quantity of an item in the user's cart",
     *     @OA\Parameter(
     *         name="cartId",
     *         in="path",
     *         required=true,
     *         description="ID of the cart item to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart updated successfully."),
     *             @OA\Property(property="cart", ref="#/components/schemas/CartItem")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cart item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart item not found.")
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
    public function updateCart(Request $request, $cartId): JsonResponse
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);
    
            // Get the authenticated user
            $user = $request->user();
    
            // Find the cart item by ID
            $cartItem = Cart::where('id', $cartId)->where('user_id', $user->id)->firstOrFail();
            
            // Find the product associated with the cart item
            $product = Product::findOrFail($cartItem->product_id);
    
            // Update the quantity and calculate the new price
            $cartItem->quantity = $request->quantity;
            $cartItem->price = $product->price * $request->quantity; // Update price based on new quantity
            $cartItem->save(); // Save changes
    
            return response()->json([
                'message' => 'Cart updated successfully.',
                'cart' => $cartItem,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found.',
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/cart/{cartId}",
     *     tags={"Cart"},
     *     summary="Remove item from the cart",
     *     description="Delete an item from the user's cart",
     *     @OA\Parameter(
     *         name="cartId",
     *         in="path",
     *         required=true,
     *         description="ID of the cart item to remove",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed from cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item removed from cart.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cart item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart item not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function removeFromCart($cartId): JsonResponse
    {
        try {
            $cartItem = Cart::findOrFail($cartId);
            $cartItem->delete();

            return response()->json([
                'message' => 'Item removed from cart.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
