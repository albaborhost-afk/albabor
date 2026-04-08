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
                <a href="{{ route('conversations.index') }}" style="color: #9BA8B7;" class="hover:opacity-80">{{ __('messages.my_messages') }}</a>
                <svg class="w-4 h-4" style="color: #E0E6ED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span style="color: #1B2A4A;" class="font-medium">{{ __('messages.conversation') }}</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4F72 0%, #2471A3 50%, #17A2B8 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl" style="background: rgba(255,255,255,0.08);"></div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-white">{{ __('messages.conversation') }}</h1>
                    <p class="text-sm mt-0.5" style="color: rgba(255,255,255,0.7);">
                        {{ $conversation->getOtherParticipant(auth()->user())?->name ?? __('messages.deleted_user') }}
                    </p>
                </div>
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
                    <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center" style="background: #F0F4F8;">
                        @if($conversation->listing?->media?->first())
                            <img src="{{ $conversation->listing->media->first()->thumbnail_url ?? $conversation->listing->media->first()->url }}"
                                 alt="" class="w-full h-full object-cover"
                                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="w-full h-full items-center justify-center" style="color: #C5D0DB; display: none;">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @else
                            <svg class="w-8 h-8" style="color: #C5D0DB;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @endif
                    </div>
                    <div class="ml-4 flex-1">
                        <a href="{{ $conversation->listing ? route('listings.show', $conversation->listing) : '#' }}" class="font-bold hover:opacity-80 transition-opacity" style="color: #1B2A4A;">
                            {{ $conversation->listing?->title ?? __('messages.listing_deleted') }}
                        </a>
                        <p class="text-lg font-black mt-1" style="color: #1B4F72;">{{ $conversation->listing?->formatted_price }}</p>
                        <div class="flex items-center mt-2 text-sm" style="color: #6B7B8D;">
                            <span>{{ __('messages.buyer') }}: {{ $conversation->buyer?->name }}</span>
                            <span class="mx-2" style="color: #E0E6ED;">|</span>
                            <span>{{ __('messages.seller') }}: {{ $conversation->seller?->name }}</span>
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

                <div x-data="chatRoom()" x-init="init()">
                    {{-- Messages --}}
                    <div id="messages-container" class="space-y-4 max-h-[500px] overflow-y-auto mb-6 scroll-smooth" x-ref="msgContainer">
                        <template x-for="msg in messages" :key="msg.id">
                            <div :class="msg.sender_id == currentUserId ? 'flex justify-end' : 'flex justify-start'">
                                <div class="max-w-xs lg:max-w-md">
                                    <div class="rounded-2xl px-4 py-3"
                                         :style="msg.sender_id == currentUserId
                                            ? 'background: linear-gradient(135deg, #1B4F72, #17A2B8); color: white;'
                                            : 'background: #F0F4F8; color: #1B2A4A;'">
                                        <p class="text-sm whitespace-pre-wrap break-words" x-text="msg.body"></p>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1" :class="msg.sender_id == currentUserId ? 'justify-end' : 'justify-start'">
                                        <span class="text-[10px] font-medium" style="color: #9BA8B7;" x-text="msg.sender?.name || ''"></span>
                                        <span class="text-[10px]" style="color: #C5D0DB;" x-text="formatTime(msg.created_at)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="messages.length === 0">
                            <p class="text-center text-sm py-8" style="color: #9BA8B7;">Aucun message pour le moment.</p>
                        </template>
                    </div>

                    {{-- Reply form --}}
                    <form @submit.prevent="sendMessage()" class="flex gap-3 items-end">
                        <textarea x-model="newMessage" rows="2" required maxlength="2000"
                                  placeholder="{{ __('messages.type_message') ?? 'Tapez votre message...' }}"
                                  class="flex-1 px-4 py-3 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 transition-all duration-200"
                                  style="background: #F0F4F8; border: 1.5px solid #E0E6ED; color: #1B2A4A;"
                                  @keydown.enter.meta.prevent="sendMessage()"
                                  @keydown.ctrl.enter.prevent="sendMessage()"
                                  :disabled="sending"></textarea>
                        <button type="submit" :disabled="sending || !newMessage.trim()"
                                class="px-6 py-3 rounded-xl text-white font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5 flex-shrink-0"
                                :style="sending ? 'opacity:0.5;' : ''"
                                style="background: linear-gradient(135deg, #1B4F72, #17A2B8); box-shadow: 0 4px 15px rgba(27,79,114,0.3);">
                            <span x-show="!sending">{{ __('messages.send') ?? 'Envoyer' }}</span>
                            <span x-show="sending">...</span>
                        </button>
                    </form>
                </div>

                <script>
                function chatRoom() {
                    return {
                        messages: @json($conversation->messages->map(fn($m) => ['id' => $m->id, 'body' => $m->body, 'sender_id' => $m->sender_id, 'sender' => $m->sender ? ['name' => $m->sender->name] : null, 'created_at' => $m->created_at->toISOString()])),
                        currentUserId: {{ auth()->id() }},
                        newMessage: '',
                        sending: false,
                        pollInterval: null,
                        pollUrl: '{{ route("conversations.messages", $conversation) }}',
                        replyUrl: '{{ route("conversations.reply", $conversation) }}',
                        csrfToken: '{{ csrf_token() }}',

                        init() {
                            this.$nextTick(() => this.scrollToBottom());
                            this.pollInterval = setInterval(() => this.fetchMessages(), 5000);
                        },

                        destroy() {
                            if (this.pollInterval) clearInterval(this.pollInterval);
                        },

                        async fetchMessages() {
                            try {
                                const res = await fetch(this.pollUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                                if (!res.ok) return;
                                const data = await res.json();
                                if (data.length > this.messages.length) {
                                    const mapped = data.map(m => ({
                                        id: m.id,
                                        body: m.body,
                                        sender_id: m.sender_id,
                                        sender: m.sender ? { name: m.sender.name } : null,
                                        created_at: m.created_at
                                    }));
                                    this.messages = mapped;
                                    this.$nextTick(() => this.scrollToBottom());
                                }
                            } catch(e) { /* silent */ }
                        },

                        async sendMessage() {
                            if (this.sending || !this.newMessage.trim()) return;
                            this.sending = true;
                            const body = this.newMessage.trim();
                            try {
                                const res = await fetch(this.replyUrl, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                                    body: JSON.stringify({ body })
                                });
                                if (res.ok) {
                                    this.newMessage = '';
                                    // Immediately add optimistic message
                                    this.messages.push({
                                        id: Date.now(),
                                        body: body,
                                        sender_id: this.currentUserId,
                                        sender: { name: 'Moi' },
                                        created_at: new Date().toISOString()
                                    });
                                    this.$nextTick(() => this.scrollToBottom());
                                    // Fetch real data shortly after
                                    setTimeout(() => this.fetchMessages(), 1000);
                                }
                            } catch(e) { /* silent */ }
                            this.sending = false;
                        },

                        scrollToBottom() {
                            const el = this.$refs.msgContainer;
                            if (el) el.scrollTop = el.scrollHeight;
                        },

                        formatTime(iso) {
                            if (!iso) return '';
                            const d = new Date(iso);
                            const day = String(d.getDate()).padStart(2, '0');
                            const month = String(d.getMonth() + 1).padStart(2, '0');
                            const hours = String(d.getHours()).padStart(2, '0');
                            const mins = String(d.getMinutes()).padStart(2, '0');
                            return day + '/' + month + ' ' + hours + ':' + mins;
                        }
                    }
                }
                </script>
            </div>

            <!-- Back link -->
            <a href="{{ route('conversations.index') }}" class="inline-flex items-center gap-2 text-sm font-medium transition-opacity hover:opacity-80" style="color: #1B4F72;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ __('messages.back_to_messages') }}
            </a>

        </div>
    </div>
</x-app-layout>
