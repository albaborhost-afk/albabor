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
                <a href="{{ route('profile.show') }}" style="color: #9BA8B7;" class="hover:opacity-80">Mon Profil</a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">Modifier</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">Modifier le profil</h1>
                    <p class="mt-0.5 text-white/70">Mettez a jour vos informations</p>
                </div>
            </div>
        </div>
    </div>

    <div style="background: #F0F4F8;" class="py-8 pb-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <ul class="list-disc list-inside text-sm" style="color: #E74C3C;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Profile Info -->
            <form action="{{ route('profile.update') }}" method="POST" class="bg-white rounded-2xl p-6 mb-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-bold mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                    <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Informations personnelles
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Nom complet</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="glass-input w-full py-3 px-4 rounded-xl">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Adresse email</label>
                        <input type="email" id="email" value="{{ $user->email }}" disabled
                               class="w-full py-3 px-4 rounded-xl cursor-not-allowed" style="background: #F0F4F8; border: 1px solid #E0E6ED; color: #9BA8B7;">
                        <p class="text-xs mt-1" style="color: #9BA8B7;">L'adresse email ne peut pas etre modifiee</p>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Telephone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" required
                               class="glass-input w-full py-3 px-4 rounded-xl">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-0.5"
                            style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                        Sauvegarder
                    </button>
                </div>
            </form>

            <!-- Change Password -->
            <form action="{{ route('profile.password') }}" method="POST" class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-bold mb-5 flex items-center gap-2" style="color: #1B2A4A;">
                    <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Changer le mot de passe
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Mot de passe actuel</label>
                        <input type="password" name="current_password" id="current_password" required
                               class="glass-input w-full py-3 px-4 rounded-xl">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Nouveau mot de passe</label>
                        <input type="password" name="password" id="password" required
                               class="glass-input w-full py-3 px-4 rounded-xl">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="glass-input w-full py-3 px-4 rounded-xl">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-0.5"
                            style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                        Mettre a jour
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <a href="{{ route('profile.show') }}" class="inline-flex items-center text-sm font-medium" style="color: #6B7B8D;">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Retour au profil
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
