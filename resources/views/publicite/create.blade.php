<x-app-layout
    :title="__('messages.banner_request_title')"
    :description="__('messages.banner_request_meta')"
>
    <!-- Breadcrumb -->
    <div style="background: #FFFFFF; border-bottom: 1px solid #E0E6ED;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('home') }}" style="color: #9BA8B7;" class="hover:opacity-80 transition-opacity flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ __('Accueil') }}
                </a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.banner_request_nav') }}</span>
            </nav>
        </div>
    </div>

    <!-- En-tête -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-12 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-4" style="background: rgba(255,255,255,0.18);">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">{{ __('messages.banner_request_title') }}</h1>
            <p class="mt-3 text-white/80 text-sm sm:text-base max-w-xl mx-auto">{{ __('messages.banner_request_intro') }}</p>
        </div>
    </div>

    <div style="background: #F0F4F8;" class="py-10 pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-5 rounded-2xl flex items-start gap-3" style="background: rgba(39,174,96,0.08); border: 1px solid rgba(39,174,96,0.25);">
                    <svg class="w-6 h-6 flex-shrink-0 mt-0.5" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold text-sm" style="color: #1E8449;">{{ __('messages.banner_request_sent') }}</p>
                        <p class="text-sm mt-1" style="color: #27AE60;">{{ __('messages.banner_request_sent_hint') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <ul class="list-disc list-inside text-sm space-y-1" style="color: #E74C3C;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Ce que l'annonceur obtient : sans cela, le formulaire arrive sans contexte. --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                @php
                    // Icônes explicites : œil (visibilité), appareils (site + appli),
                    // graphique (chiffres). Les tracés précédents dessinaient un
                    // simple point et une note de musique.
                    $perks = [
                        [
                            'icon'  => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z@@M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
                            'title' => __('messages.banner_perk_visibility_title'),
                            'text'  => __('messages.banner_perk_visibility_text'),
                        ],
                        [
                            'icon'  => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                            'title' => __('messages.banner_perk_audience_title'),
                            'text'  => __('messages.banner_perk_audience_text'),
                        ],
                        [
                            'icon'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            'title' => __('messages.banner_perk_stats_title'),
                            'text'  => __('messages.banner_perk_stats_text'),
                        ],
                    ];
                @endphp
                @foreach($perks as $perk)
                    <div class="bg-white rounded-2xl p-5" style="box-shadow: 0 4px 14px rgba(0,0,0,0.05);">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3" style="background: rgba(23,162,184,0.12);">
                            <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                @foreach(explode('@@', $perk['icon']) as $path)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                                @endforeach
                            </svg>
                        </div>
                        <p class="font-bold text-sm mb-1" style="color: #1B2A4A;">{{ $perk['title'] }}</p>
                        <p class="text-xs leading-relaxed" style="color: #6B7B8D;">{{ $perk['text'] }}</p>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('publicite.store') }}" method="POST" class="bg-white rounded-2xl p-6 sm:p-8" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                @csrf

                {{-- Piège à robots : masqué aux humains, jamais rempli par eux. --}}
                <div class="hidden" aria-hidden="true">
                    <label for="website">{{ __('Site web') }}</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>

                <h2 class="text-lg font-bold mb-1" style="color: #1B2A4A;">{{ __('messages.banner_request_form_title') }}</h2>
                <p class="text-sm mb-6" style="color: #6B7B8D;">{{ __('messages.banner_request_form_hint') }}</p>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_name" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
                                {{ __('messages.banner_field_contact') }} <span style="color: #E74C3C;">*</span>
                            </label>
                            <input type="text" name="contact_name" id="contact_name" required maxlength="255"
                                   value="{{ old('contact_name', auth()->user()?->name) }}"
                                   class="glass-input w-full py-3 px-4 rounded-xl">
                        </div>

                        <div>
                            <label for="company_name" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
                                {{ __('messages.banner_field_company') }}
                            </label>
                            <input type="text" name="company_name" id="company_name" maxlength="255"
                                   value="{{ old('company_name') }}"
                                   placeholder="{{ __('messages.banner_field_company_placeholder') }}"
                                   class="glass-input w-full py-3 px-4 rounded-xl">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
                                {{ __('messages.email') }} <span style="color: #E74C3C;">*</span>
                            </label>
                            <input type="email" name="email" id="email" required maxlength="255"
                                   value="{{ old('email', auth()->user()?->email) }}"
                                   placeholder="contact@exemple.com"
                                   class="glass-input w-full py-3 px-4 rounded-xl">
                        </div>

                        <div>
                            <label for="whatsapp" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
                                WhatsApp <span style="color: #E74C3C;">*</span>
                            </label>
                            <input type="tel" name="whatsapp" id="whatsapp" required
                                   value="{{ old('whatsapp', auth()->user()?->phone ? trim((auth()->user()->phone_country_code ?? '') . auth()->user()->phone) : '') }}"
                                   placeholder="+213 6 70 00 00 00"
                                   class="glass-input w-full py-3 px-4 rounded-xl">
                            <p class="text-xs mt-1.5" style="color: #9BA8B7;">{{ __('messages.banner_field_whatsapp_hint') }}</p>
                        </div>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
                            {{ __('messages.banner_field_message') }} <span style="color: #E74C3C;">*</span>
                        </label>
                        <textarea name="message" id="message" rows="5" required minlength="10" maxlength="2000"
                                  placeholder="{{ __('messages.banner_field_message_placeholder') }}"
                                  class="glass-input w-full py-3 px-4 rounded-xl resize-none">{{ old('message') }}</textarea>
                    </div>

                    <div>
                        <label for="budget_dzd" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
                            {{ __('messages.banner_field_budget') }}
                        </label>
                        <div class="relative">
                            <input type="number" name="budget_dzd" id="budget_dzd" min="0" max="100000000" step="1000"
                                   value="{{ old('budget_dzd') }}"
                                   placeholder="30000"
                                   class="glass-input w-full py-3 px-4 pr-14 rounded-xl">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold" style="color: #9BA8B7;">DA</span>
                        </div>
                        <p class="text-xs mt-1.5" style="color: #9BA8B7;">{{ __('messages.banner_field_budget_hint') }}</p>
                    </div>
                </div>

                <div class="mt-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-xs" style="color: #9BA8B7;">{{ __('messages.banner_request_privacy') }}</p>
                    <button type="submit"
                            class="px-7 py-3.5 rounded-xl font-bold text-white text-sm transition-all duration-300 transform hover:-translate-y-0.5 flex-shrink-0"
                            style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                        {{ __('messages.banner_request_submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
