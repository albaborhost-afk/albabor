@props(['listing'])
<div class="flex-1" x-data="{ copied: false, failed: false, url: @js(route('listings.show', $listing)), title: @js($listing->title), async share() { this.failed = false; try { if (navigator.share) { await navigator.share({title: this.title, url: this.url}); } else { await navigator.clipboard.writeText(this.url); this.copied = true; setTimeout(() => this.copied = false, 3000); } } catch (e) { if (e.name !== 'AbortError') this.failed = true; } } }">
    <button type="button" @click="share()" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700"><span x-text="copied ? 'Lien copié' : 'Partager'">{{ __('Partager') }}</span></button>
    <input x-show="failed" x-cloak readonly :value="url" @click="$el.select()" class="w-full rounded border mt-2 p-2 text-xs" aria-label="{{ __('Lien à copier') }}">
</div>
