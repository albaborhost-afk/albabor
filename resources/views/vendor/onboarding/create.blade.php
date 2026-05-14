<x-app-layout>
    @php $title = 'Créer ma boutique — Albabor Pro'; @endphp

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, #F0F4F8 0%, #E8F2F7 100%);">
        <div class="max-w-2xl mx-auto">

            {{-- Header --}}
            <div class="text-center mb-8">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold tracking-wide mb-4" style="background: #E8F2F7; color: #2471A3;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    ALBABOR PRO
                </span>
                <h1 class="text-3xl sm:text-4xl font-bold mb-3" style="color: #1B2A4A;">Créez votre boutique</h1>
                <p class="text-base max-w-xl mx-auto" style="color: #6B7B8D;">Renseignez les informations de votre boutique. Vous pourrez les modifier à tout moment depuis votre espace vendeur.</p>
            </div>

            {{-- Erreurs globales --}}
            @if(session('error'))
                <div class="bg-white rounded-xl p-4 mb-6 flex items-start gap-3" style="box-shadow: 0 4px 16px rgba(231,76,60,0.12); border-left: 4px solid #E74C3C;">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #E74C3C;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-sm" style="color: #1B2A4A;">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-white rounded-xl p-4 mb-6" style="box-shadow: 0 4px 16px rgba(231,76,60,0.12); border-left: 4px solid #E74C3C;">
                    <p class="text-sm font-semibold mb-2" style="color: #E74C3C;">Veuillez corriger les erreurs suivantes :</p>
                    <ul class="list-disc list-inside text-sm space-y-1" style="color: #1B2A4A;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulaire --}}
            <form method="POST" action="{{ route('vendor.onboarding.store') }}" enctype="multipart/form-data"
                  x-data="{ logoName: '' }"
                  class="bg-white rounded-2xl p-6 sm:p-8 space-y-6" style="box-shadow: 0 8px 24px rgba(0,0,0,0.06);">
                @csrf

                {{-- Nom de la boutique --}}
                <div>
                    <label for="shop_name" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
                        Nom de la boutique <span style="color: #E74C3C;">*</span>
                    </label>
                    <input type="text" id="shop_name" name="shop_name" value="{{ old('shop_name') }}" required
                           placeholder="Ex : Pièces Marine Oran"
                           class="w-full px-4 py-3 rounded-xl text-sm transition-all focus:outline-none"
                           style="border: 1px solid #E0E6ED; color: #1B2A4A;">
                    @error('shop_name')
                        <p class="text-xs mt-1.5" style="color: #E74C3C;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Description</label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Présentez votre boutique, votre spécialité, votre expérience..."
                              class="w-full px-4 py-3 rounded-xl text-sm transition-all focus:outline-none resize-none"
                              style="border: 1px solid #E0E6ED; color: #1B2A4A;">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs mt-1.5" style="color: #E74C3C;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Wilaya --}}
                <div>
                    <label for="wilaya" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Wilaya</label>
                    <input type="text" id="wilaya" name="wilaya" value="{{ old('wilaya') }}"
                           placeholder="Ex : Oran"
                           class="w-full px-4 py-3 rounded-xl text-sm transition-all focus:outline-none"
                           style="border: 1px solid #E0E6ED; color: #1B2A4A;">
                    @error('wilaya')
                        <p class="text-xs mt-1.5" style="color: #E74C3C;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Téléphone & Email --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_phone" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Téléphone de contact</label>
                        <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}"
                               placeholder="Ex : 0550 00 00 00"
                               class="w-full px-4 py-3 rounded-xl text-sm transition-all focus:outline-none"
                               style="border: 1px solid #E0E6ED; color: #1B2A4A;">
                        @error('contact_phone')
                            <p class="text-xs mt-1.5" style="color: #E74C3C;">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="contact_email" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">E-mail de contact</label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email') }}"
                               placeholder="Ex : contact@maboutique.dz"
                               class="w-full px-4 py-3 rounded-xl text-sm transition-all focus:outline-none"
                               style="border: 1px solid #E0E6ED; color: #1B2A4A;">
                        @error('contact_email')
                            <p class="text-xs mt-1.5" style="color: #E74C3C;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Adresse --}}
                <div>
                    <label for="address" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Adresse</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}"
                           placeholder="Ex : 12 rue du Port, Oran"
                           class="w-full px-4 py-3 rounded-xl text-sm transition-all focus:outline-none"
                           style="border: 1px solid #E0E6ED; color: #1B2A4A;">
                    @error('address')
                        <p class="text-xs mt-1.5" style="color: #E74C3C;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Logo --}}
                <div>
                    <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Logo de la boutique</label>
                    <label for="logo" class="flex items-center gap-3 px-4 py-4 rounded-xl cursor-pointer transition-all"
                           style="border: 1px dashed #B9C6D2; background: #F7FAFC;">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: #E8F2F7;">
                            <svg class="w-5 h-5" style="color: #2471A3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium" style="color: #1B2A4A;" x-text="logoName || 'Choisir une image'"></p>
                            <p class="text-xs" style="color: #9BA8B7;">PNG ou JPG, 15 Mo maximum (optionnel)</p>
                        </div>
                        <input type="file" id="logo" name="logo" accept="image/*" class="hidden"
                               @change="logoName = $event.target.files.length ? $event.target.files[0].name : ''">
                    </label>
                    @error('logo')
                        <p class="text-xs mt-1.5" style="color: #E74C3C;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="pt-2 flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="flex-1 py-3 rounded-xl font-bold text-white transition-all inline-flex items-center justify-center gap-2"
                            style="background: linear-gradient(135deg, #17A2B8, #2471A3); box-shadow: 0 4px 12px rgba(36,113,163,0.3);">
                        Créer ma boutique
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </button>
                    <a href="{{ route('vendor.landing') }}" class="py-3 px-6 rounded-xl font-semibold text-center transition-all"
                       style="background: #F0F4F8; color: #6B7B8D;">
                        Annuler
                    </a>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
