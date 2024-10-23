<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * @OA\Info(title="User Profile API", version="1.0")
 * @OA\Tag(name="Profile", description="User profile operations")
 */

/**
 * @OA\Schema(
 *     schema="UserProfile",
 *     required={"id", "name", "email", "address", "phone"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="address", type="string"),
 *     @OA\Property(property="phone", type="string")
 * )
 */
class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="View user profile",
     *     description="Retrieve the user's profile information",
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserProfile"),
     *             @OA\Property(property="message", type="string", example="Profile retrieved successfully")
     *         )
     *     )
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'data' => $user,
            'message' => 'Profile retrieved successfully',
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="Update user profile",
     *     description="Update the user's profile information",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="phone", type="string", example="123-456-7890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserProfile"),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully")
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
     *         response=500,
     *         description="Profile update failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile Update Failed")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:15',
        ]);
        $user->update($request->only('name', 'email', 'address', 'phone'));
        if(!$user){
            return response()->json([
                'message' => 'Profile Update Failed',
            ], 500);
        }
        return response()->json([
            'data' => $user,
            'message' => 'Profile updated successfully',
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/profile/password",
     *     tags={"Profile"},
     *     summary="Change user password",
     *     description="Change the user's current password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password"},
     *             @OA\Property(property="current_password", type="string", example="old_password123"),
     *             @OA\Property(property="new_password", type="string", example="new_password123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="new_password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password changed successfully")
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
     *         response=403,
     *         description="Incorrect current password",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The current password is incorrect.")
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|confirmed|min:6',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update(['password' => Hash::make($request->new_password)]);
        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }
}
