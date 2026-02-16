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
                <span style="color: #1B2A4A;" class="font-medium">Mes Favoris</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #E74C3C 0%, #FF6B6B 50%, #FF8E8E 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl animate-float" style="background: rgba(255,255,255,0.12);"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full blur-3xl animate-float-reverse" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-extrabold tracking-tight text-white">Mes Favoris</h1>
                            <p class="mt-0.5 text-white/80">
                                <span class="font-semibold text-white">{{ $favorites->total() ?? $favorites->count() }}</span>
                                {{ ($favorites->total() ?? $favorites->count()) > 1 ? 'annonces sauvegardees' : 'annonce sauvegardee' }}
                            </p>
                        </div>
                    </div>
                </div>

                <a href="{{ route('listings.index') }}"
                   class="inline-flex items-center px-5 py-2.5 bg-white rounded-xl font-semibold transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                   style="color: #E74C3C; box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Decouvrir plus d'annonces
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="background: #F0F4F8;" class="pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(39, 174, 96, 0.08); border: 1px solid rgba(39, 174, 96, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="font-medium" style="color: #27AE60;">{{ session('success') }}</p>
                </div>
            @endif

            @if($favorites->count() > 0)
                <!-- Listings Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($favorites as $listing)
                        <div class="animate-fade-in-up opacity-0" style="animation-delay: {{ $loop->index * 0.08 }}s;">
                            <x-listing-card :listing="$listing" />
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if(method_exists($favorites, 'hasPages') && $favorites->hasPages())
                    <div class="mt-12 flex justify-center">
                        <div class="inline-flex items-center gap-1 p-2 rounded-2xl bg-white" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06);">
                            {{ $favorites->links() }}
                        </div>
                    </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="bg-white rounded-2xl p-12 text-center" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <div class="relative w-40 h-40 mx-auto mb-8 animate-gentle-pulse">
                        <div class="absolute inset-0 rounded-full" style="background: rgba(255, 107, 107, 0.08);"></div>
                        <div class="absolute inset-4 rounded-full" style="background: rgba(255, 107, 107, 0.12);"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-20 h-20" style="color: #FF6B6B;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold mb-3" style="color: #1B2A4A;">
                        Aucun favori pour le moment
                    </h3>
                    <p class="text-lg mb-8 max-w-md mx-auto" style="color: #6B7B8D;">
                        Parcourez nos annonces et cliquez sur le coeur pour sauvegarder vos bateaux preferes ici.
                    </p>

                    <a href="{{ route('listings.index') }}"
                       class="inline-flex items-center px-6 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-1"
                       style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Parcourir les annonces
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
