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
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.payment') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #27AE60 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('messages.payment') }}</h1>
                    <p class="mt-0.5 text-white/70">{{ __('messages.complete_payment_to_publish') }}</p>
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
                    <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    {{ __('messages.listing_summary') }}
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

            <!-- Payment Amount -->
            <div class="rounded-2xl p-6 mb-6" style="background: rgba(23, 162, 184, 0.08); border: 1px solid rgba(23, 162, 184, 0.15);">
                <div class="flex items-center justify-between">
                    <span class="text-lg font-semibold" style="color: #1B2A4A;">{{ __('messages.publication_fee') }}</span>
                    <span class="text-2xl font-black" style="color: #1B4F72;">{{ number_format($amount, 0, ',', ' ') }} DA</span>
                </div>
                <p class="text-sm mt-2" style="color: #6B7B8D;">{{ __('messages.valid_365_days') }}</p>
            </div>

            <!-- Payment Form -->
            <form action="{{ route('payments.listing', $listing) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                        <label class="flex items-start p-4 rounded-xl cursor-pointer transition-all" style="border: 1px solid #E0E6ED;">
                            <input type="radio" name="method" value="baridimob" {{ old('method') == 'baridimob' ? 'checked' : '' }} required
                                   style="accent-color: #17A2B8; margin-top: 3px;">
                            <img src="/images/baridimob.png" alt="BaridiMob" class="ml-3 h-8 w-auto object-contain flex-shrink-0">
                            <span class="ml-3">
                                <span class="block font-semibold" style="color: #1B2A4A;">BaridiMob</span>
                                <span class="block text-sm" style="color: #6B7B8D;">{{ __('messages.baridimob_number') }}: <span class="font-mono">00799999002543569223</span></span>
                                <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Titulaire : DJAMAA BILEL</span>
                            </span>
                        </label>

                        <label class="flex items-start p-4 rounded-xl cursor-pointer transition-all" style="border: 1px solid #E0E6ED;">
                            <input type="radio" name="method" value="bank_transfer" {{ old('method') == 'bank_transfer' ? 'checked' : '' }}
                                   style="accent-color: #17A2B8; margin-top: 3px;">
                            <img src="/images/bea.png" alt="BEA" class="ml-3 h-8 w-auto object-contain flex-shrink-0">
                            <span class="ml-3">
                                <span class="block font-semibold" style="color: #1B2A4A;">BEA – Banque Extérieure d'Algérie</span>
                                <span class="block text-sm" style="color: #6B7B8D;">🇩🇿 RIB : <span class="font-mono">00200090090220206690</span></span>
                                <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Titulaire : DJAMAA BILEL</span>
                            </span>
                        </label>

                        <label class="flex items-start p-4 rounded-xl cursor-pointer transition-all" style="border: 1px solid #E0E6ED;">
                            <input type="radio" name="method" value="paypal" {{ old('method') == 'paypal' ? 'checked' : '' }}
                                   style="accent-color: #17A2B8; margin-top: 3px;">
                            <img src="/images/paypal.png" alt="PayPal" class="ml-3 h-8 w-auto object-contain flex-shrink-0">
                            <span class="ml-3">
                                <span class="block font-semibold" style="color: #1B2A4A;">PayPal</span>
                                <span class="block text-sm" style="color: #6B7B8D;">albabordz@gmail.com</span>
                                <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Titulaire : DJAMAA BILEL</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Payment Proof -->
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <h2 class="text-lg font-bold mb-2 flex items-center gap-2" style="color: #1B2A4A;">
                        <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ __('messages.payment_proof') }}
                    </h2>
                    <p class="text-sm mb-4" style="color: #6B7B8D;">{{ __('messages.upload_payment_proof') }}</p>

                    <div class="flex justify-center px-6 pt-5 pb-6 rounded-xl transition-colors" style="border: 2px dashed #E0E6ED; background: #F0F4F8;">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-12 w-12" style="color: #9BA8B7;" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm" style="color: #6B7B8D;">
                                <label for="proof" class="relative cursor-pointer rounded-md font-semibold" style="color: #17A2B8;">
                                    <span>{{ __('messages.upload_proof') }}</span>
                                    <input id="proof" name="proof" type="file" class="sr-only" accept="image/*" required>
                                </label>
                            </div>
                            <p class="text-xs" style="color: #9BA8B7;">{{ __('messages.proof_requirements') }}</p>
                        </div>
                    </div>
                    <div id="proofPreview" class="mt-4 hidden">
                        <img id="proofImage" src="" alt="" class="max-h-48 mx-auto rounded-xl">
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('listings.my') }}" class="px-5 py-3 rounded-xl font-semibold transition-all" style="color: #6B7B8D; border: 1px solid #E0E6ED;">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="px-6 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-0.5" style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                        {{ __('messages.submit_payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('proof').addEventListener('change', function(e) {
            const preview = document.getElementById('proofPreview');
            const image = document.getElementById('proofImage');

            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    image.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</x-app-layout>
