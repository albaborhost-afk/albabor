<x-app-layout
    :title="$listing->title"
    :description="$listing->category_label . ' — ' . ($listing->wilaya ?? '') . ' — ' . $listing->formatted_price . '. ' . \Str::limit(strip_tags((string) ($listing->description ?? '')), 120)"
    :ogImage="$listing->media->first()?->url ?? asset('images/og-image.png')"
>
    @php
        $specs = $listing->specs ?? [];
        $categoryLabels = ['boat' => 'Bateau', 'jetski' => 'Jet-ski', 'engine' => 'Moteur', 'parts' => 'Pieces'];
        $categoryIcons = [
            'boat' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l1.5-1.5M21 17l-1.5-1.5M12 3v4m-4 4l-1 3h10l-1-3M7 14l-4 3h18l-4-3"/></svg>',
            'jetski' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
            'engine' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            'parts' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>',
        ];
        $etatColors = [
            'jamais_utilise' => '#27AE60',
            'comme_neuf' => '#17A2B8',
            'bon_etat' => '#2471A3',
            'etat_moyen' => '#F39C12',
            'a_reviser' => '#E74C3C',
        ];
        $etatIcons = [
            'jamais_utilise' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            'comme_neuf' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            'bon_etat' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>',
            'etat_moyen' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
            'a_reviser' => '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
        ];
        $typeOffreColors = [
            'negociable' => ['bg' => 'rgba(23, 162, 184, 0.1)', 'color' => '#17A2B8', 'border' => 'rgba(23, 162, 184, 0.2)'],
            'fix' => ['bg' => 'rgba(27, 79, 114, 0.08)', 'color' => '#1B4F72', 'border' => 'rgba(27, 79, 114, 0.15)'],
            'offert' => ['bg' => 'rgba(39, 174, 96, 0.1)', 'color' => '#27AE60', 'border' => 'rgba(39, 174, 96, 0.2)'],
        ];
        $typeOffreLabels = ['negociable' => 'Negociable', 'fix' => 'Prix fixe', 'offert' => 'Offert'];

        // Format WhatsApp number: strip non-digits, replace leading 0 with 213
        $waRaw = preg_replace('/[^0-9]/', '', $listing->numero_whatsapp ?? '');
        if ($waRaw !== '' && $waRaw[0] === '0') {
            $waNumber = '213' . substr($waRaw, 1);
        } elseif ($waRaw !== '' && !str_starts_with($waRaw, '213')) {
            $waNumber = '213' . $waRaw;
        } else {
            $waNumber = $waRaw;
        }
    @endphp

    <div class="annonce-view-outside min-h-screen">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
                {{-- ============================================
                     MAIN CONTENT (Left Column)
                   ============================================ --}}
                <div class="lg:col-span-2 space-y-3 sm:space-y-6 reveal-left" style="transition-delay: 0.1s;">

                    {{-- ======== IMAGE GALLERY (Alpine.js) ======== --}}
                    @php
                        $images = $listing->images ?? ($listing->media ?? collect());
                        $hasImages = is_array($images) ? count($images) > 0 : $images->count() > 0;
                        $imageCount = is_array($images) ? count($images) : $images->count();
                        $imageUrls = collect($listing->images ?? $listing->media ?? [])->map(function ($img) {
                            if (is_string($img)) {
                                return $img;
                            }
                            return data_get($img, 'url')
                                ?: (data_get($img, 'path') ? asset('storage/' . data_get($img, 'path')) : '');
                        })->filter()->values()->toArray();
                    @endphp

                    {{-- Gallery: edge-to-edge --}}
                    <div class="-mx-3 sm:mx-0 rounded-none sm:rounded-3xl overflow-hidden" style="box-shadow: 0 8px 30px rgba(27, 79, 114, 0.12);"
                         x-data="{
                            currentIndex: 0,
                            imageCount: {{ $imageCount }},
                            images: @js($imageUrls),
                            transitioning: false,
                            // Swipe/drag state
                            dragStartX: 0, dragCurrentX: 0, dragging: false,
                            get currentImage() { return this.images[this.currentIndex] || ''; },
                            goTo(index) {
                                if (index === this.currentIndex || this.transitioning) return;
                                this.transitioning = true;
                                setTimeout(() => {
                                    this.currentIndex = index;
                                    setTimeout(() => { this.transitioning = false; }, 50);
                                }, 200);
                            },
                            next() { this.goTo((this.currentIndex + 1) % this.imageCount); },
                            prev() { this.goTo((this.currentIndex - 1 + this.imageCount) % this.imageCount); },
                            // Touch swipe
                            onTouchStart(e) { this.dragStartX = e.touches[0].clientX; this.dragging = true; },
                            onTouchMove(e) { if (this.dragging) this.dragCurrentX = e.touches[0].clientX; },
                            onTouchEnd() { this.finishDrag(); },
                            // Mouse drag
                            onMouseDown(e) { this.dragStartX = e.clientX; this.dragging = true; e.preventDefault(); },
                            onMouseMove(e) { if (this.dragging) this.dragCurrentX = e.clientX; },
                            onMouseUp() { this.finishDrag(); },
                            finishDrag() {
                                if (!this.dragging) return;
                                this.dragging = false;
                                const diff = this.dragStartX - this.dragCurrentX;
                                const threshold = 50;
                                if (diff > threshold) this.next();
                                else if (diff < -threshold) this.prev();
                                this.dragStartX = 0; this.dragCurrentX = 0;
                            }
                         }">

                        @if($hasImages)
                            {{-- Main image --}}
                            <div class="relative overflow-hidden cursor-grab active:cursor-grabbing aspect-[4/3] sm:aspect-[4/3] select-none"
                                 @touchstart="onTouchStart($event)" @touchmove="onTouchMove($event)" @touchend="onTouchEnd()"
                                 @mousedown="onMouseDown($event)" @mousemove="onMouseMove($event)" @mouseup="onMouseUp()" @mouseleave="onMouseUp()"
                                 @click="if(!dragging && Math.abs(dragStartX - dragCurrentX) < 10) openLightbox(currentIndex)">
                                <img :src="currentImage"
                                     alt="{{ $listing->title }}"
                                     draggable="false"
                                     class="listing-gallery-main-img w-full h-full object-cover transition-all duration-500 ease-out pointer-events-none"
                                     :class="{ 'opacity-0 scale-95': transitioning, 'opacity-100 scale-100': !transitioning }"
                                     style="will-change: transform, opacity;">

                                {{-- Bottom gradient overlay --}}
                                <div class="absolute inset-x-0 bottom-0 h-24 pointer-events-none" style="background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);"></div>

                                {{-- Sponsored Badge --}}
                                @if($listing->isFeatured())
                                    <div class="absolute top-3 left-3 z-10">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-semibold backdrop-blur-md" style="background: rgba(0,0,0,0.52); color: white; border: 1px solid rgba(255,255,255,0.25);">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                            {{ __('Sponsorisé') }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Top-right overlay: Edit button for owner, Favorite for others --}}
                                @auth
                                    @if(auth()->id() === $listing->user_id)
                                    {{-- Bouton Modifier (crayon) pour le propriétaire --}}
                                    <div class="absolute top-3 right-3 z-20">
                                        <a href="{{ route('listings.edit', $listing) }}"
                                           class="w-10 h-10 rounded-full flex items-center justify-center shadow-lg backdrop-blur-md transition-all duration-300 hover:scale-110 active:scale-90"
                                           style="background: rgba(255,255,255,0.92); color: #2471A3; box-shadow: 0 2px 10px rgba(0,0,0,0.18); border: 1px solid rgba(255,255,255,0.3);">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                    @else
                                    @php $isFavorited = auth()->user()->hasFavorited($listing); @endphp
                                    <div class="absolute top-3 right-3 z-20" @click.stop>
                                        <form action="{{ route('favorites.toggle', $listing) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="favorite-heart-btn w-10 h-10 rounded-full flex items-center justify-center shadow-lg backdrop-blur-md transition-all duration-300 hover:scale-110 active:scale-90"
                                                    style="{{ $isFavorited
                                                        ? 'background: #FF6B6B; color: white; box-shadow: 0 4px 14px rgba(255,107,107,0.45);'
                                                        : 'background: rgba(255,255,255,0.92); color: #9BA8B7; box-shadow: 0 2px 10px rgba(0,0,0,0.18); border: 1px solid rgba(255,255,255,0.3);' }}">
                                                <svg class="w-5 h-5" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ $isFavorited ? '0' : '2' }}">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                @endauth

                                {{-- Image Counter Badge --}}
                                <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-xl text-xs font-semibold text-white flex items-center gap-1.5 z-10 listing-badge-counter">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span x-text="(currentIndex + 1) + ' / ' + imageCount">1 / {{ $imageCount }}</span>
                                </div>

                                {{-- Navigation Arrows (only if multiple images) --}}
                                @if($imageCount > 1)
                                    <button @click.stop="prev()" class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center text-white z-10 transition-all duration-200 hover:scale-110 listing-nav-btn" style="background: rgba(0,0,0,0.35); backdrop-filter: blur(8px);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <button @click.stop="next()" class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 w-10 h-10 sm:w-11 sm:h-11 rounded-full flex items-center justify-center text-white z-10 transition-all duration-200 hover:scale-110 listing-nav-btn" style="background: rgba(0,0,0,0.35); backdrop-filter: blur(8px);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                @endif
                            </div>

                            {{-- Thumbnail Strip --}}
                            @if($imageCount > 1)
                                <div class="listing-thumb-strip flex gap-2 px-3 pb-3 pt-2.5 overflow-x-auto" style="background: linear-gradient(to bottom, rgba(27,79,114,0.04), rgba(27,79,114,0.01));">
                                    @foreach((is_array($images) ? $images : $images->toArray()) as $index => $image)
                                        @php
                                            $imageUrl = is_string($image)
                                                ? $image
                                                : (data_get($image, 'url')
                                                    ?: (data_get($image, 'path') ? asset('storage/' . data_get($image, 'path')) : ''));
                                            $thumbUrl = is_string($image)
                                                ? $image
                                                : (data_get($image, 'thumbnail_url')
                                                    ?: (data_get($image, 'thumbnail_path')
                                                        ? asset('storage/' . data_get($image, 'thumbnail_path'))
                                                        : $imageUrl));
                                        @endphp
                                        <button @click="goTo({{ $index }})"
                                                class="listing-thumb flex-shrink-0 w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] md:w-[5.5rem] md:h-[5.5rem] rounded-xl overflow-hidden transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#17A2B8]"
                                                :class="currentIndex === {{ $index }} ? 'listing-thumb-active' : 'listing-thumb-inactive'">
                                            <img src="{{ $thumbUrl }}" alt="Image {{ $index + 1 }}" class="w-full h-full object-cover">
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="flex items-center justify-center" style="aspect-ratio: 16/10; background: linear-gradient(135deg, #E8EEF4 0%, #F0F4F8 100%);">
                                <div class="text-center">
                                    <div class="w-20 h-20 mx-auto rounded-2xl flex items-center justify-center mb-3" style="background: rgba(155,168,183,0.1);">
                                        <svg class="w-10 h-10" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium" style="color: #9BA8B7;">{{ __('Aucune image disponible') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ======== YOUTUBE VIDEO ======== --}}
                    @if($listing->video_url)
                        @php
                            $videoId = null;
                            $parsed  = parse_url($listing->video_url);
                            $host    = $parsed['host'] ?? '';

                            if (str_contains($host, 'youtu.be')) {
                                // https://youtu.be/VIDEO_ID  or  https://youtu.be/VIDEO_ID?si=...
                                $candidate = trim($parsed['path'] ?? '', '/');
                                if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $candidate)) {
                                    $videoId = $candidate;
                                }
                            } elseif (str_contains($host, 'youtube.com')) {
                                $path = $parsed['path'] ?? '';
                                // /shorts/ID  |  /embed/ID  |  /live/ID
                                if (preg_match('#/(shorts|embed|live)/([a-zA-Z0-9_-]{11})#', $path, $m)) {
                                    $videoId = $m[2];
                                } else {
                                    // /watch?v=ID  —  v can be anywhere in the query string
                                    parse_str($parsed['query'] ?? '', $q);
                                    $v = $q['v'] ?? null;
                                    if ($v && preg_match('/^[a-zA-Z0-9_-]{11}$/', $v)) {
                                        $videoId = $v;
                                    }
                                }
                            }
                        @endphp
                        @if($videoId)
                            <div class="listing-card-frame rounded-3xl overflow-hidden mt-4">
                                <div class="p-4 sm:p-5">
                                    <h3 class="text-sm font-semibold mb-3 flex items-center gap-2" style="color: #1B2A4A;">
                                        <svg class="w-5 h-5" style="color: #E74C3C;" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814z"/><path fill="#fff" d="M9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                        Video
                                    </h3>
                                    <div class="rounded-2xl overflow-hidden" style="aspect-ratio: 16/9;">
                                        <iframe
                                            src="https://www.youtube.com/embed/{{ $videoId }}?rel=0&modestbranding=1"
                                            class="w-full h-full"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                            loading="lazy"
                                        ></iframe>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- ======== TITLE & PRICE SECTION ======== --}}
                    <div class="listing-card-frame listing-hero-card rounded-3xl overflow-hidden">
                        {{-- Price Hero Area --}}
                        <div class="p-4 sm:p-5 pb-4 annonce-price-hero">
                            <h1 class="text-base sm:text-lg md:text-xl font-bold leading-tight mb-3 text-slate-800 tracking-tight">{{ $listing->title }}</h1>

                            <div class="flex flex-col sm:flex-row sm:items-end gap-2 sm:gap-3">
                                {{-- Main Price --}}
                                <div class="flex items-baseline gap-2">
                                    <span class="annonce-price text-lg sm:text-xl md:text-2xl font-extrabold tracking-tight">{{ $listing->formatted_price }}</span>
                                </div>

                                {{-- Converted Price + Centimes algériens + Type Offre --}}
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($listing->centimes_display)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-lg" style="color: #92591C; background: linear-gradient(135deg, #FFF8E7, #FFE9B8); border: 1px solid rgba(241,196,15,0.45);">
                                            <span class="text-sm leading-none">🇩🇿</span>
                                            <span>{{ $listing->centimes_display }}</span>
                                        </span>
                                    @endif
                                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-lg" style="color: #9BA8B7; background: rgba(155,168,183,0.08);">{{ $listing->formatted_converted_price }}</span>

                                </div>
                            </div>
                        </div>

                        {{-- Badges Row --}}
                        <div class="px-4 sm:px-5 py-3" style="border-top: 1px solid #E0E6ED;">
                            <div class="flex flex-wrap gap-1.5">
                                {{-- Category --}}
                                @php $badgeClasses = ['boat' => 'badge-soft-boat', 'jetski' => 'badge-soft-jetski', 'engine' => 'badge-soft-engine', 'parts' => 'badge-soft-parts']; @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $badgeClasses[$listing->category] ?? 'bg-gray-100' }}">
                                    {!! $categoryIcons[$listing->category] ?? '' !!}
                                    {{ $categoryLabels[$listing->category] ?? ucfirst($listing->category) }}
                                </span>

                                {{-- Type (e.g. boat type) --}}
                                @if($listing->type_label)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold" style="background: rgba(23, 162, 184, 0.1); color: #17A2B8; border: 1px solid rgba(23, 162, 184, 0.2);">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        {{ $listing->type_label }}
                                    </span>
                                @endif

                                {{-- Etat (Condition) --}}
                                @if($listing->etat)
                                    @php $etatColor = $etatColors[$listing->etat] ?? '#6B7B8D'; @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold" style="background: {{ $etatColor }}12; color: {{ $etatColor }}; border: 1px solid {{ $etatColor }}20;">
                                        {!! $etatIcons[$listing->etat] ?? '' !!}
                                        {{ $listing->etat_short_label }}
                                    </span>
                                @endif

                                {{-- Echange --}}
                                @if($listing->remarque_echange === 'accepte')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold" style="background: rgba(39, 174, 96, 0.1); color: #27AE60; border: 1px solid rgba(39, 174, 96, 0.2);">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        {{ __('Echange accepte') }}
                                    </span>
                                @endif

                                {{-- Location --}}
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold" style="background: rgba(255, 107, 107, 0.08); color: #FF6B6B; border: 1px solid rgba(255, 107, 107, 0.15);">
                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                    @if($listing->pays){{ $listing->pays }}@endif{{ $listing->wilaya ? ($listing->pays ? ', ' : '') . $listing->wilaya : '' }}{{ $listing->visible_a ? ', ' . $listing->visible_a : '' }}
                                </span>
                            </div>
                        </div>

                        {{-- Stats Row --}}
                        <div class="px-4 sm:px-5 py-2.5 flex items-center gap-2 flex-wrap" style="border-top: 1px solid #E0E6ED; background: #FAFBFC;">
                            <div class="stat-card inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded-lg" style="color: #9BA8B7;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span style="color: #6B7B8D; font-weight: 600;">{{ number_format($listing->views_count ?? 0) }}</span> {{ __('vues') }}
                            </div>
                            <div class="w-1 h-1 rounded-full" style="background: #D0D7DE;"></div>
                            <div class="stat-card inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded-lg" style="color: #9BA8B7;">
                                <svg class="w-3.5 h-3.5" style="color: #FF6B6B;" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                <span style="color: #6B7B8D; font-weight: 600;">{{ number_format($listing->favorites_count ?? 0) }}</span> {{ __('favoris') }}
                            </div>
                            <div class="w-1 h-1 rounded-full" style="background: #D0D7DE;"></div>
                            <div class="stat-card inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded-lg" style="color: #9BA8B7;">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $listing->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    {{-- ======== DESCRIPTION ======== --}}
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2 sm:mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white gradient-primary flex-shrink-0" style="box-shadow: 0 3px 8px rgba(27,79,114,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            </span>
                            Description
                        </h2>
                        <div class="text-xs sm:text-sm leading-relaxed pl-9 sm:pl-[42px]" style="color: #6B7B8D; line-height: 1.65;">{!! nl2br(e($listing->description)) !!}</div>
                    </div>

                    {{-- ======== INFOS GENERALES ======== --}}
                    @if($listing->hasSpecSection('general'))
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2.5 sm:mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white gradient-primary flex-shrink-0" style="box-shadow: 0 3px 8px rgba(27,79,114,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            {{ __('Informations generales') }}
                        </h2>
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3">
                            @php $specIndex = 0; @endphp
                            @foreach([
                                'fabricant' => ['label' => 'Fabricant', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
                                'modele' => ['label' => 'Modele', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>'],
                                'annee_construction' => ['label' => 'Annee', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
                                'type_bateau' => ['label' => 'Type de bateau', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>'],
                                'type_jetski' => ['label' => 'Type de jet-ski', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'],
                                'nombre_places' => ['label' => 'Nb. places', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
                                'immatriculation' => ['label' => 'Immatriculation', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>'],
                                'part_type' => ['label' => 'Categorie de piece', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>'],
                                'compatible_with' => ['label' => 'Compatible avec', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>'],
                                'part_number' => ['label' => 'Reference', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'],
                                'garantie' => ['label' => 'Garantie', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'],
                                'emballage_origine' => ['label' => 'Emballage origine', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>'],
                            ] as $key => $info)
                                @if($listing->getSpec('general', $key))
                                    <div class="spec-item group p-2.5 sm:p-3.5 rounded-xl border transition-all duration-200" style="background: #FAFBFC; border-color: #EDF0F4;">
                                        <div class="flex items-center gap-1 sm:gap-1.5 mb-0.5 sm:mb-1">
                                            <span style="color: #9BA8B7;" class="group-hover:text-[#17A2B8] transition-colors">{!! $info['icon'] !!}</span>
                                            <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider" style="color: #9BA8B7;">{{ $info['label'] }}</p>
                                        </div>
                                        <p class="text-xs sm:text-sm font-semibold" style="color: #1B2A4A;">{{ $listing->getSpec('general', $key) }}</p>
                                    </div>
                                    @php $specIndex++; @endphp
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ======== DIMENSIONS ======== --}}
                    @if($listing->hasSpecSection('dimensions'))
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2.5 sm:mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white gradient-primary flex-shrink-0" style="box-shadow: 0 3px 8px rgba(27,79,114,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            </span>
                            {{ __('Dimensions') }}
                        </h2>
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3">
                            @foreach([
                                'longueur' => ['label' => 'Longueur', 'unit' => 'm', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>'],
                                'largeur' => ['label' => 'Largeur', 'unit' => 'm', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>'],
                                'tonnage' => ['label' => 'Tonnage', 'unit' => strtoupper($listing->getSpec('dimensions', 'tonnage_unit', 'kg')), 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>'],
                                'tirant_eau' => ['label' => "Tirant d'eau", 'unit' => 'm', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>'],
                                'tirant_air' => ['label' => "Tirant d'air", 'unit' => 'm', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>'],
                            ] as $key => $info)
                                @if($listing->getSpec('dimensions', $key))
                                    <div class="spec-item group p-2.5 sm:p-3.5 rounded-xl border transition-all duration-200" style="background: #FAFBFC; border-color: #EDF0F4;">
                                        <div class="flex items-center gap-1 sm:gap-1.5 mb-0.5 sm:mb-1">
                                            <span style="color: #9BA8B7;" class="group-hover:text-[#17A2B8] transition-colors">{!! $info['icon'] !!}</span>
                                            <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider" style="color: #9BA8B7;">{{ $info['label'] }}</p>
                                        </div>
                                        <p class="text-xs sm:text-sm font-semibold" style="color: #1B2A4A;">{{ $listing->getSpec('dimensions', $key) }} <span class="font-normal text-[10px] sm:text-xs" style="color: #9BA8B7;">{{ $info['unit'] }}</span></p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ======== MOTORISATION ======== --}}
                    @if($listing->hasSpecSection('motorisation'))
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2.5 sm:mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white gradient-primary flex-shrink-0" style="box-shadow: 0 3px 8px rgba(27,79,114,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            {{ __('Motorisation') }}
                        </h2>
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3">
                            @foreach([
                                'marque_moteur' => ['label' => 'Marque', 'unit' => '', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>'],
                                'propulsion' => ['label' => 'Propulsion', 'unit' => '', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'],
                                'type_carburant' => ['label' => 'Carburant', 'unit' => '', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>'],
                                'nombre_moteurs' => ['label' => 'Nb. moteurs', 'unit' => '', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>'],
                                'puissance_par_moteur' => ['label' => 'Puissance/moteur', 'unit' => 'CV', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'],
                                'puissance_totale' => ['label' => 'Puissance totale', 'unit' => 'CV', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'],
                                'nombre_heures' => ['label' => 'Heures', 'unit' => 'h', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
                                'cylindree' => ['label' => 'Cylindree', 'unit' => 'cc', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'],
                                'nombre_cylindres' => ['label' => 'Cylindres', 'unit' => '', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>'],
                                'refroidissement' => ['label' => 'Refroidissement', 'unit' => '', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>'],
                            ] as $key => $info)
                                @if($listing->getSpec('motorisation', $key))
                                    <div class="spec-item group p-2.5 sm:p-3.5 rounded-xl border transition-all duration-200" style="background: #FAFBFC; border-color: #EDF0F4;">
                                        <div class="flex items-center gap-1 sm:gap-1.5 mb-0.5 sm:mb-1">
                                            <span style="color: #9BA8B7;" class="group-hover:text-[#17A2B8] transition-colors">{!! $info['icon'] !!}</span>
                                            <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider" style="color: #9BA8B7;">{{ $info['label'] }}</p>
                                        </div>
                                        <p class="text-xs sm:text-sm font-semibold" style="color: #1B2A4A;">{{ $listing->getSpec('motorisation', $key) }}@if($info['unit']) <span class="font-normal text-[10px] sm:text-xs" style="color: #9BA8B7;">{{ $info['unit'] }}</span>@endif</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ======== RESERVOIRS ======== --}}
                    @if($listing->hasSpecSection('reservoirs'))
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2.5 sm:mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white gradient-primary flex-shrink-0" style="box-shadow: 0 3px 8px rgba(27,79,114,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            </span>
                            {{ __('Reservoirs') }}
                        </h2>
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3">
                            @foreach([
                                'reservoir_carburant' => ['label' => 'Carburant', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>'],
                                'reservoir_eau_douce' => ['label' => 'Eau douce', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>'],
                                'stockage' => ['label' => 'Stockage', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'],
                            ] as $key => $info)
                                @if($listing->getSpec('reservoirs', $key))
                                    <div class="spec-item group p-2.5 sm:p-3.5 rounded-xl border transition-all duration-200" style="background: #FAFBFC; border-color: #EDF0F4;">
                                        <div class="flex items-center gap-1 sm:gap-1.5 mb-0.5 sm:mb-1">
                                            <span style="color: #9BA8B7;" class="group-hover:text-[#17A2B8] transition-colors">{!! $info['icon'] !!}</span>
                                            <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider" style="color: #9BA8B7;">{{ $info['label'] }}</p>
                                        </div>
                                        <p class="text-xs sm:text-sm font-semibold" style="color: #1B2A4A;">{{ $listing->getSpec('reservoirs', $key) }} <span class="font-normal text-[10px] sm:text-xs" style="color: #9BA8B7;">L</span></p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ======== AMENAGEMENTS ======== --}}
                    @if($listing->hasSpecSection('amenagements'))
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2.5 sm:mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white gradient-primary flex-shrink-0" style="box-shadow: 0 3px 8px rgba(27,79,114,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 4h2v16H2M20 4h2v16h-2"/></svg>
                            </span>
                            {{ __('Amenagements') }}
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
                            @foreach([
                                'nombre_couchettes' => ['label' => 'Couchettes', 'icon' => '<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19V9a2 2 0 012-2h14a2 2 0 012 2v10M3 12h18M7 12V9h10v3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19v1M19 19v1"/></svg>'],
                                'nombre_cabines' => ['label' => 'Cabines', 'icon' => '<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M6 21V5a2 2 0 012-2h8a2 2 0 012 2v16"/><circle cx="14.5" cy="12" r="1" fill="currentColor" stroke="none"/></svg>'],
                                'nombre_sanitaire' => ['label' => 'Sanitaires', 'icon' => '<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3C8 8 5 11.4 5 15a7 7 0 0014 0c0-3.6-3-7-7-12z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 17.5a3 3 0 004 0"/></svg>'],
                                'nombre_cuisine' => ['label' => 'Cuisines', 'icon' => '<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3v18M7 3H5m2 0h2M10 3v5a2 2 0 002 2h0a2 2 0 002-2V3M10 3h4M19 3v18"/></svg>'],
                            ] as $key => $info)
                                @if($listing->getSpec('amenagements', $key))
                                    <div class="spec-item group p-2.5 sm:p-4 rounded-xl border text-center transition-all duration-200" style="background: #FAFBFC; border-color: #EDF0F4;">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 mx-auto rounded-xl flex items-center justify-center mb-1.5 sm:mb-2 transition-colors" style="background: rgba(27,79,114,0.06); color: #1B4F72;">
                                            {!! $info['icon'] !!}
                                        </div>
                                        <p class="text-xl sm:text-2xl font-extrabold" style="color: #1B4F72;">{{ $listing->getSpec('amenagements', $key) }}</p>
                                        <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider mt-0.5" style="color: #9BA8B7;">{{ $info['label'] }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ======== EQUIPEMENTS / OPTIONS / ELECTRONIQUE ======== --}}
                    @if($listing->hasSpecSection('tags'))
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2.5 sm:mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white gradient-primary flex-shrink-0" style="box-shadow: 0 3px 8px rgba(27,79,114,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            {{ __('Equipements & Options') }}
                        </h2>

                        @php
                            $tagSections = [
                                'equipement' => ['label' => __('Equipement de securite'), 'color' => '#1B4F72', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'],
                                'options' => ['label' => __('Options de confort'), 'color' => '#17A2B8', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>'],
                                'electronique' => ['label' => __('Electronique'), 'color' => '#2471A3', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>'],
                            ];
                        @endphp

                        @foreach($tagSections as $tagKey => $tagInfo)
                            @php
                                $tags = data_get($specs, "tags.{$tagKey}", []);
                                if (is_string($tags)) {
                                    $tags = array_map('trim', explode(',', $tags));
                                    $tags = array_values(array_filter($tags, fn($t) => $t !== ''));
                                } elseif (!is_array($tags)) {
                                    $tags = [];
                                }
                            @endphp
                            @if(!empty($tags))
                                <div class="{{ !$loop->first ? 'mt-5 pt-5' : '' }}" style="{{ !$loop->first ? 'border-top: 1px solid #E0E6ED;' : '' }}">
                                    <div class="flex items-center gap-2 mb-3">
                                        <span style="color: {{ $tagInfo['color'] }};">{!! $tagInfo['icon'] !!}</span>
                                        <p class="text-xs font-bold uppercase tracking-wider" style="color: {{ $tagInfo['color'] }};">{{ $tagInfo['label'] }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($tags as $tag)
                                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 hover:scale-105 cursor-default" style="background: {{ $tagInfo['color'] }}08; color: {{ $tagInfo['color'] }}; border: 1px solid {{ $tagInfo['color'] }}18;">
                                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @endif

                    {{-- ======== EXTRAS ======== --}}
                    @if($listing->hasSpecSection('extras'))
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2.5 sm:mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white gradient-primary flex-shrink-0" style="box-shadow: 0 3px 8px rgba(27,79,114,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </span>
                            {{ __('Extras') }}
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3">
                            @if($listing->getSpec('extras', 'annexe'))
                                <div class="spec-item group p-2.5 sm:p-3.5 rounded-xl border transition-all duration-200" style="background: #FAFBFC; border-color: #EDF0F4;">
                                    <div class="flex items-center gap-1 sm:gap-1.5 mb-0.5 sm:mb-1">
                                        <svg class="w-4 h-4" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        <p class="text-[10px] font-bold uppercase tracking-wider" style="color: #9BA8B7;">Annexe</p>
                                    </div>
                                    <p class="text-sm font-semibold" style="color: #1B2A4A;">{{ __('Oui, incluse') }}</p>
                                </div>
                            @endif
                            @if($listing->getSpec('extras', 'remorque'))
                                <div class="spec-item group p-2.5 sm:p-3.5 rounded-xl border transition-all duration-200" style="background: #FAFBFC; border-color: #EDF0F4;">
                                    <div class="flex items-center gap-1 sm:gap-1.5 mb-0.5 sm:mb-1">
                                        <svg class="w-4 h-4" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4"/></svg>
                                        <p class="text-[10px] font-bold uppercase tracking-wider" style="color: #9BA8B7;">Remorque</p>
                                    </div>
                                    <p class="text-sm font-semibold" style="color: #1B2A4A;">{{ __('Oui') }}{{ $listing->getSpec('extras', 'marque_remorque') ? ' — ' . $listing->getSpec('extras', 'marque_remorque') : '' }}</p>
                                </div>
                            @endif
                            @if($listing->getSpec('extras', 'place_au_port'))
                                <div class="spec-item group p-2.5 sm:p-3.5 rounded-xl border transition-all duration-200" style="background: #FAFBFC; border-color: #EDF0F4;">
                                    <div class="flex items-center gap-1 sm:gap-1.5 mb-0.5 sm:mb-1">
                                        <svg class="w-4 h-4" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <p class="text-[10px] font-bold uppercase tracking-wider" style="color: #9BA8B7;">Place au port</p>
                                    </div>
                                    <p class="text-sm font-semibold" style="color: #1B2A4A;">
                                        {{ __('Oui') }}{{ $listing->getSpec('extras', 'adresse_port') ? ' — ' . $listing->getSpec('extras', 'adresse_port') : '' }}
                                        @if($listing->getSpec('extras', 'longueur_place') || $listing->getSpec('extras', 'largeur_place'))
                                            <br><span class="text-xs font-normal" style="color: #9BA8B7;">{{ $listing->getSpec('extras', 'longueur_place') }}m x {{ $listing->getSpec('extras', 'largeur_place') }}m</span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- ======== SERVICES ALBABOR ======== --}}
                    @php
                        $services = data_get($specs, 'services', []);
                        $services = is_array($services) ? $services : [];
                    @endphp
                    @if(!empty($services))
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2.5 sm:mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white flex-shrink-0" style="background: linear-gradient(135deg, #F39C12, #F8C471); box-shadow: 0 3px 8px rgba(243,156,18,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </span>
                            {{ __('Services AlBabor demandes') }}
                        </h2>
                        <div class="grid grid-cols-1 gap-3">
                            @php
                                $serviceDetails = [
                                    'photo' => ['label' => 'Shooting photo professionnel', 'color' => '#F39C12', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
                                    'reception' => ['label' => 'Reception des appels', 'color' => '#27AE60', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>'],
                                    'video' => ['label' => 'Video de presentation professionnelle', 'color' => '#8E44AD', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>'],
                                ];
                            @endphp
                            @foreach($services as $service)
                                @php $detail = $serviceDetails[$service] ?? null; @endphp
                                @if($detail)
                                    <div class="flex items-center gap-3 p-3.5 rounded-xl border transition-all duration-200" style="background: {{ $detail['color'] }}08; border-color: {{ $detail['color'] }}20;">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: {{ $detail['color'] }}15; color: {{ $detail['color'] }};">
                                            {!! $detail['icon'] !!}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold" style="color: #1B2A4A;">{{ $detail['label'] }}</p>
                                            <p class="text-xs" style="color: #9BA8B7;">Service demande — notre equipe vous contactera</p>
                                        </div>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider flex-shrink-0" style="background: {{ $detail['color'] }}12; color: {{ $detail['color'] }};">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Demande
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ======== SHARE ======== --}}
                    <div class="listing-card-frame rounded-3xl p-3 sm:p-6">
                        <h2 class="text-sm sm:text-base font-bold mb-2 sm:mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                            <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center text-white gradient-primary flex-shrink-0" style="box-shadow: 0 3px 8px rgba(27,79,114,0.25);">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            </span>
                            {{ __('Partager cette annonce') }}
                        </h2>
                        <div class="flex flex-wrap gap-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="share-btn flex items-center gap-2 px-5 py-2.5 text-white rounded-xl text-sm font-semibold" style="background: #1877F2; box-shadow: 0 4px 12px rgba(24,119,242,0.25);">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Facebook
                            </a>
                            <a href="https://wa.me/?text={{ urlencode($listing->title . ' - ' . request()->url()) }}" target="_blank" class="share-btn flex items-center gap-2 px-5 py-2.5 text-white rounded-xl text-sm font-semibold" style="background: #25D366; box-shadow: 0 4px 12px rgba(37,211,102,0.25);">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                WhatsApp
                            </a>
                            <button onclick="copyToClipboard('{{ request()->url() }}')" class="share-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all" style="color: #6B7B8D; border: 1.5px solid #E0E6ED; background: white;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                <span id="copyBtnText">{{ __('Copier le lien') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ============================================
                     SIDEBAR (Right Column)
                   ============================================ --}}
                <div class="lg:col-span-1 space-y-5 reveal-right" style="transition-delay: 0.25s;">

                    {{-- ======== SELLER CARD ======== --}}
                    <div class="listing-card-frame listing-seller-card rounded-3xl overflow-hidden sticky top-24">
                        {{-- Seller Header --}}
                        <div class="p-5 sm:p-6 relative annonce-seller-header">
                            <div class="flex items-center gap-3.5">
                                <div class="relative flex-shrink-0">
                                    <div class="w-14 h-14 gradient-primary rounded-2xl flex items-center justify-center text-white font-bold text-xl" style="box-shadow: 0 4px 12px rgba(27,79,114,0.3);">
                                        {{ strtoupper(substr($listing->user?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    @if($listing->user?->verified_badge ?? false)
                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center" style="background: #27AE60; border: 2.5px solid white; box-shadow: 0 2px 4px rgba(39,174,96,0.3);">
                                            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-base truncate" style="color: #1B2A4A;">{{ $listing->user?->name ?? __('Vendeur') }}</h3>
                                    @if($listing->user?->verified_badge ?? false)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold" style="color: #27AE60;">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            {{ __('Vendeur verifie') }}
                                        </span>
                                    @endif
                                    <p class="text-xs mt-0.5" style="color: #9BA8B7;">{{ __('Membre depuis') }} {{ $listing->user?->created_at?->format('m/Y') ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Admin Quick Actions --}}
                        @auth
                            @if(auth()->user()->isAdmin())
                                <div class="px-5 pt-3 flex gap-2">
                                    <a href="{{ route('listings.edit', $listing) }}" class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5" style="background: rgba(36,113,163,0.1); color: #2471A3;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        {{ __('Modifier') }}
                                    </a>
                                    <a href="/admin/listings/{{ $listing->id }}/edit" class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5" style="background: rgba(27,79,114,0.1); color: #1B4F72;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Admin
                                    </a>
                                </div>
                            @endif
                        @endauth

                        {{-- Action Buttons --}}
                        <div class="p-5 space-y-3">
                            {{-- WhatsApp / Call / Email — visible to everyone (non-owner, mediation OFF) --}}
                            @if(!$listing->mediation_enabled && !(auth()->check() && auth()->id() === $listing->user_id))
                                @if($listing->numero_whatsapp)
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="w-full flex items-center justify-center gap-2.5 px-4 py-3.5 text-white rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5 cta-whatsapp-glow" style="background: linear-gradient(135deg, #25D366, #128C7E); box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z M12 0C5.373 0 0 5.373 0 12c0 2.126.553 4.122 1.519 5.859L.057 24l6.305-1.654A11.954 11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.892a9.875 9.875 0 01-5.031-1.378l-.361-.214-3.741.981.999-3.648-.235-.374A9.861 9.861 0 012.108 12C2.108 6.967 6.967 2.108 12 2.108S21.892 6.967 21.892 12 17.033 21.892 12 21.892z"/></svg>
                                        WhatsApp
                                    </a>
                                @endif

                                @if($listing->numero_mobile)
                                    <a href="tel:{{ $listing->numero_mobile }}" class="w-full flex items-center justify-center gap-2.5 px-4 py-3.5 text-white rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #2471A3, #1B4F72); box-shadow: 0 4px 15px rgba(36, 113, 163, 0.3);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ __('Appeler') }} {{ $listing->numero_mobile }}
                                    </a>
                                @elseif($listing->user?->phone ?? null)
                                    <a href="tel:{{ $listing->user?->phone }}" class="w-full flex items-center justify-center gap-2.5 px-4 py-3.5 text-white rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #2471A3, #1B4F72); box-shadow: 0 4px 15px rgba(36, 113, 163, 0.3);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ __('Appeler le vendeur') }}
                                    </a>
                                @endif

                                @if($listing->contact_email)
                                    <a href="mailto:{{ $listing->contact_email }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="color: #6B7B8D; border: 1.5px solid #E0E6ED; background: white;">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ __('Envoyer un email') }}
                                    </a>
                                @endif
                            @endif

                            {{-- Message / Mediation / Favoris — login required --}}
                            @auth
                                @if(auth()->id() !== $listing->user_id)
                                    {{-- Direct Message — pre-filled like Facebook Marketplace --}}
                                    <div class="mb-3" id="mobile-msg-section">
                                        <form action="{{ route('conversations.store', $listing) }}" method="POST" x-data="{ msg: '{{ old('body', 'Bonjour, je suis interesse(e) par votre annonce « ' . addslashes(Str::limit($listing->title, 40)) . ' ». Est-il/elle toujours disponible ?') }}' }">
                                            @csrf
                                            <textarea name="body" rows="3" required maxlength="2000" x-model="msg"
                                                      class="w-full px-4 py-3 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 transition-all duration-200 mb-2"
                                                      style="background: #F0F4F8; border: 1.5px solid #E0E6ED; color: #1B2A4A;"></textarea>
                                            @error('body')
                                                <p class="text-xs mb-2" style="color: #E74C3C;">{{ $message }}</p>
                                            @enderror
                                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl text-white font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                {{ __('Envoyer le message') }}
                                            </button>
                                        </form>
                                    </div>

                                    @if($listing->mediation_enabled)
                                        {{-- Mediation Button --}}
                                        <a href="{{ route('mediation.create', $listing) }}" class="w-full flex items-center justify-center gap-2 px-4 py-3.5 text-white rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5 animate-pulse-glow" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            {{ __('Contacter via mediation') }}
                                        </a>
                                        <div class="flex items-center justify-center gap-1.5 mt-1">
                                            <svg class="w-3 h-3" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <p class="text-center text-[10px] font-medium" style="color: #9BA8B7;">{{ __('Transaction securisee par AlBabor') }}</p>
                                        </div>
                                    @endif
                                @endif

                                {{-- Favorite Button --}}
                                <form action="{{ route('favorites.toggle', $listing) }}" method="POST">
                                    @csrf
                                    @php $isFavorited = auth()->user()->hasFavorited($listing) ?? false; @endphp
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5"
                                            style="{{ $isFavorited ? 'background: rgba(255,107,107,0.08); color: #FF6B6B; border: 1.5px solid rgba(255,107,107,0.25); box-shadow: 0 2px 8px rgba(255,107,107,0.1);' : 'background: white; color: #6B7B8D; border: 1.5px solid #E0E6ED;' }}">
                                        <svg class="w-5 h-5" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                        {{ $isFavorited ? __('Retirer des favoris') : __('Ajouter aux favoris') }}
                                    </button>
                                </form>
                            @else
                                {{-- Guest CTA: login to message/mediate --}}
                                <div class="text-center py-5">
                                    <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-4" style="background: linear-gradient(135deg, rgba(27,79,114,0.06), rgba(23,162,184,0.08));">
                                        <svg class="w-8 h-8" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium mb-4" style="color: #6B7B8D;">{{ $listing->mediation_enabled ? __('Connectez-vous pour contacter le vendeur') : __('Connectez-vous pour envoyer un message') }}</p>
                                    <a href="{{ route('login') }}" class="block w-full px-4 py-3.5 text-white rounded-xl font-semibold text-sm text-center transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">{{ __('Se connecter') }}</a>
                                    <p class="text-xs mt-3" style="color: #9BA8B7;">{{ __('Pas encore de compte?') }} <a href="{{ route('register') }}" class="font-semibold hover:underline" style="color: #17A2B8;">{{ __('Inscrivez-vous') }}</a></p>
                                </div>
                            @endauth
                        </div>

                    </div>

                    {{-- ======== SAFETY TIPS ======== --}}
                    <div class="listing-card-frame annonce-safety-card rounded-3xl p-5 sm:p-6">
                        <div class="flex items-center gap-2.5 mb-4">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center annonce-safety-icon">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Conseils de securite') }}</h3>
                        </div>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2.5 text-xs" style="color: #6B7B8D;">
                                <div class="w-5 h-5 flex-shrink-0 rounded-full flex items-center justify-center mt-0.5" style="background: rgba(39,174,96,0.1);">
                                    <svg class="w-3 h-3" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </div>
                                <span class="leading-relaxed">{{ __('Verifiez le produit avant le paiement') }}</span>
                            </li>
                            <li class="flex items-start gap-2.5 text-xs" style="color: #6B7B8D;">
                                <div class="w-5 h-5 flex-shrink-0 rounded-full flex items-center justify-center mt-0.5" style="background: rgba(39,174,96,0.1);">
                                    <svg class="w-3 h-3" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </div>
                                <span class="leading-relaxed">{{ __('Rencontrez dans un lieu public') }}</span>
                            </li>
                            <li class="flex items-start gap-2.5 text-xs" style="color: #6B7B8D;">
                                <div class="w-5 h-5 flex-shrink-0 rounded-full flex items-center justify-center mt-0.5" style="background: rgba(39,174,96,0.1);">
                                    <svg class="w-3 h-3" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </div>
                                <span class="leading-relaxed">{{ __("Ne payez jamais a l'avance") }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ======== SIMILAR LISTINGS ======== --}}
            @if(isset($relatedListings) && $relatedListings->count() > 0)
                <div class="mt-16 pt-2">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center text-white shadow-md annonce-similar-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            </span>
                            <h2 class="text-lg font-bold text-slate-800">{{ __('Annonces similaires') }}</h2>
                        </div>
                        <a href="{{ route('listings.index', ['category' => $listing->category]) }}" class="annonce-see-all text-sm font-semibold flex items-center gap-1.5 px-4 py-2.5 rounded-xl transition-all duration-200 hover:-translate-y-0.5">
                            {{ __('Voir tout') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                        @foreach($relatedListings as $related)
                            <x-listing-card :listing="$related" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ======== LIGHTBOX ======== --}}
    <div id="lightbox" class="lightbox" onclick="closeLightbox(event)">
        <button class="lightbox-close" onclick="closeLightbox(event)"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        <button class="lightbox-nav lightbox-prev" onclick="navigateLightbox(event, -1)"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
        <img id="lightboxImage" src="" alt="" class="lightbox-image">
        <button class="lightbox-nav lightbox-next" onclick="navigateLightbox(event, 1)"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 px-4 py-2 rounded-full text-white font-medium text-sm" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(8px);"><span id="lightboxCounter">1 / 1</span></div>
    </div>

    <style>
        /* WhatsApp CTA pulse glow */
        @keyframes pulse-glow-whatsapp {
            0%, 100% { box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3); }
            50% { box-shadow: 0 8px 30px rgba(37, 211, 102, 0.45); }
        }
        .cta-whatsapp-glow { animation: pulse-glow-whatsapp 3s ease-in-out infinite; }

        /* ========== Creative gallery frame ========== */
        .listing-gallery-frame {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            box-shadow:
                0 0 0 1px rgba(23, 162, 184, 0.12),
                0 0 0 4px rgba(27, 79, 114, 0.06),
                0 4px 6px -1px rgba(0, 0, 0, 0.06),
                0 14px 28px -4px rgba(27, 79, 114, 0.1),
                0 24px 48px -12px rgba(0, 0, 0, 0.08);
        }
        /* Single radius for all gallery corners (no mismatch) */
        .listing-gallery-inner {
            background: linear-gradient(135deg, #E8EEF4 0%, #F0F4F8 100%);
            border-radius: 16px;
            /* Inset frame via shadow (no extra div = clean corners) */
            box-shadow: inset 0 0 0 3px rgba(255, 255, 255, 0.9), inset 0 0 0 4px rgba(27, 79, 114, 0.06);
        }
        .listing-gallery-main-img {
            display: block;
            border-radius: 16px;
            object-fit: cover;
        }
        .listing-badge-counter {
            background: rgba(27, 79, 114, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .listing-nav-btn {
            background: rgba(27, 79, 114, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 12px rgba(27, 79, 114, 0.25);
        }
        .listing-nav-btn:hover { background: rgba(27, 79, 114, 0.9); }

        /* Thumbnail strip: creative frame */
        .listing-thumb-strip {
            background: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 100%);
            border-top: 1px solid rgba(23, 162, 184, 0.1);
            scrollbar-width: thin;
        }
        .listing-thumb-strip::-webkit-scrollbar { height: 5px; }
        .listing-thumb-strip::-webkit-scrollbar-track { background: rgba(27, 79, 114, 0.06); border-radius: 6px; }
        .listing-thumb-strip::-webkit-scrollbar-thumb { background: rgba(23, 162, 184, 0.25); border-radius: 6px; }
        .listing-thumb {
            border: 2px solid transparent;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }
        .listing-thumb-inactive {
            opacity: 0.7;
            border-color: rgba(203, 213, 225, 0.8);
        }
        .listing-thumb-inactive:hover { opacity: 1; }
        .listing-thumb-active {
            border-color: #17A2B8;
            box-shadow: 0 0 0 2px rgba(23, 162, 184, 0.2), 0 4px 14px rgba(23, 162, 184, 0.2);
            transform: scale(1.02);
        }

        /* Content cards: softer creative frame */
        .listing-card-frame {
            background: linear-gradient(145deg, #ffffff 0%, #fafbfc 100%);
            box-shadow:
                0 0 0 1px rgba(27, 79, 114, 0.06),
                0 4px 12px -2px rgba(0, 0, 0, 0.05),
                0 12px 24px -6px rgba(27, 79, 114, 0.08);
        }

        /* ========== Annonce view from outside (premium look) ========== */
        .annonce-view-outside {
            background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 24%, #f8fafc 100%);
        }
        .annonce-price-hero {
            background: linear-gradient(135deg, rgba(27, 79, 114, 0.04) 0%, rgba(23, 162, 184, 0.06) 50%, rgba(20, 184, 166, 0.03) 100%);
        }
        .annonce-price {
            color: #0f766e;
            letter-spacing: -0.03em;
            text-shadow: 0 1px 2px rgba(15, 118, 110, 0.08);
        }
        .listing-hero-card {
            box-shadow:
                0 0 0 1px rgba(15, 118, 110, 0.08),
                0 4px 14px -2px rgba(0, 0, 0, 0.06),
                0 16px 32px -8px rgba(27, 79, 114, 0.12);
        }
        .annonce-seller-header {
            background: linear-gradient(135deg, rgba(27, 79, 114, 0.05) 0%, rgba(23, 162, 184, 0.08) 100%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        }
        .listing-seller-card {
            box-shadow:
                0 0 0 1px rgba(23, 162, 184, 0.08),
                0 8px 24px -6px rgba(27, 79, 114, 0.1);
        }
        .annonce-safety-card {
            border: 1px solid rgba(251, 191, 36, 0.15);
            background: linear-gradient(145deg, #fffbeb 0%, #fefce8 50%, #ffffff 100%);
        }
        .annonce-safety-icon {
            background: rgba(245, 158, 11, 0.12);
        }
        .annonce-similar-icon {
            background: linear-gradient(135deg, #0f766e, #0d9488);
        }
        .annonce-see-all {
            color: #0f766e;
            background: rgba(20, 184, 166, 0.08);
        }
        .annonce-see-all:hover { background: rgba(20, 184, 166, 0.14); color: #0d9488; }

        .cursor-pointer { cursor: pointer; }
    </style>

    <script>
        const images = @json($imageUrls);
        let currentLightboxIndex = 0;

        function openLightbox(index) {
            if (images.length === 0) return;
            currentLightboxIndex = index;
            document.getElementById('lightboxImage').src = images[currentLightboxIndex];
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
            updateLightboxCounter();
        }
        function closeLightbox(event) {
            if (event.target.id === 'lightbox' || event.target.closest('.lightbox-close')) {
                document.getElementById('lightbox').classList.remove('active');
                document.body.style.overflow = '';
            }
        }
        function navigateLightbox(event, direction) {
            event.stopPropagation();
            currentLightboxIndex = (currentLightboxIndex + direction + images.length) % images.length;
            document.getElementById('lightboxImage').src = images[currentLightboxIndex];
            updateLightboxCounter();
        }
        function updateLightboxCounter() {
            document.getElementById('lightboxCounter').textContent = `${currentLightboxIndex + 1} / ${images.length}`;
        }

        document.addEventListener('keydown', function(e) {
            const lightbox = document.getElementById('lightbox');
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox({ target: lightbox });
            if (e.key === 'ArrowLeft') navigateLightbox(e, -1);
            if (e.key === 'ArrowRight') navigateLightbox(e, 1);
        });

        // Touch swipe in lightbox
        (function() {
            const lb = document.getElementById('lightbox');
            let lbTouchStartX = 0;
            lb.addEventListener('touchstart', function(e) {
                lbTouchStartX = e.touches[0].clientX;
            }, { passive: true });
            lb.addEventListener('touchend', function(e) {
                const diff = lbTouchStartX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 40) {
                    navigateLightbox(e, diff > 0 ? 1 : -1);
                }
            }, { passive: true });
        })();

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                const btn = document.getElementById('copyBtnText');
                btn.textContent = 'Lien copie!';
                setTimeout(() => { btn.textContent = 'Copier le lien'; }, 2000);
            });
        }

        // Scroll-reveal observer for entrance animations
        document.addEventListener('DOMContentLoaded', function() {
            const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
            if (revealElements.length > 0 && 'IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('active');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
                revealElements.forEach(function(el) { observer.observe(el); });
            } else {
                // Fallback: show everything immediately
                revealElements.forEach(function(el) { el.classList.add('active'); });
            }
        });
    </script>

    {{-- ======== STICKY MOBILE CONTACT BAR ======== --}}
    @php
        $showStickyBar = !(auth()->check() && auth()->id() === $listing->user_id)
            && (!$listing->mediation_enabled || auth()->check());
    @endphp
    @if($showStickyBar)
        <div class="lg:hidden fixed bottom-0 inset-x-0 z-50 safe-area-bottom" style="background: rgba(255,255,255,0.97); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-top: 1px solid rgba(224,230,237,0.8); box-shadow: 0 -4px 20px rgba(0,0,0,0.08);">
            <div class="flex items-center gap-2 px-4 py-3 max-w-lg mx-auto">
                {{-- Messaging / Mediation button --}}
                @auth
                    @if($listing->mediation_enabled)
                        <a href="{{ route('mediation.create', $listing) }}"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-3 rounded-xl text-white font-semibold text-xs transition-all"
                           style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 12px rgba(27,79,114,0.3);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            {{ __('Mediation') }}
                        </a>
                    @else
                        <button onclick="document.getElementById('mobile-msg-section').scrollIntoView({behavior:'smooth', block:'center'})"
                                class="flex-1 flex items-center justify-center gap-2 px-3 py-3 rounded-xl text-white font-semibold text-xs transition-all"
                                style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 12px rgba(27,79,114,0.3);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Message
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="flex-1 flex items-center justify-center gap-2 px-3 py-3 rounded-xl text-white font-semibold text-xs transition-all"
                       style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 12px rgba(27,79,114,0.3);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Message
                    </a>
                @endauth

                {{-- Direct contact — visible to everyone when mediation is OFF --}}
                @if(!$listing->mediation_enabled)
                    @if($listing->numero_whatsapp)
                        <a href="https://wa.me/{{ $waNumber }}" target="_blank"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-3 rounded-xl text-white font-semibold text-xs transition-all"
                           style="background: linear-gradient(135deg, #25D366, #128C7E); box-shadow: 0 4px 12px rgba(37,211,102,0.3);">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z M12 0C5.373 0 0 5.373 0 12c0 2.126.553 4.122 1.519 5.859L.057 24l6.305-1.654A11.954 11.954 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.892a9.875 9.875 0 01-5.031-1.378l-.361-.214-3.741.981.999-3.648-.235-.374A9.861 9.861 0 012.108 12C2.108 6.967 6.967 2.108 12 2.108S21.892 6.967 21.892 12 17.033 21.892 12 21.892z"/></svg>
                            WhatsApp
                        </a>
                    @endif

                    @if($listing->numero_mobile || $listing->user?->phone)
                        <a href="tel:{{ $listing->numero_mobile ?: $listing->user?->phone }}"
                           class="flex items-center justify-center gap-2 px-3 py-3 rounded-xl font-semibold text-xs transition-all"
                           style="background: white; color: #1B4F72; border: 1.5px solid #E0E6ED;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ __('Appeler') }}
                        </a>
                    @endif
                @endif
            </div>
        </div>
        {{-- Spacer so content isn't hidden behind sticky bar --}}
        <div class="lg:hidden h-16"></div>
    @endif

    {{-- Add scroll target ID to the message section in sidebar --}}
    <style>
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0px); }
    </style>
</x-app-layout>
