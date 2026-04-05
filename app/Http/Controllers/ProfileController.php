<?php

namespace App\Http\Controllers;

use App\Models\VerificationRequest;
use App\Rules\AlgerianPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $user->load(['activeSubscription.plan', 'latestVerificationRequest']);

        $stats = [
            'listings_count' => $user->listings()->count(),
            'active_listings' => $user->listings()->where('status', 'active')->count(),
            'total_views' => $user->listings()->sum('views_count'),
            'total_favorites' => $user->listings()->sum('favorites_count'),
        ];

        return view('profile.show', compact('user', 'stats'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable', AlgerianPhoneNumber::nullable()],
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['phone'] = AlgerianPhoneNumber::normalize($validated['phone'] ?? null);

        if ($request->hasFile('profile_picture')) {
            $disk = config('filesystems.listing_disk', 'public');

            // Delete old file from storage
            if ($user->profile_picture) {
                Storage::disk($disk)->delete($user->profile_picture);
            }

            // Resize to 300x300 and store on S3/disk
            $image = Image::read($request->file('profile_picture'));
            $image->cover(300, 300);
            $filename = 'profile-pictures/' . $user->id . '_' . time() . '.jpg';
            Storage::disk($disk)->put($filename, $image->toJpeg(quality: 85)->toString());

            $validated['profile_picture'] = $filename;
            $validated['profile_picture_data'] = null; // clear any old base64
        }

        unset($validated['profile_picture']); // handled above
        if (isset($filename)) {
            $user->update(array_merge(
                array_intersect_key($validated, array_flip(['name', 'phone'])),
                ['profile_picture' => $filename, 'profile_picture_data' => null]
            ));
        } else {
            $user->update(array_intersect_key($validated, array_flip(['name', 'phone'])));
        }

        return redirect()->route('profile.show')
            ->with('success', __('messages.profile_updated'));
    }


    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')
            ->with('success', __('messages.password_updated'));
    }

    public function verificationForm()
    {
        $user = Auth::user();
        $pendingRequest = $user->verificationRequests()
            ->whereIn('status', ['pending'])
            ->first();

        return view('profile.verification', compact('user', 'pendingRequest'));
    }

    public function submitVerification(Request $request)
    {
        $user = Auth::user();

        // Check if already verified or has pending request
        if ($user->isVerified()) {
            return back()->with('error', __('messages.already_verified'));
        }

        $pendingRequest = $user->verificationRequests()
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return back()->with('error', __('messages.verification_pending'));
        }

        $validated = $request->validate([
            'document' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $documentPath = $request->file('document')->store('verification-documents', 'public');

        VerificationRequest::create([
            'user_id' => $user->id,
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        $user->update(['verification_status' => 'pending']);

        return redirect()->route('profile.show')
            ->with('success', __('messages.verification_submitted'));
    }

    public function upgradeToVendor()
    {
        $user = Auth::user();

        if ($user->isVendor()) {
            return back()->with('error', __('messages.already_vendor'));
        }

        return view('profile.upgrade-vendor', compact('user'));
    }

    public function confirmUpgradeToVendor(Request $request)
    {
        $user = Auth::user();

        if ($user->isVendor()) {
            return back()->with('error', __('messages.already_vendor'));
        }

        $user->update(['account_type' => 'vendor']);

        return redirect()->route('subscription.plans')
            ->with('success', __('messages.upgraded_to_vendor'));
    }
}
