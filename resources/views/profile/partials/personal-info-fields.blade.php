@props([
    'user',
    'phoneId' => 'phone_number',
])

<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
            {{ __('messages.name') }}
        </label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $user->name) }}"
            required
            class="glass-input w-full py-3 px-4 rounded-xl"
        >
    </div>

    <div>
        <label for="email" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
            {{ __('messages.email') }}
        </label>
        <input
            type="email"
            id="email"
            value="{{ $user->email }}"
            disabled
            class="w-full py-3 px-4 rounded-xl cursor-not-allowed"
            style="background: #F0F4F8; border: 1px solid #E0E6ED; color: #9BA8B7;"
        >
        <p class="text-xs mt-1" style="color: #9BA8B7;">
            {{ __('messages.public_email_notice') }}
        </p>
    </div>

    @include('profile.partials.phone-input', [
        'id' => $phoneId,
        'value' => old('phone', $user->phone),
    ])
</div>
