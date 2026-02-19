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
                <a href="{{ route('listings.my') }}" style="color: #9BA8B7;" class="hover:opacity-80">Mes Annonces</a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.feature_listing') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #F39C12 0%, #E67E22 50%, #D35400 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.12);"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('messages.feature_listing') }}</h1>
                    <p class="mt-0.5 text-white/80">{{ __('messages.feature_description') }}</p>
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

            <!-- Listing Summary -->
            <div class="bg-white rounded-2xl p-6 mb-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                    <svg class="w-5 h-5" style="color: #F39C12;" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    {{ __('messages.listing_to_feature') }}
                </h2>
                <div class="flex items-start">
                    <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0" style="background: #F0F4F8;">
                        @if($listing->media->first())
                            <img src="{{ $listing->media->first()->thumbnail_url ?? $listing->media->first()->url }}"
                                 alt="" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="ml-4">
                        <h3 class="font-bold" style="color: #1B2A4A;">{{ $listing->title }}</h3>
                        <p class="text-sm" style="color: #6B7B8D;">{{ $listing->category_label }}{{ $listing->wilaya ? ' - ' . $listing->wilaya : '' }}</p>
                        <p class="text-lg font-black mt-1" style="color: #1B4F72;">{{ $listing->formatted_price }}</p>
                    </div>
                </div>
            </div>

            <!-- Feature Benefits -->
            <div class="rounded-2xl p-6 mb-6" style="background: rgba(243, 156, 18, 0.06); border: 1px solid rgba(243, 156, 18, 0.15);">
                <h3 class="font-bold mb-3" style="color: #1B2A4A;">{{ __('messages.feature_benefits') }}</h3>
                <ul class="space-y-3">
                    <li class="flex items-center" style="color: #1B2A4A;">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" style="color: #F39C12;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        {{ __('messages.benefit_top_results') }}
                    </li>
                    <li class="flex items-center" style="color: #1B2A4A;">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" style="color: #F39C12;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        {{ __('messages.benefit_highlighted') }}
                    </li>
                    <li class="flex items-center" style="color: #1B2A4A;">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" style="color: #F39C12;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        {{ __('messages.benefit_more_views') }}
                    </li>
                </ul>
            </div>

            <!-- Payment Amount -->
            <div class="rounded-2xl p-6 mb-6" style="background: rgba(23, 162, 184, 0.08); border: 1px solid rgba(23, 162, 184, 0.15);">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold" style="color: #1B2A4A;">{{ __('messages.feature_fee') }}</span>
                    <span class="text-2xl font-black" style="color: #1B4F72;">{{ number_format($amount, 0, ',', ' ') }} DA</span>
                </div>
                <p class="text-sm mt-2" style="color: #6B7B8D;">{{ __('messages.valid_30_days') }}</p>
            </div>

            <!-- Payment Form -->
            <form action="{{ route('payments.feature', $listing) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Payment Method -->
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ __('messages.payment_method') }}
                    </h2>

                    <div class="space-y-3">
                        <label class="flex items-center p-4 rounded-xl cursor-pointer transition-all" style="border: 1px solid #E0E6ED;">
                            <input type="radio" name="method" value="baridimob" required style="accent-color: #17A2B8;">
                            <img src="/images/baridimob.png" alt="BaridiMob" class="ml-3 h-8 w-auto object-contain">
                            <span class="ml-3">
                                <span class="block font-semibold" style="color: #1B2A4A;">BaridiMob</span>
                                <span class="block text-sm" style="color: #6B7B8D;">00799999002543569223</span>
                            </span>
                        </label>

                        <label class="flex items-center p-4 rounded-xl cursor-pointer transition-all" style="border: 1px solid #E0E6ED;">
                            <input type="radio" name="method" value="ccp" style="accent-color: #17A2B8;">
                            <img src="/images/ccp.png" alt="CCP" class="ml-3 h-8 w-auto object-contain">
                            <span class="ml-3">
                                <span class="block font-semibold" style="color: #1B2A4A;">CCP</span>
                                <span class="block text-sm" style="color: #6B7B8D;">0025435692 {{ __('messages.cle') }} 23</span>
                            </span>
                        </label>

                        <label class="flex items-center p-4 rounded-xl cursor-pointer transition-all" style="border: 1px solid #E0E6ED;">
                            <input type="radio" name="method" value="bank_transfer" style="accent-color: #17A2B8;">
                            <span class="ml-3">
                                <span class="block font-semibold" style="color: #1B2A4A;">{{ __('messages.bank_transfer') }}</span>
                                <span class="block text-sm" style="color: #6B7B8D;">RIB: 00123456789012345678901234</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Payment Proof -->
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ __('messages.payment_proof') }}
                    </h2>
                    <input type="file" name="proof" required accept="image/*"
                           class="block w-full text-sm rounded-xl file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:transition-colors" style="color: #6B7B8D; file:background: rgba(23, 162, 184, 0.1); file:color: #17A2B8;">
                </div>

                <!-- Submit -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('listings.my') }}" class="px-5 py-3 rounded-xl font-semibold transition-all" style="color: #6B7B8D; border: 1px solid #E0E6ED;">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-0.5" style="background: linear-gradient(135deg, #F39C12, #E67E22); box-shadow: 0 8px 25px rgba(243, 156, 18, 0.25);">
                        {{ __('messages.submit_payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
