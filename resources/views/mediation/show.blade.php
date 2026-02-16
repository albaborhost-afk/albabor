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
                <a href="{{ route('mediation.index') }}" style="color: #9BA8B7;" class="hover:opacity-80">Mediation</a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.mediation_ticket') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('messages.mediation_ticket') }}</h1>
                </div>
                @php
                    $statusStyles = [
                        'new' => 'background: rgba(243, 156, 18, 0.2); color: #FFF3CD; border: 1px solid rgba(243, 156, 18, 0.4);',
                        'awaiting_payment' => 'background: rgba(230, 126, 34, 0.2); color: #FDEBD0; border: 1px solid rgba(230, 126, 34, 0.4);',
                        'in_progress' => 'background: rgba(255,255,255, 0.2); color: white; border: 1px solid rgba(255,255,255, 0.3);',
                        'closed' => 'background: rgba(39, 174, 96, 0.2); color: #D4EDDA; border: 1px solid rgba(39, 174, 96, 0.4);',
                        'cancelled' => 'background: rgba(255,255,255, 0.1); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255, 0.2);',
                    ];
                    $ticketStyle = $statusStyles[$ticket->status] ?? 'background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7);';
                @endphp
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold" style="{{ $ticketStyle }}">
                    {{ $ticket->status_label }}
                </span>
            </div>
        </div>
    </div>

    <div style="background: #F0F4F8;" class="py-8 pb-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(39, 174, 96, 0.08); border: 1px solid rgba(39, 174, 96, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #27AE60;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-medium" style="color: #27AE60;">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl flex items-center gap-3" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.2);">
                    <svg class="w-5 h-5 flex-shrink-0" style="color: #E74C3C;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <p class="font-medium" style="color: #E74C3C;">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Listing Info -->
            <div class="bg-white rounded-2xl p-6 mb-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                <div class="flex items-start">
                    <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0" style="background: #F0F4F8;">
                        @if($ticket->listing->media->first())
                            <img src="{{ Storage::url($ticket->listing->media->first()->thumbnail_path ?? $ticket->listing->media->first()->path) }}"
                                 alt="" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="ml-4 flex-1">
                        <a href="{{ route('listings.show', $ticket->listing) }}" class="font-bold hover:opacity-80 transition-opacity" style="color: #1B2A4A;">
                            {{ $ticket->listing->title }}
                        </a>
                        <p class="text-lg font-black mt-1" style="color: #1B4F72;">{{ $ticket->listing->formatted_price }}</p>
                        <div class="flex items-center mt-2 text-sm" style="color: #6B7B8D;">
                            <span>{{ __('messages.buyer') }}: {{ $ticket->buyer->name }}</span>
                            <span class="mx-2" style="color: #E0E6ED;">|</span>
                            <span>{{ __('messages.seller') }}: {{ $ticket->seller->name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="bg-white rounded-2xl p-6 mb-6" style="box-shadow: 0 10px 25px rgba(0,0,0,0.06), 0 3px 8px rgba(0,0,0,0.03);">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2" style="color: #1B2A4A;">
                    <svg class="w-5 h-5" style="color: #17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    {{ __('messages.conversation') }}
                </h2>

                <div class="space-y-4 max-h-96 overflow-y-auto mb-6">
                    @foreach($ticket->messages ?? [] as $message)
                        @php
                            $isCurrentUser = $message['user_id'] == auth()->id();
                            $userName = $message['user_id'] == $ticket->buyer_id ? $ticket->buyer->name : $ticket->seller->name;
                        @endphp
                        <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-xs lg:max-w-md rounded-2xl px-4 py-3" style="{{ $isCurrentUser ? 'background: linear-gradient(135deg, #1B4F72, #17A2B8); color: white;' : 'background: #F0F4F8; color: #1B2A4A;' }}">
                                <p class="text-xs mb-1" style="{{ $isCurrentUser ? 'color: rgba(255,255,255,0.7);' : 'color: #9BA8B7;' }}">
                                    {{ $userName }} - {{ \Carbon\Carbon::parse($message['created_at'])->format('d/m/Y H:i') }}
                                </p>
                                <p class="text-sm">{{ $message['message'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(!in_array($ticket->status, ['closed', 'cancelled']))
                    <!-- Reply Form -->
                    <form action="{{ route('mediation.message', $ticket) }}" method="POST" style="border-top: 1px solid #E0E6ED;" class="pt-4">
                        @csrf
                        <div class="flex space-x-4">
                            <input type="text" name="message" required
                                   class="glass-input flex-1 py-3 px-4 rounded-xl"
                                   placeholder="{{ __('messages.type_message') }}">
                            <button type="submit" class="px-5 py-3 gradient-primary rounded-xl font-bold text-white transition-all duration-300 hover:-translate-y-0.5" style="box-shadow: 0 4px 15px rgba(27, 79, 114, 0.25);">
                                {{ __('messages.send') }}
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-center pt-4" style="border-top: 1px solid #E0E6ED; color: #9BA8B7;">{{ __('messages.ticket_closed') }}</p>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('mediation.index') }}" class="inline-flex items-center text-sm font-medium" style="color: #6B7B8D;">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('messages.back_to_tickets') }}
                </a>

                @if($ticket->status === 'new' && $ticket->buyer_id === auth()->id())
                    <form action="{{ route('mediation.cancel', $ticket) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_cancel_ticket') }}')">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl font-semibold transition-all" style="color: #E74C3C; border: 1px solid rgba(231, 76, 60, 0.3);">
                            {{ __('messages.cancel_ticket') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
