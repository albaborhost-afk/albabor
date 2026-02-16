<x-app-layout>
    <!-- Breadcrumb Bar -->
    <div style="background: #FFFFFF; border-bottom: 1px solid #E0E6ED;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('home') }}" style="color: #9BA8B7;" class="hover:opacity-80 transition-opacity flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Accueil
                </a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">Mes Annonces</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl animate-float" style="background: rgba(255,255,255,0.08);"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full blur-3xl animate-float-reverse" style="background: rgba(255,255,255,0.05);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">Mes Annonces</h1>
                    <p class="mt-1 text-white/70">Gerez vos annonces publiees</p>
                </div>
                <a href="{{ route('listings.create') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-white rounded-xl font-bold transition-all duration-300 transform hover:-translate-y-0.5"
                   style="color: #1B4F72; box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouvelle annonce
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="background: #F0F4F8;" class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(39, 174, 96, 0.08); border: 1px solid rgba(39, 174, 96, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-medium" style="color: #27AE60;">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #E74C3C;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-medium" style="color: #E74C3C;">{{ session('error') }}</p>
                </div>
            @endif

            @if($listings->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden md:block bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <table class="min-w-full">
                        <thead>
                            <tr style="background: #F0F4F8; border-bottom: 1px solid #E0E6ED;">
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">Annonce</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">Prix</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">Statut</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">Stats</th>
                                <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listings as $listing)
                                <tr style="border-bottom: 1px solid #F0F4F8;" class="hover:bg-[#F8FAFC] transition-colors">
                                    <!-- Listing Info -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden" style="background: #E8EEF4;">
                                                @if($listing->media->first())
                                                    <img src="{{ Storage::url($listing->media->first()->thumbnail_path ?? $listing->media->first()->path) }}"
                                                         alt="" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center" style="color: #9BA8B7;">
                                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold" style="color: #1B2A4A;">{{ Str::limit($listing->title, 40) }}</div>
                                                <div class="text-sm mt-0.5" style="color: #9BA8B7;">{{ $listing->category_label }} · {{ $listing->wilaya }}</div>
                                                @if($listing->isFeatured())
                                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-semibold" style="background: linear-gradient(135deg, #FFA500, #FF7200); color: white;">
                                                        En vedette · {{ $listing->featured_until->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Price -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold" style="color: #1B4F72;">{{ $listing->formatted_price }}</div>
                                        @if($listing->formatted_converted_price)
                                            <div class="text-xs mt-0.5" style="color: #9BA8B7;">{{ $listing->formatted_converted_price }}</div>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusStyles = [
                                                'active' => ['bg' => 'rgba(39, 174, 96, 0.1)', 'color' => '#27AE60'],
                                                'pending_review' => ['bg' => 'rgba(243, 156, 18, 0.1)', 'color' => '#F39C12'],
                                                'awaiting_payment' => ['bg' => 'rgba(255, 107, 107, 0.1)', 'color' => '#FF6B6B'],
                                                'sold' => ['bg' => 'rgba(23, 162, 184, 0.1)', 'color' => '#17A2B8'],
                                                'paused' => ['bg' => 'rgba(155, 168, 183, 0.1)', 'color' => '#9BA8B7'],
                                                'rejected' => ['bg' => 'rgba(231, 76, 60, 0.1)', 'color' => '#E74C3C'],
                                                'draft' => ['bg' => 'rgba(107, 123, 141, 0.1)', 'color' => '#6B7B8D'],
                                            ];
                                            $s = $statusStyles[$listing->status] ?? $statusStyles['draft'];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                              style="background: {{ $s['bg'] }}; color: {{ $s['color'] }};">
                                            {{ $listing->status_label }}
                                        </span>
                                    </td>

                                    <!-- Stats -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-4 text-sm" style="color: #6B7B8D;">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                {{ $listing->views_count }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" style="color: #FF6B6B;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                </svg>
                                                {{ $listing->favorites_count }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($listing->status === 'awaiting_payment')
                                                <a href="{{ route('listings.payment', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(255, 107, 107, 0.1); color: #FF6B6B;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                    Payer
                                                </a>
                                            @endif

                                            @if($listing->status === 'active')
                                                <a href="{{ route('listings.show', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(23, 162, 184, 0.1); color: #17A2B8;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    Voir
                                                </a>
                                                <a href="{{ route('listings.edit', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(107, 123, 141, 0.1); color: #6B7B8D;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Modifier
                                                </a>

                                                @if(!$listing->isFeatured())
                                                    <a href="{{ route('listings.feature', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(243, 156, 18, 0.1); color: #F39C12;">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                                        Vedette
                                                    </a>
                                                @endif

                                                <form action="{{ route('listings.pause', $listing) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(155, 168, 183, 0.1); color: #9BA8B7;">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        Pause
                                                    </button>
                                                </form>

                                                <form action="{{ route('listings.sold', $listing) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        Vendu
                                                    </button>
                                                </form>
                                            @endif

                                            @if($listing->status === 'paused')
                                                <form action="{{ route('listings.reactivate', $listing) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        Reactiver
                                                    </button>
                                                </form>
                                            @endif

                                            @if(in_array($listing->status, ['draft', 'rejected', 'paused']))
                                                <a href="{{ route('listings.edit', $listing) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(107, 123, 141, 0.1); color: #6B7B8D;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Modifier
                                                </a>
                                            @endif

                                            <form action="{{ route('listings.destroy', $listing) }}" method="POST" class="inline" onsubmit="return confirm('Etes-vous sur de vouloir supprimer cette annonce ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105" style="background: rgba(231, 76, 60, 0.1); color: #E74C3C;">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden space-y-4">
                    @foreach($listings as $listing)
                        <div class="bg-white rounded-2xl overflow-hidden animate-fade-in-up opacity-0" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03); animation-delay: {{ $loop->index * 0.08 }}s;">
                            <div class="flex items-start p-4 gap-4">
                                <!-- Image -->
                                <div class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden" style="background: #E8EEF4;">
                                    @if($listing->media->first())
                                        <img src="{{ Storage::url($listing->media->first()->thumbnail_path ?? $listing->media->first()->path) }}"
                                             alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center" style="color: #9BA8B7;">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold truncate" style="color: #1B2A4A;">{{ $listing->title }}</h3>
                                    <p class="text-xs mt-0.5" style="color: #9BA8B7;">{{ $listing->category_label }} · {{ $listing->wilaya }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-sm font-bold" style="color: #1B4F72;">{{ $listing->formatted_price }}</span>
                                        @php
                                            $s = $statusStyles[$listing->status] ?? $statusStyles['draft'];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background: {{ $s['bg'] }}; color: {{ $s['color'] }};">{{ $listing->status_label }}</span>
                                    </div>
                                    <!-- Stats -->
                                    <div class="flex items-center gap-3 mt-1.5 text-xs" style="color: #9BA8B7;">
                                        <span class="flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-0.5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            {{ $listing->views_count }}
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-0.5" style="color: #FF6B6B;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            {{ $listing->favorites_count }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Actions -->
                            <div class="flex flex-wrap gap-2 px-4 pb-4" style="border-top: 1px solid #F0F4F8; padding-top: 0.75rem;">
                                @if($listing->status === 'awaiting_payment')
                                    <a href="{{ route('listings.payment', $listing) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background: rgba(255, 107, 107, 0.1); color: #FF6B6B;">Payer</a>
                                @endif
                                @if($listing->status === 'active')
                                    <a href="{{ route('listings.show', $listing) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background: rgba(23, 162, 184, 0.1); color: #17A2B8;">Voir</a>
                                    <a href="{{ route('listings.edit', $listing) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background: rgba(107, 123, 141, 0.1); color: #6B7B8D;">Modifier</a>
                                    @if(!$listing->isFeatured())
                                        <a href="{{ route('listings.feature', $listing) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background: rgba(243, 156, 18, 0.1); color: #F39C12;">Vedette</a>
                                    @endif
                                    <form action="{{ route('listings.sold', $listing) }}" method="POST" class="inline">@csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">Vendu</button>
                                    </form>
                                @endif
                                @if($listing->status === 'paused')
                                    <form action="{{ route('listings.reactivate', $listing) }}" method="POST" class="inline">@csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background: rgba(39, 174, 96, 0.1); color: #27AE60;">Reactiver</button>
                                    </form>
                                @endif
                                @if(in_array($listing->status, ['draft', 'rejected', 'paused']))
                                    <a href="{{ route('listings.edit', $listing) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background: rgba(107, 123, 141, 0.1); color: #6B7B8D;">Modifier</a>
                                @endif
                                <form action="{{ route('listings.destroy', $listing) }}" method="POST" class="inline" onsubmit="return confirm('Etes-vous sur de vouloir supprimer cette annonce ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background: rgba(231, 76, 60, 0.1); color: #E74C3C;">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($listings->hasPages())
                    <div class="mt-8 flex justify-center">
                        <div class="inline-flex items-center gap-1 p-2 rounded-2xl bg-white" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                            {{ $listings->links() }}
                        </div>
                    </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="bg-white rounded-2xl p-12 text-center" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <div class="relative w-40 h-40 mx-auto mb-8 animate-float-gentle">
                        <div class="absolute inset-0 rounded-full" style="background: rgba(23, 162, 184, 0.08);"></div>
                        <div class="absolute inset-4 rounded-full" style="background: rgba(23, 162, 184, 0.12);"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-20 h-20" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">Aucune annonce pour le moment</h3>
                    <p class="text-lg mb-8 max-w-md mx-auto" style="color: #6B7B8D;">
                        Commencez par publier votre premiere annonce et atteignez des milliers d'acheteurs.
                    </p>
                    <a href="{{ route('listings.create') }}"
                       class="inline-flex items-center px-6 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-1"
                       style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Publier une annonce
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
