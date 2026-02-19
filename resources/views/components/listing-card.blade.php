@props(['listing'])

@php
    $categoryLabels = [
        'boat' => 'Bateau',
        'jetski' => 'Jet-ski',
        'engine' => 'Moteur',
        'parts' => 'Pieces',
    ];
    $categoryBadgeClasses = [
        'boat' => 'badge-boat',
        'jetski' => 'badge-jetski',
        'engine' => 'badge-engine',
        'parts' => 'badge-parts',
    ];
    $categoryAccentColors = [
        'boat' => '#1B4F72',
        'jetski' => '#17A2B8',
        'engine' => '#F39C12',
        'parts' => '#9B59B6',
    ];
    $label = $categoryLabels[$listing->category] ?? 'Autre';
    $badgeClass = $categoryBadgeClasses[$listing->category] ?? 'bg-gray-500/80';
    $accentColor = $categoryAccentColors[$listing->category] ?? '#1B4F72';
    $firstMedia = $listing->media->first();
    $isFavorited = auth()->check() && auth()->user()->hasFavorited($listing);
    $isFeatured = $listing->isFeatured();
@endphp

<div class="listing-card card-shine group bg-white rounded-2xl overflow-hidden relative {{ $isFeatured ? 'listing-card--featured' : '' }}"
     style="{{ $isFeatured ? 'box-shadow: 0 0 0 1.5px rgba(255,184,0,0.35), 0 2px 12px rgba(0,0,0,0.06);' : '' }}">

    {{-- Category accent bar at the top --}}
    <div class="h-[3px] w-full" style="background: linear-gradient(90deg, {{ $accentColor }}, {{ $accentColor }}99);"></div>

    <a href="{{ route('listings.show', $listing) }}" class="block">
        {{-- Image Section --}}
        <div class="relative aspect-[4/3] overflow-hidden" style="background: linear-gradient(135deg, #E8EEF4 0%, #F0F4F8 100%);">
            @if($firstMedia)
                <img src="{{ $firstMedia->url }}"
                     alt="{{ $listing->title }}"
                     class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                     loading="lazy"
                     onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="absolute inset-0 flex-col items-center justify-center gap-2" style="display: none;">
                    <svg class="w-12 h-12" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs font-medium" style="color: #B8C4CE;">Pas de photo</span>
                </div>
            @else
                <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                    @if($listing->category === 'boat')
                        <svg class="w-12 h-12" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                            <path d="M3 17h18l-3-8H6L3 17zM12 3v6M9 6h6"/>
                        </svg>
                    @elseif($listing->category === 'jetski')
                        <svg class="w-12 h-12" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                            <path d="M3 17h18l-3-8H6L3 17zM8 9l4-6 4 6"/>
                        </svg>
                    @elseif($listing->category === 'engine')
                        <svg class="w-12 h-12" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    @else
                        <svg class="w-12 h-12" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    @endif
                    <span class="text-xs font-medium" style="color: #B8C4CE;">Pas de photo</span>
                </div>
            @endif

            {{-- Bottom gradient overlay for text readability --}}
            @if($firstMedia)
                <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/30 via-black/10 to-transparent pointer-events-none"></div>
            @endif

            {{-- Category Badge (glass-morphism) --}}
            <span class="absolute top-3 left-3 px-2.5 py-1 text-[11px] font-semibold text-white rounded-full shadow-md backdrop-blur-md {{ $badgeClass }}" style="border: 1px solid rgba(255,255,255,0.15);">
                {{ $label }}
            </span>

            {{-- Featured Badge --}}
            @if($isFeatured)
                <span class="absolute top-3 right-10 px-2.5 py-1 text-[11px] font-semibold rounded-full shadow-md featured-badge-glow" style="background: linear-gradient(135deg, #FFB800, #FF8C00); color: white; border: 1px solid rgba(255,255,255,0.2);">
                    <svg class="w-3 h-3 inline -mt-0.5 mr-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Vedette
                </span>
            @endif

            {{-- Image count badge --}}
            @if($listing->media->count() > 1)
                <span class="absolute bottom-3 right-3 px-2 py-0.5 text-[10px] font-semibold rounded-full backdrop-blur-md" style="background: rgba(0,0,0,0.45); color: white; border: 1px solid rgba(255,255,255,0.1);">
                    <svg class="w-3 h-3 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $listing->media->count() }}
                </span>
            @endif

            {{-- Condition badge --}}
            @if($listing->etat)
                <span class="absolute bottom-3 left-3 px-2 py-0.5 text-[10px] font-bold rounded-full backdrop-blur-md"
                      style="background: rgba(255,255,255,0.92); color: {{ $listing->etat === 'jamais_utilise' ? '#27AE60' : ($listing->etat === 'comme_neuf' ? '#17A2B8' : ($listing->etat === 'bon_etat' ? '#2471A3' : '#6B7B8D')) }}; border: 1px solid rgba(0,0,0,0.04);">
                    {{ $listing->etat_short_label }}
                </span>
            @endif
        </div>

        {{-- Divider between image and content --}}
        <div class="h-px w-full" style="background: linear-gradient(90deg, transparent, #E0E6ED, transparent);"></div>

        {{-- Content --}}
        <div class="p-4">
            {{-- Title --}}
            <h3 class="font-bold line-clamp-2 mb-2.5 leading-snug text-[13.5px] group-hover:text-[#1B4F72] transition-colors duration-300" style="color: #1B2A4A;">
                {{ $listing->title }}
            </h3>

            {{-- Location & Time --}}
            <div class="flex items-center gap-3 text-[11px] mb-3" style="color: #9BA8B7;">
                {{-- Wilaya with map pin --}}
                <span class="flex items-center gap-1 min-w-0">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate font-medium" style="color: #6B7B8D;">{{ $listing->wilaya }}</span>
                </span>

                {{-- Separator dot --}}
                <span class="flex-shrink-0 w-1 h-1 rounded-full" style="background: #D5DCE4;"></span>

                {{-- Time with clock icon --}}
                <span class="flex items-center gap-1 min-w-0">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="truncate">{{ $listing->created_at->diffForHumans() }}</span>
                </span>
            </div>

            {{-- Price & Offer Type --}}
            <div class="flex items-end justify-between pt-3" style="border-top: 1px solid #F0F4F8;">
                <div>
                    <span class="text-[17px] font-extrabold block leading-tight transition-colors duration-300 group-hover:text-[#17A2B8]" style="color: #1B4F72; letter-spacing: -0.02em;">
                        {{ $listing->formatted_price }}
                    </span>
                    @if($listing->formatted_converted_price)
                        <span class="text-[10.5px] block mt-1 font-medium" style="color: #9BA8B7;">
                            {{ $listing->formatted_converted_price }}
                        </span>
                    @endif
                </div>
                @if($listing->type_offre === 'negociable')
                    <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full" style="background: rgba(23, 162, 184, 0.08); color: #17A2B8;">Neg.</span>
                @elseif($listing->type_offre === 'offert')
                    <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full" style="background: rgba(39, 174, 96, 0.08); color: #27AE60;">Offert</span>
                @elseif($listing->type_offre === 'fix')
                    <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full" style="background: rgba(27, 79, 114, 0.08); color: #1B4F72;">Fixe</span>
                @endif
            </div>
        </div>
    </a>

    {{-- Favorite Button --}}
    @auth
        <div class="absolute top-4 right-3 z-10" style="top: calc(3px + 0.75rem);">
            <form action="{{ route('favorites.toggle', $listing) }}" method="POST">
                @csrf
                <button type="submit"
                        class="favorite-heart-btn w-9 h-9 rounded-full flex items-center justify-center shadow-md backdrop-blur-md transition-all duration-300 hover:scale-110 active:scale-90"
                        style="{{ $isFavorited
                            ? 'background: #FF6B6B; color: white; box-shadow: 0 4px 12px rgba(255,107,107,0.4);'
                            : 'background: rgba(255,255,255,0.92); color: #9BA8B7; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.3);' }}">
                    <svg class="w-4 h-4 transition-transform duration-300" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ $isFavorited ? '0' : '2' }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
            </form>
        </div>
    @endauth
</div>
