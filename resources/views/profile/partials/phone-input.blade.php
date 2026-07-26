@props([
    'id' => 'phone_number',
    'name' => 'phone',
    'value' => null,
    'required' => false,
])

<div
    data-current-phone="{{ $value }}"
    x-data="{
        open: false,
        search: '',
        countries: [
            { name: 'Algerie', code: '+213' },
            { name: 'Maroc', code: '+212' },
            { name: 'Tunisie', code: '+216' },
            { name: 'Allemagne', code: '+49' },
            { name: 'Autriche', code: '+43' },
            { name: 'Belgique', code: '+32' },
            { name: 'Bulgarie', code: '+359' },
            { name: 'Croatie', code: '+385' },
            { name: 'Danemark', code: '+45' },
            { name: 'Espagne', code: '+34' },
            { name: 'Estonie', code: '+372' },
            { name: 'Finlande', code: '+358' },
            { name: 'France', code: '+33' },
            { name: 'Grece', code: '+30' },
            { name: 'Hongrie', code: '+36' },
            { name: 'Irlande', code: '+353' },
            { name: 'Italie', code: '+39' },
            { name: 'Lettonie', code: '+371' },
            { name: 'Lituanie', code: '+370' },
            { name: 'Luxembourg', code: '+352' },
            { name: 'Malte', code: '+356' },
            { name: 'Pays-Bas', code: '+31' },
            { name: 'Pologne', code: '+48' },
            { name: 'Portugal', code: '+351' },
            { name: 'Rep. tcheque', code: '+420' },
            { name: 'Roumanie', code: '+40' },
            { name: 'Slovaquie', code: '+421' },
            { name: 'Slovenie', code: '+386' },
            { name: 'Suede', code: '+46' },
        ],
        selected: { name: 'Algerie', code: '+213' },
        phoneNumber: '',
        get filteredCountries() {
            if (!this.search) return this.countries;

            const searchTerm = this.search.toLowerCase();
            return this.countries.filter((country) => {
                return country.name.toLowerCase().includes(searchTerm) || country.code.includes(searchTerm);
            });
        },
        sanitizeNumber(value) {
            return value
                .replace(/[٠-٩]/g, d => String.fromCharCode(d.charCodeAt(0) - 1584))
                .replace(/[۰-۹]/g, d => String.fromCharCode(d.charCodeAt(0) - 1728))
                .replace(/\D/g, '')
                .replace(/^0+/, '');
        },
        get fullPhone() {
            const number = this.sanitizeNumber(this.phoneNumber);
            return number ? `${this.selected.code}${number}` : '';
        },
        handlePhoneInput() {
            const cleaned = this.sanitizeNumber(this.phoneNumber);
            if (cleaned !== this.phoneNumber) this.phoneNumber = cleaned;
        },
        selectCountry(country) {
            this.selected = country;
            this.open = false;
            this.search = '';
        },
        init() {
            const currentPhone = (this.$el.dataset.currentPhone || '').replace(/[^0-9+]/g, '');
            if (!currentPhone) {
                return;
            }

            const sortedCountries = [...this.countries].sort((left, right) => right.code.length - left.code.length);
            for (const country of sortedCountries) {
                if (currentPhone.startsWith(country.code)) {
                    this.selected = country;
                    this.phoneNumber = currentPhone.substring(country.code.length);
                    return;
                }
            }

            this.phoneNumber = currentPhone;
        },
    }"
>
    <label for="{{ $id }}" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">
        {{ __('messages.phone') }}
    </label>

    <input type="hidden" name="{{ $name }}" :value="fullPhone">

    <div class="flex gap-2">
        <div class="relative">
            <button
                type="button"
                @click="open = !open"
                class="glass-input flex items-center gap-2 pl-3 pr-2 py-3 text-sm font-medium rounded-xl h-full"
                style="min-width: 122px;"
            >
                <span x-text="selected.code" class="font-semibold" style="color: #1B2A4A;"></span>
                <svg class="w-3.5 h-3.5 ml-auto flex-shrink-0 transition-transform duration-200" :class="open && 'rotate-180'" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div
                x-show="open"
                x-cloak
                @click.away="open = false; search = ''"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute z-50 left-0 mt-1.5 w-72 rounded-xl overflow-hidden"
                style="background: white; border: 1px solid #E0E6ED; box-shadow: 0 20px 40px rgba(0,0,0,0.12), 0 4px 12px rgba(0,0,0,0.06);"
            >
                <div class="p-2" style="border-bottom: 1px solid #E0E6ED;">
                    <input
                        type="text"
                        x-model="search"
                        x-ref="countrySearch"
                        @keydown.escape="open = false"
                        placeholder="{{ __('messages.country_search_placeholder') }}"
                        class="w-full px-3 py-2 text-sm rounded-lg"
                        style="border: 1px solid #E0E6ED; outline: none; color: #1B2A4A;"
                        x-init="$watch('open', value => value && $nextTick(() => $refs.countrySearch.focus()))"
                    >
                </div>

                <div class="overflow-y-auto" style="max-height: min(70vh, 480px); scrollbar-width: thin;">
                    <template x-for="country in filteredCountries" :key="country.code">
                        <button
                            type="button"
                            @click="selectCountry(country)"
                            class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm transition-colors duration-150 hover:bg-gray-50"
                            :class="selected.code === country.code && 'bg-cyan-50'"
                        >
                            <span x-text="country.name" class="flex-1 text-left font-medium" style="color: #1B2A4A;"></span>
                            <span x-text="country.code" class="font-medium" style="color: #6B7B8D;"></span>
                        </button>
                    </template>

                    <div x-show="filteredCountries.length === 0" class="px-3 py-4 text-center text-sm" style="color: #9BA8B7;">
                        {{ __('messages.no_country_found') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1">
            <input
                id="{{ $id }}"
                type="tel"
                x-model="phoneNumber"
                @input="handlePhoneInput()"
                @if($required) required @endif
                placeholder="{{ __('messages.phone_placeholder') }}"
                class="glass-input w-full py-3 px-4 rounded-xl"
            >
        </div>
    </div>
</div>
