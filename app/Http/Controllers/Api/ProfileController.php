<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['activeSubscription.plan', 'latestVerificationRequest']);

        $stats = [
            'listings_count' => $user->listings()->count(),
            'active_listings' => $user->listings()->where('status', 'active')->count(),
            'total_views' => $user->listings()->sum('views_count'),
            'total_favorites' => $user->listings()->sum('favorites_count'),
        ];

        return response()->json([
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk(config('filesystems.listing_disk', 'public'))->delete($user->profile_picture);
            }

            // Resize and store
            $image = Image::read($request->file('profile_picture'));
            $image->cover(400, 400);

            $filename = 'profile-pictures/' . uniqid() . '.jpg';
            Storage::disk(config('filesystems.listing_disk', 'public'))->put($filename, $image->toJpeg(quality: 85)->toString());

            $validated['profile_picture'] = $filename;
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user->fresh(),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.',
        ]);
    }

    public function submitVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->verification_status === 'verified') {
            return response()->json([
                'message' => 'Votre compte est déjà vérifié.',
            ], 422);
        }

        if ($user->verification_status === 'pending') {
            return response()->json([
                'message' => 'Une demande de vérification est déjà en cours.',
            ], 422);
        }

        $request->validate([
            'document' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $documentPath = $request->file('document')->store('verification-documents', config('filesystems.listing_disk', 'public'));

        VerificationRequest::create([
            'user_id' => $user->id,
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        $user->update(['verification_status' => 'pending']);

        return response()->json([
            'message' => 'Demande de vérification soumise avec succès.',
        ]);
    }

    public function upgradeToVendor(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->account_type === 'vendor') {
            return response()->json([
                'message' => 'Vous êtes déjà un vendeur.',
            ], 422);
        }

        $user->update(['account_type' => 'vendor']);

        return response()->json([
            'message' => 'Votre compte a été mis à niveau en vendeur.',
            'user' => $user->fresh(),
        ]);
    }

    public function updatePicture(Request $request): JsonResponse
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        // Delete old profile picture if exists
        if ($user->profile_picture) {
            Storage::disk(config('filesystems.listing_disk', 'public'))->delete($user->profile_picture);
        }

        // Resize and store
        $image = Image::read($request->file('profile_picture'));
        $image->cover(400, 400);

        $filename = 'profile-pictures/' . uniqid() . '.jpg';
        Storage::disk(config('filesystems.listing_disk', 'public'))->put($filename, $image->toJpeg(quality: 85)->toString());

        $user->update(['profile_picture' => $filename]);

        return response()->json([
            'message' => 'Photo de profil mise à jour.',
            'profile_picture_url' => $user->profile_picture_url,
        ]);
    }

    public function deletePicture(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk(config('filesystems.listing_disk', 'public'))->delete($user->profile_picture);
            $user->update(['profile_picture' => null]);
        }

        return response()->json([
            'message' => 'Photo de profil supprimée.',
        ]);
    }
}
