@props(['specs' => []])
<section class="bg-white rounded-2xl p-6 space-y-6" style="border-top:4px solid #17A2B8; box-shadow:0 10px 25px rgba(0,0,0,.06)" x-show="category === 'boat' || category === 'jetski'">
    <h2 class="text-base font-semibold" style="color:#1B2A4A">{{ __('Équipements et options') }}</h2>
    @foreach(\App\Support\ListingCatalog::EQUIPMENT as $group => $suggestions)
        @php
            $selected = old('specs.tags.'.$group, data_get($specs, 'tags.'.$group, []));
            $selected = is_array($selected) ? $selected : array_filter(array_map('trim', explode(',', (string) $selected)));
            $custom = array_values(array_diff($selected, $suggestions));
        @endphp
        <div x-data="{ customTags: @js($custom), newTag: '', add() { const tag = this.newTag.trim(); if (tag && !this.customTags.includes(tag)) this.customTags.push(tag); this.newTag = ''; } }">
            <h3 class="text-sm font-semibold mb-3" style="color:#526478">{{ __(\App\Support\ListingCatalog::EQUIPMENT_LABELS[$group]) }}</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($suggestions as $item)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="specs[tags][{{ $group }}][]" value="{{ $item }}" @checked(in_array($item, $selected)) class="peer sr-only">
                        <span class="inline-flex rounded-full border border-slate-200 px-3 py-2 text-xs text-slate-600 peer-checked:bg-cyan-50 peer-checked:border-cyan-600 peer-checked:text-cyan-800 peer-focus-visible:ring-2 peer-focus-visible:ring-cyan-600">{{ $item }}</span>
                    </label>
                @endforeach
                <template x-for="(tag, index) in customTags" :key="tag">
                    <button type="button" @click="customTags.splice(index, 1)" class="rounded-full border border-cyan-600 bg-cyan-50 px-3 py-2 text-xs text-cyan-800" :aria-label="'Retirer ' + tag">
                        <input type="hidden" name="specs[tags][{{ $group }}][]" :value="tag">
                        <span x-text="tag"></span> ×
                    </button>
                </template>
            </div>
            <div class="flex gap-2 mt-3">
                <input type="text" x-model="newTag" @keydown.enter.prevent="add()" maxlength="150" class="glass-input min-w-0 flex-1 rounded-xl px-3 py-2 text-sm" placeholder="{{ __('Ajouter un équipement…') }}" aria-label="{{ __('Ajouter un équipement') }}">
                <button type="button" @click="add()" class="rounded-xl px-4 py-2 text-sm font-semibold text-white gradient-primary">{{ __('Ajouter') }}</button>
            </div>
        </div>
    @endforeach
</section>
