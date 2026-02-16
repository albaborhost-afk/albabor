<?php

namespace App\Http\Controllers;

use App\Models\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

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
            'phone' => 'required|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $validated['profile_picture'] = $path;
        }

        $user->update($validated);

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
