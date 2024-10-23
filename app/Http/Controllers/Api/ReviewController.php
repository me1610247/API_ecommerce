<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(title="Review API", version="1.0")
 * @OA\Tag(name="Review", description="Review operations")
 */

/**
 * @OA\Schema(
 *     schema="Review",
 *     required={"id", "product_id", "user_id", "rating"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="product_id", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer", format="int64"),
 *     @OA\Property(property="rating", type="integer", format="int32", example=5),
 *     @OA\Property(property="comment", type="string"),
 * )
 */
class ReviewController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/reviews",
     *     tags={"Review & Feedback"},
     *     summary="Add a review",
     *     description="Submit a review for a product",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "rating"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="rating", type="integer", example=5),
     *             @OA\Property(property="comment", type="string", example="Great product!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review added successfully."),
     *             @OA\Property(property="review", ref="#/components/schemas/Review")
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
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id' => $request->user()->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review added successfully.',
            'review' => $review,
        ], 201);
    }
 /**
     * @OA\Get(
     *     path="/api/reviews/{productId}",
     *     tags={"Review & Feedback"},
     *     summary="Get reviews for a product",
     *     description="Retrieve all reviews for a specific product",
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reviews retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No Reviews Yet"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Review"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No reviews found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No Reviews Yet")
     *         )
     *     )
     * )
     */
    public function index(Request $request, $productId): JsonResponse
    {
        $reviews = Review::where('product_id', $productId)->with('user')->get();
        if($reviews->isEmpty()){
            return response()->json([
                'message'=>"No Reviews Yet"
            ]);
        }
        return response()->json($reviews);
    }
     /**
     * @OA\Put(
     *     path="/api/reviews/{id}",
     *     tags={"Review & Feedback"},
     *     summary="Update a review",
     *     description="Update the specified review",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", example=4),
     *             @OA\Property(property="comment", type="string", example="Updated comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review updated successfully."),
     *             @OA\Property(property="review", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review Not Found")
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
    public function update(Request $request, $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);
        if($reviews->isEmpty()){
            return response()->json([
                'message'=>"Review Not Found"
            ],404);
        }
        $review->update($request->only('rating', 'comment'));

        return response()->json([
            'message' => 'Review updated successfully.',
            'review' => $review,
        ]);
    }
     /**
     * @OA\Delete(
     *     path="/api/reviews/{id}",
     *     tags={"Review & Feedback"},
     *     summary="Delete a review",
     *     description="Remove the specified review",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review Not Found")
     *         )
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $review = Review::find($id);
        if(!$review){
            return response()->json([
                'message'=>"Review Not Found"
            ],404);
        }
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }
}
