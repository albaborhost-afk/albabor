<x-app-layout>
    <!-- Breadcrumb Bar -->
    <div style="background: #FFFFFF; border-bottom: 1px solid #E0E6ED;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('home') }}" style="color: #9BA8B7;" class="hover:opacity-80 transition-opacity flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Accueil
                </a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="{{ route('mediation.index') }}" style="color: #9BA8B7;" class="hover:opacity-80">Mediation</a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.contact_seller') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('messages.contact_seller') }}</h1>
                    <p class="mt-0.5 text-white/70">{{ __('messages.mediation_contact_info') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div style="background: #F0F4F8;" class="py-8 pb-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <ul class="list-disc list-inside text-sm" style="color: #E74C3C;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Listing Info -->
            <div class="bg-white rounded-2xl p-6 mb-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                <div class="flex items-start">
                    <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center" style="background: #F0F4F8;">
                        @if($listing->media->first())
                            <img src="{{ $listing->media->first()->thumbnail_url ?? $listing->media->first()->url }}"
                                 alt="" class="w-full h-full object-cover"
                                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="w-full h-full items-center justify-center" style="color: #C5D0DB; display: none;">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @else
                            <svg class="w-8 h-8" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @endif
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold" style="color: #1B2A4A;">{{ $listing->title }}</h3>
                        <p class="text-lg font-black mt-1" style="color: #1B4F72;">{{ $listing->formatted_price }}</p>
                        <p class="text-sm mt-1" style="color: #6B7B8D;">{{ __('messages.seller') }}: {{ $listing->user?->name ?? 'Vendeur' }}</p>
                    </div>
                </div>
            </div>

            <!-- Message Form -->
            <form action="{{ route('mediation.store', $listing) }}" method="POST" class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                @csrf

                <div class="mb-6">
                    <label for="message" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">{{ __('messages.your_message') }}</label>
                    <textarea name="message" id="message" rows="5" required
                              class="glass-input w-full py-3 px-4 rounded-xl"
                              placeholder="{{ __('messages.message_placeholder') }}">{{ old('message') }}</textarea>
                    <p class="text-sm mt-2" style="color: #9BA8B7;">{{ __('messages.mediation_message_info') }}</p>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('listings.show', $listing) }}" class="px-5 py-3 rounded-xl font-semibold transition-all" style="color: #6B7B8D; border: 1px solid #E0E6ED;">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="px-6 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-0.5" style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                        {{ __('messages.send_message') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
