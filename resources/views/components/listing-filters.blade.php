@php
    $labels = ['q'=>'Recherche', 'category'=>'Catégorie', 'type'=>'Type', 'price_min'=>'Prix min.', 'price_max'=>'Prix max.', 'currency'=>'Devise', 'length_min'=>'Longueur min.', 'length_max'=>'Longueur max.', 'width_min'=>'Largeur min.', 'width_max'=>'Largeur max.', 'pays'=>'Pays', 'wilaya'=>'Localisation', 'location'=>'Ville / région', 'year_min'=>'Année min.', 'year_max'=>'Année max.', 'fabricant'=>'Marque', 'etat'=>'État', 'type_offre'=>'Offre', 'propulsion'=>'Type de moteur', 'fuel_type'=>'Carburant', 'engine_count'=>'Moteurs', 'power_min'=>'Puissance min.', 'power_max'=>'Puissance max.', 'drive_type'=>'Type d’hélice', 'engine_brand'=>'Marque moteur', 'cabins_min'=>'Cabines min.', 'berths_min'=>'Couchettes min.'];
    $active = collect(request()->only(array_keys($labels)))->filter(fn ($value) => is_scalar($value) && (string) $value !== '');
    $categories = ['boat'=>'Bateaux', 'jetski'=>'Jet-Skis', 'engine'=>'Moteurs', 'parts'=>'Pièces'];
