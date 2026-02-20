{{--
  Photo Uploader Component
  Props:
    - inputName: string  (e.g. 'images' → field name="images[]")
    - max: int           (max total photos, default 10)
    - required: bool     (default false)
    - existingMedia: Collection of ListingMedia (for edit mode, default empty)
--}}
@props([
    'inputName'     => 'images',
    'max'           => 10,
    'required'      => false,
    'existingMedia' => collect(),
])

@php
    $existingCount = $existingMedia->count();
    $maxNew        = $max - $existingCount;
    $hasExisting   = $existingCount > 0;
@endphp

<div
    x-data="photoUploader({{ $maxNew }}, {{ $required ? 'true' : 'false' }})"
    x-init="init()"
>

    {{-- ─── Existing images (edit mode) ─────────────────────────────── --}}
    @if($hasExisting)
    <div class="mb-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold uppercase tracking-wider" style="color:#6B7B8D;">
                Photos actuelles
            </p>
            <span class="text-[11px] px-2 py-0.5 rounded-full font-medium" style="background:#F0F4F8; color:#6B7B8D;">
                {{ $existingCount }} photo{{ $existingCount > 1 ? 's' : '' }}
            </span>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3" x-data="{ markedIds: [] }">
            @foreach($existingMedia as $media)
            <div
                x-data="{ marked: false }"
                class="relative group"
            >
                {{-- Hidden delete input (only active when marked) --}}
                <input x-show="marked" x-cloak type="hidden" name="delete_images[]" value="{{ $media->id }}">

                {{-- Image tile --}}
                <div
                    class="aspect-square rounded-xl overflow-hidden relative transition-all duration-200 cursor-pointer"
                    :class="marked ? 'ring-2 ring-red-400 ring-offset-1' : 'ring-1 ring-[#E0E6ED]'"
                    @click="marked = !marked"
                >
                    <img
                        src="{{ $media->thumbnail_url ?? $media->url }}"
                        alt=""
                        class="w-full h-full object-cover transition-all duration-200"
                        :class="marked ? 'opacity-30 scale-95' : ''"
                        loading="lazy"
                    >

                    {{-- "Principale" badge on first --}}
                    @if($loop->first)
                    <span
                        x-show="!marked"
                        class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded-lg text-[9px] font-bold text-white backdrop-blur-sm"
                        style="background:rgba(23,162,184,0.85);"
                    >Principale</span>
                    @endif

                    {{-- Delete overlay --}}
                    <div
                        x-show="marked"
                        class="absolute inset-0 flex items-center justify-center"
                        style="background:rgba(231,76,60,0.15);"
                    >
                        <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background:rgba(231,76,60,0.9);">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Hover hint --}}
                    <div
                        x-show="!marked"
                        class="absolute inset-0 flex items-end justify-center pb-2 opacity-0 group-hover:opacity-100 transition-opacity"
                        style="background:linear-gradient(to top, rgba(0,0,0,0.5), transparent);"
                    >
                        <span class="text-[10px] text-white font-medium">Cliquer pour supprimer</span>
                    </div>
                </div>

                {{-- Status label --}}
                <p
                    class="text-[9px] text-center mt-1 font-medium transition-colors"
                    :style="marked ? 'color:#E74C3C;' : 'color:#9BA8B7;'"
                    x-text="marked ? 'À supprimer' : ''"
                ></p>
            </div>
            @endforeach
        </div>

        <p class="text-[10px] mt-2" style="color:#9BA8B7;">
            Cliquez sur une photo pour la marquer pour suppression
        </p>
    </div>

    @if($maxNew > 0)
    <div class="h-px w-full mb-5" style="background:linear-gradient(90deg, transparent, #E0E6ED, transparent);"></div>
    <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color:#6B7B8D;">
        Ajouter de nouvelles photos
        <span class="ml-1 font-normal normal-case" style="color:#9BA8B7;">({{ $maxNew }} slot{{ $maxNew > 1 ? 's' : '' }} disponible{{ $maxNew > 1 ? 's' : '' }})</span>
    </p>
    @endif
    @endif

    {{-- ─── Upload zone ──────────────────────────────────────────────── --}}
    @if(!$hasExisting || $maxNew > 0)
    <div>
        {{-- Drop zone --}}
        <div
            class="relative border-2 border-dashed rounded-2xl transition-all duration-300 cursor-pointer overflow-hidden"
            :class="{
                'border-[#17A2B8] bg-[#17A2B8]/5 scale-[1.01]': isDragging,
                'border-[#E0E6ED] hover:border-[#17A2B8]/50 hover:bg-[#17A2B8]/3': !isDragging
            }"
            @dragenter.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @dragover.prevent
            @drop.prevent="handleDrop($event)"
            @click="$refs.fileInput.click()"
            :style="files.length > 0 ? 'padding: 1rem;' : 'padding: 2.5rem 1.5rem;'"
        >
            {{-- Empty state --}}
            <div x-show="files.length === 0" class="text-center">
                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl flex items-center justify-center transition-all duration-300"
                     :style="isDragging ? 'background:rgba(23,162,184,0.15);' : 'background:#F0F4F8;'">
                    <svg class="w-7 h-7 transition-colors duration-300" :style="isDragging ? 'color:#17A2B8;' : 'color:#9BA8B7;'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium" :style="isDragging ? 'color:#17A2B8;' : 'color:#1B2A4A;'">
                    <span x-show="!isDragging">Cliquez ou glissez vos photos ici</span>
                    <span x-show="isDragging">Lâchez pour ajouter</span>
                </p>
                <p class="text-[11px] mt-1" style="color:#9BA8B7;">JPEG, PNG, WebP · Max 5 Mo · {{ $hasExisting ? $maxNew : $max }} photos max</p>
            </div>

            {{-- Filled state — preview grid + add more button --}}
            <div x-show="files.length > 0">
                {{-- Counter bar --}}
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold" style="color:#1B2A4A;">
                        <span x-text="files.length"></span> / {{ $hasExisting ? $maxNew : $max }} photo<span x-show="files.length > 1">s</span>
                    </span>
                    <span
                        class="text-[11px] px-2.5 py-0.5 rounded-full font-medium"
                        :style="slotsLeft === 0 ? 'background:#FEF2F2; color:#E74C3C;' : 'background:#F0FDF4; color:#27AE60;'"
                    >
                        <span x-show="slotsLeft > 0">
                            <span x-text="slotsLeft"></span> slot<span x-show="slotsLeft > 1">s</span> restant<span x-show="slotsLeft > 1">s</span>
                        </span>
                        <span x-show="slotsLeft === 0">Limite atteinte</span>
                    </span>
                </div>

                {{-- Previews --}}
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2.5 mb-3">
                    <template x-for="(file, index) in files" :key="file.id">
                        <div
                            class="relative group"
                            @click.stop
                        >
                            <div
                                class="aspect-square rounded-xl overflow-hidden ring-1 ring-[#E0E6ED] transition-all duration-200 group-hover:ring-[#17A2B8]/60"
                            >
                                <img :src="file.preview" class="w-full h-full object-cover" alt="">
                            </div>

                            {{-- "Principale" on first --}}
                            <div
                                x-show="index === 0"
                                class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded-lg text-[9px] font-bold text-white"
                                style="background:rgba(23,162,184,0.9); backdrop-filter: blur(4px);"
                            >Principale</div>

                            {{-- Remove button --}}
                            <button
                                type="button"
                                @click.stop="removeFile(index)"
                                class="absolute top-1.5 right-1.5 w-5 h-5 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110"
                                style="background:rgba(231,76,60,0.9); color:white;"
                                title="Supprimer"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            {{-- File size --}}
                            <p class="text-[9px] text-center mt-0.5 truncate px-0.5" style="color:#9BA8B7;" x-text="formatSize(file.size)"></p>
                        </div>
                    </template>

                    {{-- Add more slot --}}
                    <template x-if="slotsLeft > 0">
                        <div
                            class="aspect-square rounded-xl border-2 border-dashed flex flex-col items-center justify-center cursor-pointer transition-all duration-200 hover:border-[#17A2B8] hover:bg-[#17A2B8]/5"
                            style="border-color:#E0E6ED;"
                            @click.stop="$refs.fileInput.click()"
                        >
                            <svg class="w-5 h-5 mb-1" style="color:#9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="text-[9px] font-medium" style="color:#9BA8B7;">Ajouter</span>
                        </div>
                    </template>
                </div>

                {{-- Drag hint --}}
                <p class="text-[10px] text-center" style="color:#C5D0DB;">
                    La première photo sera la photo principale
                </p>
            </div>

            {{-- Drag overlay --}}
            <div
                x-show="isDragging"
                class="absolute inset-0 flex items-center justify-center rounded-2xl pointer-events-none"
                style="background:rgba(23,162,184,0.08);"
            >
                <div class="text-center">
                    <svg class="w-10 h-10 mx-auto mb-2" style="color:#17A2B8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm font-semibold" style="color:#17A2B8;">Lâchez pour ajouter</p>
                </div>
            </div>
        </div>

        {{-- Error messages --}}
        <template x-if="errors.length > 0">
            <div class="mt-2 space-y-1">
                <template x-for="err in errors" :key="err">
                    <p class="text-[11px] flex items-center gap-1" style="color:#E74C3C;">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="err"></span>
                    </p>
                </template>
            </div>
        </template>

        {{-- Hidden actual file input --}}
        <input
            x-ref="fileInput"
            type="file"
            name="{{ $inputName }}[]"
            multiple
            accept="image/jpeg,image/png,image/webp,image/jpg"
            class="sr-only"
            @change="handleSelect($event)"
        >
    </div>
    @endif

