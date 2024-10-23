<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
/**
 * @OA\Info(title="Wishlist API", version="1.0")
 * @OA\Tag(name="Wishlist", description="Wishlist operations")
 */

/**
 * @OA\Schema(
 *     schema="WishlistItem",
 *     required={"id", "user_id", "product_id"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="product_id", type="integer", format="int64"),
 * )
 */
class WishlistController extends Controller
{   
     /**
     * @OA\Post(
     *     path="/api/wishlist",
     *     tags={"Wishlist"},
     *     summary="Add item to wishlist",
     *     description="Add a product to the user's wishlist",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product added to wishlist successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product added to wishlist successfully."),
     *             @OA\Property(property="wishlist_item", ref="#/components/schemas/WishlistItem")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict error - product already in wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product already in wishlist."),
     *             @OA\Property(property="wishlist_item", ref="#/components/schemas/WishlistItem")
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
    public function addToWishlist(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404); // Not Found
        }
        // Ensure the product is not in the wishlist
        $existingWishlistItem = Wishlist::where('user_id', $user->id)
        ->where('product_id', $request->product_id)
        ->first();

        if ($existingWishlistItem) {
            return response()->json([
                'message' => 'Product already in wishlist.',
                'wishlist_item' => $existingWishlistItem,
            ], 409);
        }

        $wishlistItem = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'message' => 'Product added to wishlist successfully.',
            'wishlist_item' => $wishlistItem,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/wishlist",
     *     tags={"Wishlist"},
     *     summary="View wishlist",
     *     description="Retrieve the user's wishlist",
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="wishlist", type="array", @OA\Items(ref="#/components/schemas/WishlistItem"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wishlist is empty",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your wishlist is empty.")
     *         )
     *     )
     * )
     */
    public function viewWishlist(Request $request): JsonResponse
    {
        $user = $request->user();
        $wishlistItems = Wishlist::where('user_id', $user->id)->with('product')->get();
        if ($wishlistItems->isEmpty()) {
            return response()->json([
                'message' => 'Your wishlist is empty.',
            ], 404); // Not Found
        }
        return response()->json([
            'wishlist' => $wishlistItems,
        ], 200);
    }

     /**
     * @OA\Delete(
     *     path="/api/wishlist/{id}",
     *     tags={"Wishlist"},
     *     summary="Remove item from wishlist",
     *     description="Remove a product from the user's wishlist",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed from wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item removed from wishlist.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wishlist item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Wishlist item not found.")
     *         )
     *     )
     * )
     */
    public function removeFromWishlist($id): JsonResponse
    {
        $wishlistItem = Wishlist::findOrFail($id);
        $wishlistItem->delete();

        return response()->json([
            'message' => 'Item removed from wishlist.',
        ], 200);
    }
}
