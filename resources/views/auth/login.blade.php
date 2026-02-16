<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - AlBabor</title>
    <link rel="icon" type="image/png" href="/favicon.png?v=5">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }

        /* ---- Animations ---- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInHeading {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes floatBlob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(20px, -30px) scale(1.1); }
            50% { transform: translate(-15px, -50px) scale(1.05); }
            75% { transform: translate(25px, -20px) scale(0.95); }
        }
        @keyframes floatBlobReverse {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(-20px, 25px) scale(1.08); }
            50% { transform: translate(25px, 35px) scale(0.92); }
            75% { transform: translate(-15px, -10px) scale(1.1); }
        }
        @keyframes shimmerBtn {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        @keyframes subtlePulse {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.7; }
        }
        @keyframes waveFloat {
            0%, 100% { transform: translateX(0) translateY(0); }
            50% { transform: translateX(-15px) translateY(-8px); }
        }

        .auth-heading {
            opacity: 0;
            animation: slideInHeading 0.7s ease-out 0.1s forwards;
        }
        .auth-subheading {
            opacity: 0;
            animation: fadeInUp 0.6s ease-out 0.3s forwards;
        }
        .auth-feature-item {
            opacity: 0;
            animation: fadeInUp 0.6s ease-out forwards;
        }
        .auth-card {
            opacity: 0;
            animation: fadeInUp 0.7s ease-out 0.2s forwards;
        }

        /* ---- Glass Card ---- */
        .auth-glass-card {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.08),
                0 8px 24px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        /* ---- Input Focus Effects ---- */
        .auth-form-group label {
            transition: color 0.3s ease;
        }
        .auth-form-group:focus-within label {
            color: #17A2B8 !important;
        }
        .auth-form-group .auth-input-icon {
            transition: all 0.3s ease;
        }
        .auth-form-group:focus-within .auth-input-icon {
            color: #17A2B8 !important;
            transform: scale(1.1);
        }
        .auth-input {
            background: rgba(255, 255, 255, 0.7);
            border: 1.5px solid #E0E6ED;
            color: #1B2A4A;
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(8px);
        }
        .auth-input:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: #17A2B8;
            box-shadow: 0 0 0 4px rgba(23, 162, 184, 0.12), 0 4px 16px rgba(23, 162, 184, 0.08);
            outline: none;
        }
        .auth-input::placeholder {
            color: #9BA8B7;
        }

        /* ---- Submit Button ---- */
        .auth-submit-btn {
            background: linear-gradient(135deg, #1B4F72 0%, #17A2B8 100%);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .auth-submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 200%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s ease;
        }
        .auth-submit-btn:hover::before {
            left: 100%;
        }
        .auth-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(27, 79, 114, 0.35), 0 4px 12px rgba(23, 162, 184, 0.2);
        }
        .auth-submit-btn:active {
            transform: translateY(0);
        }

        /* ---- Password Toggle ---- */
        .password-toggle {
            cursor: pointer;
            transition: all 0.3s ease;
            color: #9BA8B7;
        }
        .password-toggle:hover {
            color: #17A2B8;
            transform: scale(1.1);
        }

        /* ---- Checkbox Custom ---- */
        .auth-checkbox {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            border: 2px solid #D0D8E4;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            background: rgba(255,255,255,0.7);
        }
        .auth-checkbox:checked {
            background: linear-gradient(135deg, #1B4F72, #17A2B8);
            border-color: #17A2B8;
        }
        .auth-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 2px;
            width: 5px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        .auth-checkbox:focus {
            box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.15);
        }

        /* ---- Link Hover ---- */
        .auth-link {
            color: #17A2B8;
            transition: all 0.3s ease;
            position: relative;
        }
        .auth-link::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 0;
            height: 1.5px;
            background: linear-gradient(90deg, #1B4F72, #17A2B8);
            border-radius: 1px;
            transition: width 0.3s ease;
        }
        .auth-link:hover::after {
            width: 100%;
        }
        .auth-link:hover {
            color: #1B4F72;
        }

        /* ---- Decorative Blobs on Right Panel ---- */
        .blob-1 {
            position: absolute;
            top: -60px;
            right: -40px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(23,162,184,0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: floatBlob 12s ease-in-out infinite;
            pointer-events: none;
        }
        .blob-2 {
            position: absolute;
            bottom: -80px;
            left: -60px;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(27,79,114,0.06) 0%, transparent 70%);
            border-radius: 50%;
            animation: floatBlobReverse 15s ease-in-out infinite;
            pointer-events: none;
        }
        .blob-3 {
            position: absolute;
            top: 40%;
            left: -30px;
            width: 120px;
            height: 120px;
            background: radial-gradient(circle, rgba(23,162,184,0.05) 0%, transparent 70%);
            border-radius: 50%;
            animation: subtlePulse 6s ease-in-out infinite;
            pointer-events: none;
        }

        /* ---- Error Alert ---- */
        .auth-error {
            background: rgba(231, 76, 60, 0.06);
            border: 1px solid rgba(231, 76, 60, 0.15);
            border-radius: 10px;
            padding: 8px 12px;
            margin-top: 6px;
        }
        .auth-error p {
            color: #E74C3C;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ---- Success Alert ---- */
        .auth-success {
            background: rgba(39, 174, 96, 0.06);
            border: 1px solid rgba(39, 174, 96, 0.15);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .auth-success p {
            color: #27AE60;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ---- Divider ---- */
        .auth-divider {
            border-top: 1px solid rgba(224, 230, 237, 0.6);
        }

        /* ---- Wave SVG on left panel bottom ---- */
        .wave-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            opacity: 0.1;
            animation: waveFloat 8s ease-in-out infinite;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #F0F4F8 0%, #E8EEF4 50%, #F0F4F8 100%);">
    <div class="min-h-screen flex">
        <!-- Left Side - Video Background -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <!-- Video Background -->
            <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover">
                <source src="/videos/albabor_hero_video.mp4" type="video/mp4">
            </video>
            <div class="absolute inset-0" style="background: linear-gradient(160deg, rgba(10,25,47,0.88) 0%, rgba(27,79,114,0.72) 45%, rgba(23,162,184,0.62) 100%);"></div>

            <!-- Floating decorative elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div style="position: absolute; top: 8%; left: 6%; width: 160px; height: 160px; background: rgba(23,162,184,0.12); border-radius: 50%; filter: blur(50px); animation: floatBlob 10s ease-in-out infinite;"></div>
                <div style="position: absolute; bottom: 12%; right: 8%; width: 200px; height: 200px; background: rgba(255,255,255,0.06); border-radius: 50%; filter: blur(60px); animation: floatBlobReverse 12s ease-in-out infinite;"></div>
                <div style="position: absolute; top: 50%; left: 50%; width: 100px; height: 100px; background: rgba(93,173,226,0.08); border-radius: 50%; filter: blur(40px); animation: subtlePulse 5s ease-in-out infinite;"></div>
            </div>

            <!-- Wave decoration at bottom -->
            <svg class="wave-decoration" viewBox="0 0 1440 200" fill="white" preserveAspectRatio="none">
                <path d="M0,100 C360,180 720,20 1080,100 C1260,140 1380,80 1440,100 L1440,200 L0,200 Z"/>
            </svg>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-center w-full px-12">
                <div class="mb-10">
                    <img src="/images/logo-full.png" alt="AlBabor" style="height: 48px; filter: brightness(0) invert(1) drop-shadow(0 4px 16px rgba(0,0,0,0.3));">
                </div>

                <h1 class="text-3xl font-bold text-white mb-3 text-center auth-heading" style="text-shadow: 0 2px 20px rgba(0,0,0,0.25); letter-spacing: -0.02em;">La marketplace nautique</h1>
                <p class="text-lg mb-10 text-center auth-subheading" style="color: rgba(255,255,255,0.7);">N°1 en Algerie</p>

                <div class="space-y-5 max-w-sm w-full">
                    @foreach([
                        ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text' => 'Achetez et vendez en toute securite'],
                        ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 'text' => '69 wilayas couvertes en Algerie'],
                        ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Paiement DZD et EUR acceptes'],
                    ] as $item)
                        <div class="flex items-center gap-4 auth-feature-item" style="color: rgba(255,255,255,0.85); animation-delay: {{ ($loop->index + 1) * 0.2 }}s;">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path></svg>
                            </div>
                            <span class="text-sm font-medium">{{ $item['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-6 lg:p-8 relative overflow-hidden">
            <!-- Decorative blobs -->
            <div class="blob-1"></div>
            <div class="blob-2"></div>
            <div class="blob-3"></div>

            <div class="w-full max-w-md relative z-10">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <a href="{{ route('home') }}">
                        <img src="/images/logo-full.png" alt="AlBabor" class="h-9 mx-auto">
                    </a>
                </div>

                <!-- Glass Card -->
                <div class="auth-glass-card p-5 sm:p-8 lg:p-10 auth-card">
                    <!-- Logo inside card (desktop) -->
                    <div class="hidden lg:flex justify-center mb-6">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(27,79,114,0.08), rgba(23,162,184,0.08));">
                            <svg class="w-7 h-7" style="color: #1B4F72;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 17h18M3 17l2-9h14l2 9M5 8l1-4h12l1 4M7 17v3M17 17v3M12 8v4"/>
                            </svg>
                        </div>
                    </div>

                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold mb-2" style="color: #1B2A4A; letter-spacing: -0.02em;">Bon retour parmi nous</h2>
                        <p class="text-sm" style="color: #6B7B8D;">Connectez-vous pour continuer</p>
                    </div>

                    @if (session('status'))
                        <div class="auth-success mb-6">
                            <p>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ session('status') }}
                            </p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false }">
                        @csrf

                        <!-- Email -->
                        <div class="auth-form-group">
                            <label for="email" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Adresse email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-[18px] h-[18px] auth-input-icon" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                </div>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                       class="auth-input w-full pl-12 pr-4 py-3.5 text-sm font-medium @error('email') !border-red-400 @enderror"
                                       placeholder="votre@email.com">
                            </div>
                            @error('email')
                                <div class="auth-error">
                                    <p>
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $message }}
                                    </p>
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="auth-form-group">
                            <label for="password" class="block text-sm font-semibold mb-2" style="color: #1B2A4A;">Mot de passe</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-[18px] h-[18px] auth-input-icon" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                       class="auth-input w-full pl-12 pr-12 py-3.5 text-sm font-medium @error('password') !border-red-400 @enderror"
                                       placeholder="Votre mot de passe">
                                <!-- Password Toggle -->
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center password-toggle" tabindex="-1">
                                    <svg x-show="!showPassword" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPassword" x-cloak class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <div class="auth-error">
                                    <p>
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $message }}
                                    </p>
                                </div>
                            @enderror
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                                <input id="remember_me" type="checkbox" name="remember" class="auth-checkbox">
                                <span class="ml-2.5 text-sm transition-colors group-hover:text-[#1B2A4A]" style="color: #6B7B8D;">Se souvenir de moi</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="text-sm font-semibold auth-link">
                                Mot de passe oublie?
                            </a>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="w-full py-3.5 rounded-2xl text-white font-semibold text-sm auth-submit-btn"
                                style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.3);">
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                Se connecter
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-7">
                        <div class="absolute inset-0 flex items-center">
                            <div class="auth-divider w-full"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="px-4 font-medium" style="background: rgba(255,255,255,0.72); color: #9BA8B7;">ou</span>
                        </div>
                    </div>

                    <!-- Google Sign In -->
                    <a href="{{ route('auth.google') }}"
                       class="w-full flex items-center justify-center gap-3 py-3.5 rounded-2xl text-sm font-semibold transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0"
                       style="background: white; color: #1B2A4A; border: 1.5px solid #E0E6ED; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Se connecter avec Google
                    </a>

                    <!-- Register Link -->
                    <div class="text-center mt-5">
                        <p class="text-sm" style="color: #6B7B8D;">
                            Pas de compte?
                            <a href="{{ route('register') }}" class="font-semibold ml-1 auth-link">S'inscrire</a>
                        </p>
                    </div>
                </div>

                <!-- Back to home -->
                <div class="text-center mt-7">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium transition-all duration-300 hover:text-[#1B4F72]" style="color: #9BA8B7;">
                        <svg class="w-4 h-4 mr-1.5 transition-transform duration-300" style="transform: translateX(0);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Retour a l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
