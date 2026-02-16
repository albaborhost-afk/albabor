<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AlBabor') }}</title>
    <link rel="icon" type="image/png" href="/favicon.png?v=5">
    <link rel="apple-touch-icon" href="/favicon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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

        .guest-card {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.08),
                0 8px 24px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            animation: fadeInUp 0.7s ease-out 0.1s forwards;
            opacity: 0;
            overflow: hidden;
        }

        .guest-blob-1 {
            position: fixed;
            top: -60px;
            right: -40px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(23,162,184,0.08) 0%, transparent 70%);
            border-radius: 50%;
            animation: floatBlob 12s ease-in-out infinite;
            pointer-events: none;
        }
        .guest-blob-2 {
            position: fixed;
            bottom: -80px;
            left: -60px;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(27,79,114,0.06) 0%, transparent 70%);
            border-radius: 50%;
            animation: floatBlobReverse 15s ease-in-out infinite;
            pointer-events: none;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col" style="background: linear-gradient(135deg, #F0F4F8 0%, #E8EEF4 50%, #F0F4F8 100%);">
    <!-- Decorative blobs -->
    <div class="guest-blob-1"></div>
    <div class="guest-blob-2"></div>

    <!-- Minimal top bar -->
    <div class="glass-nav sticky top-0 z-50" style="background: rgba(255,255,255,0.8); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid rgba(224,230,237,0.5);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14 items-center">
                <a href="{{ url('/') }}" class="logo-hover flex items-center">
                    <img src="/images/logo-full.png" alt="AlBabor" style="height: 28px;">
                </a>
                <a href="{{ url('/') }}" class="text-sm font-medium transition-all duration-300 hover:text-[#1B4F72]" style="color: #6B7B8D;">
                    <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Main content area -->
    <div class="flex-1 flex flex-col sm:justify-center items-center px-4 py-8 sm:py-0 relative z-10">
        <div class="w-full sm:max-w-md">
            <!-- Card with glass-morphism styling -->
            <div class="guest-card">
                <!-- Gradient accent top -->
                <div style="height: 3px; background: linear-gradient(90deg, #1B4F72, #17A2B8, #5DADE2);"></div>
                <div class="px-6 sm:px-8 py-6 sm:py-8">
                    {{ $slot }}
                </div>
            </div>

            <!-- Subtle branding below card -->
            <div class="mt-6 text-center">
                <p class="text-xs" style="color: #9BA8B7;">&copy; {{ date('Y') }} AlBabor. Tous droits reserves.</p>
            </div>
        </div>
    </div>
</body>
</html>