</div>

{{-- ─── Alpine Component ─────────────────────────────────────────────── --}}
<script>
if (typeof photoUploader === 'undefined') {
    function photoUploader(maxFiles, isRequired = false) {
        return {
            files: [],
            isDragging: false,
            errors: [],
            maxFiles: maxFiles,
            isRequired: isRequired,

            get slotsLeft() {
                return Math.max(0, this.maxFiles - this.files.length);
            },

            init() {
                // Re-sync files to input right before form submission
                // (ensures DataTransfer is fresh; critical for all browsers)
                this.$nextTick(() => {
                    const form = this.$el.closest('form');
                    if (form) {
                        form.addEventListener('submit', (e) => {
                            this.syncInput();
                            // Manual required validation (browser native check runs before submit event)
                            if (this.isRequired && this.files.length === 0) {
                                e.preventDefault();
                                e.stopImmediatePropagation();
                                this.errors = ['Veuillez ajouter au moins une photo.'];
                                this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, { capture: true });
                    }
                });

                // Clean up previews on page unload
                window.addEventListener('beforeunload', () => {
                    this.files.forEach(f => URL.revokeObjectURL(f.preview));
                });
            },

            handleSelect(e) {
                this.addFiles(Array.from(e.target.files));
                // Reset so same file can be re-selected
                e.target.value = '';
            },

            handleDrop(e) {
                this.isDragging = false;
                const dropped = Array.from(e.dataTransfer.files)
                    .filter(f => f.type.startsWith('image/'));
                this.addFiles(dropped);
            },

            addFiles(newFiles) {
                this.errors = [];
                const available = this.maxFiles - this.files.length;
                let added = 0;

                for (const file of newFiles) {
                    if (added >= available) {
                        this.errors.push(`Limite de ${this.maxFiles} photo(s) atteinte.`);
                        break;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        this.errors.push(`"${file.name}" dépasse la limite de 5 Mo.`);
                        continue;
                    }
                    const allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
                    if (!allowed.includes(file.type)) {
                        this.errors.push(`"${file.name}" : format non supporté.`);
                        continue;
                    }
                    const id = crypto.randomUUID ? crypto.randomUUID() : (Date.now() + Math.random());
                    const preview = URL.createObjectURL(file);
                    this.files.push({ id, file, preview, size: file.size, name: file.name });
                    added++;
                }
                this.syncInput();
            },

            removeFile(index) {
                URL.revokeObjectURL(this.files[index].preview);
                this.files.splice(index, 1);
                this.syncInput();
            },

            syncInput() {
                if (!this.$refs.fileInput) return;
                try {
                    const dt = new DataTransfer();
                    this.files.forEach(f => dt.items.add(f.file));
                    this.$refs.fileInput.files = dt.files;
                } catch (e) {
                    console.warn('[PhotoUploader] DataTransfer sync failed:', e);
                }
            },

            formatSize(bytes) {
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' Ko';
                return (bytes / (1024 * 1024)).toFixed(1) + ' Mo';
            }
        };
    }
}
</script>
