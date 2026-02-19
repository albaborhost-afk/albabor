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
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.subscription_plans') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full blur-3xl" style="background: rgba(255,255,255,0.05);"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('messages.subscription_plans') }}</h1>
                    <p class="mt-0.5 text-white/70">{{ __('messages.subscription_info') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div style="background: #F0F4F8;" class="py-8 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(39, 174, 96, 0.08); border: 1px solid rgba(39, 174, 96, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-medium" style="color: #27AE60;">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <ul class="list-disc list-inside text-sm" style="color: #E74C3C;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($activeSubscription)
                <div class="bg-white rounded-2xl p-6 mb-8 flex items-center" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: rgba(39, 174, 96, 0.1);">
                        <svg class="w-8 h-8" style="color: #27AE60;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold" style="color: #27AE60;">{{ __('messages.active_subscription') }}</h3>
                        <p style="color: #6B7B8D;">{{ $activeSubscription->plan->name }} - {{ __('messages.expires') }} {{ $activeSubscription->ends_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            @endif

            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach($plans as $plan)
                    @php
                        $planFeatures = [
                            'Starter' => [
                                'Badge vendeur verifie',
                                'Jusqu\'a 10 annonces actives',
                                'Support par email'
                            ],
                            'Pro' => [
                                'Badge vendeur verifie',
                                '<strong>Annonces illimitees</strong>',
                                '<strong>2 mises en avant gratuites/mois</strong>',
                                'Support prioritaire',
                                'Statistiques avancees'
                            ],
                            'Premium' => [
                                'Badge vendeur verifie',
                                '<strong>Annonces illimitees</strong>',
                                '<strong>5 mises en avant gratuites/mois</strong>',
                                '<strong>Support dedie 24/7</strong>',
                                'Statistiques avancees',
                                '<strong>Page vendeur personnalisee</strong>',
                                '<strong>Priorite dans les resultats</strong>'
                            ]
                        ];
                        $features = $planFeatures[$plan->name] ?? [];
                        $isPro = $plan->name === 'Pro';
                        $borderColor = $isPro ? '#E74C3C' : '#E0E6ED';
                        $buttonBg = $isPro ? '#E74C3C' : '#2C5F7C';
                    @endphp

                    <div class="bg-white rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 relative" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03); border: 2px solid {{ $borderColor }};">
                        @if($isPro)
                            <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 px-4 py-1 rounded-full text-xs font-bold text-white" style="background: #E74C3C;">
                                ★ RECOMMANDÉ
                            </div>
                        @endif

                        <div class="p-6 {{ $isPro ? 'pt-8' : '' }}">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold" style="color: {{ $isPro ? '#E74C3C' : '#2C5F7C' }};">{{ $plan->name }}</h3>
                                <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background: {{ $buttonBg }};">
                                    @if($plan->name === 'Starter')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    @elseif($plan->name === 'Pro')
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-6">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-4xl font-black" style="color: {{ $isPro ? '#E74C3C' : '#17A2B8' }};">{{ number_format($plan->price_dzd, 0, ',', ' ') }} DA</span>
                                    <span class="text-sm" style="color: #6B7B8D;">/ mois</span>
                                </div>
                            </div>

                            <ul class="space-y-3 mb-6">
                                @foreach($features as $feature)
                                    <li class="flex items-start gap-2 text-sm" style="color: #1B2A4A;">
                                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #27AE60;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>{!! $feature !!}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <button type="button" onclick="selectPlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price_dzd }})"
                                    class="w-full px-4 py-3 rounded-xl font-bold text-white transition-all duration-300 hover:-translate-y-0.5"
                                    style="background: {{ $buttonBg }}; {{ $isPro ? 'box-shadow: 0 4px 12px rgba(231,76,60,0.3);' : '' }}">
                                Choisir ce plan
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Payment Form (hidden by default) -->
            <div id="paymentForm" class="hidden">
                <form action="{{ route('payments.subscription') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <input type="hidden" name="plan_id" id="selectedPlanId">

                    <div class="rounded-2xl p-6" style="background: rgba(23, 162, 184, 0.08); border: 1px solid rgba(23, 162, 184, 0.15);">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-lg font-semibold" style="color: #1B2A4A;">{{ __('messages.selected_plan') }}: </span>
                                <span id="selectedPlanName" class="text-lg font-black" style="color: #1B4F72;"></span>
                            </div>
                            <span id="selectedPlanPrice" class="text-2xl font-black" style="color: #1B4F72;"></span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                            <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ __('messages.payment_method') }}
                        </h2>

                        <div class="space-y-3">
                            <label class="flex items-center p-4 rounded-xl cursor-pointer transition-all" style="border: 1px solid #E0E6ED;">
                                <input type="radio" name="method" value="baridimob" required style="accent-color: #17A2B8;">
                                <span class="ml-3">
                                    <span class="block font-semibold" style="color: #1B2A4A;">BaridiMob</span>
                                    <span class="block text-sm" style="color: #6B7B8D;">00799999002543569223</span>
                                </span>
                            </label>

                            <label class="flex items-center p-4 rounded-xl cursor-pointer transition-all" style="border: 1px solid #E0E6ED;">
                                <input type="radio" name="method" value="ccp" style="accent-color: #17A2B8;">
                                <span class="ml-3">
                                    <span class="block font-semibold" style="color: #1B2A4A;">CCP</span>
                                    <span class="block text-sm" style="color: #6B7B8D;">0025435692 {{ __('messages.cle') }} 23</span>
                                </span>
                            </label>

                            <label class="flex items-center p-4 rounded-xl cursor-pointer transition-all" style="border: 1px solid #E0E6ED;">
                                <input type="radio" name="method" value="bank_transfer" style="accent-color: #17A2B8;">
                                <span class="ml-3">
                                    <span class="block font-semibold" style="color: #1B2A4A;">{{ __('messages.bank_transfer') }}</span>
                                    <span class="block text-sm" style="color: #6B7B8D;">RIB: 00123456789012345678901234</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Payment Proof -->
                    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                            <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ __('messages.payment_proof') }}
                        </h2>
                        <input type="file" name="proof" required accept="image/*"
                               class="block w-full text-sm rounded-xl file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold" style="color: #6B7B8D;">
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="hidePaymentForm()" class="px-5 py-3 rounded-xl font-semibold transition-all" style="color: #6B7B8D; border: 1px solid #E0E6ED;">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="px-6 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 transform hover:-translate-y-0.5" style="box-shadow: 0 8px 25px rgba(27, 79, 114, 0.25);">
                            {{ __('messages.submit_payment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectPlan(id, name, price) {
            document.getElementById('selectedPlanId').value = id;
            document.getElementById('selectedPlanName').textContent = name;
            document.getElementById('selectedPlanPrice').textContent = new Intl.NumberFormat('fr-DZ').format(price) + ' DA';
            document.getElementById('paymentForm').classList.remove('hidden');
            document.getElementById('paymentForm').scrollIntoView({ behavior: 'smooth' });
        }

        function hidePaymentForm() {
            document.getElementById('paymentForm').classList.add('hidden');
        }
    </script>
</x-app-layout>
