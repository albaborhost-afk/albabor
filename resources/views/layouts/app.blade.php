<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'DZ Boats') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <a href="{{ url('/') }}" class="flex items-center">
                            <span class="text-xl font-bold text-blue-600">DZ Boats</span>
                        </a>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-10 sm:flex sm:space-x-8">
                            <a href="{{ url('/') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-transparent hover:border-blue-500">
                                {{ __('messages.home') }}
                            </a>
                            <a href="#" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:border-blue-500 hover:text-gray-900">
                                {{ __('messages.boats') }}
                            </a>
                            <a href="#" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:border-blue-500 hover:text-gray-900">
                                {{ __('messages.jetskis') }}
                            </a>
                            <a href="#" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:border-blue-500 hover:text-gray-900">
                                {{ __('messages.engines') }}
                            </a>
                            <a href="#" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:border-blue-500 hover:text-gray-900">
                                {{ __('messages.parts') }}
                            </a>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="#" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                                {{ __('messages.my_listings') }}
                            </a>
                            <a href="#" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                                {{ __('messages.favorites') }}
                            </a>
                            <div class="relative">
                                <button type="button" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-900">
                                    {{ Auth::user()->name }}
                                </button>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                                    {{ __('auth.logout') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                                {{ __('auth.login') }}
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out">
                                {{ __('auth.register') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} DZ Boats. {{ __('messages.all') }} droits réservés.
                    </div>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-sm text-gray-500 hover:text-gray-900">Conditions d'utilisation</a>
                        <a href="#" class="text-sm text-gray-500 hover:text-gray-900">Politique de confidentialité</a>
                        <a href="#" class="text-sm text-gray-500 hover:text-gray-900">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    {{-- ★ Floating Contact Button ★ --}}
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3" x-data="{ open: false }">

        {{-- Tooltip / options (ouvre au clic) --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             x-cloak
             class="flex flex-col gap-2 mb-1">

            {{-- WhatsApp --}}
            <a href="https://wa.me/213791807475?text={{ urlencode('Bonjour AlBabor 👋, j\'ai une question. Pouvez-vous m\'aider ? 🙏') }}"
               target="_blank" rel="noopener"
               class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl text-white text-sm font-semibold shadow-lg transition-all hover:-translate-y-0.5"
               style="background: linear-gradient(135deg, #25D366, #128C7E); box-shadow: 0 6px 20px rgba(37,211,102,0.4); white-space: nowrap;">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                Écrire sur WhatsApp
            </a>

            {{-- Appel --}}
            <a href="tel:+213791807475"
               class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl text-white text-sm font-semibold transition-all hover:-translate-y-0.5"
               style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 6px 20px rgba(27,79,114,0.35); white-space: nowrap;">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Appeler 0791807475
            </a>
        </div>

        {{-- Main FAB button --}}
        <button @click="open = !open"
                class="relative w-14 h-14 rounded-full flex items-center justify-center text-white shadow-xl transition-all duration-300 hover:scale-110 focus:outline-none"
                style="background: linear-gradient(135deg, #25D366, #128C7E); box-shadow: 0 8px 25px rgba(37,211,102,0.5);">
            {{-- WhatsApp icon (menu fermé) --}}
            <svg x-show="!open" class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
            </svg>
            {{-- Close icon (menu ouvert) --}}
            <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{-- Pulse ring --}}
            <span x-show="!open" class="absolute -top-0.5 -right-0.5 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-60" style="background: #25D366;"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 border-2 border-white" style="background: #25D366;"></span>
            </span>
        </button>
    </div>

    @stack('scripts')
</body>
</html>
