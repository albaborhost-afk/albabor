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
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.my_payments') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #27AE60 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full blur-3xl" style="background: rgba(255,255,255,0.05);"></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('messages.my_payments') }}</h1>
                    <p class="mt-0.5 text-white/70">Historique de vos transactions</p>
                </div>
            </div>
        </div>
    </div>

    <div style="background: #F0F4F8;" class="py-8 pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($payments->count() > 0)
                <div class="bg-white rounded-2xl overflow-hidden" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr style="background: #F0F4F8; border-bottom: 1px solid #E0E6ED;">
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('messages.date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('messages.type') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('messages.amount') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('messages.method') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider" style="color: #6B7B8D;">{{ __('messages.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr style="border-bottom: 1px solid #E0E6ED;">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: #6B7B8D;">
                                            {{ $payment->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold" style="color: #1B2A4A;">{{ $payment->type_label }}</div>
                                            @if($payment->listing)
                                                <div class="text-sm" style="color: #6B7B8D;">{{ Str::limit($payment->listing->title, 30) }}</div>
                                            @elseif($payment->subscription)
                                                <div class="text-sm" style="color: #6B7B8D;">{{ $payment->subscription->plan->name ?? '-' }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" style="color: #1B4F72;">
                                            {{ number_format($payment->amount_dzd, 0, ',', ' ') }} DA
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: #6B7B8D;">
                                            {{ $payment->method_label }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusStyles = [
                                                    'approved' => 'background: rgba(39, 174, 96, 0.1); color: #27AE60;',
                                                    'pending' => 'background: rgba(243, 156, 18, 0.1); color: #F39C12;',
                                                    'rejected' => 'background: rgba(231, 76, 60, 0.1); color: #E74C3C;',
                                                ];
                                                $style = $statusStyles[$payment->status] ?? 'background: rgba(107, 123, 141, 0.1); color: #6B7B8D;';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $style }}">
                                                {{ $payment->status_label }}
                                            </span>
                                            @if($payment->status === 'rejected' && $payment->rejection_reason)
                                                <p class="text-xs mt-1" style="color: #E74C3C;">{{ $payment->rejection_reason }}</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden divide-y" style="border-color: #E0E6ED;">
                        @foreach($payments as $payment)
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold" style="color: #1B2A4A;">{{ $payment->type_label }}</span>
                                    @php
                                        $statusStyles = [
                                            'approved' => 'background: rgba(39, 174, 96, 0.1); color: #27AE60;',
                                            'pending' => 'background: rgba(243, 156, 18, 0.1); color: #F39C12;',
                                            'rejected' => 'background: rgba(231, 76, 60, 0.1); color: #E74C3C;',
                                        ];
                                        $style = $statusStyles[$payment->status] ?? 'background: rgba(107, 123, 141, 0.1); color: #6B7B8D;';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="{{ $style }}">
                                        {{ $payment->status_label }}
                                    </span>
                                </div>
                                <p class="text-lg font-bold" style="color: #1B4F72;">{{ number_format($payment->amount_dzd, 0, ',', ' ') }} DA</p>
                                <div class="flex items-center gap-3 mt-1 text-sm" style="color: #9BA8B7;">
                                    <span>{{ $payment->method_label }}</span>
                                    <span>{{ $payment->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(method_exists($payments, 'hasPages') && $payments->hasPages())
                    <div class="mt-8">
                        {{ $payments->links() }}
                    </div>
                @endif
            @else
                <div class="bg-white rounded-2xl p-12 text-center" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                    <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-6" style="background: rgba(23, 162, 184, 0.08);">
                        <svg class="w-12 h-12" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2" style="color: #1B2A4A;">{{ __('messages.no_payments_yet') }}</h3>
                    <p style="color: #6B7B8D;">{{ __('messages.payments_will_appear_here') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
