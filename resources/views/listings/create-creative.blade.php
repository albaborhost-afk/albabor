<x-app-layout>
    <div class="min-h-screen" style="background: linear-gradient(135deg, #F0F4F8 0%, #E8EEF3 100%);">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            {{-- Creative Hero Header --}}
            <div class="mb-10 relative overflow-hidden rounded-3xl p-8 md:p-12"
                 style="background: linear-gradient(135deg, #1B4F72 0%, #17A2B8 100%); box-shadow: 0 20px 60px rgba(27,79,114,0.3);">
                {{-- Decorative circles --}}
                <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full -ml-24 -mb-24"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl md:text-4xl font-black text-white">Créer une annonce</h1>
                            <p class="text-white/80 mt-1">Vendez votre bateau, jet-ski, moteur ou pièces en quelques clics</p>
                        </div>
                    </div>

                    {{-- Progress Steps --}}
                    <div class="flex items-center gap-2 mt-8 flex-wrap">
                        @foreach(['Catégorie', 'Détails', 'Prix', 'Photos'] as $step)
                            <div class="flex items-center gap-2">
                                <span class="px-4 py-2 rounded-full text-sm font-semibold bg-white/10 backdrop-blur-sm text-white border border-white/20">
                                    {{ $loop->iteration }}. {{ $step }}
                                </span>
                                @if(!$loop->last)
                                    <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-2xl p-5 mb-8 shadow-lg">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-red-900 mb-1">Erreurs de validation</h3>
                            <ul class="text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('listings.store') }}" method="POST" enctype="multipart/form-data"
                  x-data="listingForm()" class="space-y-8">
                @csrf

                {{-- STEP 1: Category Selection - CREATIVE CARDS --}}
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-white/50">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900">Choisissez une catégorie</h2>
                            <p class="text-gray-600">Sélectionnez le type d'article que vous souhaitez vendre</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach([
                            'boat' => ['label' => 'Bateau', 'img' => '/images/yacht.png', 'color' => 'from-blue-500 to-cyan-500', 'bg' => 'bg-blue-50'],
                            'jetski' => ['label' => 'Jet-ski', 'img' => '/images/jetski.png', 'color' => 'from-purple-500 to-pink-500', 'bg' => 'bg-purple-50'],
                            'engine' => ['label' => 'Moteur', 'img' => '/images/moteurs.png', 'color' => 'from-orange-500 to-red-500', 'bg' => 'bg-orange-50'],
                            'parts' => ['label' => 'Pièces', 'img' => '/images/pieces.png', 'color' => 'from-green-500 to-emerald-500', 'bg' => 'bg-green-50'],
                        ] as $value => $cat)
                            <label class="group relative cursor-pointer">
                                <input type="radio" name="category" value="{{ $value }}" x-model="category" class="peer sr-only" required>

                                {{-- Card --}}
                                <div class="relative p-6 rounded-2xl border-3 border-gray-200 bg-white transition-all duration-500
                                            hover:scale-105 hover:shadow-2xl hover:border-gray-300
                                            peer-checked:border-transparent peer-checked:shadow-2xl peer-checked:scale-105 peer-checked:-translate-y-2">

                                    {{-- Gradient overlay when selected --}}
                                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br {{ $cat['color'] }} opacity-0 peer-checked:opacity-10 transition-opacity duration-500"></div>

                                    {{-- Checkmark badge --}}
                                    <div class="absolute -top-3 -right-3 w-10 h-10 rounded-full bg-gradient-to-br {{ $cat['color'] }}
                                                flex items-center justify-center shadow-lg opacity-0 scale-0 transition-all duration-500
                                                peer-checked:opacity-100 peer-checked:scale-100 peer-checked:rotate-12">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>

                                    {{-- Icon container --}}
                                    <div class="relative mb-4 mx-auto w-20 h-20 rounded-2xl {{ $cat['bg'] }}
                                                flex items-center justify-center transition-all duration-500
                                                group-hover:scale-110 peer-checked:scale-110 peer-checked:{{ $cat['bg'] }}">
                                        <img src="{{ $cat['img'] }}" alt="{{ $cat['label'] }}" class="w-14 h-14 object-contain">
                                    </div>

                                    {{-- Label --}}
                                    <p class="text-center font-bold text-gray-700 transition-all duration-300
                                              peer-checked:text-lg peer-checked:bg-gradient-to-r peer-checked:{{ $cat['color'] }}
                                              peer-checked:bg-clip-text peer-checked:text-transparent">
                                        {{ $cat['label'] }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-6 p-4 rounded-xl bg-blue-50 border border-blue-200">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-blue-800">
                                <span class="font-bold">Tarifs :</span> Bateaux et Jet-skis : 5 000 DA • Moteurs et Pièces : gratuit avec abonnement
                            </p>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Basic Info - Show when category selected --}}
                <div x-show="category" x-transition.duration.500ms
                     class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-white/50">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900">Informations générales</h2>
                            <p class="text-gray-600">Décrivez votre article en détail</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- Title --}}
                        <div class="group">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Titre de l'annonce *</label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                   class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 bg-white
                                          focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100 focus:outline-none
                                          transition-all duration-300 text-gray-900 font-medium placeholder-gray-400"
                                   placeholder="Ex: Magnifique bateau de pêche 6m avec moteur Yamaha 150CV">
                        </div>

                        {{-- Description --}}
                        <div class="group">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Description *</label>
                            <textarea name="description" rows="5" required
                                      class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 bg-white
                                             focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100 focus:outline-none
                                             transition-all duration-300 text-gray-900 placeholder-gray-400"
                                      placeholder="Décrivez votre article : état, équipements, historique...">{{ old('description') }}</textarea>
                        </div>

                        {{-- Quick specs grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div x-show="category !== 'parts'">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Fabricant</label>
                                <input type="text" name="specs[general][fabricant]" value="{{ old('specs.general.fabricant') }}"
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100 focus:outline-none transition-all"
                                       placeholder="Ex: Yamaha, Mercury...">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Modèle</label>
                                <input type="text" name="specs[general][modele]" value="{{ old('specs.general.modele') }}"
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100 focus:outline-none transition-all"
                                       placeholder="Ex: F150, Bayliner...">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Price & Location --}}
                <div x-show="category" x-transition.duration.500ms
                     class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-white/50">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900">Prix & Localisation</h2>
                            <p class="text-gray-600">Définissez votre prix et où se trouve l'article</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- Currency --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3">Devise *</label>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach(['DZD' => '🇩🇿', 'EUR' => '🇪🇺'] as $curr => $flag)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="currency" value="{{ $curr }}" x-model="currency" class="peer sr-only" {{ $curr === 'DZD' ? 'required' : '' }}>
                                        <div class="p-5 rounded-2xl border-3 border-gray-200 bg-white text-center transition-all duration-300
                                                    hover:border-green-300 hover:shadow-lg
                                                    peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-xl peer-checked:scale-105">
                                            <span class="text-4xl block mb-2">{{ $flag }}</span>
                                            <span class="block font-black text-lg text-gray-900 peer-checked:text-green-600">{{ $curr }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Price --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Prix *</label>
                            <div class="relative">
                                <input type="number" name="price_dzd" value="{{ old('price_dzd') }}" required min="0"
                                       class="w-full pl-6 pr-24 py-5 rounded-2xl border-3 border-gray-200 bg-white
                                              focus:border-green-500 focus:ring-4 focus:ring-green-100 focus:outline-none
                                              transition-all text-2xl font-black text-gray-900">
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 text-2xl font-black text-green-600" x-text="currency === 'EUR' ? '€' : 'DA'">DA</div>
                            </div>
                        </div>

                        {{-- Wilaya --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Wilaya *</label>
                            <select name="wilaya" required
                                    class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 bg-white
                                           focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100 focus:outline-none
                                           transition-all font-medium">
                                <option value="">-- Choisir la wilaya --</option>
                                @foreach($wilayas as $code => $name)
                                    <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- STEP 4: Photos --}}
                <div x-show="category" x-transition.duration.500ms
                     class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-white/50">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900">Photos de l'article *</h2>
                            <p class="text-gray-600">Ajoutez jusqu'à 10 photos de qualité</p>
                        </div>
                    </div>

                    <label for="images" class="block cursor-pointer group">
                        <div class="border-3 border-dashed border-gray-300 rounded-3xl p-12 text-center
                                    hover:border-pink-400 hover:bg-pink-50 transition-all duration-300 group-hover:scale-[1.02]">
                            <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600
                                        flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Cliquez pour ajouter des photos</h3>
                            <p class="text-gray-600">ou glissez-déposez vos images ici</p>
                            <p class="text-sm text-gray-500 mt-4">JPEG, PNG, WebP • Max 5 Mo • 10 photos max</p>
                        </div>
                        <input id="images" name="images[]" type="file" class="sr-only" multiple accept="image/*" required>
                    </label>

                    <div id="imagePreview" class="mt-6 grid grid-cols-5 gap-4"></div>
                </div>

                {{-- Submit --}}
                <div x-show="category" x-transition.duration.500ms class="flex justify-between items-center pt-4">
                    <a href="{{ route('listings.my') }}"
                       class="px-8 py-4 rounded-2xl font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all duration-300 hover:scale-105">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-10 py-4 rounded-2xl font-black text-white bg-gradient-to-r from-cyan-500 to-blue-600
                                   hover:from-cyan-600 hover:to-blue-700 shadow-xl hover:shadow-2xl
                                   transition-all duration-300 hover:scale-105 hover:-translate-y-1">
                        Continuer vers le paiement →
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function listingForm() {
            return {
                category: '{{ old('category', '') }}',
                currency: '{{ old('currency', 'DZD') }}'
            }
        }

        document.getElementById('images').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            Array.from(e.target.files).slice(0, 10).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'aspect-square rounded-2xl overflow-hidden border-3 border-gray-200 hover:border-cyan-500 transition-all hover:scale-105';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        });
    </script>
</x-app-layout>
