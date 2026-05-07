<x-app-layout>
    <div class="min-h-screen" style="background-color: #F0F4F8;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            @php
                $startStep = 1;
                if ($errors->any()) {
                    if ($errors->has('new_images') || $errors->has('video_url') || $errors->has('cover_image_id') || $errors->has('delete_images')) $startStep = 6;
                    elseif ($errors->hasAny(['numero_whatsapp', 'numero_mobile', 'contact_email', 'mediation_enabled'])) $startStep = 5;
                    elseif ($errors->hasAny(['wilaya', 'pays', 'visible_a', 'price_dzd', 'currency', 'type_offre', 'etat', 'remarque_echange'])) $startStep = 4;
                    elseif ($errors->hasAny(['category', 'type'])) $startStep = 1;
                    else $startStep = 2;
                }
                $specs = $listing->specs ?? [];
            @endphp

            @if($errors->any())
                <div class="bg-white rounded-2xl p-4 mb-6" style="border: 1px solid rgba(231, 76, 60, 0.3); box-shadow: 0 4px 12px rgba(231,76,60,0.08);">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 shrink-0" style="color: #E74C3C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <ul class="text-sm space-y-1" style="color: #E74C3C;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($listing->status === 'rejected' && $listing->rejection_reason)
                <div class="bg-white rounded-2xl p-5 mb-6" style="border: 1.5px solid rgba(231,76,60,0.3); box-shadow: 0 4px 15px rgba(231,76,60,0.08);">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgba(231,76,60,0.1);">
                            <svg class="w-5 h-5" style="color: #E74C3C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold" style="color: #E74C3C;">Votre annonce a ete refusee</h3>
                            <p class="text-sm mt-1" style="color: #6B7B8D;">{{ $listing->rejection_reason }}</p>
                            <p class="text-xs mt-2" style="color: #9BA8B7;">Corrigez les problemes ci-dessus puis enregistrez pour soumettre a nouveau votre annonce.</p>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('listings.update', $listing) }}" method="POST" enctype="multipart/form-data"
                  class="create-form"
                  x-data="editForm()" novalidate
                  @submit="submitForm($event)"
                  @photos-processing.window="photosProcessing = true"
                  @photos-ready.window="photosProcessing = false">
                @csrf
                @method('PUT')

                <style>
                    /* ── Unified Design System ── */
                    .create-form .field-label,
                    .create-form .space-y-4 > div > label,
                    .create-form .grid > div > label {
                        font-family: 'Inter', sans-serif;
                        font-size: 11px;
                        font-weight: 600;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        color: #6B7B8D;
                        margin-bottom: 6px;
                        display: block;
                    }
                    .create-form input[type="text"],
                    .create-form input[type="number"],
                    .create-form input[type="email"],
                    .create-form input[type="url"],
                    .create-form input[type="tel"],
                    .create-form select,
                    .create-form textarea {
                        font-family: 'Inter', sans-serif;
                        font-size: 14px;
                        font-weight: 400;
                        color: #1B2A4A;
                        background: #F8FAFC;
                        border: 1.5px solid #E0E6ED;
                        border-radius: 12px;
                        padding: 10px 14px;
                        width: 100%;
                        transition: all 0.2s ease;
                        outline: none;
                    }
                    .create-form input:focus,
                    .create-form select:focus,
                    .create-form textarea:focus {
                        border-color: #17A2B8;
                        box-shadow: 0 0 0 3px rgba(23,162,184,0.1);
                        background: white;
                    }
                    .create-form input::placeholder,
                    .create-form textarea::placeholder {
                        color: #B0BEC5;
                        font-weight: 400;
                    }
                    .create-form h2, .create-form h3,
                    .create-form .section-title {
                        font-family: 'Inter', sans-serif;
                        color: #1B2A4A;
                    }
                    .create-form p, .create-form span {
                        font-family: 'Inter', sans-serif;
                    }

                    input[name="currency"]:checked + div {
                        border-color: #27AE60 !important;
                        background: linear-gradient(135deg, #E8F8F5 0%, #D5F4E6 100%) !important;
                        box-shadow: 0 0 0 4px rgba(39,174,96,0.2), 0 8px 20px rgba(39,174,96,0.35) !important;
                        transform: translateY(-3px) !important;
                    }
                    input[name="currency"]:checked + div .checkmark-icon { opacity: 1 !important; transform: scale(1) !important; }
                    input[name="currency"]:checked + div .currency-text { font-weight: 800 !important; color: #27AE60 !important; }
                    input[name="type_offre"]:checked + span {
                        background: linear-gradient(135deg, #1B4F72, #17A2B8) !important;
                        color: white !important; border-color: transparent !important;
                        box-shadow: 0 4px 15px rgba(23,162,184,0.4) !important; transform: scale(1.05) !important;
                    }
                    input[name="type_offre"]:checked + span svg { opacity: 1 !important; }
                    input[name="etat"]:checked + div {
                        background: linear-gradient(135deg, #1B4F72, #17A2B8) !important;
                        color: white !important; border-color: transparent !important;
                        box-shadow: 0 4px 15px rgba(23,162,184,0.4) !important; transform: scale(1.05) !important;
                    }
                    input[name="etat"]:checked + div svg { opacity: 1 !important; }
                    input[name="remarque_echange"][value="accepte"]:checked + span {
                        background: #27AE60 !important; color: white !important;
                        border-color: #27AE60 !important; box-shadow: 0 4px 15px rgba(39,174,96,0.4) !important; transform: scale(1.05) !important;
                    }
                    input[name="remarque_echange"][value="refuse"]:checked + span {
                        background: #E74C3C !important; color: white !important;
                        border-color: #E74C3C !important; box-shadow: 0 4px 15px rgba(231,76,60,0.4) !important; transform: scale(1.05) !important;
                    }
                    input[name="remarque_echange"]:checked + span svg { opacity: 1 !important; }
                    input[type="checkbox"][name^="specs"]:checked + span {
                        border-color: #17A2B8 !important; background: rgba(23,162,184,0.15) !important;
                        color: #17A2B8 !important; font-weight: 700 !important; box-shadow: 0 2px 8px rgba(23,162,184,0.2) !important;
                    }
                    .category-card-boat, .category-card-jetski, .category-card-engine, .category-card-parts {
                        padding: 1rem; border: 3px solid #E0E6ED; border-radius: 1rem;
                        text-align: center; background: white; transition: all 0.3s ease;
                    }
                    .category-card-boat:hover, .category-card-jetski:hover, .category-card-engine:hover, .category-card-parts:hover {
                        border-color: #17A2B8; box-shadow: 0 4px 12px rgba(23,162,184,0.15);
                    }
                    input[name="category"][value="boat"]:checked ~ .category-card-boat,
                    input[name="category"][value="jetski"]:checked ~ .category-card-jetski,
                    input[name="category"][value="engine"]:checked ~ .category-card-engine,
                    input[name="category"][value="parts"]:checked ~ .category-card-parts {
                        border-color: #17A2B8 !important;
                        background: linear-gradient(135deg, #E8F8FA 0%, #D4F1F4 100%) !important;
                        box-shadow: 0 0 0 4px rgba(23,162,184,0.15), 0 8px 25px rgba(23,162,184,0.3) !important;
                        transform: translateY(-4px) !important;
                    }
                    input[name="category"]:checked ~ div .checkmark { display: flex !important; }
                    input[name="category"]:checked ~ div .icon-bg { background: rgba(23,162,184,0.2) !important; }
                    input[name="category"]:checked ~ div .label-text { font-weight: 800 !important; color: #17A2B8 !important; }
                    .country-scroll::-webkit-scrollbar { display: none; }
                </style>

                {{-- ===== UNIFIED PROGRESS + CONTENT WRAPPER ===== --}}
                <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 4px 20px rgba(0,0,0,0.07);">

                    {{-- En-tête page unique --}}
                    <div class="px-5 pt-5 pb-4 flex items-center gap-3" style="background: linear-gradient(135deg, rgba(27,79,114,0.04), rgba(23,162,184,0.06)); border-bottom: 1px solid #EDF0F4;">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #1B4F72, #17A2B8);">
                            <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-black" style="color: #1B2A4A;">Modifier l'annonce</h2>
                            <p class="text-xs" style="color: #9BA8B7;">Toutes les sections sont modifiables directement</p>
                        </div>
                    </div>

                    {{-- Validation errors --}}
                    <div x-show="stepErrors.length > 0" x-transition
                         class="px-5 py-3" style="background: #FEF2F2; border-bottom: 1px solid rgba(231,76,60,0.15);">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" style="color: #E74C3C;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <ul class="text-xs space-y-0.5" style="color: #E74C3C;">
                                <template x-for="err in stepErrors" :key="err">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    {{-- Step content area --}}
                    <div class="p-0">

                {{-- ===================================================== --}}
                {{-- STEP 1 : Catégorie                                     --}}
                {{-- ===================================================== --}}
                <div class="edit-section">
                    <div class="flex items-center gap-2 px-5 py-3" style="background: #17A2B810; border-top: 1px solid #17A2B820; border-bottom: 1px solid #17A2B815;">
                        <div class="w-1 h-5 rounded-full flex-shrink-0" style="background: #17A2B8;"></div>
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #17A2B8;">Catégorie</span>
                    </div>

                    <div class="px-5 py-5">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach([
                                'boat'   => ['label' => 'Bateau',  'img' => '/images/yacht.png'],
                                'jetski' => ['label' => 'Jet-ski', 'img' => '/images/jetski.png'],
                                'engine' => ['label' => 'Moteur',  'img' => '/images/moteurs.png'],
                                'parts'  => ['label' => 'Pieces',  'img' => '/images/pieces.png'],
                            ] as $value => $cat)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="category" value="{{ $value }}"
                                           x-model="category" @change="if(category !== 'boat') boatType = ''; if(category !== 'jetski') jetskiType = ''; if(category === 'boat') $nextTick(() => { document.getElementById('boat-type-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' }) }); if(category === 'jetski') $nextTick(() => { document.getElementById('jetski-type-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' }) })" class="peer sr-only" required>
                                    <div class="category-card-{{ $value }}">
                                        <div class="checkmark absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center"
                                             style="background: #17A2B8; display: none; box-shadow: 0 2px 8px rgba(23,162,184,0.4);">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="icon-bg w-16 h-16 sm:w-28 sm:h-28 mx-auto mb-2 rounded-xl flex items-center justify-center" style="background: #F0F4F8; transition: all 0.3s;">
                                            <img src="{{ $cat['img'] }}" alt="{{ $cat['label'] }}" class="w-14 h-14 sm:w-24 sm:h-24 object-contain">
                                        </div>
                                        <span class="label-text text-sm font-medium" style="color: #6B7B8D; display: block; transition: all 0.3s;">{{ $cat['label'] }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs" style="color: #9BA8B7;">
                            Modifiez la categorie de votre annonce si necessaire.
                        </p>
                    </div>

                    {{-- Avertissement abonnement requis (Moteur / Pièces) — only when changing TO engine/parts --}}
                    <div class="mt-3 mx-5 flex items-center gap-2 px-3 py-2 rounded-lg"
                         style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); border: 1px solid #ffc107;"
                         x-show="(category === 'engine' || category === 'parts') && originalCategory !== 'engine' && originalCategory !== 'parts' && !canPublishEngineOrParts"
                         x-transition>
                        <svg class="w-5 h-5 flex-shrink-0" style="color: #856404;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium" style="color: #856404;">
                            Un abonnement actif est requis pour publier des moteurs et pièces.
                            <a href="{{ route('subscription.plans') }}" class="underline font-semibold">Voir les abonnements</a>
                        </p>
                    </div>

                    {{-- Type de bateau (visible only when category is boat) --}}
                    <div id="boat-type-section" class="mt-4 mx-5 bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);" x-show="category === 'boat'" x-transition>
                        <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg, #17A2B8, #48C9B0);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </span>
                            Type de bateau *
                        </h2>
                        <select name="type" x-model="boatType"
                                class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                :required="category === 'boat'"
                                :disabled="category !== 'boat'">
                            <option value="">-- Choisir le type de bateau --</option>
                            @foreach(\App\Models\Listing::BOAT_TYPES as $slug => $label)
                                <option value="{{ $slug }}" {{ old('type', $listing->type) == $slug ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Type de Jet-ski (visible only when category is jetski) --}}
                    <div id="jetski-type-section" class="mt-4 mx-5 bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);" x-show="category === 'jetski'" x-transition>
                        <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg, #17A2B8, #48C9B0);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            Type de Jet-ski *
                        </h2>
                        <select name="type" x-model="jetskiType"
                                class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                :required="category === 'jetski'"
                                :disabled="category !== 'jetski'">
                            <option value="">-- Choisir le type de jet-ski --</option>
                            @foreach(\App\Models\Listing::JETSKI_TYPES as $slug => $label)
                                <option value="{{ $slug }}" {{ old('type', $listing->type) == $slug ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- STEP 2 : Informations générales                        --}}
                {{-- ===================================================== --}}
                <div class="edit-section">
                    <div class="flex items-center gap-2 px-5 py-3" style="background: #2471A310; border-top: 1px solid #2471A320; border-bottom: 1px solid #2471A315;">
                        <div class="w-1 h-5 rounded-full flex-shrink-0" style="background: #2471A3;"></div>
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #2471A3;">Informations générales</span>
                    </div>

                    <div class="px-5 py-5">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Titre de l'annonce *</label>
                                <input type="text" name="title" value="{{ old('title', $listing->title) }}" required
                                       class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                       placeholder="Ex: Bateau de peche 5m avec moteur">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div x-show="category !== 'parts'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Fabricant</label>
                                    <input type="text" name="specs[general][fabricant]" value="{{ old('specs.general.fabricant', data_get($specs, 'general.fabricant')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Yamaha, Mercury...">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Modele</label>
                                    <input type="text" name="specs[general][modele]" value="{{ old('specs.general.modele', data_get($specs, 'general.modele')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: F150, Bayliner...">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div x-show="category !== 'parts'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Annee de construction</label>
                                    <input type="number" name="specs[general][annee_construction]" value="{{ old('specs.general.annee_construction', data_get($specs, 'general.annee_construction')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: 2020" min="1900" max="{{ date('Y') + 1 }}">
                                </div>
                                <div x-show="category === 'boat' || category === 'jetski'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Immatriculation</label>
                                    <select name="specs[general][immatriculation]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        @foreach(\App\Models\Listing::IMMATRICULATION_OPTIONS as $opt)
                                            <option value="{{ $opt }}" {{ old('specs.general.immatriculation', data_get($specs, 'general.immatriculation')) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Jet-ski: type + nombre de places --}}
                            <div x-show="category === 'jetski'" x-data="{ places: '{{ old('specs.general.nombre_places', data_get($specs, 'general.nombre_places', '')) }}' }">
                                <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Nombre de places</label>
                                <input type="hidden" name="specs[general][nombre_places]" :value="places">
                                <div class="flex gap-2 flex-wrap">
                                    <template x-for="n in [1, 2, 3, 4, 5]" :key="n">
                                        <button type="button"
                                                @click="places = String(n)"
                                                :class="places === String(n) ? 'text-white shadow-md' : 'text-gray-500 bg-white'"
                                                :style="places === String(n) ? 'background: linear-gradient(135deg, #1B4F72, #17A2B8); border: 1.5px solid #17A2B8;' : 'border: 1.5px solid #E0E6ED;'"
                                                class="flex-1 min-w-[56px] rounded-xl px-3 py-2.5 text-sm font-semibold transition-all hover:-translate-y-0.5">
                                            <span x-text="n"></span>
                                            <span class="text-[10px] ml-0.5 opacity-80" x-text="n === 1 ? 'place' : 'places'"></span>
                                        </button>
                                    </template>
                                </div>
                                <p class="mt-1.5 text-[11px]" style="color: #9BA8B7;">Jet-ski standard : 1 à 3 places · Jet Car : jusqu'à 5 places</p>
                            </div>

                            <div x-show="category === 'parts'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Categorie de piece</label>
                                    <select name="specs[general][part_type]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Categorie --</option>
                                        <option value="Hélice & Transmission" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Hélice & Transmission' ? 'selected' : '' }}>🔩 Hélice & Transmission</option>
                                        <option value="Électronique & Navigation" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Électronique & Navigation' ? 'selected' : '' }}>📡 Électronique & Navigation</option>
                                        <option value="Accastillage" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Accastillage' ? 'selected' : '' }}>⚓ Accastillage</option>
                                        <option value="Moteur & Mécanique" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Moteur & Mécanique' ? 'selected' : '' }}>⚙️ Moteur & Mécanique</option>
                                        <option value="Sécurité & Survie" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Sécurité & Survie' ? 'selected' : '' }}>🦺 Sécurité & Survie</option>
                                        <option value="Pontage & Sellerie" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Pontage & Sellerie' ? 'selected' : '' }}>🪑 Pontage & Sellerie</option>
                                        <option value="Remorquage & Accessoires" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Remorquage & Accessoires' ? 'selected' : '' }}>🔗 Remorquage & Accessoires</option>
                                        <option value="Coque & Structure" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Coque & Structure' ? 'selected' : '' }}>🚤 Coque & Structure</option>
                                        <option value="Voilure & Gréement" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Voilure & Gréement' ? 'selected' : '' }}>⛵ Voilure & Gréement</option>
                                        <option value="Autre" {{ old('specs.general.part_type', data_get($specs, 'general.part_type')) == 'Autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Compatible avec</label>
                                    <input type="text" name="specs[general][compatible_with]" value="{{ old('specs.general.compatible_with', data_get($specs, 'general.compatible_with')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Yamaha F150...">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Reference / N de piece</label>
                                    <input type="text" name="specs[general][part_number]" value="{{ old('specs.general.part_number', data_get($specs, 'general.part_number')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: 6D8-WS24A-00-00">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Description *</label>
                                <textarea name="description" rows="4" required
                                          class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                          placeholder="Decrivez votre annonce en detail...">{{ old('description', $listing->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- STEP 3 : Spécifications (boat / jetski / engine)       --}}
                {{-- ===================================================== --}}
                <div class="edit-section">
                    <div class="flex items-center gap-2 px-5 py-3" style="background: #8E44AD10; border-top: 1px solid #8E44AD20; border-bottom: 1px solid #8E44AD15;">
                        <div class="w-1 h-5 rounded-full flex-shrink-0" style="background: #8E44AD;"></div>
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #8E44AD;">Spécifications techniques</span>
                    </div>

                    <div class="space-y-5">

                        {{-- Fallback pour 'parts' (ne devrait pas arriver) --}}
                        <div x-show="category === 'parts'" class="bg-white rounded-2xl p-10 text-center" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-sm font-medium" style="color: #9BA8B7;">Aucune specification requise pour les pieces.</p>
                        </div>

                        {{-- Dimensions (boat / jetski) --}}
                        <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #8E44AD;"
                             x-show="category === 'boat' || category === 'jetski'">
                            <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #8E44AD, #BB8FCE);">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                </div>
                                <div>
                                    <span class="block">Dimensions</span>
                                    <span class="block text-xs font-normal" style="color: #9BA8B7;">Taille et poids</span>
                                </div>
                            </h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Longueur (m)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][longueur]" value="{{ old('specs.dimensions.longueur', data_get($specs, 'dimensions.longueur')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="5.50">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Largeur (m)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][largeur]" value="{{ old('specs.dimensions.largeur', data_get($specs, 'dimensions.largeur')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="2.30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Poids (KG)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][tonnage]" value="{{ old('specs.dimensions.tonnage', data_get($specs, 'dimensions.tonnage')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="1500">
                                    <input type="hidden" name="specs[dimensions][tonnage_unit]" value="kg">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Tonnage (T)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][tonnage_t]" value="{{ old('specs.dimensions.tonnage_t', data_get($specs, 'dimensions.tonnage_t')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="1.5">
                                </div>
                                <div x-show="category === 'boat'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Tirant d'eau (m)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][tirant_eau]" value="{{ old('specs.dimensions.tirant_eau', data_get($specs, 'dimensions.tirant_eau')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0.80">
                                </div>
                                <div x-show="category === 'boat'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Tirant d'air (m)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][tirant_air]" value="{{ old('specs.dimensions.tirant_air', data_get($specs, 'dimensions.tirant_air')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="3.20">
                                </div>
                            </div>
                        </div>

                        {{-- Motorisation (boat / jetski / engine) --}}
                        <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #E67E22;"
                             x-show="category === 'boat' || category === 'jetski' || category === 'engine'">
                            <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #E67E22, #F39C12);">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <span class="block">Motorisation</span>
                                    <span class="block text-xs font-normal" style="color: #9BA8B7;">Puissance et carburant</span>
                                </div>
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                 x-data="{
                                     nbMoteurs: '{{ old('specs.motorisation.nombre_moteurs', data_get($specs, 'motorisation.nombre_moteurs', '1')) }}',
                                     puissanceParMoteur: '{{ old('specs.motorisation.puissance_par_moteur', data_get($specs, 'motorisation.puissance_par_moteur', '')) }}',
                                     get puissanceTotale() {
                                         const n = parseFloat(this.nbMoteurs) || 0;
                                         const p = parseFloat(this.puissanceParMoteur) || 0;
                                         return (n > 0 && p > 0) ? String(n * p) : '';
                                     }
                                 }">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Marque du moteur</label>
                                    <input type="text" name="specs[motorisation][marque_moteur]" value="{{ old('specs.motorisation.marque_moteur', data_get($specs, 'motorisation.marque_moteur')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Yamaha, Mercury...">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Propulsion</label>
                                    <select name="specs[motorisation][propulsion]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        @foreach(\App\Models\Listing::PROPULSION_OPTIONS as $opt)
                                            <option value="{{ $opt }}" {{ old('specs.motorisation.propulsion', data_get($specs, 'motorisation.propulsion')) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Type de carburant</label>
                                    <select name="specs[motorisation][type_carburant]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        @foreach(\App\Models\Listing::CARBURANT_OPTIONS as $opt)
                                            <option value="{{ $opt }}" {{ old('specs.motorisation.type_carburant', data_get($specs, 'motorisation.type_carburant')) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div x-show="category === 'boat' || category === 'jetski'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Nombre de moteurs</label>
                                    <input type="number" name="specs[motorisation][nombre_moteurs]"
                                           x-model="nbMoteurs"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="1" min="1">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Puissance par moteur (CV)</label>
                                    <input type="number" name="specs[motorisation][puissance_par_moteur]"
                                           x-model="puissanceParMoteur"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="150">
                                </div>
                                <div x-show="category === 'boat' || category === 'jetski'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5 flex items-center gap-1.5" style="color: #6B7B8D;">
                                        Puissance totale (CV)
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md" style="background: rgba(23,162,184,0.12); color: #17A2B8;">Auto</span>
                                    </label>
                                    <input type="number" name="specs[motorisation][puissance_totale]"
                                           :value="puissanceTotale"
                                           :placeholder="puissanceTotale || '300'"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                           style="background: rgba(23,162,184,0.04); cursor: not-allowed;"
                                           readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Nombre d'heures</label>
                                    <input type="number" name="specs[motorisation][nombre_heures]" value="{{ old('specs.motorisation.nombre_heures', data_get($specs, 'motorisation.nombre_heures')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="250">
                                </div>
                                <div x-show="category === 'engine'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Cylindree (cc)</label>
                                    <input type="number" name="specs[motorisation][cylindree]" value="{{ old('specs.motorisation.cylindree', data_get($specs, 'motorisation.cylindree')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="2670">
                                </div>
                                <div x-show="category === 'engine' || category === 'jetski'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Nombre de cylindres</label>
                                    <select name="specs[motorisation][nombre_cylindres]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        <option value="1" {{ old('specs.motorisation.nombre_cylindres', data_get($specs, 'motorisation.nombre_cylindres')) == '1' ? 'selected' : '' }}>1 cylindre</option>
                                        <option value="2" {{ old('specs.motorisation.nombre_cylindres', data_get($specs, 'motorisation.nombre_cylindres')) == '2' ? 'selected' : '' }}>2 cylindres</option>
                                        <option value="3" {{ old('specs.motorisation.nombre_cylindres', data_get($specs, 'motorisation.nombre_cylindres')) == '3' ? 'selected' : '' }}>3 cylindres</option>
                                        <option value="4" {{ old('specs.motorisation.nombre_cylindres', data_get($specs, 'motorisation.nombre_cylindres')) == '4' ? 'selected' : '' }}>4 cylindres</option>
                                        <option value="6" {{ old('specs.motorisation.nombre_cylindres', data_get($specs, 'motorisation.nombre_cylindres')) == '6' ? 'selected' : '' }}>6 cylindres</option>
                                        <option value="8" {{ old('specs.motorisation.nombre_cylindres', data_get($specs, 'motorisation.nombre_cylindres')) == '8' ? 'selected' : '' }}>8 cylindres</option>
                                    </select>
                                </div>
                                <div x-show="category === 'engine'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Refroidissement</label>
                                    <select name="specs[motorisation][refroidissement]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        <option value="Eau de mer" {{ old('specs.motorisation.refroidissement', data_get($specs, 'motorisation.refroidissement')) == 'Eau de mer' ? 'selected' : '' }}>Eau de mer</option>
                                        <option value="Eau douce (circuit fermé)" {{ old('specs.motorisation.refroidissement', data_get($specs, 'motorisation.refroidissement')) == 'Eau douce (circuit fermé)' ? 'selected' : '' }}>Eau douce (circuit fermé)</option>
                                        <option value="Air" {{ old('specs.motorisation.refroidissement', data_get($specs, 'motorisation.refroidissement')) == 'Air' ? 'selected' : '' }}>Air</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Reservoirs (boat / jetski) --}}
                        <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #1ABC9C;"
                             x-show="category === 'boat' || category === 'jetski'"
                             x-data="{
                                 nbRes: {{ (int) old('specs.reservoirs.nombre_reservoirs', data_get($specs, 'reservoirs.nombre_reservoirs', 1)) ?: 1 }},
                                 carb:  {{ (float) old('specs.reservoirs.reservoir_carburant', data_get($specs, 'reservoirs.reservoir_carburant', 0)) }},
                                 eau:   {{ (float) old('specs.reservoirs.reservoir_eau_douce', data_get($specs, 'reservoirs.reservoir_eau_douce', 0)) }},
                                 stk:   {{ (float) old('specs.reservoirs.stockage', data_get($specs, 'reservoirs.stockage', 0)) }},
                                 totalCarbLitres: 0,
                                 totalLitres: 0,
                             }"
                             x-effect="
                                 const n = Math.max(1, parseInt(nbRes, 10) || 1);
                                 const c = parseFloat(carb) || 0;
                                 const e = parseFloat(eau) || 0;
                                 const s = parseFloat(stk) || 0;
                                 totalCarbLitres = n * c;
                                 totalLitres = totalCarbLitres + e + s;
                             ">
                            <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #1ABC9C, #48C9B0);">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                                </div>
                                <div>
                                    <span class="block">Reservoirs</span>
                                    <span class="block text-xs font-normal" style="color: #9BA8B7;">Capacites — calcul automatique</span>
                                </div>
                            </h2>

                            <!-- Nombre de réservoirs -->
                            <div class="mb-4" x-show="category === 'boat'">
                                <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Nombre de reservoirs <span class="normal-case font-normal text-[10px]" style="color: #9BA8B7;">(carburant)</span></label>
                                <input type="number" name="specs[reservoirs][nombre_reservoirs]" x-model.number="nbRes"
                                       class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="1" min="1" max="10">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Carburant / reservoir (L)</label>
                                    <input type="number" name="specs[reservoirs][reservoir_carburant]" x-model.number="carb"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="200" min="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Eau douce (L)</label>
                                    <input type="number" name="specs[reservoirs][reservoir_eau_douce]" x-model.number="eau"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="100" min="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Stockage (L)</label>
                                    <input type="number" name="specs[reservoirs][stockage]" x-model.number="stk"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="50" min="0">
                                </div>
                            </div>

                            <!-- Capacité totale auto -->
                            <div class="mt-4 px-4 py-3 rounded-xl" style="background: rgba(26,188,156,0.08); border: 1px solid rgba(26,188,156,0.2);">
                                <div class="flex flex-wrap items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" style="color: #1ABC9C;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2z"/></svg>
                                    <span class="text-xs font-semibold" style="color: #6B7B8D;">Capacite totale</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md" style="background: rgba(26,188,156,0.15); color: #16A085;">Auto</span>
                                    <span class="text-sm font-bold" style="color: #1ABC9C;" x-text="totalLitres + ' L'"></span>
                                </div>
                                <div x-show="totalLitres > 0" x-transition class="mt-1.5 pl-7 text-[10px] leading-relaxed" style="color: #9BA8B7;">
                                    <span x-text="Math.max(1, parseInt(nbRes, 10) || 1) + ' rés. × ' + (parseFloat(carb) || 0) + ' L carburant = ' + totalCarbLitres + ' L'"></span>
                                    <span x-show="(parseFloat(eau) || 0) > 0" x-text="' + ' + (parseFloat(eau) || 0) + ' L eau douce'"></span>
                                    <span x-show="(parseFloat(stk) || 0) > 0" x-text="' + ' + (parseFloat(stk) || 0) + ' L stockage'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Amenagements (boat) --}}
                        <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #2980B9;"
                             x-show="category === 'boat'">
                            <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #2980B9, #5DADE2);">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                </div>
                                <div>
                                    <span class="block">Amenagements</span>
                                    <span class="block text-xs font-normal" style="color: #9BA8B7;">Cabines et confort</span>
                                </div>
                            </h2>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Couchettes</label>
                                    <input type="number" name="specs[amenagements][nombre_couchettes]" value="{{ old('specs.amenagements.nombre_couchettes', data_get($specs, 'amenagements.nombre_couchettes')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0" min="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Cabines</label>
                                    <input type="number" name="specs[amenagements][nombre_cabines]" value="{{ old('specs.amenagements.nombre_cabines', data_get($specs, 'amenagements.nombre_cabines')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0" min="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Sanitaires</label>
                                    <input type="number" name="specs[amenagements][nombre_sanitaire]" value="{{ old('specs.amenagements.nombre_sanitaire', data_get($specs, 'amenagements.nombre_sanitaire')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0" min="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Cuisines</label>
                                    <input type="number" name="specs[amenagements][nombre_cuisine]" value="{{ old('specs.amenagements.nombre_cuisine', data_get($specs, 'amenagements.nombre_cuisine')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- Equipements (boat / jetski) --}}
                        @php
                            $predefinedEquipement = ['Gilets de sauvetage', 'Extincteur', 'Fusees de detresse', 'Ancre', 'Bimini', 'Echelle de bain', 'Plateforme de bain', 'Douche de pont', 'Guindeau'];
                            $predefinedOptions = ['Taud de soleil', 'Glaciere', 'Table cockpit', 'Coffre de rangement', 'Porte-cannes', 'Vivier', 'Siege rabattable', 'Cabine'];
                            $predefinedElectronique = ['GPS', 'Sondeur', 'VHF', 'Radar', 'Pilote automatique', 'Eclairage LED', 'Bluetooth / Audio', 'Chargeur de batterie'];

                            $existingEquipement = old('specs.tags.equipement', data_get($specs, 'tags.equipement', []));
                            $existingOptions = old('specs.tags.options', data_get($specs, 'tags.options', []));
                            $existingElectronique = old('specs.tags.electronique', data_get($specs, 'tags.electronique', []));

                            if (!is_array($existingEquipement)) $existingEquipement = $existingEquipement ? [$existingEquipement] : [];
                            if (!is_array($existingOptions)) $existingOptions = $existingOptions ? [$existingOptions] : [];
                            if (!is_array($existingElectronique)) $existingElectronique = $existingElectronique ? [$existingElectronique] : [];

                            $customEquipement = array_values(array_diff($existingEquipement, $predefinedEquipement));
                            $customOptions = array_values(array_diff($existingOptions, $predefinedOptions));
                            $customElectronique = array_values(array_diff($existingElectronique, $predefinedElectronique));
                        @endphp
                        <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #17A2B8;"
                             x-show="category === 'boat' || category === 'jetski'">
                            <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center gradient-primary">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <div>
                                    <span class="block">Equipements, Options & Electronique</span>
                                    <span class="block text-xs font-normal" style="color: #9BA8B7;">Selectionnez ou ajoutez</span>
                                </div>
                            </h2>

                            <div class="mb-5" x-data="{ customTags: @js($customEquipement), newTag: '' }">
                                <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Equipement de securite</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($predefinedEquipement as $equip)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="specs[tags][equipement][]" value="{{ $equip }}"
                                                   {{ in_array($equip, $existingEquipement) ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <span class="px-3 py-1.5 rounded-full text-xs font-medium transition-all cursor-pointer peer-checked:border-[#17A2B8] peer-checked:text-[#17A2B8] peer-checked:bg-[#17A2B8]/10"
                                                  style="border: 1.5px solid #E0E6ED; color: #6B7B8D;">{{ $equip }}</span>
                                        </label>
                                    @endforeach
                                    <template x-for="(tag, i) in customTags" :key="'ce-'+i">
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium"
                                              style="border: 1.5px solid #17A2B8; color: #17A2B8; background: rgba(23,162,184,0.1);">
                                            <span x-text="tag"></span>
                                            <button type="button" @click="customTags.splice(i, 1)" class="ml-0.5 hover:opacity-60 text-sm leading-none">&times;</button>
                                            <input type="hidden" name="specs[tags][equipement][]" :value="tag">
                                        </span>
                                    </template>
                                </div>
                                <div class="mt-2.5 flex gap-2">
                                    <input type="text" x-model="newTag" @keydown.enter.prevent="if(newTag.trim()) { customTags.push(newTag.trim()); newTag = ''; }"
                                           class="glass-input flex-1 rounded-xl px-3 py-2 text-xs" placeholder="Ajouter un equipement..." style="min-width: 0;">
                                    <button type="button" @click="if(newTag.trim()) { customTags.push(newTag.trim()); newTag = ''; }"
                                            class="px-3 py-2 rounded-xl text-xs font-semibold text-white transition-all hover:-translate-y-0.5"
                                            style="background: linear-gradient(135deg, #1B4F72, #17A2B8); white-space: nowrap;">+ Ajouter</button>
                                </div>
                            </div>

                            <div class="mb-5" x-data="{ customTags: @js($customOptions), newTag: '' }">
                                <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Options de confort</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($predefinedOptions as $opt)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="specs[tags][options][]" value="{{ $opt }}"
                                                   {{ in_array($opt, $existingOptions) ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <span class="px-3 py-1.5 rounded-full text-xs font-medium transition-all cursor-pointer peer-checked:border-[#17A2B8] peer-checked:text-[#17A2B8] peer-checked:bg-[#17A2B8]/10"
                                                  style="border: 1.5px solid #E0E6ED; color: #6B7B8D;">{{ $opt }}</span>
                                        </label>
                                    @endforeach
                                    <template x-for="(tag, i) in customTags" :key="'co-'+i">
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium"
                                              style="border: 1.5px solid #17A2B8; color: #17A2B8; background: rgba(23,162,184,0.1);">
                                            <span x-text="tag"></span>
                                            <button type="button" @click="customTags.splice(i, 1)" class="ml-0.5 hover:opacity-60 text-sm leading-none">&times;</button>
                                            <input type="hidden" name="specs[tags][options][]" :value="tag">
                                        </span>
                                    </template>
                                </div>
                                <div class="mt-2.5 flex gap-2">
                                    <input type="text" x-model="newTag" @keydown.enter.prevent="if(newTag.trim()) { customTags.push(newTag.trim()); newTag = ''; }"
                                           class="glass-input flex-1 rounded-xl px-3 py-2 text-xs" placeholder="Ajouter une option..." style="min-width: 0;">
                                    <button type="button" @click="if(newTag.trim()) { customTags.push(newTag.trim()); newTag = ''; }"
                                            class="px-3 py-2 rounded-xl text-xs font-semibold text-white transition-all hover:-translate-y-0.5"
                                            style="background: linear-gradient(135deg, #1B4F72, #17A2B8); white-space: nowrap;">+ Ajouter</button>
                                </div>
                            </div>

                            <div x-data="{ customTags: @js($customElectronique), newTag: '' }">
                                <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Electronique</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($predefinedElectronique as $elec)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="specs[tags][electronique][]" value="{{ $elec }}"
                                                   {{ in_array($elec, $existingElectronique) ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <span class="px-3 py-1.5 rounded-full text-xs font-medium transition-all cursor-pointer peer-checked:border-[#17A2B8] peer-checked:text-[#17A2B8] peer-checked:bg-[#17A2B8]/10"
                                                  style="border: 1.5px solid #E0E6ED; color: #6B7B8D;">{{ $elec }}</span>
                                        </label>
                                    @endforeach
                                    <template x-for="(tag, i) in customTags" :key="'cel-'+i">
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium"
                                              style="border: 1.5px solid #17A2B8; color: #17A2B8; background: rgba(23,162,184,0.1);">
                                            <span x-text="tag"></span>
                                            <button type="button" @click="customTags.splice(i, 1)" class="ml-0.5 hover:opacity-60 text-sm leading-none">&times;</button>
                                            <input type="hidden" name="specs[tags][electronique][]" :value="tag">
                                        </span>
                                    </template>
                                </div>
                                <div class="mt-2.5 flex gap-2">
                                    <input type="text" x-model="newTag" @keydown.enter.prevent="if(newTag.trim()) { customTags.push(newTag.trim()); newTag = ''; }"
                                           class="glass-input flex-1 rounded-xl px-3 py-2 text-xs" placeholder="Ajouter un electronique..." style="min-width: 0;">
                                    <button type="button" @click="if(newTag.trim()) { customTags.push(newTag.trim()); newTag = ''; }"
                                            class="px-3 py-2 rounded-xl text-xs font-semibold text-white transition-all hover:-translate-y-0.5"
                                            style="background: linear-gradient(135deg, #1B4F72, #17A2B8); white-space: nowrap;">+ Ajouter</button>
                                </div>
                            </div>
                        </div>

                        {{-- Extras (boat / jetski) --}}
                        <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);"
                             x-show="category === 'boat' || category === 'jetski'">
                            <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #566573, #85929E);">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                </div>
                                <div>
                                    <span class="block">Extras</span>
                                    <span class="block text-xs font-normal" style="color: #9BA8B7;">Remorque, port...</span>
                                </div>
                            </h2>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Remorque</label>
                                        <select name="specs[extras][remorque]" x-model="hasRemorque" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                            <option value="">-- Choisir --</option>
                                            <option value="oui" {{ old('specs.extras.remorque', data_get($specs, 'extras.remorque')) == 'oui' ? 'selected' : '' }}>Oui, incluse</option>
                                            <option value="non" {{ old('specs.extras.remorque', data_get($specs, 'extras.remorque')) == 'non' ? 'selected' : '' }}>Non</option>
                                        </select>
                                    </div>
                                </div>
                                <div x-show="hasRemorque === 'oui'" x-transition>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Marque de la remorque</label>
                                    <input type="text" name="specs[extras][marque_remorque]" value="{{ old('specs.extras.marque_remorque', data_get($specs, 'extras.marque_remorque')) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Satellite...">
                                </div>
                                <div class="pt-4 mt-4" style="border-top: 1px solid #E0E6ED;">
                                    <h3 class="text-xs font-semibold uppercase mb-3" style="color: #6B7B8D;">Place au port</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <select name="specs[extras][place_au_port]" x-model="hasPort" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                                <option value="">-- Choisir --</option>
                                                <option value="oui" {{ old('specs.extras.place_au_port', data_get($specs, 'extras.place_au_port')) == 'oui' ? 'selected' : '' }}>Oui</option>
                                                <option value="non" {{ old('specs.extras.place_au_port', data_get($specs, 'extras.place_au_port')) == 'non' ? 'selected' : '' }}>Non</option>
                                            </select>
                                        </div>
                                        <div x-show="hasPort === 'oui'" x-transition>
                                            <input type="text" name="specs[extras][adresse_port]" value="{{ old('specs.extras.adresse_port', data_get($specs, 'extras.adresse_port')) }}"
                                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Adresse du port">
                                        </div>
                                    </div>
                                    <div x-show="hasPort === 'oui'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Longueur place (m)</label>
                                            <input type="number" step="0.01" name="specs[extras][longueur_place]" value="{{ old('specs.extras.longueur_place', data_get($specs, 'extras.longueur_place')) }}"
                                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="8.00">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Largeur place (m)</label>
                                            <input type="number" step="0.01" name="specs[extras][largeur_place]" value="{{ old('specs.extras.largeur_place', data_get($specs, 'extras.largeur_place')) }}"
                                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="3.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /space-y-5 step 3 --}}
                </div>

                {{-- ===================================================== --}}
                {{-- STEP 4 : Prix & État                                   --}}
                {{-- ===================================================== --}}
                <div class="edit-section">
                    <div class="flex items-center gap-2 px-5 py-3" style="background: #E67E2210; border-top: 1px solid #E67E2220; border-bottom: 1px solid #E67E2215;">
                        <div class="w-1 h-5 rounded-full flex-shrink-0" style="background: #E67E22;"></div>
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #E67E22;">Prix, État & Localisation</span>
                    </div>

                    @php
                        $existingUnit = $listing->price_display_unit;
                        $existingDisplay = $listing->price_dzd;
                        if ($existingUnit === 'milliard') {
                            $existingDisplay = $listing->price_dzd / 10000000;
                            $existingDisplay = floor($existingDisplay) == $existingDisplay ? (int)$existingDisplay : round($existingDisplay, 2);
                        } elseif ($existingUnit === 'million') {
                            $existingDisplay = $listing->price_dzd / 10000;
                            $existingDisplay = floor($existingDisplay) == $existingDisplay ? (int)$existingDisplay : round($existingDisplay, 2);
                        }
                    @endphp

                    <div class="px-5 py-5 space-y-5"
                         @change="if ($event.target.matches && $event.target.matches('input[name=type_offre]')) {
                             $dispatch('albabor-offert', { offert: $event.target.value === 'offert' });
                         }">

                            {{-- ① DEVISE --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-3" style="color: #6B7B8D;">
                                    Devise <span style="color: #E74C3C;">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    {{-- DZD --}}
                                    <label class="cursor-pointer">
                                        <input type="radio" name="currency" value="DZD" x-model="currency" class="peer sr-only" required>
                                        <div class="flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 transition-all duration-200
                                                    peer-checked:border-[#1B4F72] peer-checked:bg-[#EBF2FA]
                                                    border-gray-200 bg-gray-50 hover:border-gray-300">
                                            <span class="text-xl">🇩🇿</span>
                                            <span class="text-xs font-bold" :style="currency === 'DZD' ? 'color:#1B4F72' : 'color:#6B7B8D'">DZD</span>
                                            <span class="text-[10px]" style="color:#9BA8B7;">Dinar</span>
                                        </div>
                                    </label>
                                    {{-- EUR --}}
                                    <label class="cursor-pointer">
                                        <input type="radio" name="currency" value="EUR" x-model="currency" class="peer sr-only">
                                        <div class="flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 transition-all duration-200
                                                    peer-checked:border-[#1B4F72] peer-checked:bg-[#EBF2FA]
                                                    border-gray-200 bg-gray-50 hover:border-gray-300">
                                            <span class="text-xl">🇪🇺</span>
                                            <span class="text-xs font-bold" :style="currency === 'EUR' ? 'color:#1B4F72' : 'color:#6B7B8D'">EUR</span>
                                            <span class="text-[10px]" style="color:#9BA8B7;">Euro</span>
                                        </div>
                                    </label>
                                    {{-- Autre --}}
                                    <label class="cursor-pointer">
                                        <input type="radio" name="currency" value="OTHER" x-model="currency" class="peer sr-only">
                                        <div class="flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 transition-all duration-200
                                                    peer-checked:border-[#1B4F72] peer-checked:bg-[#EBF2FA]
                                                    border-gray-200 bg-gray-50 hover:border-gray-300">
                                            <span class="text-xl">🌐</span>
                                            <span class="text-xs font-bold" :style="currency === 'OTHER' ? 'color:#1B4F72' : 'color:#6B7B8D'">Autre</span>
                                            <span class="text-[10px]" style="color:#9BA8B7;">Devise</span>
                                        </div>
                                    </label>
                                </div>

                                {{-- Custom currency name --}}
                                <div x-show="currency === 'OTHER'" x-transition class="mt-2">
                                    <input type="text" name="currency_label" value="{{ old('currency_label', $listing->currency_label) }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                           placeholder="Ex: GBP, CHF, TND..." maxlength="10"
                                           :required="currency === 'OTHER'">
                                </div>

                                {{-- Exchange rate --}}
                                <div x-show="currency === 'EUR'" x-transition class="mt-2 flex items-center gap-1.5 text-xs rounded-lg px-3 py-2" style="background:#EBF8FA; color:#17A2B8;">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Taux indicatif : 1 EUR ≈ {{ number_format($exchangeRate ?? 238, 2) }} DZD
                                </div>
                            </div>

                            {{-- ② PRIX : entree libre + 2 boutons (Milliard / Million) qui choisissent l'unite d'affichage --}}
                            <div x-data="{
                                rawValue: '{{ (old('type_offre', $listing->type_offre) ?? '') === 'offert' ? '0' : old('price_input', $existingDisplay ?? '') }}',
                                unit: '{{ (old('type_offre', $listing->type_offre) ?? '') === 'offert' ? '' : old('price_display_unit', $existingUnit ?? '') }}',
                                offertSelected: {{ (old('type_offre', $listing->type_offre) ?? '') === 'offert' ? 'true' : 'false' }},
                                get actualValue() { return parseFloat(this.rawValue) || 0; },
                                get factor() {
                                    if (this.unit === 'milliard') return 1e7;
                                    if (this.unit === 'million')  return 1e4;
                                    return 1;
                                },
                                get dzdValue() {
                                    return Math.round(this.actualValue * this.factor);
                                },
                                _fmtNum(v) {
                                    const r = Math.round(v * 100) / 100;
                                    if (r === Math.floor(r)) return r.toLocaleString('fr-FR');
                                    return r.toLocaleString('fr-FR', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
                                },
                                get unitSuffix() {
                                    if (this.currency === 'EUR') return '€';
                                    if (this.currency === 'OTHER') return ($el.closest('form')?.querySelector('[name=currency_label]')?.value || '?');
                                    if (this.unit === 'milliard') return this.actualValue >= 2 ? 'Milliards' : 'Milliard';
                                    if (this.unit === 'million')  return this.actualValue >= 2 ? 'Millions'  : 'Million';
                                    return 'DA';
                                },
                                get displayPreview() {
                                    if (this.actualValue <= 0) return '';
                                    return this._fmtNum(this.actualValue) + ' ' + this.unitSuffix;
                                },
                                toggleUnit(u) {
                                    if (this.currency !== 'DZD') return;
                                    this.unit = (this.unit === u) ? '' : u;
                                }
                            }"
                                 @albabor-offert.window="
                                     offertSelected = $event.detail.offert;
                                     if (offertSelected) { rawValue = '0'; unit = ''; }
                                 ">
                                <label class="block text-xs font-bold uppercase tracking-wide mb-2" style="color: #6B7B8D;">
                                    Prix <span style="color: #E74C3C;">*</span>
                                    <span x-show="offertSelected" x-cloak class="ml-1 text-[10px] font-semibold normal-case" style="color:#27AE60;">(gratuit si « Offert »)</span>
                                </label>

                                {{-- Visible: l'utilisateur tape ce qui sera affiche (4,5 ou 1400000) --}}
                                <div class="relative">
                                    <input type="number" x-model="rawValue"
                                           min="0" step="any"
                                           :required="!offertSelected"
                                           :disabled="offertSelected"
                                           :placeholder="offertSelected ? '0' : (unit === 'milliard' ? 'Ex: 4,5' : (unit === 'million' ? 'Ex: 900' : 'Ex: 1400000'))"
                                           class="glass-input w-full rounded-xl px-4 py-3.5 pr-24 text-xl font-bold"
                                           :class="offertSelected ? 'opacity-60 cursor-not-allowed' : ''"
                                           style="color: #1B2A4A;">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                        <span class="text-sm font-bold px-2 py-1 rounded-lg whitespace-nowrap"
                                              :style="unit ? 'background: linear-gradient(135deg, #FFF8E7, #FFE9B8); color: #92591C; border: 1px solid rgba(241,196,15,0.5);' : 'background:#EBF2FA; color:#1B4F72;'"
                                              x-text="unitSuffix">DA</span>
                                    </div>
                                </div>

                                {{-- Hidden submitted fields (price_dzd reste en DA pour le tri/filtre) --}}
                                <input type="hidden" name="price_dzd" :value="dzdValue">
                                <input type="hidden" name="price_display_unit" :value="unit">

                                {{-- 2 boutons : Milliard / Million (DZD uniquement) --}}
                                <div x-show="currency === 'DZD' && !offertSelected" x-transition class="mt-2.5 grid grid-cols-2 gap-2">
                                    <button type="button" @click="toggleUnit('milliard')"
                                            class="px-3 py-3 rounded-xl text-sm font-bold transition-all hover:shadow-md active:scale-95"
                                            :style="unit === 'milliard'
                                                ? 'background: linear-gradient(135deg, #F1C40F, #F39C12); color: white; border: 1.5px solid #F39C12; box-shadow: 0 2px 8px rgba(241,196,15,0.4);'
                                                : 'background: linear-gradient(135deg, #FFF8E7, #FFE9B8); color: #92591C; border: 1.5px solid #F1C40F;'">
                                        Milliard
                                    </button>
                                    <button type="button" @click="toggleUnit('million')"
                                            class="px-3 py-3 rounded-xl text-sm font-bold transition-all hover:shadow-md active:scale-95"
                                            :style="unit === 'million'
                                                ? 'background: linear-gradient(135deg, #F1C40F, #F39C12); color: white; border: 1.5px solid #F39C12; box-shadow: 0 2px 8px rgba(241,196,15,0.4);'
                                                : 'background: linear-gradient(135deg, #FFF8E7, #FFE9B8); color: #92591C; border: 1.5px solid #F1C40F;'">
                                        Million
                                    </button>
                                </div>

                                {{-- Apercu : ce qui sera affiche sur l'annonce --}}
                                <div x-show="actualValue > 0" x-transition class="mt-2 space-y-1.5">
                                    <div class="flex items-center gap-1.5 text-xs rounded-lg px-3 py-2" style="background: rgba(27,79,114,0.06); color: #1B4F72;">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Affiche sur l'annonce :</span>
                                        <span class="font-bold" x-text="displayPreview"></span>
                                    </div>
                                    <div x-show="currency === 'DZD' && unit" x-transition class="text-[11px] px-3 py-1.5 rounded-lg" style="background: rgba(155,168,183,0.08); color: #6B7B8D;">
                                        <span x-text="'(equivalent : ' + dzdValue.toLocaleString('fr-FR') + ' DA)'"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- ③ TYPE D'OFFRE --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-2" style="color: #6B7B8D;">
                                    Type d'offre
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    @foreach([
                                        'negociable' => ['label'=>'Négociable','desc'=>'Prix ouvert','icon'=>'🤝'],
                                        'fix'        => ['label'=>'Prix fixe','desc'=>'Non négociable','icon'=>'🔒'],
                                        'offert'     => ['label'=>'Offert','desc'=>'Gratuit / don','icon'=>'🎁'],
                                    ] as $val => $item)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="type_offre" value="{{ $val }}"
                                                   {{ old('type_offre', $listing->type_offre ?? 'negociable') == $val ? 'checked' : '' }}
                                                   class="peer sr-only">
                                            <div class="flex flex-col items-center gap-1 py-3 px-1 rounded-xl border-2 text-center transition-all duration-200
                                                        peer-checked:border-[#1B4F72] peer-checked:bg-[#EBF2FA]
                                                        border-gray-200 bg-gray-50 hover:border-gray-300">
                                                <span class="text-lg">{{ $item['icon'] }}</span>
                                                <span class="text-[11px] font-bold leading-tight" :style="''" style="color:#1B2A4A;">{{ $item['label'] }}</span>
                                                <span class="text-[9px]" style="color:#9BA8B7;">{{ $item['desc'] }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- ④ ÉTAT --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-2" style="color: #6B7B8D;">
                                    État de l'article <span style="color: #E74C3C;">*</span>
                                </label>
                                <select name="etat" required
                                        class="glass-input w-full rounded-xl px-4 py-3.5 text-sm font-medium"
                                        style="color: #1B2A4A;">
                                    @foreach(\App\Models\Listing::ETAT_LABELS as $val => $label)
                                        <option value="{{ $val }}" {{ old('etat', $listing->etat ?? 'bon_etat') == $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ⑤ LOCALISATION --}}
                            <div style="border-top: 1px solid #F0F4F8; padding-top: 1.25rem;">
                                <p class="text-xs font-bold uppercase tracking-wide mb-3" style="color: #6B7B8D;">📍 Localisation</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium mb-1.5" style="color: #6B7B8D;">Pays</label>
                                        <select name="pays" class="glass-input w-full rounded-xl px-4 py-3 text-sm" style="color: #1B2A4A;">
                                            <option value="">— Sélectionner —</option>
                                            @foreach(['Algérie'=>'🇩🇿','Tunisie'=>'🇹🇳','Maroc'=>'🇲🇦','Égypte'=>'🇪🇬','Espagne'=>'🇪🇸','France'=>'🇫🇷','Italie'=>'🇮🇹','Grèce'=>'🇬🇷','Croatie'=>'🇭🇷','Turquie'=>'🇹🇷','Liban'=>'🇱🇧','Malte'=>'🇲🇹','Monaco'=>'🇲🇨','Slovénie'=>'🇸🇮'] as $country => $flag)
                                                <option value="{{ $country }}" {{ old('pays', $listing->pays ?? 'Algérie') == $country ? 'selected' : '' }}>{{ $flag }} {{ $country }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1.5" style="color: #6B7B8D;">Ville / Région</label>
                                        <input type="text" name="wilaya" value="{{ old('wilaya', $listing->wilaya) }}"
                                               class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                               placeholder="Ex: Alger, Oran, Skikda...">
                                    </div>
                                </div>
                            </div>

                            {{-- Echange --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-2" style="color: #6B7B8D;">Echange</label>
                                <div class="flex gap-2">
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="remarque_echange" value="accepte" {{ old('remarque_echange', $listing->remarque_echange) == 'accepte' ? 'checked' : '' }} class="peer sr-only">
                                        <span class="px-4 py-2 rounded-full text-xs font-semibold inline-block transition-all cursor-pointer"
                                              style="border: 1.5px solid #E0E6ED; color: #6B7B8D;">Accepte l'echange</span>
                                    </label>
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="remarque_echange" value="refuse" {{ old('remarque_echange', $listing->remarque_echange) == 'refuse' ? 'checked' : '' }} class="peer sr-only">
                                        <span class="px-4 py-2 rounded-full text-xs font-semibold inline-block transition-all cursor-pointer"
                                              style="border: 1.5px solid #E0E6ED; color: #6B7B8D;">N'accepte pas</span>
                                    </label>
                                </div>
                            </div>

                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- STEP 5 : Contact + Services                            --}}
                {{-- ===================================================== --}}
                <div class="edit-section">
                    <div class="flex items-center gap-2 px-5 py-3" style="background: #27AE6010; border-top: 1px solid #27AE6020; border-bottom: 1px solid #27AE6015;">
                        <div class="w-1 h-5 rounded-full flex-shrink-0" style="background: #27AE60;"></div>
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #27AE60;">Contact & Services</span>
                    </div>

                    <div class="px-5 py-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- WhatsApp --}}
                            <div data-old-phone="{{ old('numero_whatsapp', $listing->numero_whatsapp) }}" x-data="{
                                open: false, search: '', phoneNumber: '',
                                countries: [
                                    { name: 'Algerie', code: '+213', flag: '🇩🇿' },
                                    { name: 'Maroc', code: '+212', flag: '🇲🇦' },
                                    { name: 'Tunisie', code: '+216', flag: '🇹🇳' },
                                    { name: 'Allemagne', code: '+49', flag: '🇩🇪' },
                                    { name: 'Autriche', code: '+43', flag: '🇦🇹' },
                                    { name: 'Belgique', code: '+32', flag: '🇧🇪' },
                                    { name: 'France', code: '+33', flag: '🇫🇷' },
                                    { name: 'Italie', code: '+39', flag: '🇮🇹' },
                                    { name: 'Espagne', code: '+34', flag: '🇪🇸' },
                                    { name: 'Portugal', code: '+351', flag: '🇵🇹' },
                                    { name: 'Pays-Bas', code: '+31', flag: '🇳🇱' },
                                    { name: 'Suede', code: '+46', flag: '🇸🇪' },
                                    { name: 'Grece', code: '+30', flag: '🇬🇷' }
                                ],
                                selected: { name: 'Algerie', code: '+213', flag: '🇩🇿' },
                                get filteredCountries() {
                                    if (!this.search) return this.countries;
                                    const s = this.search.toLowerCase();
                                    return this.countries.filter(c => c.name.toLowerCase().includes(s) || c.code.includes(s));
                                },
                                get fullPhone() {
                                    const num = this.phoneNumber.replace(/^0+/, '');
                                    return num ? this.selected.code + num : '';
                                },
                                handlePhoneInput() {
                                    if (this.phoneNumber.startsWith('0')) this.phoneNumber = this.phoneNumber.replace(/^0+/, '');
                                },
                                selectCountry(country) { this.selected = country; this.open = false; this.search = ''; },
                                init() {
                                    const old = this.$el.dataset.oldPhone || '';
                                    if (old) {
                                        const sorted = [...this.countries].sort((a, b) => b.code.length - a.code.length);
                                        for (const c of sorted) { if (old.startsWith(c.code)) { this.selected = c; this.phoneNumber = old.substring(c.code.length); return; } }
                                        if (old.startsWith('0')) { this.selected = { name: 'Algerie', code: '+213', flag: '🇩🇿' }; this.phoneNumber = old.substring(1); return; }
                                        this.phoneNumber = old;
                                    }
                                }
                            }">
                                <label class="block text-xs font-semibold uppercase mb-1.5 flex items-center gap-1" style="color: #6B7B8D;">
                                    <svg class="w-3.5 h-3.5" style="color: #27AE60;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                    WhatsApp
                                </label>
                                <input type="hidden" name="numero_whatsapp" :value="fullPhone">
                                <div class="flex gap-2">
                                    <div class="relative">
                                        <button type="button" @click="open = !open" class="glass-input flex items-center gap-1.5 pl-3 pr-2 py-3 text-sm font-medium rounded-xl h-full" style="min-width: 90px;">
                                            <span x-text="selected.flag" class="text-base leading-none"></span>
                                            <span x-text="selected.code" class="font-semibold" style="color: #1B2A4A; font-size: 12px;"></span>
                                            <svg class="w-3 h-3 ml-auto flex-shrink-0 transition-transform duration-200" :class="open && 'rotate-180'" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div x-show="open" x-cloak @click.away="open = false; search = ''" x-transition class="absolute z-50 left-0 mt-1.5 w-56 rounded-xl overflow-hidden" style="background: white; border: 1px solid #E0E6ED; box-shadow: 0 20px 40px rgba(0,0,0,0.12);">
                                            <div class="p-2" style="border-bottom: 1px solid #E0E6ED;">
                                                <input type="text" x-model="search" @keydown.escape="open = false" placeholder="Rechercher..." class="w-full px-3 py-2 text-xs rounded-lg" style="border: 1px solid #E0E6ED; outline: none; color: #1B2A4A;">
                                            </div>
                                            <div class="max-h-48 overflow-y-auto" style="scrollbar-width: thin;">
                                                <template x-for="country in filteredCountries" :key="'ws-'+country.code">
                                                    <button type="button" @click="selectCountry(country)" class="w-full flex items-center gap-2 px-3 py-2 text-xs transition-colors hover:bg-gray-50" :class="selected.code === country.code && 'bg-cyan-50'">
                                                        <span x-text="country.flag" class="text-base leading-none"></span>
                                                        <span x-text="country.name" class="flex-1 text-left font-medium" style="color: #1B2A4A;"></span>
                                                        <span x-text="country.code" style="color: #6B7B8D;"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="tel" x-model="phoneNumber" @input="handlePhoneInput()" class="glass-input flex-1 rounded-xl px-4 py-3 text-sm" placeholder="676085441" style="min-width: 0;">
                                </div>
                            </div>

                            {{-- Mobile --}}
                            <div data-old-phone="{{ old('numero_mobile', $listing->numero_mobile) }}" x-data="{
                                open: false, search: '', phoneNumber: '',
                                countries: [
                                    { name: 'Algerie', code: '+213', flag: '🇩🇿' },
                                    { name: 'Maroc', code: '+212', flag: '🇲🇦' },
                                    { name: 'Tunisie', code: '+216', flag: '🇹🇳' },
                                    { name: 'Allemagne', code: '+49', flag: '🇩🇪' },
                                    { name: 'France', code: '+33', flag: '🇫🇷' },
                                    { name: 'Italie', code: '+39', flag: '🇮🇹' },
                                    { name: 'Espagne', code: '+34', flag: '🇪🇸' },
                                    { name: 'Portugal', code: '+351', flag: '🇵🇹' },
                                    { name: 'Pays-Bas', code: '+31', flag: '🇳🇱' },
                                    { name: 'Suede', code: '+46', flag: '🇸🇪' },
                                    { name: 'Grece', code: '+30', flag: '🇬🇷' }
                                ],
                                selected: { name: 'Algerie', code: '+213', flag: '🇩🇿' },
                                get filteredCountries() {
                                    if (!this.search) return this.countries;
                                    const s = this.search.toLowerCase();
                                    return this.countries.filter(c => c.name.toLowerCase().includes(s) || c.code.includes(s));
                                },
                                get fullPhone() {
                                    const num = this.phoneNumber.replace(/^0+/, '');
                                    return num ? this.selected.code + num : '';
                                },
                                handlePhoneInput() {
                                    if (this.phoneNumber.startsWith('0')) this.phoneNumber = this.phoneNumber.replace(/^0+/, '');
                                },
                                selectCountry(country) { this.selected = country; this.open = false; this.search = ''; },
                                init() {
                                    const old = this.$el.dataset.oldPhone || '';
                                    if (old) {
                                        const sorted = [...this.countries].sort((a, b) => b.code.length - a.code.length);
                                        for (const c of sorted) { if (old.startsWith(c.code)) { this.selected = c; this.phoneNumber = old.substring(c.code.length); return; } }
                                        if (old.startsWith('0')) { this.selected = { name: 'Algerie', code: '+213', flag: '🇩🇿' }; this.phoneNumber = old.substring(1); return; }
                                        this.phoneNumber = old;
                                    }
                                }
                            }">
                                <label class="block text-xs font-semibold uppercase mb-1.5 flex items-center gap-1" style="color: #6B7B8D;">
                                    <svg class="w-3.5 h-3.5" style="color: #2471A3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    Mobile
                                </label>
                                <input type="hidden" name="numero_mobile" :value="fullPhone">
                                <div class="flex gap-2">
                                    <div class="relative">
                                        <button type="button" @click="open = !open" class="glass-input flex items-center gap-1.5 pl-3 pr-2 py-3 text-sm font-medium rounded-xl h-full" style="min-width: 90px;">
                                            <span x-text="selected.flag" class="text-base leading-none"></span>
                                            <span x-text="selected.code" class="font-semibold" style="color: #1B2A4A; font-size: 12px;"></span>
                                            <svg class="w-3 h-3 ml-auto flex-shrink-0 transition-transform duration-200" :class="open && 'rotate-180'" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div x-show="open" x-cloak @click.away="open = false; search = ''" x-transition class="absolute z-50 left-0 mt-1.5 w-56 rounded-xl overflow-hidden" style="background: white; border: 1px solid #E0E6ED; box-shadow: 0 20px 40px rgba(0,0,0,0.12);">
                                            <div class="p-2" style="border-bottom: 1px solid #E0E6ED;">
                                                <input type="text" x-model="search" @keydown.escape="open = false" placeholder="Rechercher..." class="w-full px-3 py-2 text-xs rounded-lg" style="border: 1px solid #E0E6ED; outline: none; color: #1B2A4A;">
                                            </div>
                                            <div class="max-h-48 overflow-y-auto" style="scrollbar-width: thin;">
                                                <template x-for="country in filteredCountries" :key="'mb-'+country.code">
                                                    <button type="button" @click="selectCountry(country)" class="w-full flex items-center gap-2 px-3 py-2 text-xs transition-colors hover:bg-gray-50" :class="selected.code === country.code && 'bg-cyan-50'">
                                                        <span x-text="country.flag" class="text-base leading-none"></span>
                                                        <span x-text="country.name" class="flex-1 text-left font-medium" style="color: #1B2A4A;"></span>
                                                        <span x-text="country.code" style="color: #6B7B8D;"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="tel" x-model="phoneNumber" @input="handlePhoneInput()" class="glass-input flex-1 rounded-xl px-4 py-3 text-sm" placeholder="676085441" style="min-width: 0;">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-semibold uppercase mb-1.5 flex items-center gap-1" style="color: #6B7B8D;">
                                <svg class="w-3.5 h-3.5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Email
                            </label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $listing->contact_email) }}"
                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="exemple@email.com">
                        </div>

                        <div class="mt-5 pt-5" style="border-top: 1px solid #E0E6ED;">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="mediation_enabled" value="1"
                                       x-model="mediationEnabled"
                                       {{ old('mediation_enabled', $listing->mediation_enabled) ? 'checked' : '' }}
                                       class="mt-1 rounded text-[#17A2B8] focus:ring-[#17A2B8]/30" style="border-color: #E0E6ED;">
                                <span>
                                    <span class="text-sm font-medium" style="color: #1B2A4A;">Activer la mediation AlBabor</span>
                                    <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Les acheteurs passeront par nous. Votre numero restera masque.</span>
                                </span>
                            </label>
                        </div>

                        {{-- ---- Services AlBabor (shown when mediation enabled) ---- --}}
                        <div x-show="mediationEnabled"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0 -translate-y-2">

                            <div class="mt-5 rounded-2xl p-5" style="border: 1px solid rgba(27,79,114,0.2); background: linear-gradient(160deg, #fff 0%, #F0F7FC 100%);">

                                {{-- Header --}}
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #1B4F72, #17A2B8);">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold" style="color: #1B2A4A;">Médiation activée</h3>
                                        <p class="text-xs" style="color: #6B7B8D;">Les acheteurs passeront par AlBabor — votre numéro reste confidentiel</p>
                                    </div>
                                </div>

                                {{-- Info text --}}
                                <div class="space-y-3">
                                    <div class="flex items-start gap-3 p-3 rounded-xl" style="background: rgba(27,79,114,0.05);">
                                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <div>
                                            <p class="text-xs font-semibold" style="color: #1B2A4A;">Comment ça marche ?</p>
                                            <p class="text-xs mt-1" style="color: #6B7B8D; line-height: 1.6;">
                                                Un acheteur intéressé ouvrira un ticket de médiation. Notre équipe AlBabor servira d'intermédiaire entre vous et l'acheteur pour une transaction sécurisée.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3 p-3 rounded-xl" style="background: rgba(39,174,96,0.06);">
                                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #27AE60;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <div>
                                            <p class="text-xs font-semibold" style="color: #1B2A4A;">Vos avantages</p>
                                            <ul class="text-xs mt-1 space-y-1" style="color: #6B7B8D;">
                                                <li>• Votre numéro de téléphone reste masqué</li>
                                                <li>• Transaction sécurisée par AlBabor</li>
                                                <li>• Protection contre les arnaques</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- WhatsApp contact --}}
                                <div class="mt-4 rounded-xl p-3.5 flex items-center gap-3" style="background: linear-gradient(135deg, #25D366, #128C7E); box-shadow: 0 4px 15px rgba(37,211,102,0.25);">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-bold text-white">Des questions ? Contactez-nous sur WhatsApp</p>
                                        <p class="text-sm font-black text-white mt-0.5">+213 791 807 475</p>
                                    </div>
                                    <a href="https://wa.me/213791807475?text=Bonjour%2C%20j%27ai%20une%20question%20sur%20la%20m%C3%A9diation%20AlBabor" target="_blank"
                                       class="flex-shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition-all hover:scale-105"
                                       style="background: rgba(255,255,255,0.25); color: white; backdrop-filter: blur(4px);"
                                       @click.stop>
                                        Écrire →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- STEP 6 : Photos                                        --}}
                {{-- ===================================================== --}}
                <div class="edit-section">
                    <div class="flex items-center gap-2 px-5 py-3" style="background: #E74C3C10; border-top: 1px solid #E74C3C20; border-bottom: 1px solid #E74C3C15;">
                        <div class="w-1 h-5 rounded-full flex-shrink-0" style="background: #E74C3C;"></div>
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: #E74C3C;">Photos & Médias</span>
                    </div>

                    <div class="px-5 py-5">
                        <x-photo-uploader
                            input-name="new_images"
                            :max="20"
                            :existing-media="$listing->media"
                        />

                        {{-- YouTube Video URL --}}
                        <div class="mt-6 pt-6" style="border-top: 1px solid #E0E6ED;">
                            <label class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" style="color: #E74C3C;" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814z"/><path fill="#fff" d="M9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    Video YouTube (optionnel)
                                </span>
                            </label>
                            <input type="url"
                                   name="video_url"
                                   value="{{ old('video_url', $listing->video_url) }}"
                                   placeholder="https://www.youtube.com/watch?v=..."
                                   class="w-full px-4 py-3 rounded-xl text-sm transition-all focus:ring-2 focus:ring-opacity-50"
                                   style="border: 1.5px solid #E0E6ED; color: #1B2A4A; background: #F8FAFC; outline: none;"
                                   onfocus="this.style.borderColor='#17A2B8'; this.style.boxShadow='0 0 0 3px rgba(23,162,184,0.1)'"
                                   onblur="this.style.borderColor='#E0E6ED'; this.style.boxShadow='none'">
                            <p class="mt-1.5 text-xs" style="color: #9BA8B7;">Ajoutez un lien YouTube pour presenter votre annonce en video</p>
                        </div>
                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- NAVIGATION BUTTONS                                     --}}
                {{-- ===================================================== --}}
                <div class="flex justify-between items-center px-5 py-4" style="border-top: 1px solid #EDF0F4;">
                    <a href="{{ route('listings.my') }}"
                       class="px-4 py-2.5 rounded-xl text-xs font-medium transition-all hover:bg-gray-50"
                       style="color: #9BA8B7; border: 1.5px solid #E0E6ED;">
                        Annuler
                    </a>
                    <button type="submit"
                            :disabled="submitting || photosProcessing"
                            :class="(submitting || photosProcessing) ? 'opacity-50 cursor-wait' : 'hover:-translate-y-0.5 hover:shadow-lg'"
                            class="px-7 py-3 rounded-xl text-white text-sm font-bold transition-all"
                            style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27,79,114,0.3);">
                        <span x-show="!submitting && !photosProcessing" class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Enregistrer les modifications
                        </span>
                        <span x-show="photosProcessing && !submitting" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Optimisation...
                        </span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Envoi en cours...
                        </span>
                    </button>
                </div>

                </div> {{-- End step content area --}}
                </div> {{-- End unified wrapper --}}

            </form>
        </div>
    </div>

    <script>
        function editForm() {
            return {
                category: '{{ old('category', $listing->category) }}',
                originalCategory: '{{ $listing->category }}',
                boatType: '{{ in_array(old('type', $listing->type ?? ''), array_keys(\App\Models\Listing::BOAT_TYPES)) ? old('type', $listing->type ?? '') : '' }}',
                jetskiType: '{{ in_array(old('type', $listing->type ?? ''), array_keys(\App\Models\Listing::JETSKI_TYPES)) ? old('type', $listing->type ?? '') : '' }}',
                currency: '{{ old('currency', $listing->currency ?? 'DZD') }}',
                hasRemorque: '{{ old('specs.extras.remorque', data_get($listing->specs, 'extras.remorque', '')) }}',
                hasPort: '{{ old('specs.extras.place_au_port', data_get($listing->specs, 'extras.place_au_port', '')) }}',
                mediationEnabled: {{ old('mediation_enabled', $listing->mediation_enabled) ? 'true' : 'false' }},
                canPublishEngineOrParts: {{ ($canPublishEngineOrParts ?? false) ? 'true' : 'false' }},
                existingMediaCount: {{ $listing->media->count() }},
                stepErrors: [],
                submitting: false,
                photosProcessing: false,

                init() {},

                getPhotoUploader() {
                    return this.$root.querySelector('[data-photo-uploader]')?._albaborPhotoUploader || null;
                },

                clearFieldErrors() {
                    this.$root.querySelectorAll('.step-field-error').forEach(el => {
                        el.style.borderColor = '';
                        el.style.boxShadow = '';
                    });
                },

                markField(name) {
                    const el = this.$root.querySelector('[name="' + name + '"]');
                    if (el) {
                        el.classList.add('step-field-error');
                        el.style.borderColor = '#E74C3C';
                        el.style.boxShadow = '0 0 0 3px rgba(231,76,60,0.1)';
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                },

                applyServerValidationErrors(errors = {}) {
                    const entries = Object.entries(errors || {});
                    if (!entries.length) {
                        this.stepErrors = ["Une erreur est survenue lors de l'envoi de l'annonce."];
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                    this.stepErrors = entries.flatMap(([, messages]) => Array.isArray(messages) ? messages : [messages]).filter(Boolean);
                    this.$nextTick(() => {
                        entries.forEach(([field]) => {
                            const baseField = (field || '').replace(/\.\d+$/, '');
                            if (!baseField.startsWith('new_images') && !baseField.startsWith('delete_images') && baseField !== 'cover_image_id' && baseField !== 'video_url') {
                                this.markField(baseField);
                            }
                        });
                    });
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                async submitWithAjax(uploader) {
                    try {
                        const form = this.$root;
                        const formData = new FormData(form);
                        formData.delete('new_images');
                        formData.delete('new_images[]');
                        uploader.getFilesForSubmit().forEach((file, index) => {
                            formData.append('new_images[]', file, file.name || `photo-${index + 1}.jpg`);
                        });
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            credentials: 'same-origin',
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const contentType = response.headers.get('content-type') || '';
                        const payload = contentType.includes('application/json') ? await response.json() : null;
                        if (response.ok) {
                            window.location.href = payload?.redirect || '{{ route("listings.my") }}';
                            return;
                        }
                        if (response.status === 422) {
                            this.submitting = false;
                            this.applyServerValidationErrors(payload?.errors || {});
                            return;
                        }
                        this.submitting = false;
                        this.stepErrors = [payload?.message || "Une erreur est survenue."];
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } catch (e) {
                        this.submitting = false;
                        this.stepErrors = ["Le réseau a interrompu l'envoi. Veuillez réessayer."];
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                async submitForm(event) {
                    if (this.submitting) { event.preventDefault(); return; }
                    this.clearFieldErrors();
                    this.stepErrors = [];

                    const val = (name) => (this.$root.querySelector('[name="' + name + '"]')?.value || '').trim();
                    let errors = [];

                    if (!this.category) errors.push('Veuillez choisir une catégorie.');
                    if (!val('title')) { errors.push("Le titre est obligatoire."); this.markField('title'); }
                    if (!val('description')) { errors.push("La description est obligatoire."); this.markField('description'); }

                    const offer = this.$root.querySelector('input[name="type_offre"]:checked')?.value || '';
                    const price = val('price_dzd');
                    if (offer !== 'offert' && (!price || parseFloat(price) <= 0)) {
                        errors.push("Le prix est obligatoire.");
                        this.markField('price_dzd');
                    }

                    // Photos: compter uniquement les inputs delete NON-disabled (= vraiment supprimés)
                    const uploader = this.getPhotoUploader();
                    const newFiles = uploader?.getFilesForSubmit?.()?.length || 0;
                    const deletedCount = Array.from(
                        this.$root.querySelectorAll('input[name="delete_images[]"]')
                    ).filter(el => !el.disabled).length;
                    const remaining = Math.max(0, this.existingMediaCount - deletedCount);
                    if (newFiles === 0 && remaining <= 0) {
                        errors.push('Veuillez conserver ou ajouter au moins une photo.');
                    }

                    if (errors.length > 0) {
                        event.preventDefault();
                        this.stepErrors = errors;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    if (uploader) {
                        event.preventDefault();
                        this.submitting = true;
                        await this.submitWithAjax(uploader);
                        return;
                    }
                    this.submitting = true;
                },
            }
        }
    </script>
</x-app-layout>
