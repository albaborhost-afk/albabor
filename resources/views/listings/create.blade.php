<x-app-layout>
    <div class="min-h-screen" style="background-color: #F0F4F8;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            @php
                $startStep = 1;
                if ($errors->any()) {
                    if ($errors->has('images')) $startStep = 6;
                    elseif ($errors->hasAny(['numero_whatsapp', 'numero_mobile', 'contact_email', 'mediation_enabled'])) $startStep = 5;
                    elseif ($errors->hasAny(['wilaya', 'pays', 'visible_a', 'price_dzd', 'currency', 'type_offre', 'etat', 'remarque_echange'])) $startStep = 4;
                    elseif ($errors->hasAny(['category', 'type'])) $startStep = 1;
                    else $startStep = 2;
                }
            @endphp

            {{-- Header removed for cleaner mobile experience --}}

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

            <form action="{{ route('listings.store') }}" method="POST" enctype="multipart/form-data"
                  class="create-form"
                  x-data="listingForm()" novalidate
                  @submit.prevent="submitForm($event)"
                  @photos-processing.window="photosProcessing = true"
                  @photos-ready.window="photosProcessing = false">
                @csrf

                <style>
                    /* ── Unified Design System ── */
                    .create-form label,
                    .create-form .field-label {
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
                </style>

                {{-- ===== UNIFIED PROGRESS + CONTENT WRAPPER ===== --}}
                <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 4px 20px rgba(0,0,0,0.07);">

                    {{-- Progress bar strip --}}
                    <div class="px-5 pt-4 pb-3" style="background: linear-gradient(135deg, rgba(27,79,114,0.04), rgba(23,162,184,0.06));">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-1.5">
                                <template x-for="(step, idx) in stepsList" :key="step.n">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center rounded-full text-[11px] font-bold transition-all duration-300"
                                             :class="isStepCompleted(step.n)
                                                ? 'w-7 h-7 text-white'
                                                : isStepActive(step.n)
                                                    ? 'w-8 h-8 text-white shadow-md'
                                                    : 'w-7 h-7 text-gray-400'"
                                             :style="(isStepCompleted(step.n) || isStepActive(step.n))
                                                ? 'background: linear-gradient(135deg, #1B4F72, #17A2B8);'
                                                : 'background: #EDF0F4;'">
                                            <template x-if="isStepCompleted(step.n)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </template>
                                            <template x-if="!isStepCompleted(step.n)">
                                                <span x-text="idx + 1"></span>
                                            </template>
                                        </div>
                                        <div x-show="idx < stepsList.length - 1"
                                             class="h-0.5 transition-all duration-500"
                                             :class="isStepCompleted(step.n) ? 'w-3 sm:w-5' : 'w-3 sm:w-5'"
                                             :style="isStepCompleted(step.n) ? 'background:#17A2B8;' : 'background:#E0E6ED;'">
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); color: white;">
                                <span x-text="visualStep"></span>/<span x-text="totalVisualSteps"></span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold" style="color: #1B2A4A;" x-text="currentStepLabel"></span>
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
                <div x-show="currentStep === 1"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">

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
                                           x-model="category" @change="if(category !== 'boat') boatType = ''; if(category === 'boat') $nextTick(() => { document.getElementById('boat-type-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' }) })" class="peer sr-only" required>
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
                        @if($isFirstListing)
                            <div class="mt-3 flex items-center gap-2 px-3 py-2 rounded-lg" style="background: linear-gradient(135deg, #d4edda, #c3e6cb); border: 1px solid #28a745;">
                                <svg class="w-5 h-5 flex-shrink-0" style="color: #155724;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                <p class="text-sm font-medium" style="color: #155724;">
                                    Votre première annonce est gratuite ! Aucun frais de publication.
                                </p>
                            </div>
                        @else
                            <p class="mt-3 text-xs" style="color: #9BA8B7;">
                                Bateaux et Jet-skis : 5 000 DA de frais de publication. Moteurs et Pieces : gratuit avec abonnement.
                            </p>
                        @endif
                    </div>

                    {{-- Type de bateau (visible only when category is boat) --}}
                    <div id="boat-type-section" class="mt-4 bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);" x-show="category === 'boat'" x-transition>
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
                                <option value="{{ $slug }}" {{ old('type') == $slug ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- STEP 2 : Informations générales                        --}}
                {{-- ===================================================== --}}
                <div x-show="currentStep === 2"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">

                    <div class="px-5 py-5">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Titre de l'annonce *</label>
                                <input type="text" name="title" value="{{ old('title') }}" required
                                       class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                       placeholder="Ex: Bateau de peche 5m avec moteur">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div x-show="category !== 'parts'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Fabricant</label>
                                    <input type="text" name="specs[general][fabricant]" value="{{ old('specs.general.fabricant') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Yamaha, Mercury...">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Modele</label>
                                    <input type="text" name="specs[general][modele]" value="{{ old('specs.general.modele') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: F150, Bayliner...">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div x-show="category !== 'parts'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Annee de construction</label>
                                    <input type="number" name="specs[general][annee_construction]" value="{{ old('specs.general.annee_construction') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: 2020" min="1900" max="{{ date('Y') + 1 }}">
                                </div>
                                <div x-show="category === 'boat' || category === 'jetski'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Immatriculation</label>
                                    <select name="specs[general][immatriculation]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        @foreach(\App\Models\Listing::IMMATRICULATION_OPTIONS as $opt)
                                            <option value="{{ $opt }}" {{ old('specs.general.immatriculation') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Jet-ski: type + nombre de places --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="category === 'jetski'">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Type de jet-ski</label>
                                    <select name="specs[general][type_jetski]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        <option value="Stand-up" {{ old('specs.general.type_jetski') == 'Stand-up' ? 'selected' : '' }}>Stand-up</option>
                                        <option value="Sit-down Runabout" {{ old('specs.general.type_jetski') == 'Sit-down Runabout' ? 'selected' : '' }}>Sit-down (Runabout)</option>
                                        <option value="Crossover" {{ old('specs.general.type_jetski') == 'Crossover' ? 'selected' : '' }}>Crossover</option>
                                        <option value="Touring" {{ old('specs.general.type_jetski') == 'Touring' ? 'selected' : '' }}>Touring</option>
                                        <option value="Performance" {{ old('specs.general.type_jetski') == 'Performance' ? 'selected' : '' }}>Performance / Sport</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Nombre de places</label>
                                    <select name="specs[general][nombre_places]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        <option value="1" {{ old('specs.general.nombre_places') == '1' ? 'selected' : '' }}>1 place</option>
                                        <option value="2" {{ old('specs.general.nombre_places') == '2' ? 'selected' : '' }}>2 places</option>
                                        <option value="3" {{ old('specs.general.nombre_places') == '3' ? 'selected' : '' }}>3 places</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="category === 'parts'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Categorie de piece</label>
                                    <select name="specs[general][part_type]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Categorie --</option>
                                        <option value="Hélice & Transmission" {{ old('specs.general.part_type') == 'Hélice & Transmission' ? 'selected' : '' }}>🔩 Hélice & Transmission</option>
                                        <option value="Électronique & Navigation" {{ old('specs.general.part_type') == 'Électronique & Navigation' ? 'selected' : '' }}>📡 Électronique & Navigation</option>
                                        <option value="Accastillage" {{ old('specs.general.part_type') == 'Accastillage' ? 'selected' : '' }}>⚓ Accastillage</option>
                                        <option value="Moteur & Mécanique" {{ old('specs.general.part_type') == 'Moteur & Mécanique' ? 'selected' : '' }}>⚙️ Moteur & Mécanique</option>
                                        <option value="Sécurité & Survie" {{ old('specs.general.part_type') == 'Sécurité & Survie' ? 'selected' : '' }}>🦺 Sécurité & Survie</option>
                                        <option value="Pontage & Sellerie" {{ old('specs.general.part_type') == 'Pontage & Sellerie' ? 'selected' : '' }}>🪑 Pontage & Sellerie</option>
                                        <option value="Remorquage & Accessoires" {{ old('specs.general.part_type') == 'Remorquage & Accessoires' ? 'selected' : '' }}>🔗 Remorquage & Accessoires</option>
                                        <option value="Coque & Structure" {{ old('specs.general.part_type') == 'Coque & Structure' ? 'selected' : '' }}>🚤 Coque & Structure</option>
                                        <option value="Voilure & Gréement" {{ old('specs.general.part_type') == 'Voilure & Gréement' ? 'selected' : '' }}>⛵ Voilure & Gréement</option>
                                        <option value="Autre" {{ old('specs.general.part_type') == 'Autre' ? 'selected' : '' }}>Autre</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Compatible avec</label>
                                    <input type="text" name="specs[general][compatible_with]" value="{{ old('specs.general.compatible_with') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Yamaha F150...">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Reference / N de piece</label>
                                    <input type="text" name="specs[general][part_number]" value="{{ old('specs.general.part_number') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: 6D8-WS24A-00-00">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Description *</label>
                                <textarea name="description" rows="4" required
                                          class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                          placeholder="Decrivez votre annonce en detail...">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- STEP 3 : Spécifications (boat / jetski / engine)       --}}
                {{-- ===================================================== --}}
                <div x-show="currentStep === 3"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">

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
                                    <input type="number" step="0.01" name="specs[dimensions][longueur]" value="{{ old('specs.dimensions.longueur') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="5.50">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Largeur (m)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][largeur]" value="{{ old('specs.dimensions.largeur') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="2.30">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Tonnage</label>
                                    <div class="flex gap-2">
                                        <input type="number" step="0.01" name="specs[dimensions][tonnage]" value="{{ old('specs.dimensions.tonnage') }}"
                                               class="glass-input w-full rounded-xl px-4 py-3 text-sm flex-1" placeholder="1500">
                                        <select name="specs[dimensions][tonnage_unit]"
                                                class="glass-input rounded-xl px-3 py-3 text-sm font-semibold" style="min-width: 70px;">
                                            <option value="kg" {{ old('specs.dimensions.tonnage_unit', 'kg') === 'kg' ? 'selected' : '' }}>KG</option>
                                            <option value="t" {{ old('specs.dimensions.tonnage_unit', 'kg') === 't' ? 'selected' : '' }}>T</option>
                                        </select>
                                    </div>
                                </div>
                                <div x-show="category === 'boat'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Tirant d'eau (m)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][tirant_eau]" value="{{ old('specs.dimensions.tirant_eau') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0.80">
                                </div>
                                <div x-show="category === 'boat'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Tirant d'air (m)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][tirant_air]" value="{{ old('specs.dimensions.tirant_air') }}"
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
                                     nbMoteurs: '{{ old('specs.motorisation.nombre_moteurs', '1') }}',
                                     puissanceParMoteur: '{{ old('specs.motorisation.puissance_par_moteur', '') }}',
                                     get puissanceTotale() {
                                         const n = parseFloat(this.nbMoteurs) || 0;
                                         const p = parseFloat(this.puissanceParMoteur) || 0;
                                         return (n > 0 && p > 0) ? String(n * p) : '';
                                     }
                                 }">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Marque du moteur</label>
                                    <input type="text" name="specs[motorisation][marque_moteur]" value="{{ old('specs.motorisation.marque_moteur') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Yamaha, Mercury...">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Propulsion</label>
                                    <select name="specs[motorisation][propulsion]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        @foreach(\App\Models\Listing::PROPULSION_OPTIONS as $opt)
                                            <option value="{{ $opt }}" {{ old('specs.motorisation.propulsion') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Type de carburant</label>
                                    <select name="specs[motorisation][type_carburant]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        @foreach(\App\Models\Listing::CARBURANT_OPTIONS as $opt)
                                            <option value="{{ $opt }}" {{ old('specs.motorisation.type_carburant') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
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
                                    <input type="number" name="specs[motorisation][nombre_heures]" value="{{ old('specs.motorisation.nombre_heures') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="250">
                                </div>
                                <div x-show="category === 'engine'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Cylindree (cc)</label>
                                    <input type="number" name="specs[motorisation][cylindree]" value="{{ old('specs.motorisation.cylindree') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="2670">
                                </div>
                                <div x-show="category === 'engine' || category === 'jetski'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Nombre de cylindres</label>
                                    <select name="specs[motorisation][nombre_cylindres]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        <option value="1" {{ old('specs.motorisation.nombre_cylindres') == '1' ? 'selected' : '' }}>1 cylindre</option>
                                        <option value="2" {{ old('specs.motorisation.nombre_cylindres') == '2' ? 'selected' : '' }}>2 cylindres</option>
                                        <option value="3" {{ old('specs.motorisation.nombre_cylindres') == '3' ? 'selected' : '' }}>3 cylindres</option>
                                        <option value="4" {{ old('specs.motorisation.nombre_cylindres') == '4' ? 'selected' : '' }}>4 cylindres</option>
                                        <option value="6" {{ old('specs.motorisation.nombre_cylindres') == '6' ? 'selected' : '' }}>6 cylindres</option>
                                        <option value="8" {{ old('specs.motorisation.nombre_cylindres') == '8' ? 'selected' : '' }}>8 cylindres</option>
                                    </select>
                                </div>
                                <div x-show="category === 'engine'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Refroidissement</label>
                                    <select name="specs[motorisation][refroidissement]" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                        <option value="">-- Choisir --</option>
                                        <option value="Eau de mer" {{ old('specs.motorisation.refroidissement') == 'Eau de mer' ? 'selected' : '' }}>Eau de mer</option>
                                        <option value="Eau douce (circuit fermé)" {{ old('specs.motorisation.refroidissement') == 'Eau douce (circuit fermé)' ? 'selected' : '' }}>Eau douce (circuit fermé)</option>
                                        <option value="Air" {{ old('specs.motorisation.refroidissement') == 'Air' ? 'selected' : '' }}>Air</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Reservoirs (boat) --}}
                        <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #1ABC9C;"
                             x-show="category === 'boat' || category === 'jetski'"
                             x-data="{
                                 nbRes: {{ old('specs.reservoirs.nombre_reservoirs', 1) }},
                                 carb:  {{ old('specs.reservoirs.reservoir_carburant', 0) }},
                                 eau:   {{ old('specs.reservoirs.reservoir_eau_douce', 0) }},
                                 stk:   {{ old('specs.reservoirs.stockage', 0) }},
                                 get totalCarb() { return (Number(this.nbRes)||1) * (Number(this.carb)||0); },
                                 get total() { return this.totalCarb + (Number(this.eau)||0) + (Number(this.stk)||0); }
                             }">
                            <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #1ABC9C, #48C9B0);">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                                </div>
                                <div>
                                    <span class="block">Reservoirs</span>
                                    <span class="block text-xs font-normal" style="color: #9BA8B7;">Capacites</span>
                                </div>
                            </h2>

                            <!-- Nombre de réservoirs -->
                            <div class="mb-4" x-show="category === 'boat'">
                                <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Nombre de reservoirs</label>
                                <input type="number" name="specs[reservoirs][nombre_reservoirs]" x-model="nbRes"
                                       class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="1" min="1" max="10">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Carburant / reservoir (L)</label>
                                    <input type="number" name="specs[reservoirs][reservoir_carburant]" x-model="carb"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="200">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Eau douce (L)</label>
                                    <input type="number" name="specs[reservoirs][reservoir_eau_douce]" x-model="eau"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Stockage (L)</label>
                                    <input type="number" name="specs[reservoirs][stockage]" x-model="stk"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="50">
                                </div>
                            </div>

                            <!-- Capacité totale auto -->
                            <div class="mt-4 px-4 py-3 rounded-xl" style="background: rgba(26,188,156,0.08); border: 1px solid rgba(26,188,156,0.2);">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 flex-shrink-0" style="color: #1ABC9C;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2z"/></svg>
                                    <span class="text-xs font-semibold" style="color: #6B7B8D;">Capacite totale :</span>
                                    <span class="text-sm font-bold" style="color: #1ABC9C;" x-text="total + ' L'"></span>
                                </div>
                                <div x-show="nbRes > 1 && carb > 0" x-transition class="mt-1.5 pl-7 text-[10px]" style="color: #9BA8B7;">
                                    <span x-text="nbRes + ' reservoirs × ' + carb + ' L = ' + totalCarb + ' L carburant'"></span>
                                    <span x-show="eau > 0" x-text="' + ' + eau + ' L eau'"></span>
                                    <span x-show="stk > 0" x-text="' + ' + stk + ' L stockage'"></span>
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
                                    <input type="number" name="specs[amenagements][nombre_couchettes]" value="{{ old('specs.amenagements.nombre_couchettes') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0" min="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Cabines</label>
                                    <input type="number" name="specs[amenagements][nombre_cabines]" value="{{ old('specs.amenagements.nombre_cabines') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0" min="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Sanitaires</label>
                                    <input type="number" name="specs[amenagements][nombre_sanitaire]" value="{{ old('specs.amenagements.nombre_sanitaire') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0" min="0">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Cuisines</label>
                                    <input type="number" name="specs[amenagements][nombre_cuisine]" value="{{ old('specs.amenagements.nombre_cuisine') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="0" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- Equipements (boat / jetski) --}}
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

                            <div class="mb-5" x-data="{ customTags: [], newTag: '' }">
                                <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Equipement de securite</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['Gilets de sauvetage', 'Extincteur', 'Fusees de detresse', 'Ancre', 'Bimini', 'Echelle de bain', 'Plateforme de bain', 'Douche de pont', 'Guindeau'] as $equip)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="specs[tags][equipement][]" value="{{ $equip }}"
                                                   {{ in_array($equip, old('specs.tags.equipement', [])) ? 'checked' : '' }}
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

                            <div class="mb-5" x-data="{ customTags: [], newTag: '' }">
                                <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Options de confort</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['Taud de soleil', 'Glaciere', 'Table cockpit', 'Coffre de rangement', 'Porte-cannes', 'Vivier', 'Siege rabattable', 'Cabine'] as $opt)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="specs[tags][options][]" value="{{ $opt }}"
                                                   {{ in_array($opt, old('specs.tags.options', [])) ? 'checked' : '' }}
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

                            <div x-data="{ customTags: [], newTag: '' }">
                                <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Electronique</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['GPS', 'Sondeur', 'VHF', 'Radar', 'Pilote automatique', 'Eclairage LED', 'Bluetooth / Audio', 'Chargeur de batterie'] as $elec)
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="specs[tags][electronique][]" value="{{ $elec }}"
                                                   {{ in_array($elec, old('specs.tags.electronique', [])) ? 'checked' : '' }}
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
                                            <option value="oui" {{ old('specs.extras.remorque') == 'oui' ? 'selected' : '' }}>Oui, incluse</option>
                                            <option value="non" {{ old('specs.extras.remorque') == 'non' ? 'selected' : '' }}>Non</option>
                                        </select>
                                    </div>
                                </div>
                                <div x-show="hasRemorque === 'oui'" x-transition>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Marque de la remorque</label>
                                    <input type="text" name="specs[extras][marque_remorque]" value="{{ old('specs.extras.marque_remorque') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Satellite...">
                                </div>
                                <div class="pt-4 mt-4" style="border-top: 1px solid #E0E6ED;">
                                    <h3 class="text-xs font-semibold uppercase mb-3" style="color: #6B7B8D;">Place au port</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <select name="specs[extras][place_au_port]" x-model="hasPort" class="glass-input w-full rounded-xl px-4 py-3 text-sm">
                                                <option value="">-- Choisir --</option>
                                                <option value="oui" {{ old('specs.extras.place_au_port') == 'oui' ? 'selected' : '' }}>Oui</option>
                                                <option value="non" {{ old('specs.extras.place_au_port') == 'non' ? 'selected' : '' }}>Non</option>
                                            </select>
                                        </div>
                                        <div x-show="hasPort === 'oui'" x-transition>
                                            <input type="text" name="specs[extras][adresse_port]" value="{{ old('specs.extras.adresse_port') }}"
                                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Adresse du port">
                                        </div>
                                    </div>
                                    <div x-show="hasPort === 'oui'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                        <div>
                                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Longueur place (m)</label>
                                            <input type="number" step="0.01" name="specs[extras][longueur_place]" value="{{ old('specs.extras.longueur_place') }}"
                                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="8.00">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Largeur place (m)</label>
                                            <input type="number" step="0.01" name="specs[extras][largeur_place]" value="{{ old('specs.extras.largeur_place') }}"
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
                <div x-show="currentStep === 4"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">

                    <div class="px-5 py-5 space-y-5">

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
                                    <input type="text" name="currency_label" value="{{ old('currency_label') }}"
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

                            {{-- ② PRIX with multiplier --}}
                            <div x-data="{
                                rawPrice: '{{ old('price_dzd', '') }}',
                                multiplier: 1,
                                init() {
                                    // Detect multiplier from old value
                                    const v = parseInt(this.rawPrice) || 0;
                                    if (v >= 1000000000 && v % 1000000000 === 0) {
                                        this.multiplier = 1000000000;
                                        this.rawPrice = String(v / 1000000000);
                                    } else if (v >= 1000000 && v % 1000000 === 0) {
                                        this.multiplier = 1000000;
                                        this.rawPrice = String(v / 1000000);
                                    }
                                },
                                get actualPrice() { return (parseFloat(this.rawPrice) || 0) * this.multiplier; },
                                get formattedTotal() {
                                    const v = this.actualPrice;
                                    if (v <= 0) return '';
                                    return v.toLocaleString('fr-FR');
                                },
                                get currencySymbol() {
                                    if (this.currency === 'EUR') return '€';
                                    if (this.currency === 'OTHER') return '';
                                    return 'DA';
                                }
                            }">
                                <label class="block text-xs font-bold uppercase tracking-wide mb-2" style="color: #6B7B8D;">
                                    Prix <span style="color: #E74C3C;">*</span>
                                </label>
                                {{-- Hidden actual value for form submission --}}
                                <input type="hidden" name="price_dzd" :value="actualPrice">

                                <div class="flex gap-2">
                                    {{-- Number input --}}
                                    <div class="relative flex-1">
                                        <input type="number" x-model="rawPrice"
                                               required min="0" step="any" placeholder="0"
                                               class="glass-input w-full rounded-xl px-4 py-3.5 pr-16 text-xl font-bold"
                                               style="color: #1B2A4A;">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <span class="text-sm font-bold px-2 py-1 rounded-lg" style="background:#EBF2FA; color:#1B4F72;"
                                                  x-text="currency === 'EUR' ? '€' : (currency === 'OTHER' ? ($el.closest('form').querySelector('[name=currency_label]')?.value || '?') : 'DA')">DA</span>
                                        </div>
                                    </div>

                                    {{-- Multiplier selector (DZD only) --}}
                                    <div x-show="currency === 'DZD'" class="flex rounded-xl overflow-hidden" style="border: 1.5px solid #E0E6ED;">
                                        <button type="button" @click="multiplier = 1"
                                                :style="multiplier === 1 ? 'background: #1B4F72; color: white;' : 'background: white; color: #6B7B8D;'"
                                                class="px-3 py-2 text-xs font-bold transition-all whitespace-nowrap">DA</button>
                                        <button type="button" @click="multiplier = 1000000"
                                                :style="multiplier === 1000000 ? 'background: #1B4F72; color: white;' : 'background: white; color: #6B7B8D;'"
                                                class="px-3 py-2 text-xs font-bold transition-all whitespace-nowrap" style="border-left: 1px solid #E0E6ED;">Million</button>
                                        <button type="button" @click="multiplier = 1000000000"
                                                :style="multiplier === 1000000000 ? 'background: #1B4F72; color: white;' : 'background: white; color: #6B7B8D;'"
                                                class="px-3 py-2 text-xs font-bold transition-all whitespace-nowrap" style="border-left: 1px solid #E0E6ED;">Milliard</button>
                                    </div>
                                </div>

                                {{-- Price preview --}}
                                <div x-show="actualPrice > 0" x-transition class="mt-2 flex items-center gap-1.5 text-xs rounded-lg px-3 py-2" style="background: rgba(27,79,114,0.06); color: #1B4F72;">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span class="font-semibold" x-text="formattedTotal + ' ' + (currency === 'EUR' ? '€' : (currency === 'OTHER' ? '' : 'DA'))"></span>
                                </div>
                            </div>

                            {{-- ③ TYPE D'OFFRE --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide mb-2" style="color: #6B7B8D;">
                                    Type d'offre
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach(['negociable' => ['label'=>'Négociable','desc'=>'Prix ouvert','icon'=>'🤝'], 'fix' => ['label'=>'Prix fixe','desc'=>'Non négociable','icon'=>'🔒']] as $val => $item)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="type_offre" value="{{ $val }}"
                                                   {{ old('type_offre', 'negociable') == $val ? 'checked' : '' }}
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
                                        <option value="{{ $val }}" {{ old('etat', 'bon_etat') == $val ? 'selected' : '' }}>
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
                                                <option value="{{ $country }}" {{ old('pays','Algérie') == $country ? 'selected' : '' }}>{{ $flag }} {{ $country }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1.5" style="color: #6B7B8D;">Ville / Région</label>
                                        <input type="text" name="wilaya" value="{{ old('wilaya') }}"
                                               class="glass-input w-full rounded-xl px-4 py-3 text-sm"
                                               placeholder="Ex: Alger, Oran, Skikda...">
                                    </div>
                                </div>
                            </div>

                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- STEP 5 : Contact + Services                            --}}
                {{-- ===================================================== --}}
                <div x-show="currentStep === 5"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">

                    <div class="px-5 py-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- WhatsApp --}}
                            <div data-old-phone="{{ old('numero_whatsapp', '') }}" x-data="{
                                open: false, search: '', phoneNumber: '',
                                countries: [
                                    { name: 'Algerie', code: '+213', flag: '\ud83c\udde9\ud83c\uddff' },
                                    { name: 'Maroc', code: '+212', flag: '\ud83c\uddf2\ud83c\udde6' },
                                    { name: 'Tunisie', code: '+216', flag: '\ud83c\uddf9\ud83c\uddf3' },
                                    { name: 'Allemagne', code: '+49', flag: '\ud83c\udde9\ud83c\uddea' },
                                    { name: 'Autriche', code: '+43', flag: '\ud83c\udde6\ud83c\uddf9' },
                                    { name: 'Belgique', code: '+32', flag: '\ud83c\udde7\ud83c\uddea' },
                                    { name: 'France', code: '+33', flag: '\ud83c\uddeb\ud83c\uddf7' },
                                    { name: 'Italie', code: '+39', flag: '\ud83c\uddee\ud83c\uddf9' },
                                    { name: 'Espagne', code: '+34', flag: '\ud83c\uddea\ud83c\uddf8' },
                                    { name: 'Portugal', code: '+351', flag: '\ud83c\uddf5\ud83c\uddf9' },
                                    { name: 'Pays-Bas', code: '+31', flag: '\ud83c\uddf3\ud83c\uddf1' },
                                    { name: 'Suede', code: '+46', flag: '\ud83c\uddf8\ud83c\uddea' },
                                    { name: 'Grece', code: '+30', flag: '\ud83c\uddec\ud83c\uddf7' }
                                ],
                                selected: { name: 'Algerie', code: '+213', flag: '\ud83c\udde9\ud83c\uddff' },
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
                            <div data-old-phone="{{ old('numero_mobile', '') }}" x-data="{
                                open: false, search: '', phoneNumber: '',
                                countries: [
                                    { name: 'Algerie', code: '+213', flag: '\ud83c\udde9\ud83c\uddff' },
                                    { name: 'Maroc', code: '+212', flag: '\ud83c\uddf2\ud83c\udde6' },
                                    { name: 'Tunisie', code: '+216', flag: '\ud83c\uddf9\ud83c\uddf3' },
                                    { name: 'Allemagne', code: '+49', flag: '\ud83c\udde9\ud83c\uddea' },
                                    { name: 'France', code: '+33', flag: '\ud83c\uddeb\ud83c\uddf7' },
                                    { name: 'Italie', code: '+39', flag: '\ud83c\uddee\ud83c\uddf9' },
                                    { name: 'Espagne', code: '+34', flag: '\ud83c\uddea\ud83c\uddf8' },
                                    { name: 'Portugal', code: '+351', flag: '\ud83c\uddf5\ud83c\uddf9' },
                                    { name: 'Pays-Bas', code: '+31', flag: '\ud83c\uddf3\ud83c\uddf1' },
                                    { name: 'Suede', code: '+46', flag: '\ud83c\uddf8\ud83c\uddea' },
                                    { name: 'Grece', code: '+30', flag: '\ud83c\uddec\ud83c\uddf7' }
                                ],
                                selected: { name: 'Algerie', code: '+213', flag: '\ud83c\udde9\ud83c\uddff' },
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
                            <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="exemple@email.com">
                        </div>

                        <div class="mt-5 pt-5" style="border-top: 1px solid #E0E6ED;">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="mediation_enabled" value="1"
                                       x-model="mediationEnabled"
                                       {{ old('mediation_enabled') ? 'checked' : '' }}
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
                <div x-show="currentStep === 6"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">

                    <div class="px-5 py-5">
                        <x-photo-uploader
                            input-name="images"
                            :max="20"
                            :required="true"
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
                                   value="{{ old('video_url') }}"
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

                    <div class="flex gap-2">
                        {{-- Bouton Précédent --}}
                        <button type="button"
                                x-show="currentStep > 1"
                                @click="prevStep()"
                                class="px-5 py-2.5 rounded-xl text-xs font-semibold transition-all hover:-translate-y-0.5"
                                style="border: 2px solid #17A2B8; color: #17A2B8; background: white;">
                            ← Precedent
                        </button>

                        {{-- Bouton Suivant --}}
                        <button type="button"
                                x-show="currentStep < 6"
                                @click="nextStep()"
                                :disabled="currentStep === 1 && !category"
                                :class="currentStep === 1 && !category
                                    ? 'opacity-40 cursor-not-allowed'
                                    : 'hover:-translate-y-0.5 hover:shadow-lg'"
                                class="px-6 py-2.5 rounded-xl text-white text-xs font-semibold transition-all"
                                style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27,79,114,0.3);">
                            Suivant →
                        </button>

                        {{-- Bouton Soumettre (dernier step) --}}
                        <button type="submit"
                                x-show="currentStep === 6"
                                :disabled="submitting || photosProcessing"
                                :class="(submitting || photosProcessing) ? 'opacity-50 cursor-wait' : ''"
                                class="px-6 py-2.5 rounded-xl text-white text-xs font-semibold btn-gradient-animated"
                                style="box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">
                            <span x-show="!submitting && !photosProcessing">Continuer vers le paiement</span>
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
                </div>

                </div> {{-- End step content area --}}
                </div> {{-- End unified wrapper --}}

            </form>
        </div>
    </div>

    <script>
        const DRAFT_KEY = 'albabor_listing_draft';

        function listingForm() {
            return {
                currentStep: {{ $startStep ?? 1 }},
                category: '{{ old('category', '') }}',
                boatType: '{{ old('type', '') }}',
                currency: '{{ old('currency', 'DZD') }}',
                hasRemorque: '{{ old('specs.extras.remorque', '') }}',
                hasPort: '{{ old('specs.extras.place_au_port', '') }}',
                mediationEnabled: {{ old('mediation_enabled') ? 'true' : 'false' }},
                stepErrors: [],
                submitting: false,
                photosProcessing: false,
                _saveTimer: null,

                // ── Lifecycle: restore draft on init ──
                init() {
                    const hasServerData = {{ ($errors->any() || old('title')) ? 'true' : 'false' }};
                    if (!hasServerData) {
                        this.restoreFromStorage();
                    } else {
                        // Server sent old() data after validation failure — save it to storage as backup
                        this.$nextTick(() => this.saveToStorage());
                    }
                    // Auto-save on every input/change
                    this.$nextTick(() => {
                        const save = () => { clearTimeout(this._saveTimer); this._saveTimer = setTimeout(() => this.saveToStorage(), 400); };
                        this.$root.addEventListener('input', save);
                        this.$root.addEventListener('change', save);
                    });
                },

                // ── sessionStorage persistence ──
                saveToStorage() {
                    try {
                        const form = this.$root;
                        const state = {
                            _step: this.currentStep,
                            _category: this.category,
                            _boatType: this.boatType,
                            _currency: this.currency,
                            _hasRemorque: this.hasRemorque,
                            _hasPort: this.hasPort,
                            _mediationEnabled: this.mediationEnabled,
                        };
                        // Capture all non-file inputs
                        form.querySelectorAll('input:not([type=file]), select, textarea').forEach(el => {
                            if (!el.name || el.name === '_token') return;
                            if (el.type === 'radio') { if (el.checked) state[el.name] = el.value; }
                            else if (el.type === 'checkbox') {
                                if (el.name.endsWith('[]')) {
                                    if (!state[el.name]) state[el.name] = [];
                                    if (el.checked) state[el.name].push(el.value);
                                } else {
                                    state[el.name] = el.checked;
                                }
                            }
                            else { state[el.name] = el.value; }
                        });
                        sessionStorage.setItem(DRAFT_KEY, JSON.stringify(state));
                    } catch(e) { /* storage full or unavailable */ }
                },

                restoreFromStorage() {
                    try {
                        const raw = sessionStorage.getItem(DRAFT_KEY);
                        if (!raw) return;
                        const state = JSON.parse(raw);

                        // Restore Alpine reactive properties first (controls visibility)
                        if (state._category) this.category = state._category;
                        if (state._boatType) this.boatType = state._boatType;
                        if (state._currency) this.currency = state._currency;
                        if (state._hasRemorque) this.hasRemorque = state._hasRemorque;
                        if (state._hasPort) this.hasPort = state._hasPort;
                        if (state._mediationEnabled !== undefined) this.mediationEnabled = !!state._mediationEnabled;
                        if (state._step && state._step >= 1 && state._step <= 6) this.currentStep = state._step;

                        // Restore DOM form fields after Alpine renders the correct step
                        this.$nextTick(() => {
                            const form = this.$root;
                            Object.entries(state).forEach(([name, value]) => {
                                if (name.startsWith('_')) return;
                                if (Array.isArray(value)) {
                                    // Checkbox array (e.g. specs[tags][equipement][])
                                    form.querySelectorAll('[name="' + name + '"]').forEach(cb => {
                                        cb.checked = value.includes(cb.value);
                                    });
                                    return;
                                }
                                const els = form.querySelectorAll('[name="' + name + '"]');
                                if (!els.length) return;
                                const el = els[0];
                                if (el.type === 'radio') {
                                    els.forEach(r => { r.checked = (r.value === value); });
                                } else if (el.type === 'checkbox') {
                                    el.checked = !!value;
                                } else if (el.type !== 'file') {
                                    el.value = value;
                                }
                            });
                        });
                    } catch(e) { /* ignore */ }
                },

                clearDraft() {
                    sessionStorage.removeItem(DRAFT_KEY);
                },

                get stepsList() {
                    return [
                        { n: 1, label: 'Categorie' },
                        { n: 2, label: 'Infos' },
                        { n: 3, label: 'Specs' },
                        { n: 4, label: 'Prix' },
                        { n: 5, label: 'Contact' },
                        { n: 6, label: 'Photos' },
                    ];
                },

                isStepCompleted(n) {
                    if (n === 3 && this.category === 'parts' && this.currentStep >= 4) return true;
                    return this.currentStep > n;
                },
                isStepActive(n) {
                    if (n === 3 && this.category === 'parts') return false;
                    return this.currentStep === n;
                },

                get visualStep() {
                    let skipped = 0;
                    if (this.category === 'parts' && this.currentStep >= 4) skipped++;
                    return this.currentStep - skipped;
                },
                get totalVisualSteps() {
                    if (this.category === 'parts') return 5;
                    return 6;
                },
                get currentStepLabel() {
                    const labels = {
                        1: 'Categorie',
                        2: 'Informations generales',
                        3: 'Specifications',
                        4: 'Prix, Etat & Localisation',
                        5: 'Contact & Services',
                        6: 'Photos',
                    };
                    return labels[this.currentStep] || '';
                },

                // ── Validation helpers ──
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
                    }
                },

                validateStep(step) {
                    let errors = [];
                    const val = (name) => {
                        const el = this.$root.querySelector('[name="' + name + '"]');
                        return el ? (el.value || '').trim() : '';
                    };

                    if (step === 1) {
                        if (!this.category) errors.push('Veuillez choisir une categorie.');
                    }
                    if (step === 2) {
                        if (!val('title')) { errors.push('Le titre de l\'annonce est obligatoire.'); this.markField('title'); }
                        if (!val('description')) { errors.push('La description est obligatoire.'); this.markField('description'); }
                    }
                    if (step === 4) {
                        const price = val('price_dzd');
                        if (!price || parseFloat(price) <= 0) { errors.push('Le prix est obligatoire.'); this.markField('price_dzd'); }
                    }
                    if (step === 6) {
                        const fileInputs = this.$root.querySelectorAll('input[type="file"][name="images[]"]');
                        let hasFiles = false;
                        fileInputs.forEach(input => { if (input.files && input.files.length > 0) hasFiles = true; });
                        const previews = this.$root.querySelectorAll('.photo-preview-item, .preview-thumb, [data-photo-item]');
                        if (!hasFiles && previews.length === 0) {
                            errors.push('Veuillez ajouter au moins une photo.');
                        }
                    }
                    return errors;
                },

                // ── Navigation ──
                nextStep() {
                    this.clearFieldErrors();
                    this.stepErrors = this.validateStep(this.currentStep);
                    if (this.stepErrors.length > 0) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    let next = this.currentStep + 1;
                    if (next === 3 && this.category === 'parts') next = 4;
                    if (next <= 6) {
                        this.currentStep = next;
                        this.saveToStorage();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                prevStep() {
                    this.stepErrors = [];
                    this.clearFieldErrors();
                    let prev = this.currentStep - 1;
                    if (prev === 3 && this.category === 'parts') prev = 2;
                    if (prev >= 1) {
                        this.currentStep = prev;
                        this.saveToStorage();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                // ── Submit with full validation ──
                submitForm(event) {
                    if (this.submitting) return;
                    this.clearFieldErrors();

                    const stepsToCheck = this.category === 'parts' ? [1, 2, 4, 6] : [1, 2, 4, 6];
                    for (const step of stepsToCheck) {
                        const errors = this.validateStep(step);
                        if (errors.length > 0) {
                            this.currentStep = step;
                            this.$nextTick(() => {
                                this.stepErrors = errors;
                                if (step === 2) { if (!this.$root.querySelector('[name="title"]')?.value?.trim()) this.markField('title'); if (!this.$root.querySelector('[name="description"]')?.value?.trim()) this.markField('description'); }
                                if (step === 4) { const p = this.$root.querySelector('[name="price_dzd"]')?.value?.trim(); if (!p || parseFloat(p) <= 0) this.markField('price_dzd'); }
                            });
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            return;
                        }
                    }

                    // All valid — clear draft and submit
                    this.submitting = true;
                    this.clearDraft();
                    event.target.submit();
                },
            }
        }
    </script>
</x-app-layout>
