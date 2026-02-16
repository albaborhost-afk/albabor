<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mot de passe oublie - AlBabor</title>
    <link rel="icon" type="image/png" href="/favicon.png">
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
        @keyframes subtlePulse {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.7; }
        }
        @keyframes waveFloat {
            0%, 100% { transform: translateX(0) translateY(0); }
            50% { transform: translateX(-15px) translateY(-8px); }
        }
        @keyframes iconFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-4px) rotate(3deg); }
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

        /* ---- Decorative Blobs ---- */
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

        /* ---- Wave decoration ---- */
        .wave-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            opacity: 0.1;
            animation: waveFloat 8s ease-in-out infinite;
        }

        /* ---- Floating icon ---- */
        .icon-float {
            animation: iconFloat 3s ease-in-out infinite;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #F0F4F8 0%, #E8EEF4 50%, #F0F4F8 100%);">
    <div class="min-h-screen flex">
        <!-- Left Side - Video Background -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
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

            <!-- Wave decoration -->
            <svg class="wave-decoration" viewBox="0 0 1440 200" fill="white" preserveAspectRatio="none">
                <path d="M0,100 C360,180 720,20 1080,100 C1260,140 1380,80 1440,100 L1440,200 L0,200 Z"/>
            </svg>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-center w-full px-12">
                <div class="mb-10">
                    <img src="/images/logo-full.png" alt="AlBabor" style="height: 48px; filter: brightness(0) invert(1) drop-shadow(0 4px 16px rgba(0,0,0,0.3));">
                </div>

                <h1 class="text-3xl font-bold text-white mb-3 text-center auth-heading" style="text-shadow: 0 2px 20px rgba(0,0,0,0.25); letter-spacing: -0.02em;">Recuperez votre compte</h1>
                <p class="text-lg mb-10 text-center auth-subheading" style="color: rgba(255,255,255,0.7);">Un lien de reinitialisation vous sera envoye</p>

                <div class="space-y-5 max-w-sm w-full">
                    @foreach([
                        ['icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z', 'text' => 'Reinitialisation securisee par email'],
                        ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'text' => 'Protegez votre compte avec un mot de passe fort'],
                        ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text' => 'Vos donnees sont protegees et chiffrees'],
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

        <!-- Right Side - Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-8 relative overflow-hidden">
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
                <div class="auth-glass-card p-8 sm:p-10 auth-card">
                    <!-- Icon -->
                    <div class="text-center mb-7">
                        <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-5 icon-float" style="background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(27, 79, 114, 0.08)); box-shadow: 0 8px 24px rgba(23, 162, 184, 0.1);">
                            <svg class="w-8 h-8" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold mb-2" style="color: #1B2A4A; letter-spacing: -0.02em;">Mot de passe oublie ?</h2>
                        <p class="text-sm leading-relaxed mx-auto" style="color: #6B7B8D; max-width: 320px;">Pas de panique ! Entrez votre adresse email et nous vous enverrons un lien pour reinitialiser votre mot de passe.</p>
                    </div>

                    @if (session('status'))
                        <div class="auth-success mb-6">
                            <p>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ session('status') }}
                            </p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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

                        <!-- Submit -->
                        <button type="submit" class="w-full py-3.5 rounded-2xl text-white font-semibold text-sm auth-submit-btn"
                                style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.3);">
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Envoyer le lien de reinitialisation
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

                    <!-- Login Link -->
                    <div class="text-center">
                        <p class="text-sm" style="color: #6B7B8D;">
                            Vous vous souvenez ?
                            <a href="{{ route('login') }}" class="font-semibold ml-1 auth-link">Se connecter</a>
                        </p>
                    </div>
                </div>

                <!-- Back to home -->
                <div class="text-center mt-7">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium transition-all duration-300 hover:text-[#1B4F72]" style="color: #9BA8B7;">
                        <svg class="w-4 h-4 mr-1.5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Retour a l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