@endphp
<form id="filterForm" action="{{ route('listings.index') }}" method="GET" class="listing-search bg-white rounded-2xl p-4 sm:p-6 mb-6 shadow-sm" style="color:#1B2A4A">
    <div class="flex gap-2 mb-4">
        <input name="q" value="{{ request('q') }}" class="glass-input min-w-0 flex-1 rounded-xl px-4 py-3" placeholder="{{ __('Marque, modèle ou caractéristiques…') }}" aria-label="{{ __('Rechercher des annonces') }}">
        <button class="rounded-xl px-4 py-3 font-semibold" style="background:#FACC15;color:#1B2A4A">{{ __('Rechercher') }}</button>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-2">
        <details>
            <summary>{{ __('Catégorie') }}</summary>
            <div class="filter-panel">
                <label>{{ __('Catégorie') }}<select name="category" onchange="this.form.elements.type.value = ''"><option value="">{{ __('Toutes') }}</option>@foreach($categories as $value => $label)<option value="{{ $value }}" @selected(request('category') === $value)>{{ __($label) }}</option>@endforeach</select></label>
                <label>{{ __('Type') }}<select name="type"><option value="">{{ __('Tous') }}</option>@foreach(\App\Models\Listing::CATEGORY_TYPES as $category => $types)<optgroup label="{{ __($categories[$category]) }}">@foreach($types as $value => $label)<option value="{{ $value }}" @selected(request('type') === $value)>{{ __($label) }}</option>@endforeach</optgroup>@endforeach</select></label>
            </div>
        </details>
        <details>
            <summary>{{ __('Prix') }}</summary>
            <div class="filter-panel">
                <label>{{ __('Devise') }}<select name="currency"><option value="">{{ __('Toutes') }}</option>@foreach(['DZD'=>'DZD · DA', 'EUR'=>'EUR · €', 'OTHER'=>'Autre'] as $value => $label)<option value="{{ $value }}" @selected(request('currency') === $value)>{{ __($label) }}</option>@endforeach</select></label>
                @foreach(['price_min'=>'Minimum', 'price_max'=>'Maximum'] as $key=>$label)<label>{{ __($label) }}<input type="number" min="0" name="{{ $key }}" value="{{ request($key) }}"></label>@endforeach
            </div>
        </details>
        <details>
            <summary>{{ __('Dimensions') }}</summary>
            <div class="filter-panel">
                @foreach(['length_min'=>'Longueur min. (m)', 'length_max'=>'Longueur max. (m)', 'width_min'=>'Largeur min. (m)', 'width_max'=>'Largeur max. (m)'] as $key=>$label)<label>{{ __($label) }}<input type="number" min="0" step="0.01" name="{{ $key }}" value="{{ request($key) }}"></label>@endforeach
            </div>
        </details>
        <details>
            <summary>{{ __('Emplacement') }}</summary>
            <div class="filter-panel">
                <label>{{ __('Pays') }}<select name="pays"><option value="">{{ __('Tous les pays') }}</option>@foreach(\App\Support\ListingCatalog::COUNTRIES as $country=>$flag)<option value="{{ $country }}" @selected(request('pays', request('wilaya')) === $country)>{{ $flag }} {{ $country }}</option>@endforeach</select></label>
                <label>{{ __('Ville / région') }}<input name="location" value="{{ request('location') }}" placeholder="{{ __('Alger, Oran, Alicante…') }}"></label>
            </div>
        </details>
        <details>
            <summary>{{ __('Année') }}</summary>
            <div class="filter-panel">@foreach(['year_min'=>'À partir de', 'year_max'=>'Jusqu’à'] as $key=>$label)<label>{{ __($label) }}<input type="number" min="1900" max="{{ date('Y')+1 }}" name="{{ $key }}" value="{{ request($key) }}"></label>@endforeach</div>
        </details>
        <details>
            <summary>{{ __('Marque') }}</summary>
            <div class="filter-panel"><label>{{ __('Fabricant') }}<input name="fabricant" value="{{ request('fabricant') }}" list="filter-brands"></label><datalist id="filter-brands">@foreach(\App\Support\ListingCatalog::BRANDS as $brand)<option value="{{ $brand }}">@endforeach</datalist></div>
        </details>
    </div>
    <details class="mt-3" @if(request()->anyFilled(['propulsion','fuel_type','engine_count','power_min','power_max','drive_type','engine_brand','etat','type_offre','cabins_min','berths_min'])) open @endif>
        <summary>{{ __('Recherche avancée · moteur et équipements') }}</summary>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 py-4">
            @foreach([
                'propulsion'=>['Type de moteur', array_combine(\App\Models\Listing::PROPULSION_OPTIONS, \App\Models\Listing::PROPULSION_OPTIONS)],
                'fuel_type'=>['Carburant', array_combine(\App\Models\Listing::CARBURANT_OPTIONS, \App\Models\Listing::CARBURANT_OPTIONS)],
                'drive_type'=>['Type d’hélice', array_combine(\App\Support\ListingCatalog::DRIVE_TYPES, \App\Support\ListingCatalog::DRIVE_TYPES)],
                'etat'=>['État', \App\Models\Listing::ETAT_LABELS], 'type_offre'=>['Type d’offre', \App\Models\Listing::TYPE_OFFRE_LABELS]
            ] as $key=>[$label,$options])
                <label>{{ __($label) }}<select name="{{ $key }}"><option value="">{{ __('Tous') }}</option>@foreach($options as $value=>$text)<option value="{{ $value }}" @selected(request($key)===$value)>{{ __($text) }}</option>@endforeach</select></label>
            @endforeach
            @foreach(['engine_count'=>'Nombre de moteurs', 'power_min'=>'Puissance min. (CV)', 'power_max'=>'Puissance max. (CV)', 'cabins_min'=>'Cabines min.', 'berths_min'=>'Couchettes min.'] as $key=>$label)
                <label>{{ __($label) }}<input type="number" min="0" name="{{ $key }}" value="{{ request($key) }}"></label>
            @endforeach
            <label>{{ __('Marque moteur') }}<input name="engine_brand" value="{{ request('engine_brand') }}"></label>
        </div>
    </details>
    <div class="flex flex-wrap items-center gap-2 mt-4">
        <button class="gradient-primary rounded-xl px-4 py-2 text-sm font-semibold text-white">{{ __('Appliquer les filtres') }}</button>
        <a href="{{ route('listings.index') }}" class="text-sm font-semibold px-2 py-2 text-cyan-700">{{ __('Réinitialiser les filtres') }}</a>
        @foreach($active as $key=>$value)
            <a class="rounded-full bg-cyan-50 border border-cyan-200 px-3 py-1.5 text-xs text-cyan-800" href="{{ route('listings.index', request()->except([$key, 'page'])) }}" aria-label="{{ __('Retirer') }} {{ __($labels[$key]) }}">{{ __($labels[$key]) }} : {{ $categories[$value] ?? $value }} ×</a>
        @endforeach
    </div>
</form>
<style>
    .listing-search summary { cursor:pointer; border-radius:8px; background:#E8F2FC; padding:10px 12px; font-size:13px; font-weight:600; color:#15547C; }
    .listing-search .filter-panel { display:grid; gap:10px; padding:12px 0; }
    .listing-search label { display:block; font-size:12px; font-weight:600; color:#526478; }
    .listing-search label input, .listing-search label select { display:block; width:100%; min-width:0; margin-top:5px; border:1px solid #DCE5ED; background:#F8FAFC; color:#1B2A4A; padding:10px; border-radius:8px; font-size:14px; }
</style>
