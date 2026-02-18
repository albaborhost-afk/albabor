<x-app-layout>
    <div class="min-h-screen" style="background-color: #F0F4F8;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-bold" style="color: #1B2A4A;">Modifier l'annonce</h1>
                <p class="mt-1 text-sm" style="color: #6B7B8D;">Modifiez les informations de votre annonce</p>
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

            @php
                $specs = $listing->specs ?? [];
            @endphp

            <form action="{{ route('listings.update', $listing) }}" method="POST" enctype="multipart/form-data"
                  x-data="editForm()" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- SECTION 1: Categorie --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">1</span>
                        Categorie
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach([
                            'boat' => ['label' => 'Bateau', 'img' => '/images/yacht.png'],
                            'jetski' => ['label' => 'Jet-ski', 'img' => '/images/jetski.png'],
                            'engine' => ['label' => 'Moteur', 'img' => '/images/moteurs.png'],
                            'parts' => ['label' => 'Pieces', 'img' => '/images/pieces.png'],
                        ] as $value => $cat)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="category" value="{{ $value }}"
                                       x-model="category" class="peer sr-only" required>
                                <div class="p-4 border-2 rounded-2xl text-center transition-all
                                            peer-checked:border-[#17A2B8] peer-checked:shadow-md
                                            hover:border-gray-300" style="border-color: #E0E6ED; background: white;"
                                     :style="category === '{{ $value }}' ? 'border-color: #17A2B8; box-shadow: 0 0 0 3px rgba(23,162,184,0.15);' : ''">
                                    <div class="w-14 h-14 mx-auto mb-2 rounded-xl flex items-center justify-center" style="background: #F0F4F8;">
                                        <img src="{{ $cat['img'] }}" alt="{{ $cat['label'] }}" class="w-10 h-10 object-contain">
                                    </div>
                                    <span class="text-sm font-medium" style="color: #1B2A4A;">{{ $cat['label'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SECTION 2: Informations generales --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);" x-show="category" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">2</span>
                        Informations generales
                    </h2>
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

                        <div x-show="category === 'parts'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Type de piece</label>
                                <input type="text" name="specs[general][part_type]" value="{{ old('specs.general.part_type', data_get($specs, 'general.part_type')) }}"
                                       class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="Ex: Helice, Filtre...">
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

                {{-- SECTION 3: Dimensions (boat / jetski) --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);"
                     x-show="category === 'boat' || category === 'jetski'" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">3</span>
                        Dimensions
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
                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Tonnage (T)</label>
                            <input type="number" step="0.01" name="specs[dimensions][tonnage]" value="{{ old('specs.dimensions.tonnage', data_get($specs, 'dimensions.tonnage')) }}"
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
                    <div class="mt-4 pt-4" style="border-top: 1px solid #E0E6ED;" x-show="category === 'boat'" x-transition>
                        <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Reservoirs carburant (L)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: #9BA8B7;">Reservoir 1</label>
                                <input type="number" step="1" name="specs[dimensions][reservoir_1]" value="{{ old('specs.dimensions.reservoir_1', data_get($specs, 'dimensions.reservoir_1')) }}"
                                       class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="500" min="0">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1" style="color: #9BA8B7;">Reservoir 2</label>
                                <input type="number" step="1" name="specs[dimensions][reservoir_2]" value="{{ old('specs.dimensions.reservoir_2', data_get($specs, 'dimensions.reservoir_2')) }}"
                                       class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="500" min="0">
                            </div>
                        </div>
                        <p class="text-xs mt-1.5" style="color: #9BA8B7;">Laissez le reservoir 2 vide si un seul reservoir</p>
                    </div>
                </div>

                {{-- SECTION 4: Motorisation --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);"
                     x-show="category === 'boat' || category === 'jetski' || category === 'engine'" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">
                            <span x-text="category === 'boat' || category === 'jetski' ? '4' : '3'">4</span>
                        </span>
                        Motorisation
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <input type="number" name="specs[motorisation][nombre_moteurs]" value="{{ old('specs.motorisation.nombre_moteurs', data_get($specs, 'motorisation.nombre_moteurs')) }}"
                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="1" min="1">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Puissance par moteur (CV)</label>
                            <input type="number" name="specs[motorisation][puissance_par_moteur]" value="{{ old('specs.motorisation.puissance_par_moteur', data_get($specs, 'motorisation.puissance_par_moteur')) }}"
                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="150">
                        </div>
                        <div x-show="category === 'boat' || category === 'jetski'">
                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Puissance totale (CV)</label>
                            <input type="number" name="specs[motorisation][puissance_totale]" value="{{ old('specs.motorisation.puissance_totale', data_get($specs, 'motorisation.puissance_totale')) }}"
                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="300">
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
                    </div>
                </div>

                {{-- SECTION 5: Reservoirs (boat) --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);"
                     x-show="category === 'boat'" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">5</span>
                        Reservoirs
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Carburant (L)</label>
                            <input type="number" name="specs[reservoirs][reservoir_carburant]" value="{{ old('specs.reservoirs.reservoir_carburant', data_get($specs, 'reservoirs.reservoir_carburant')) }}"
                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="200">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Eau douce (L)</label>
                            <input type="number" name="specs[reservoirs][reservoir_eau_douce]" value="{{ old('specs.reservoirs.reservoir_eau_douce', data_get($specs, 'reservoirs.reservoir_eau_douce')) }}"
                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="100">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Stockage (L)</label>
                            <input type="number" name="specs[reservoirs][stockage]" value="{{ old('specs.reservoirs.stockage', data_get($specs, 'reservoirs.stockage')) }}"
                                   class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="50">
                        </div>
                    </div>
                </div>

                {{-- SECTION 6: Amenagements (boat) --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);"
                     x-show="category === 'boat'" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">6</span>
                        Amenagements
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

                {{-- SECTION 7: Equipements (boat / jetski) --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);"
                     x-show="category === 'boat' || category === 'jetski'" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">7</span>
                        Equipements, Options & Electronique
                    </h2>

                    @php
                        $predefinedEquipement = ['Gilets de sauvetage', 'Extincteur', 'Fusees de detresse', 'Ancre', 'Bimini', 'Echelle de bain', 'Plateforme de bain', 'Douche de pont', 'Guindeau'];
                        $predefinedOptions = ['Taud de soleil', 'Glaciere', 'Table cockpit', 'Coffre de rangement', 'Porte-cannes', 'Vivier', 'Siege rabattable', 'Cabine'];
                        $predefinedElectronique = ['GPS', 'Sondeur', 'VHF', 'Radar', 'Pilote automatique', 'Eclairage LED', 'Bluetooth / Audio', 'Chargeur de batterie'];

                        $existingEquipement = old('specs.tags.equipement', data_get($specs, 'tags.equipement', []));
                        $existingOptions = old('specs.tags.options', data_get($specs, 'tags.options', []));
                        $existingElectronique = old('specs.tags.electronique', data_get($specs, 'tags.electronique', []));

                        $customEquipement = array_values(array_diff($existingEquipement, $predefinedEquipement));
                        $customOptions = array_values(array_diff($existingOptions, $predefinedOptions));
                        $customElectronique = array_values(array_diff($existingElectronique, $predefinedElectronique));
                    @endphp

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

                {{-- SECTION 8: Extras (boat / jetski) --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);"
                     x-show="category === 'boat' || category === 'jetski'" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">8</span>
                        Extras
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

                {{-- SECTION: Prix & Etat --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);" x-show="category" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">
                            <span x-text="getSectionNumber('prix')">9</span>
                        </span>
                        Prix & Etat
                    </h2>

                    {{-- Currency --}}
                    <div class="mb-5">
                        <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Devise *</label>
                        <div class="flex gap-3">
                            <label class="flex-1 relative cursor-pointer">
                                <input type="radio" name="currency" value="DZD" x-model="currency" class="peer sr-only" required>
                                <div class="p-3 border-2 rounded-xl text-center transition-all peer-checked:shadow-md"
                                     style="border-color: #E0E6ED;"
                                     :style="currency === 'DZD' ? 'border-color: #17A2B8; box-shadow: 0 0 0 3px rgba(23,162,184,0.15);' : ''">
                                    <span class="block text-lg mb-0.5">🇩🇿</span>
                                    <span class="block text-sm font-bold" style="color: #1B2A4A;">DZD</span>
                                    <span class="block text-[10px]" style="color: #9BA8B7;">Dinar Algerien</span>
                                </div>
                            </label>
                            <label class="flex-1 relative cursor-pointer">
                                <input type="radio" name="currency" value="EUR" x-model="currency" class="peer sr-only">
                                <div class="p-3 border-2 rounded-xl text-center transition-all peer-checked:shadow-md"
                                     style="border-color: #E0E6ED;"
                                     :style="currency === 'EUR' ? 'border-color: #17A2B8; box-shadow: 0 0 0 3px rgba(23,162,184,0.15);' : ''">
                                    <span class="block text-lg mb-0.5">🇪🇺</span>
                                    <span class="block text-sm font-bold" style="color: #1B2A4A;">EUR</span>
                                    <span class="block text-[10px]" style="color: #9BA8B7;">Euro</span>
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

                    {{-- Price --}}
                    <div class="mb-5">
                        <label class="block text-xs font-semibold uppercase mb-1.5" style="color: #6B7B8D;">Prix *</label>
                        <div class="relative">
                            <input type="number" name="price_dzd" value="{{ old('price_dzd', $listing->price_dzd) }}" required min="0"
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
                                           {{ old('type_offre', $listing->type_offre ?? 'negociable') == $val ? 'checked' : '' }}
                                           class="peer sr-only" required>
                                    <span class="px-4 py-2 rounded-full text-xs font-semibold inline-block transition-all cursor-pointer
                                                 peer-checked:text-white peer-checked:shadow-md"
                                          style="border: 1.5px solid #E0E6ED; color: #6B7B8D;"
                                          :class="{ 'peer-checked:gradient-primary peer-checked:!border-transparent': true }">{{ $label }}</span>
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
                                           {{ old('etat', $listing->etat ?? 'bon_etat') == $val ? 'checked' : '' }}
                                           class="peer sr-only" required>
                                    <div class="px-4 py-2.5 rounded-xl text-xs font-medium transition-all
                                                peer-checked:text-white peer-checked:shadow-md"
                                         style="border: 1.5px solid #E0E6ED; color: #6B7B8D;"
                                         :class="{ 'peer-checked:gradient-primary peer-checked:!border-transparent': true }">
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
                                <input type="radio" name="remarque_echange" value="accepte" {{ old('remarque_echange', $listing->remarque_echange) == 'accepte' ? 'checked' : '' }} class="peer sr-only">
                                <span class="px-4 py-2 rounded-full text-xs font-semibold inline-block transition-all cursor-pointer
                                             peer-checked:!border-[#27AE60] peer-checked:!text-[#27AE60] peer-checked:!bg-[#27AE60]/10"
                                      style="border: 1.5px solid #E0E6ED; color: #6B7B8D;">Accepte l'echange</span>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="remarque_echange" value="refuse" {{ old('remarque_echange', $listing->remarque_echange) == 'refuse' ? 'checked' : '' }} class="peer sr-only">
                                <span class="px-4 py-2 rounded-full text-xs font-semibold inline-block transition-all cursor-pointer
                                             peer-checked:!border-[#FF6B6B] peer-checked:!text-[#FF6B6B] peer-checked:!bg-[#FF6B6B]/10"
                                      style="border: 1.5px solid #E0E6ED; color: #6B7B8D;">N'accepte pas</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- SECTION: Contact --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);" x-show="category" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">
                            <span x-text="getSectionNumber('contact')">11</span>
                        </span>
                        Contact
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div data-old-phone="{{ old('numero_whatsapp', $listing->numero_whatsapp) }}" x-data="{
                            open: false, search: '', phoneNumber: '',
                            countries: [
                                { name: 'Algerie', code: '+213', flag: '\ud83c\udde9\ud83c\uddff' },
                                { name: 'Maroc', code: '+212', flag: '\ud83c\uddf2\ud83c\udde6' },
                                { name: 'Tunisie', code: '+216', flag: '\ud83c\uddf9\ud83c\uddf3' },
                                { name: 'Allemagne', code: '+49', flag: '\ud83c\udde9\ud83c\uddea' },
                                { name: 'Autriche', code: '+43', flag: '\ud83c\udde6\ud83c\uddf9' },
                                { name: 'Belgique', code: '+32', flag: '\ud83c\udde7\ud83c\uddea' },
                                { name: 'Bulgarie', code: '+359', flag: '\ud83c\udde7\ud83c\uddec' },
                                { name: 'Chypre', code: '+357', flag: '\ud83c\udde8\ud83c\uddfe' },
                                { name: 'Croatie', code: '+385', flag: '\ud83c\udded\ud83c\uddf7' },
                                { name: 'Danemark', code: '+45', flag: '\ud83c\udde9\ud83c\uddf0' },
                                { name: 'Espagne', code: '+34', flag: '\ud83c\uddea\ud83c\uddf8' },
                                { name: 'Estonie', code: '+372', flag: '\ud83c\uddea\ud83c\uddea' },
                                { name: 'Finlande', code: '+358', flag: '\ud83c\uddeb\ud83c\uddee' },
                                { name: 'France', code: '+33', flag: '\ud83c\uddeb\ud83c\uddf7' },
                                { name: 'Grece', code: '+30', flag: '\ud83c\uddec\ud83c\uddf7' },
                                { name: 'Hongrie', code: '+36', flag: '\ud83c\udded\ud83c\uddfa' },
                                { name: 'Irlande', code: '+353', flag: '\ud83c\uddee\ud83c\uddea' },
                                { name: 'Italie', code: '+39', flag: '\ud83c\uddee\ud83c\uddf9' },
                                { name: 'Lettonie', code: '+371', flag: '\ud83c\uddf1\ud83c\uddfb' },
                                { name: 'Lituanie', code: '+370', flag: '\ud83c\uddf1\ud83c\uddf9' },
                                { name: 'Luxembourg', code: '+352', flag: '\ud83c\uddf1\ud83c\uddfa' },
                                { name: 'Malte', code: '+356', flag: '\ud83c\uddf2\ud83c\uddf9' },
                                { name: 'Pays-Bas', code: '+31', flag: '\ud83c\uddf3\ud83c\uddf1' },
                                { name: 'Pologne', code: '+48', flag: '\ud83c\uddf5\ud83c\uddf1' },
                                { name: 'Portugal', code: '+351', flag: '\ud83c\uddf5\ud83c\uddf9' },
                                { name: 'Rep. tcheque', code: '+420', flag: '\ud83c\udde8\ud83c\uddff' },
                                { name: 'Roumanie', code: '+40', flag: '\ud83c\uddf7\ud83c\uddf4' },
                                { name: 'Slovaquie', code: '+421', flag: '\ud83c\uddf8\ud83c\uddf0' },
                                { name: 'Slovenie', code: '+386', flag: '\ud83c\uddf8\ud83c\uddee' },
                                { name: 'Suede', code: '+46', flag: '\ud83c\uddf8\ud83c\uddea' }
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
                                    <button type="button" @click="open = !open" class="glass-input flex items-center gap-1.5 pl-3 pr-2 py-3 text-sm font-medium rounded-xl h-full" style="min-width: 100px;">
                                        <span x-text="selected.flag" class="text-base leading-none"></span>
                                        <span x-text="selected.code" class="font-semibold" style="color: #1B2A4A; font-size: 12px;"></span>
                                        <svg class="w-3 h-3 ml-auto flex-shrink-0 transition-transform duration-200" :class="open && 'rotate-180'" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="open" x-cloak @click.away="open = false; search = ''" x-transition class="absolute z-50 left-0 mt-1.5 w-64 rounded-xl overflow-hidden" style="background: white; border: 1px solid #E0E6ED; box-shadow: 0 20px 40px rgba(0,0,0,0.12);">
                                        <div class="p-2" style="border-bottom: 1px solid #E0E6ED;">
                                            <input type="text" x-model="search" x-ref="wsSearchEdit" @keydown.escape="open = false" placeholder="Rechercher..." class="w-full px-3 py-2 text-xs rounded-lg" style="border: 1px solid #E0E6ED; outline: none; color: #1B2A4A;" x-init="$watch('open', v => v && $nextTick(() => $refs.wsSearchEdit.focus()))">
                                        </div>
                                        <div class="max-h-48 overflow-y-auto" style="scrollbar-width: thin;">
                                            <template x-for="country in filteredCountries" :key="'ews-'+country.code">
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
                        <div data-old-phone="{{ old('numero_mobile', $listing->numero_mobile) }}" x-data="{
                            open: false, search: '', phoneNumber: '',
                            countries: [
                                { name: 'Algerie', code: '+213', flag: '\ud83c\udde9\ud83c\uddff' },
                                { name: 'Maroc', code: '+212', flag: '\ud83c\uddf2\ud83c\udde6' },
                                { name: 'Tunisie', code: '+216', flag: '\ud83c\uddf9\ud83c\uddf3' },
                                { name: 'Allemagne', code: '+49', flag: '\ud83c\udde9\ud83c\uddea' },
                                { name: 'Autriche', code: '+43', flag: '\ud83c\udde6\ud83c\uddf9' },
                                { name: 'Belgique', code: '+32', flag: '\ud83c\udde7\ud83c\uddea' },
                                { name: 'Bulgarie', code: '+359', flag: '\ud83c\udde7\ud83c\uddec' },
                                { name: 'Chypre', code: '+357', flag: '\ud83c\udde8\ud83c\uddfe' },
                                { name: 'Croatie', code: '+385', flag: '\ud83c\udded\ud83c\uddf7' },
                                { name: 'Danemark', code: '+45', flag: '\ud83c\udde9\ud83c\uddf0' },
                                { name: 'Espagne', code: '+34', flag: '\ud83c\uddea\ud83c\uddf8' },
                                { name: 'Estonie', code: '+372', flag: '\ud83c\uddea\ud83c\uddea' },
                                { name: 'Finlande', code: '+358', flag: '\ud83c\uddeb\ud83c\uddee' },
                                { name: 'France', code: '+33', flag: '\ud83c\uddeb\ud83c\uddf7' },
                                { name: 'Grece', code: '+30', flag: '\ud83c\uddec\ud83c\uddf7' },
                                { name: 'Hongrie', code: '+36', flag: '\ud83c\udded\ud83c\uddfa' },
                                { name: 'Irlande', code: '+353', flag: '\ud83c\uddee\ud83c\uddea' },
                                { name: 'Italie', code: '+39', flag: '\ud83c\uddee\ud83c\uddf9' },
                                { name: 'Lettonie', code: '+371', flag: '\ud83c\uddf1\ud83c\uddfb' },
                                { name: 'Lituanie', code: '+370', flag: '\ud83c\uddf1\ud83c\uddf9' },
                                { name: 'Luxembourg', code: '+352', flag: '\ud83c\uddf1\ud83c\uddfa' },
                                { name: 'Malte', code: '+356', flag: '\ud83c\uddf2\ud83c\uddf9' },
                                { name: 'Pays-Bas', code: '+31', flag: '\ud83c\uddf3\ud83c\uddf1' },
                                { name: 'Pologne', code: '+48', flag: '\ud83c\uddf5\ud83c\uddf1' },
                                { name: 'Portugal', code: '+351', flag: '\ud83c\uddf5\ud83c\uddf9' },
                                { name: 'Rep. tcheque', code: '+420', flag: '\ud83c\udde8\ud83c\uddff' },
                                { name: 'Roumanie', code: '+40', flag: '\ud83c\uddf7\ud83c\uddf4' },
                                { name: 'Slovaquie', code: '+421', flag: '\ud83c\uddf8\ud83c\uddf0' },
                                { name: 'Slovenie', code: '+386', flag: '\ud83c\uddf8\ud83c\uddee' },
                                { name: 'Suede', code: '+46', flag: '\ud83c\uddf8\ud83c\uddea' }
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
                                    <button type="button" @click="open = !open" class="glass-input flex items-center gap-1.5 pl-3 pr-2 py-3 text-sm font-medium rounded-xl h-full" style="min-width: 100px;">
                                        <span x-text="selected.flag" class="text-base leading-none"></span>
                                        <span x-text="selected.code" class="font-semibold" style="color: #1B2A4A; font-size: 12px;"></span>
                                        <svg class="w-3 h-3 ml-auto flex-shrink-0 transition-transform duration-200" :class="open && 'rotate-180'" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="open" x-cloak @click.away="open = false; search = ''" x-transition class="absolute z-50 left-0 mt-1.5 w-64 rounded-xl overflow-hidden" style="background: white; border: 1px solid #E0E6ED; box-shadow: 0 20px 40px rgba(0,0,0,0.12);">
                                        <div class="p-2" style="border-bottom: 1px solid #E0E6ED;">
                                            <input type="text" x-model="search" x-ref="mbSearchEdit" @keydown.escape="open = false" placeholder="Rechercher..." class="w-full px-3 py-2 text-xs rounded-lg" style="border: 1px solid #E0E6ED; outline: none; color: #1B2A4A;" x-init="$watch('open', v => v && $nextTick(() => $refs.mbSearchEdit.focus()))">
                                        </div>
                                        <div class="max-h-48 overflow-y-auto" style="scrollbar-width: thin;">
                                            <template x-for="country in filteredCountries" :key="'emb-'+country.code">
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
                        <input type="email" name="contact_email" value="{{ old('contact_email', $listing->contact_email) }}"
                               class="glass-input w-full rounded-xl px-4 py-3 text-sm" placeholder="exemple@email.com">
                    </div>

                    <div class="mt-5 pt-5" style="border-top: 1px solid #E0E6ED;">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="mediation_enabled" value="1"
                                   {{ old('mediation_enabled', $listing->mediation_enabled) ? 'checked' : '' }}
                                   class="mt-1 rounded text-[#17A2B8] focus:ring-[#17A2B8]/30" style="border-color: #E0E6ED;">
                            <span>
                                <span class="text-sm font-medium" style="color: #1B2A4A;">Activer la mediation AlBabor</span>
                                <span class="block text-xs mt-0.5" style="color: #9BA8B7;">Les acheteurs passeront par nous. Votre numero restera masque.</span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- SECTION: Photos --}}
                <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);" x-show="category" x-transition>
                    <h2 class="text-base font-semibold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold gradient-primary">
                            <span x-text="getSectionNumber('photos')">12</span>
                        </span>
                        Photos
                    </h2>

                    {{-- Existing images --}}
                    @if($listing->media->count() > 0)
                        <div class="mb-5">
                            <label class="block text-xs font-semibold uppercase mb-2" style="color: #6B7B8D;">Images actuelles</label>
                            <div class="grid grid-cols-5 gap-3">
                                @foreach($listing->media as $media)
                                    <div class="relative group" x-data="{ marked: false }">
                                        <div class="aspect-square rounded-xl overflow-hidden" style="border: 1px solid #E0E6ED;">
                                            <img src="{{ Storage::url($media->thumbnail_path ?? $media->path) }}"
                                                 alt="" class="w-full h-full object-cover" :class="marked && 'opacity-30'">
                                        </div>
                                        <label class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full flex items-center justify-center cursor-pointer transition-all"
                                               :style="marked ? 'background: #FF6B6B; color: white;' : 'background: rgba(255,255,255,0.85); color: #9BA8B7; border: 1px solid rgba(0,0,0,0.06);'"
                                               @click="marked = !marked">
                                            <input type="checkbox" name="delete_images[]" value="{{ $media->id }}" class="sr-only" :checked="marked">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-2 text-[10px]" style="color: #9BA8B7;">Cliquez sur X pour marquer une image a supprimer</p>
                        </div>
                    @endif

                    {{-- New images --}}
                    <div class="border-2 border-dashed rounded-2xl p-6 text-center transition-colors" style="border-color: #E0E6ED;">
                        <svg class="mx-auto h-8 w-8" style="color: #9BA8B7;" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="mt-2">
                            <label for="new_images" class="cursor-pointer">
                                <span class="font-medium text-sm" style="color: #17A2B8;">Ajouter des photos</span>
                                <span class="text-sm" style="color: #9BA8B7;"> ou glissez ici</span>
                                <input id="new_images" name="new_images[]" type="file" class="sr-only" multiple accept="image/*">
                            </label>
                        </div>
                        <p class="text-[10px] mt-1" style="color: #9BA8B7;">JPEG, PNG, WebP — Max 5 Mo — 10 photos max</p>
                    </div>
                    <div id="newImagePreview" class="mt-3 grid grid-cols-5 gap-3"></div>
                </div>

                {{-- SUBMIT --}}
                <div class="flex justify-between items-center pt-2" x-show="category" x-transition>
                    <a href="{{ route('listings.my') }}"
                       class="px-6 py-3 rounded-xl text-sm font-medium transition-all" style="color: #6B7B8D; border: 1.5px solid #E0E6ED;">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-8 py-3 rounded-xl text-white text-sm font-semibold gradient-primary transition-all" style="box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editForm() {
            return {
                category: '{{ old('category', $listing->category) }}',
                currency: '{{ old('currency', $listing->currency ?? 'DZD') }}',
                hasRemorque: '{{ old('specs.extras.remorque', data_get($listing->specs, 'extras.remorque', '')) }}',
                hasPort: '{{ old('specs.extras.place_au_port', data_get($listing->specs, 'extras.place_au_port', '')) }}',
                getSectionNumber(section) {
                    let n = 3;
                    const c = this.category;
                    const isBJ = c === 'boat' || c === 'jetski';
                    if (section === 'prix') {
                        if (isBJ) n++;
                        if (c !== 'parts') n++;
                        if (c === 'boat') n += 2;
                        if (isBJ) n += 2;
                        return n;
                    }
                    if (section === 'contact') return this.getSectionNumber('prix') + 1;
                    if (section === 'photos') return this.getSectionNumber('prix') + 2;
                    return n;
                }
            }
        }
        document.getElementById('new_images')?.addEventListener('change', function(e) {
            const preview = document.getElementById('newImagePreview');
            preview.innerHTML = '';
            Array.from(e.target.files).slice(0, 10).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'aspect-square rounded-xl overflow-hidden';
                    div.style.border = '1px solid #E0E6ED';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        });
    </script>
</x-app-layout>
