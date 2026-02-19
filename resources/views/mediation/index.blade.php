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
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.mediation_tickets') }}</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('messages.mediation_tickets') }}</h1>
                    <p class="mt-0.5 text-white/70">Gerez vos demandes de mediation</p>
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

            <!-- As Buyer -->
            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                    <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                    </svg>
                    {{ __('messages.as_buyer') }}
                </h2>

                @if($buyerTickets->count() > 0)
                    <div class="space-y-4">
                        @foreach($buyerTickets as $ticket)
                            <a href="{{ route('mediation.show', $ticket) }}" class="block bg-white rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center">
                                        <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0" style="background: #F0F4F8;">
                                            @if($ticket->listing->media->first())
                                                <img src="{{ $ticket->listing->media->first()->thumbnail_url ?? $ticket->listing->media->first()->url }}"
                                                     alt="" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="font-bold" style="color: #1B2A4A;">{{ $ticket->listing->title }}</h3>
                                            <p class="text-sm" style="color: #6B7B8D;">{{ __('messages.seller') }}: {{ $ticket->seller->name }}</p>
                                            <p class="text-sm" style="color: #9BA8B7;">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    @php
                                        $statusStyles = [
                                            'new' => 'background: rgba(243, 156, 18, 0.1); color: #F39C12;',
                                            'awaiting_payment' => 'background: rgba(230, 126, 34, 0.1); color: #E67E22;',
                                            'in_progress' => 'background: rgba(23, 162, 184, 0.1); color: #17A2B8;',
                                            'closed' => 'background: rgba(39, 174, 96, 0.1); color: #27AE60;',
                                            'cancelled' => 'background: rgba(107, 123, 141, 0.1); color: #6B7B8D;',
                                        ];
                                        $style = $statusStyles[$ticket->status] ?? 'background: rgba(107, 123, 141, 0.1); color: #6B7B8D;';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold flex-shrink-0" style="{{ $style }}">
                                        {{ $ticket->status_label }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                        <p style="color: #6B7B8D;">{{ __('messages.no_buyer_tickets') }}</p>
                    </div>
                @endif
            </div>

            <!-- As Seller -->
            <div>
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                    <svg class="w-5 h-5" style="color: #F39C12;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    {{ __('messages.as_seller') }}
                </h2>

                @if($sellerTickets->count() > 0)
                    <div class="space-y-4">
                        @foreach($sellerTickets as $ticket)
                            <a href="{{ route('mediation.show', $ticket) }}" class="block bg-white rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center">
                                        <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0" style="background: #F0F4F8;">
                                            @if($ticket->listing->media->first())
                                                <img src="{{ $ticket->listing->media->first()->thumbnail_url ?? $ticket->listing->media->first()->url }}"
                                                     alt="" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="font-bold" style="color: #1B2A4A;">{{ $ticket->listing->title }}</h3>
                                            <p class="text-sm" style="color: #6B7B8D;">{{ __('messages.buyer') }}: {{ $ticket->buyer->name }}</p>
                                            <p class="text-sm" style="color: #9BA8B7;">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    @php
                                        $statusStyles = [
                                            'new' => 'background: rgba(243, 156, 18, 0.1); color: #F39C12;',
                                            'awaiting_payment' => 'background: rgba(230, 126, 34, 0.1); color: #E67E22;',
                                            'in_progress' => 'background: rgba(23, 162, 184, 0.1); color: #17A2B8;',
                                            'closed' => 'background: rgba(39, 174, 96, 0.1); color: #27AE60;',
                                            'cancelled' => 'background: rgba(107, 123, 141, 0.1); color: #6B7B8D;',
                                        ];
                                        $style = $statusStyles[$ticket->status] ?? 'background: rgba(107, 123, 141, 0.1); color: #6B7B8D;';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold flex-shrink-0" style="{{ $style }}">
                                        {{ $ticket->status_label }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                        <p style="color: #6B7B8D;">{{ __('messages.no_seller_tickets') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
