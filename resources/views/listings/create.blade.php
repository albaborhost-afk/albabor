<x-app-layout>
    <div class="min-h-screen" style="background-color: #F0F4F8;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            @php
                $startStep = 1;
                if ($errors->any()) {
                    if ($errors->has('images')) $startStep = 6;
                    elseif ($errors->hasAny(['numero_whatsapp', 'numero_mobile', 'contact_email'])) $startStep = 5;
                    elseif ($errors->hasAny(['price_dzd', 'type_offre', 'etat'])) $startStep = 4;
                    elseif ($errors->has('category')) $startStep = 1;
                    else $startStep = 2;
                }
            @endphp

            {{-- Header --}}
            <div class="mb-8 bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); background: linear-gradient(135deg, rgba(27,79,114,0.03), rgba(23,162,184,0.03));">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center gradient-primary">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold" style="color: #1B2A4A;">Creer une annonce</h1>
                        <p class="mt-1 text-sm" style="color: #6B7B8D;">Remplissez les informations de votre annonce etape par etape</p>
                    </div>
                </div>
            </div>

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
                  x-data="listingForm()">
                @csrf

                <style>
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

                {{-- ===== PROGRESS BAR ===== --}}
                <div class="bg-white rounded-2xl px-6 py-5 mb-5" style="box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                    <div class="relative">
                        {{-- Connector lines --}}
                        <div class="absolute top-5 left-0 right-0 flex items-center" aria-hidden="true" style="padding: 0 20px;">
                            <template x-for="i in 5" :key="i">
                                <div class="flex-1 h-0.5 transition-colors duration-500"
                                     :class="isStepCompleted(i) ? 'bg-[#17A2B8]' : 'bg-gray-200'">
                                </div>
                            </template>
                        </div>
                        {{-- Step circles --}}
                        <div class="relative flex justify-between">
                            <template x-for="(step, idx) in stepsList" :key="step.n">
                                <div class="flex flex-col items-center" style="min-width: 0;">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold relative z-10 transition-all duration-300"
                                         :class="isStepCompleted(step.n)
                                            ? 'text-white shadow-md'
                                            : isStepActive(step.n)
                                                ? 'text-white shadow-lg scale-110'
                                                : 'bg-gray-100 text-gray-400'"
                                         :style="(isStepCompleted(step.n) || isStepActive(step.n)) ? 'background: linear-gradient(135deg, #1B4F72, #17A2B8);' : ''">
                                        <template x-if="isStepCompleted(step.n)">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </template>
                                        <template x-if="!isStepCompleted(step.n)">
                                            <span x-text="idx + 1"></span>
                                        </template>
                                    </div>
                                    <span class="text-[10px] mt-1.5 font-medium text-center leading-tight transition-colors hidden sm:block"
                                          style="max-width: 60px;"
                                          x-text="step.label"
                                          :class="(isStepCompleted(step.n) || isStepActive(step.n)) ? 'text-[#17A2B8]' : 'text-gray-400'">
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                    {{-- Step counter --}}
                    <div class="mt-4 pt-3 flex items-center justify-between" style="border-top: 1px solid #F0F4F8;">
                        <span class="text-xs font-medium" style="color: #9BA8B7;">
                            Etape <span x-text="visualStep" class="font-bold" style="color: #17A2B8;"></span>
                            sur <span x-text="totalVisualSteps" class="font-bold" style="color: #1B2A4A;"></span>
                        </span>
                        <span class="text-xs font-semibold" style="color: #1B2A4A;" x-text="currentStepLabel"></span>
                    </div>
                </div>

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

                    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #17A2B8;">
                        <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center gradient-primary">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            </div>
                            <div>
                                <span class="block">Categorie</span>
                                <span class="block text-xs font-normal" style="color: #9BA8B7;">Choisissez le type d'annonce</span>
                            </div>
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach([
                                'boat'   => ['label' => 'Bateau',  'img' => '/images/yacht.png'],
                                'jetski' => ['label' => 'Jet-ski', 'img' => '/images/jetski.png'],
                                'engine' => ['label' => 'Moteur',  'img' => '/images/moteurs.png'],
                                'parts'  => ['label' => 'Pieces',  'img' => '/images/pieces.png'],
                            ] as $value => $cat)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="category" value="{{ $value }}"
                                           x-model="category" class="peer sr-only" required>
                                    <div class="category-card-{{ $value }}">
                                        <div class="checkmark absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center"
                                             style="background: #17A2B8; display: none; box-shadow: 0 2px 8px rgba(23,162,184,0.4);">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="icon-bg w-14 h-14 mx-auto mb-2 rounded-xl flex items-center justify-center" style="background: #F0F4F8; transition: all 0.3s;">
                                            <img src="{{ $cat['img'] }}" alt="{{ $cat['label'] }}" class="w-10 h-10 object-contain">
                                        </div>
                                        <span class="label-text text-sm font-medium" style="color: #6B7B8D; display: block; transition: all 0.3s;">{{ $cat['label'] }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs" style="color: #9BA8B7;">
                            Bateaux et Jet-skis : 5 000 DA de frais de publication. Moteurs et Pieces : gratuit avec abonnement.
                        </p>
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

                    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #2471A3;">
                        <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #2471A3, #5DADE2);">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <span class="block">Informations generales</span>
                                <span class="block text-xs font-normal" style="color: #9BA8B7;">Details de base</span>
                            </div>
                        </h2>
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

                            <div x-show="category === 'parts'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Type de piece</label>
                                    <input type="text" name="specs[general][part_type]" value="{{ old('specs.general.part_type') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Helice, Filtre...">
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
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Tonnage (T)</label>
                                    <input type="number" step="0.01" name="specs[dimensions][tonnage]" value="{{ old('specs.dimensions.tonnage') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="1.5">
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                    <input type="number" name="specs[motorisation][nombre_moteurs]" value="{{ old('specs.motorisation.nombre_moteurs') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="1" min="1">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Puissance par moteur (CV)</label>
                                    <input type="number" name="specs[motorisation][puissance_par_moteur]" value="{{ old('specs.motorisation.puissance_par_moteur') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="150">
                                </div>
                                <div x-show="category === 'boat' || category === 'jetski'">
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Puissance totale (CV)</label>
                                    <input type="number" name="specs[motorisation][puissance_totale]" value="{{ old('specs.motorisation.puissance_totale') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="300">
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
                            </div>
                        </div>

                        {{-- Reservoirs (boat) --}}
                        <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #1ABC9C;"
                             x-show="category === 'boat'">
                            <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #1ABC9C, #48C9B0);">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                                </div>
                                <div>
                                    <span class="block">Reservoirs</span>
                                    <span class="block text-xs font-normal" style="color: #9BA8B7;">Capacites</span>
                                </div>
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Carburant (L)</label>
                                    <input type="number" name="specs[reservoirs][reservoir_carburant]" value="{{ old('specs.reservoirs.reservoir_carburant') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="200">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Eau douce (L)</label>
                                    <input type="number" name="specs[reservoirs][reservoir_eau_douce]" value="{{ old('specs.reservoirs.reservoir_eau_douce') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="100">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Stockage (L)</label>
                                    <input type="number" name="specs[reservoirs][stockage]" value="{{ old('specs.reservoirs.stockage') }}"
                                           class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="50">
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

                    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #27AE60;">
                        <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #27AE60, #2ECC71);">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="block">Prix & Etat</span>
                                <span class="block text-xs font-normal" style="color: #9BA8B7;">Tarif et condition</span>
                            </div>
                        </h2>

                        {{-- Devise --}}
                        <div class="mb-5">
                            <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Devise *</label>
                            <div class="flex gap-3">
                                <label class="flex-1 relative cursor-pointer">
                                    <input type="radio" name="currency" value="DZD" x-model="currency" class="peer sr-only" required>
                                    <div class="p-3 border-[3px] border-gray-200 rounded-xl text-center bg-white transition-all duration-300
                                                peer-checked:!border-[#27AE60] peer-checked:!bg-gradient-to-br peer-checked:!from-green-50 peer-checked:!to-emerald-50
                                                peer-checked:shadow-[0_0_0_4px_rgba(39,174,96,0.2),0_8px_25px_rgba(39,174,96,0.4)]
                                                peer-checked:-translate-y-1 hover:border-green-300 hover:shadow-md">
                                        <div class="absolute top-1 right-1 w-6 h-6 rounded-full bg-[#27AE60] flex items-center justify-center opacity-0 scale-0 transition-all duration-300
                                                    peer-checked:opacity-100 peer-checked:scale-100 shadow-lg">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <span class="block text-2xl mb-0.5">🇩🇿</span>
                                        <span class="block text-sm font-medium text-gray-700 transition-all peer-checked:!font-bold peer-checked:!text-[#27AE60]">DZD</span>
                                        <span class="block text-[10px] text-gray-500">Dinar Algerien</span>
                                    </div>
                                </label>
                                <label class="flex-1 relative cursor-pointer">
                                    <input type="radio" name="currency" value="EUR" x-model="currency" class="peer sr-only">
                                    <div class="p-3 border-[3px] border-gray-200 rounded-xl text-center bg-white transition-all duration-300
                                                peer-checked:!border-[#27AE60] peer-checked:!bg-gradient-to-br peer-checked:!from-green-50 peer-checked:!to-emerald-50
                                                peer-checked:shadow-[0_0_0_4px_rgba(39,174,96,0.2),0_8px_25px_rgba(39,174,96,0.4)]
                                                peer-checked:-translate-y-1 hover:border-green-300 hover:shadow-md">
                                        <div class="absolute top-1 right-1 w-6 h-6 rounded-full bg-[#27AE60] flex items-center justify-center opacity-0 scale-0 transition-all duration-300
                                                    peer-checked:opacity-100 peer-checked:scale-100 shadow-lg">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <span class="block text-2xl mb-0.5">🇪🇺</span>
                                        <span class="block text-sm font-medium text-gray-700 transition-all peer-checked:!font-bold peer-checked:!text-[#27AE60]">EUR</span>
                                        <span class="block text-[10px] text-gray-500">Euro</span>
                                    </div>
                                </label>
                            </div>
                            <p class="mt-2 text-xs flex items-center gap-1" style="color: #9BA8B7;">
                                <svg class="w-3.5 h-3.5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Taux : 1 EUR ≈ {{ number_format($exchangeRate ?? 238, 2) }} DZD
                            </p>
                        </div>

                        {{-- Prix --}}
                        <div class="mb-5">
                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Prix *</label>
                            <div class="relative">
                                <input type="number" name="price_dzd" value="{{ old('price_dzd') }}" required min="0"
                                       class="glass-input w-full rounded-xl px-4 py-3 pr-16 text-lg font-semibold" style="color: #1B4F72;">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    <span class="font-semibold" style="color: #17A2B8;" x-text="currency === 'EUR' ? '€' : 'DA'">DA</span>
                                </div>
                            </div>
                        </div>

                        {{-- Type offre --}}
                        <div class="mb-5">
                            <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Type d'offre *</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['negociable' => 'Negociable', 'fix' => 'Prix fixe', 'offert' => 'Offert'] as $val => $label)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="type_offre" value="{{ $val }}"
                                               {{ old('type_offre', 'negociable') == $val ? 'checked' : '' }}
                                               class="peer sr-only" required>
                                        <span class="px-4 py-2 rounded-full text-xs font-bold inline-flex items-center gap-1.5 transition-all cursor-pointer peer-checked:text-white peer-checked:shadow-lg peer-checked:gradient-primary peer-checked:!border-transparent peer-checked:scale-105"
                                              style="border: 2px solid #E0E6ED; color: #6B7B8D;">
                                            <svg class="w-3.5 h-3.5 opacity-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Etat --}}
                        <div class="mb-5">
                            <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Etat *</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach(\App\Models\Listing::ETAT_LABELS as $val => $label)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="etat" value="{{ $val }}"
                                               {{ old('etat', 'bon_etat') == $val ? 'checked' : '' }}
                                               class="peer sr-only" required>
                                        <div class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 peer-checked:text-white peer-checked:shadow-lg peer-checked:gradient-primary peer-checked:!border-transparent peer-checked:scale-105"
                                             style="border: 2px solid #E0E6ED; color: #6B7B8D;">
                                            <svg class="w-4 h-4 opacity-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            {{ $label }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Echange --}}
                        <div>
                            <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Echange</label>
                            <div class="flex gap-2">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="remarque_echange" value="accepte" {{ old('remarque_echange') == 'accepte' ? 'checked' : '' }} class="peer sr-only">
                                    <span class="px-4 py-2 rounded-full text-xs font-bold inline-flex items-center gap-1.5 transition-all cursor-pointer peer-checked:!border-[#27AE60] peer-checked:!text-white peer-checked:!bg-[#27AE60] peer-checked:shadow-lg peer-checked:scale-105"
                                          style="border: 2px solid #E0E6ED; color: #6B7B8D;">
                                        <svg class="w-3.5 h-3.5 opacity-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        Accepte l'echange
                                    </span>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="remarque_echange" value="refuse" {{ old('remarque_echange') == 'refuse' ? 'checked' : '' }} class="peer sr-only">
                                    <span class="px-4 py-2 rounded-full text-xs font-bold inline-flex items-center gap-1.5 transition-all cursor-pointer peer-checked:!border-[#E74C3C] peer-checked:!text-white peer-checked:!bg-[#E74C3C] peer-checked:shadow-lg peer-checked:scale-105"
                                          style="border: 2px solid #E0E6ED; color: #6B7B8D;">
                                        <svg class="w-3.5 h-3.5 opacity-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                        N'accepte pas
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- STEP 5 : Contact                                       --}}
                {{-- ===================================================== --}}
                <div x-show="currentStep === 5"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">

                    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #3498DB;">
                        <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #3498DB, #5DADE2);">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <span class="block">Contact</span>
                                <span class="block text-xs font-normal" style="color: #9BA8B7;">Vos coordonnees</span>
                            </div>
                        </h2>
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
                                get fullPhone() { return this.phoneNumber ? this.selected.code + this.phoneNumber : ''; },
                                selectCountry(country) { this.selected = country; this.open = false; this.search = ''; },
                                init() {
                                    const old = this.$el.dataset.oldPhone || '';
                                    if (old) {
                                        const sorted = [...this.countries].sort((a, b) => b.code.length - a.code.length);
                                        for (const c of sorted) { if (old.startsWith(c.code)) { this.selected = c; this.phoneNumber = old.substring(c.code.length); return; } }
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
                                    <input type="tel" x-model="phoneNumber" class="glass-input flex-1 rounded-xl px-4 py-3 text-sm" placeholder="5XX XX XX XX" style="min-width: 0;">
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
                                get fullPhone() { return this.phoneNumber ? this.selected.code + this.phoneNumber : ''; },
                                selectCountry(country) { this.selected = country; this.open = false; this.search = ''; },
                                init() {
                                    const old = this.$el.dataset.oldPhone || '';
                                    if (old) {
                                        const sorted = [...this.countries].sort((a, b) => b.code.length - a.code.length);
                                        for (const c of sorted) { if (old.startsWith(c.code)) { this.selected = c; this.phoneNumber = old.substring(c.code.length); return; } }
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
                                    <input type="tel" x-model="phoneNumber" class="glass-input flex-1 rounded-xl px-4 py-3 text-sm" placeholder="5XX XX XX XX" style="min-width: 0;">
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
                                       {{ old('mediation_enabled') ? 'checked' : '' }}
                                       class="mt-1 rounded text-[#17A2B8] focus:ring-[#17A2B8]/30" style="border-color: #E0E6ED;">
                                <span>
                                    <span class="text-sm font-medium" style="color: #1B2A4A;">Activer la mediation AlBabor</span>
                                    <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Les acheteurs passeront par nous. Votre numero restera masque.</span>
                                </span>
                            </label>
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

                    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06); border-top: 4px solid #9B59B6;">
                        <h2 class="text-base font-semibold mb-4 flex items-center gap-3" style="color: #1B2A4A;">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #9B59B6, #BB8FCE);">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <span class="block">Photos *</span>
                                <span class="block text-xs font-normal" style="color: #9BA8B7;">La premiere photo sera la photo principale</span>
                            </div>
                        </h2>
                        <x-photo-uploader
                            input-name="images"
                            :max="10"
                            :required="true"
                        />
                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- NAVIGATION BUTTONS                                     --}}
                {{-- ===================================================== --}}
                <div class="flex justify-between items-center pt-2">
                    <a href="{{ route('listings.my') }}"
                       class="px-5 py-3 rounded-xl text-sm font-medium transition-all hover:bg-gray-50"
                       style="color: #9BA8B7; border: 1.5px solid #E0E6ED;">
                        Annuler
                    </a>

                    <div class="flex gap-3">
                        {{-- Bouton Précédent --}}
                        <button type="button"
                                x-show="currentStep > 1"
                                @click="prevStep()"
                                class="px-6 py-3 rounded-xl text-sm font-semibold transition-all hover:-translate-y-0.5"
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
                                class="px-8 py-3 rounded-xl text-white text-sm font-semibold transition-all"
                                style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27,79,114,0.3);">
                            Suivant →
                        </button>

                        {{-- Bouton Soumettre (dernier step) --}}
                        <button type="submit"
                                x-show="currentStep === 6"
                                class="px-8 py-3 rounded-xl text-white text-sm font-semibold btn-gradient-animated"
                                style="box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">
                            Continuer vers le paiement
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        function listingForm() {
            return {
                currentStep: {{ $startStep ?? 1 }},
                category: '{{ old('category', '') }}',
                currency: '{{ old('currency', 'DZD') }}',
                hasRemorque: '{{ old('specs.extras.remorque', '') }}',
                hasPort: '{{ old('specs.extras.place_au_port', '') }}',

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

                // Step 3 is auto-completed for parts (skipped)
                isStepCompleted(n) {
                    if (n === 3 && this.category === 'parts' && this.currentStep >= 4) return true;
                    return this.currentStep > n;
                },
                isStepActive(n) {
                    if (n === 3 && this.category === 'parts') return false;
                    return this.currentStep === n;
                },

                // Visual step number (for "Etape X sur Y" counter)
                get visualStep() {
                    if (this.category === 'parts' && this.currentStep >= 4) return this.currentStep - 1;
                    return this.currentStep;
                },
                get totalVisualSteps() {
                    return this.category === 'parts' ? 5 : 6;
                },
                get currentStepLabel() {
                    const labels = {
                        1: 'Categorie',
                        2: 'Informations generales',
                        3: 'Specifications',
                        4: 'Prix & Etat',
                        5: 'Contact',
                        6: 'Photos',
                    };
                    return labels[this.currentStep] || '';
                },

                nextStep() {
                    // Validation step 1: category required
                    if (this.currentStep === 1 && !this.category) return;

                    let next = this.currentStep + 1;
                    // Skip specs step (3) for parts
                    if (next === 3 && this.category === 'parts') next = 4;
                    if (next <= 6) {
                        this.currentStep = next;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                prevStep() {
                    let prev = this.currentStep - 1;
                    // Skip back over specs step (3) for parts
                    if (prev === 3 && this.category === 'parts') prev = 2;
                    if (prev >= 1) {
                        this.currentStep = prev;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
            }
        }
    </script>
</x-app-layout>
