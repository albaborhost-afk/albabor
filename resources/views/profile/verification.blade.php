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
                <a href="{{ route('profile.show') }}" style="color: #9BA8B7;" class="hover:opacity-80">Mon Profil</a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.get_verified') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #27AE60 0%, #2ECC71 50%, #1ABC9C 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.12);"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('messages.get_verified') }}</h1>
                    <p class="mt-0.5 text-white/80">{{ __('messages.verification_description') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div style="background: #F0F4F8;" class="py-8 pb-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #E74C3C;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-medium" style="color: #E74C3C;">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <ul class="list-disc list-inside text-sm" style="color: #E74C3C;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($user->isVerified())
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <div class="flex items-center">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: rgba(39, 174, 96, 0.1);">
                            <svg class="w-10 h-10" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold" style="color: #27AE60;">{{ __('messages.already_verified') }}</h3>
                            <p style="color: #27AE60;">{{ __('messages.verification_badge_active') }}</p>
                        </div>
                    </div>
                </div>
            @elseif($pendingRequest)
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <div class="flex items-center">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: rgba(243, 156, 18, 0.1);">
                            <svg class="w-10 h-10" style="color: #F39C12;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold" style="color: #F39C12;">{{ __('messages.verification_pending') }}</h3>
                            <p style="color: #6B7B8D;">{{ __('messages.verification_in_review') }}</p>
                            <p class="text-sm mt-1" style="color: #9BA8B7;">{{ __('messages.submitted_on') }} {{ $pendingRequest->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            @else
                <!-- Benefits -->
                <div class="bg-white rounded-2xl p-6 mb-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <h3 class="font-bold mb-3" style="color: #1B4F72;">{{ __('messages.verification_benefits') }}</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center" style="color: #1B2A4A;">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" style="color: #17A2B8;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            {{ __('messages.benefit_trust_badge') }}
                        </li>
                        <li class="flex items-center" style="color: #1B2A4A;">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" style="color: #17A2B8;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            {{ __('messages.benefit_buyer_confidence') }}
                        </li>
                        <li class="flex items-center" style="color: #1B2A4A;">
                            <svg class="w-5 h-5 mr-3 flex-shrink-0" style="color: #17A2B8;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            {{ __('messages.benefit_priority_support') }}
                        </li>
                    </ul>
                </div>

                <!-- Upload Form -->
                <form action="{{ route('profile.verification.submit') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    @csrf

                    <h2 class="text-lg font-bold mb-2 flex items-center gap-2" style="color: #1B2A4A;">
                        <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        {{ __('messages.upload_id_document') }}
                    </h2>
                    <p class="text-sm mb-4" style="color: #6B7B8D;">{{ __('messages.id_document_info') }}</p>

                    <div class="flex justify-center px-6 pt-5 pb-6 rounded-xl transition-colors cursor-pointer" style="border: 2px dashed #E0E6ED; background: #F0F4F8;">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-12 w-12" style="color: #9BA8B7;" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm" style="color: #6B7B8D;">
                                <label for="document" class="relative cursor-pointer rounded-md font-semibold" style="color: #17A2B8;">
                                    <span>{{ __('messages.upload_document') }}</span>
                                    <input id="document" name="document" type="file" class="sr-only" accept="image/*" required>
                                </label>
                            </div>
                            <p class="text-xs" style="color: #9BA8B7;">{{ __('messages.document_requirements') }}</p>
                        </div>
                    </div>

                    <div id="documentPreview" class="mt-4 hidden">
                        <img id="documentImage" src="" alt="" class="max-h-48 mx-auto rounded-xl">
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('profile.show') }}" class="px-5 py-3 rounded-xl font-semibold transition-all" style="color: #6B7B8D; border: 1px solid #E0E6ED;">
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="px-6 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-0.5" style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                            {{ __('messages.submit_verification') }}
                        </button>
                    </div>
                </form>
            @endif

            <div class="mt-6">
                <a href="{{ route('profile.show') }}" class="inline-flex items-center text-sm font-medium" style="color: #6B7B8D;">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('messages.back_to_profile') }}
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('document')?.addEventListener('change', function(e) {
            const preview = document.getElementById('documentPreview');
            const image = document.getElementById('documentImage');

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
