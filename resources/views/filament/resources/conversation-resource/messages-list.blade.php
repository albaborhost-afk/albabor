<div class="space-y-3 max-h-[500px] overflow-y-auto">
    @forelse($getRecord()->messages()->with('sender')->orderBy('created_at')->get() as $message)
        <div class="flex items-start gap-3 p-3 rounded-lg" style="background: {{ $message->sender_id === $getRecord()->buyer_id ? '#f0f9ff' : '#f0fdf4' }};">
            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: {{ $message->sender_id === $getRecord()->buyer_id ? '#1B4F72' : '#17A2B8' }};">
                {{ strtoupper(substr($message->sender?->name ?? '?', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-sm font-semibold" style="color: #1B2A4A;">{{ $message->sender?->name ?? 'Utilisateur supprime' }}</span>
                    <span class="text-xs px-1.5 py-0.5 rounded" style="background: {{ $message->sender_id === $getRecord()->buyer_id ? '#dbeafe' : '#dcfce7' }}; color: {{ $message->sender_id === $getRecord()->buyer_id ? '#1e40af' : '#166534' }};">
                        {{ $message->sender_id === $getRecord()->buyer_id ? 'Acheteur' : 'Vendeur' }}
                    </span>
                    <span class="text-xs" style="color: #9BA8B7;">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="text-sm whitespace-pre-wrap" style="color: #374151;">{{ $message->body }}</p>
            </div>
        </div>
    @empty
        <p class="text-sm text-center py-4" style="color: #9BA8B7;">Aucun message dans cette conversation.</p>
    @endforelse
</div>
