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

    {{-- Confidentialité : publier sous « Invité ». Le champ caché est toujours
         posté (une case décochée ne l'est pas), le bouton ne fait que le
         basculer — le réglage reste enregistrable sans JavaScript. --}}
    <div
        x-data="{ hideName: {{ old('hide_name', $user->hide_name) ? 'true' : 'false' }} }"
        class="rounded-xl p-4"
        style="background: #F7F9FC; border: 1px solid #E0E6ED;"
    >
        <input type="hidden" name="hide_name" :value="hideName ? 1 : 0" value="{{ old('hide_name', $user->hide_name) ? 1 : 0 }}">

        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-sm font-semibold flex items-center gap-2" style="color: #1B2A4A;">
                    <svg class="w-4 h-4 flex-shrink-0" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                    {{ __('messages.hide_name_label') }}
                </p>
                <p class="text-xs mt-1.5 leading-relaxed" style="color: #6B7B8D;">
                    {{ __('messages.hide_name_help') }}
                </p>
            </div>

            <button
                type="button"
                role="switch"
                :aria-checked="hideName ? 'true' : 'false'"
                aria-label="{{ __('messages.hide_name_label') }}"
                @click="hideName = !hideName"
                class="relative inline-flex flex-shrink-0 h-7 w-12 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                :style="hideName ? 'background: #17A2B8;' : 'background: #D3DCE6;'"
            >
                <span
                    class="inline-block h-5 w-5 mt-1 bg-white rounded-full shadow transform transition-transform duration-200"
                    :class="hideName ? 'translate-x-6' : 'translate-x-1'"
                ></span>
            </button>
        </div>

        <p
            x-show="hideName"
            x-cloak
            class="text-xs mt-3 rounded-lg px-3 py-2 leading-relaxed"
            style="background: rgba(23,162,184,0.08); color: #117A8B;"
        >
            {{ __('messages.hide_name_active_notice') }}
        </p>
    </div>
</div>
