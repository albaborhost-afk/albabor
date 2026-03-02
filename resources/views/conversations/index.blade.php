<x-app-layout>
    <!-- Breadcrumb Bar -->
    <div style="background: #FFFFFF; border-bottom: 1px solid #E0E6ED;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('home') }}" style="color: #9BA8B7;" class="hover:opacity-80 transition-opacity flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ __('messages.home') }}
                </a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.my_messages') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ __('messages.my_messages') }}</h1>
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

            @if($allItems->isEmpty())
                <!-- Empty State -->
                <div class="bg-white rounded-2xl p-12 text-center" style="box-shadow: 0 10px 30px rgba(0,0,0,0.07), 0 2px 8px rgba(0,0,0,0.04);">
                    <div class="w-20 h-20 mx-auto rounded-2xl flex items-center justify-center mb-6" style="background: linear-gradient(135deg, rgba(27,79,114,0.06), rgba(23,162,184,0.08));">
                        <svg class="w-10 h-10" style="color: #9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2" style="color: #1B2A4A;">{{ __('messages.no_messages_yet') }}</h3>
                    <p class="text-sm mb-6" style="color: #9BA8B7;">{{ __('messages.no_messages_desc') }}</p>
                    <a href="{{ route('listings.index') }}" class="inline-flex items-center gap-2 px-6 py-3 text-white rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27, 79, 114, 0.3);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        {{ __('messages.browse_listings') }}
                    </a>
                </div>
            @else
                <!-- Unified Message List -->
                <div class="space-y-3">
                    @foreach($allItems as $entry)
                        @if($entry->type === 'conversation')
                            @php
                                $conversation = $entry->item;
                                $otherUser = $conversation->getOtherParticipant(auth()->user());
                                $unreadCount = $conversation->unreadCountFor(auth()->user());
                                $isBuyer = $conversation->isBuyer(auth()->user());
                            @endphp
                            <a href="{{ route('conversations.show', $conversation) }}" class="block bg-white rounded-2xl p-4 sm:p-5 transition-all duration-200 hover:-translate-y-0.5" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05), 0 1px 4px rgba(0,0,0,0.03); {{ $unreadCount > 0 ? 'border-left: 3px solid #17A2B8;' : '' }}">
                                <div class="flex items-start gap-4">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center" style="background: #F0F4F8;">
                                        @if($conversation->listing?->media?->first())
                                            <img src="{{ $conversation->listing->media->first()->thumbnail_url ?? $conversation->listing->media->first()->url }}"
                                                 alt="" class="w-full h-full object-cover"
                                                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                                            <div class="w-full h-full items-center justify-center" style="color: #C5D0DB; display: none;">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @else
                                            <svg class="w-6 h-6" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <h3 class="text-sm font-bold truncate" style="color: #1B2A4A;">{{ $conversation->listing?->title ?? __('messages.listing_deleted') }}</h3>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background: rgba(23,162,184,0.08); color: #17A2B8;">
                                                        Message direct
                                                    </span>
                                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background: {{ $isBuyer ? 'rgba(23,162,184,0.08)' : 'rgba(243,156,18,0.08)' }}; color: {{ $isBuyer ? '#17A2B8' : '#F39C12' }};">
                                                        {{ $isBuyer ? __('messages.buyer') : __('messages.seller') }}
                                                    </span>
                                                    <span class="text-xs" style="color: #9BA8B7;">{{ $otherUser?->name ?? __('messages.deleted_user') }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                @if($unreadCount > 0)
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold text-white" style="background: linear-gradient(135deg, #1B4F72, #17A2B8); min-width: 20px;">{{ $unreadCount }}</span>
                                                @endif
                                                <span class="text-xs whitespace-nowrap" style="color: #9BA8B7;">{{ $conversation->last_message_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        @if($conversation->latestMessage)
                                            <p class="text-sm mt-1.5 truncate" style="color: {{ $unreadCount > 0 ? '#1B2A4A; font-weight: 600;' : '#6B7B8D;' }}">
                                                {{ Str::limit($conversation->latestMessage->body, 80) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </a>

                        @elseif($entry->type === 'mediation')
                            @php
                                $ticket = $entry->item;
                                $isBuyer = $ticket->buyer_id === auth()->id();
                                $otherUser = $isBuyer ? $ticket->seller : $ticket->buyer;
                                $lastMsg = collect($ticket->messages ?? [])->last();
                            @endphp
                            <a href="{{ route('mediation.show', $ticket) }}" class="block bg-white rounded-2xl p-4 sm:p-5 transition-all duration-200 hover:-translate-y-0.5" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05), 0 1px 4px rgba(0,0,0,0.03); border-left: 3px solid #F39C12;">
                                <div class="flex items-start gap-4">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center" style="background: #F0F4F8;">
                                        @if($ticket->listing?->media?->first())
                                            <img src="{{ $ticket->listing->media->first()->thumbnail_url ?? $ticket->listing->media->first()->url }}"
                                                 alt="" class="w-full h-full object-cover"
                                                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                                            <div class="w-full h-full items-center justify-center" style="color: #C5D0DB; display: none;">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @else
                                            <svg class="w-6 h-6" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <h3 class="text-sm font-bold truncate" style="color: #1B2A4A;">{{ $ticket->listing?->title ?? __('messages.listing_deleted') }}</h3>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background: rgba(243,156,18,0.08); color: #F39C12;">
                                                        Mediation
                                                    </span>
                                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background: {{ $isBuyer ? 'rgba(23,162,184,0.08)' : 'rgba(243,156,18,0.08)' }}; color: {{ $isBuyer ? '#17A2B8' : '#F39C12' }};">
                                                        {{ $isBuyer ? __('messages.buyer') : __('messages.seller') }}
                                                    </span>
                                                    <span class="text-xs" style="color: #9BA8B7;">{{ $otherUser?->name ?? __('messages.deleted_user') }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background: rgba(39,174,96,0.08); color: #27AE60;">
                                                    {{ $ticket->status_label }}
                                                </span>
                                                <span class="text-xs whitespace-nowrap" style="color: #9BA8B7;">{{ $ticket->updated_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        @if($lastMsg)
                                            <p class="text-sm mt-1.5 truncate" style="color: #6B7B8D;">
                                                {{ Str::limit($lastMsg['body'] ?? $lastMsg['message'] ?? '', 80) }}
                                            </p>
                                        @elseif($ticket->buyer_message)
                                            <p class="text-sm mt-1.5 truncate" style="color: #6B7B8D;">
                                                {{ Str::limit($ticket->buyer_message, 80) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
