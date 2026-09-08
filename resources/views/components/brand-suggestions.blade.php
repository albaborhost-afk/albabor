<div class="flex flex-wrap gap-1.5 mt-2" aria-label="{{ __('Suggestions de fabricants') }}">
    @foreach(\App\Support\ListingCatalog::BRANDS as $brand)
        <button type="button" data-brand="{{ $brand }}" @click="const input = $el.parentElement.parentElement.querySelector('input'); input.value = $el.dataset.brand; input.dispatchEvent(new Event('input', { bubbles: true }));" class="rounded-full border border-slate-200 px-2.5 py-1 text-xs text-slate-600 hover:border-cyan-600 hover:text-cyan-800">{{ $brand }}</button>
    @endforeach
</div>
