@props(['listing'])

@php
    $categoryLabels = [
        'boat' => __('Bateau'),
        'jetski' => __('Jet-ski'),
        'engine' => __('Moteur'),
        'parts' => __('Pieces'),
    ];
    $categoryBadgeClasses = [
        'boat' => 'badge-boat',
        'jetski' => 'badge-jetski',
        'engine' => 'badge-engine',
        'parts' => 'badge-parts',
    ];
    $label = $categoryLabels[$listing->category] ?? __('Autre');
    $badgeClass = $categoryBadgeClasses[$listing->category] ?? 'bg-gray-500/80';
    $firstMedia = $listing->media->first();
    $isFavorited = auth()->check() && auth()->user()->hasFavorited($listing);
    $isFeatured = $listing->isFeatured();
    $annee = $listing->getSpec('general', 'annee_construction');
    $puissance = $listing->getSpec('motorisation', 'puissance_totale');
    $location = collect([$listing->wilaya, $listing->pays])->filter()->implode(', ');
    $isFreshlyRenewed = $listing->last_renewed_at && $listing->last_renewed_at->gt(now()->subHours(24));

    // Build outer card style — featured ring + freshly renewed glow can coexist
    $cardStyles = [];
    if ($isFreshlyRenewed && $isFeatured) {
        $cardStyles[] = 'box-shadow: 0 0 0 2px rgba(39,174,96,0.22), 0 0 0 4px rgba(255,184,0,0.30), 0 8px 24px rgba(39,174,96,0.14);';
    } elseif ($isFreshlyRenewed) {
        $cardStyles[] = 'box-shadow: 0 0 0 2px rgba(39,174,96,0.18), 0 8px 24px rgba(39,174,96,0.12);';
    } elseif ($isFeatured) {
        $cardStyles[] = 'box-shadow: 0 0 0 1.5px rgba(255,184,0,0.35), 0 2px 12px rgba(0,0,0,0.08);';
    }
    $cardInlineStyle = implode(' ', $cardStyles);
@endphp

<div class="listing-card group bg-white rounded-2xl overflow-hidden relative cursor-pointer shadow-sm hover:shadow-md transition-shadow duration-300 {{ $isFeatured ? 'listing-card--featured' : '' }} {{ $isFreshlyRenewed ? 'listing-card--fresh' : '' }}"
     style="{{ $cardInlineStyle }}"
     role="link"
     tabindex="0"
     onclick="if (!event.target.closest('form')) window.location.href='{{ route('listings.show', $listing) }}';"
     onkeydown="if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('form')) { event.preventDefault(); window.location.href='{{ route('listings.show', $listing) }}'; }">

    <a href="{{ route('listings.show', $listing) }}" class="block">

        {{-- Image Section --}}
        <div class="relative overflow-hidden" style="aspect-ratio: 4/3; background: linear-gradient(135deg, #E8EEF4 0%, #F0F4F8 100%);">

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
                    <span class="text-xs font-medium" style="color: #B8C4CE;">{{ __('Pas de photo') }}</span>
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
                    <span class="text-xs font-medium" style="color: #B8C4CE;">{{ __('Pas de photo') }}</span>
                </div>
            @endif

            {{-- Price overlay — bottom-left, dark gradient scrim --}}
            <div class="absolute inset-x-0 bottom-0 pointer-events-none" style="background: linear-gradient(to top, rgba(0,0,0,0.72) 0%, rgba(0,0,0,0.28) 55%, transparent 100%); height: 55%;">
            </div>
            <div class="absolute bottom-0 left-0 px-3 pb-2.5 z-10 max-w-[90%]">
                <span class="text-base font-extrabold text-white leading-tight block" style="text-shadow: 0 1px 4px rgba(0,0,0,0.4); letter-spacing: -0.01em;">
                    {{ $listing->formatted_price }}
                </span>
                @if($listing->centimes_display)
                    <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold leading-tight" style="background: rgba(241,196,15,0.92); color: #5A4214; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                        <span style="font-size:9px;">🇩🇿</span>
                        <span>{{ $listing->centimes_display }}</span>
                    </span>
                @endif
                @if($listing->formatted_converted_price)
                    <span class="text-[11px] font-medium block mt-0.5" style="color: rgba(255,255,255,0.78);">
                        {{ $listing->formatted_converted_price }}
                    </span>
                @endif
            </div>

            {{-- Category Badge (glass-morphism) — top-left --}}
            <span class="absolute top-3 left-3 z-10 px-2.5 py-1 text-[11px] font-semibold text-white rounded-full shadow-md backdrop-blur-md {{ $badgeClass }}" style="border: 1px solid rgba(255,255,255,0.15);">
                {{ $label }}
            </span>

            {{-- Freshly renewed badge — stacked below the category badge --}}
            @if($isFreshlyRenewed)
                <span class="listing-card__fresh-badge absolute left-3 z-10 inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold text-white rounded-full shadow-md"
                      style="top: 38px; background: linear-gradient(135deg, #27AE60 0%, #16A085 100%); border: 1px solid rgba(255,255,255,0.2); letter-spacing: 0.02em;">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    {{ __('Remontee') }}
                </span>
            @endif

        </div>

        {{-- Info Section --}}
        <div class="bg-white px-3 pt-2.5 pb-3">

            {{-- Title --}}
            <h3 class="text-sm font-bold line-clamp-1 leading-snug mb-1.5 group-hover:text-[#2471A3] transition-colors duration-200" style="color: #1B2A4A;">
                {{ $listing->title }}
            </h3>

            {{-- Location --}}
            @if($location)
                <div class="flex items-center gap-1 mb-2">
                    <svg class="w-3 h-3 flex-shrink-0" style="color: #6B7B8D;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-xs leading-none truncate" style="color: #6B7B8D;">{{ $location }}</span>
                </div>
            @endif

            {{-- Chips: Année + CV --}}
            @if($annee || $puissance)
                <div class="flex flex-wrap gap-2">
                    @if($annee)
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full" style="background: #F0F4F8; color: #1B2A4A;">
                            {{ $annee }}
                        </span>
                    @endif
                    @if($puissance)
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full" style="background: #F0F4F8; color: #1B2A4A;">
                            {{ $puissance }} CV
                        </span>
                    @endif
                </div>
            @endif

        </div>
    </a>

    {{-- Favorite Button — top-right of image --}}
    @auth
        <div class="absolute top-3 right-3 z-20">
            <form action="{{ route('favorites.toggle', $listing) }}" method="POST">
                @csrf
                <button type="submit"
                        class="favorite-heart-btn w-8 h-8 rounded-full flex items-center justify-center shadow-md backdrop-blur-md transition-all duration-300 hover:scale-110 active:scale-90"
                        style="{{ $isFavorited
                            ? 'background: #FF6B6B; color: white; box-shadow: 0 4px 12px rgba(255,107,107,0.4);'
                            : 'background: rgba(255,255,255,0.92); color: #9BA8B7; box-shadow: 0 2px 8px rgba(0,0,0,0.12); border: 1px solid rgba(255,255,255,0.3);' }}">
                    <svg class="w-4 h-4" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ $isFavorited ? '0' : '2' }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
            </form>
        </div>
    @endauth

</div>
