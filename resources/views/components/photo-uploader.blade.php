{{--
  Photo Uploader Component
  Props:
    - inputName: string  (e.g. 'images' → field name="images[]")
    - max: int           (max total photos, default 20)
    - required: bool     (default false)
    - existingMedia: Collection of ListingMedia (for edit mode, default empty)
--}}
@props([
    'inputName'     => 'images',
    'max'           => 20,
    'required'      => false,
    'existingMedia' => collect(),
    'persistKey'    => null,
])

@php
    $existingCount = $existingMedia->count();
    $maxNew        = $max - $existingCount;
    $hasExisting   = $existingCount > 0;
@endphp

<div
    data-photo-uploader
    x-data="photoUploader({{ $max }}, {{ $existingCount }}, {{ $required ? 'true' : 'false' }}, @js($persistKey))"
    x-init="init()"
>

    {{-- Unified Principale (cover) hidden inputs — root-level state --}}
    <input type="hidden" name="cover_image_id" :value="coverImageIdValue" :disabled="!coverImageIdValue">
    <input type="hidden" name="cover_new_index" :value="coverNewIndexValue" :disabled="coverNewIndexValue === ''">

    {{-- ─── Existing images (edit mode) ─────────────────────────────── --}}
    @if($hasExisting)
    <div class="mb-5">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold uppercase tracking-wider" style="color:#6B7B8D;">
                Photos actuelles
            </p>
            <span class="text-[11px] px-2 py-0.5 rounded-full font-medium" style="background:#F0F4F8; color:#6B7B8D;">
                {{ $existingCount }} photo{{ $existingCount > 1 ? 's' : '' }}
            </span>
        </div>

        {{-- Hint --}}
        <p class="text-[11px] mb-3 flex items-center gap-1.5" style="color:#9BA8B7;">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24" style="color:#17A2B8;">
                <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            Cliquez sur une photo pour la définir comme photo principale
        </p>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
            @foreach($existingMedia as $media)
            <div
                x-data="{ removed: false }"
                x-show="!removed"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
                data-existing-id="{{ $media->id }}"
                class="relative group"
            >
                {{-- Hidden delete input (only submitted once removed) --}}
                <input type="hidden" name="delete_images[]" value="{{ $media->id }}" x-bind:disabled="!removed">

                {{-- Image tile — click sets as principale --}}
                <div class="aspect-square rounded-xl overflow-hidden relative transition-all duration-200 cursor-pointer"
                     :class="cover === 'existing:{{ $media->id }}'
                         ? 'ring-2 ring-[#17A2B8] ring-offset-2 shadow-[0_0_0_1px_#17A2B8]'
                         : 'ring-1 ring-[#E0E6ED]'"
                     @click="setCoverExisting({{ $media->id }})">

                    <img src="{{ $media->thumbnail_url ?? $media->url }}" alt=""
                         class="w-full h-full object-cover" loading="lazy">

                    {{-- Principale badge (dynamic) --}}
                    <div x-show="cover === 'existing:{{ $media->id }}'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-75"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute top-1.5 left-1.5 flex items-center gap-1 px-1.5 py-0.5 rounded-lg text-[9px] font-bold text-white backdrop-blur-sm"
                         :class="coverPulse ? 'animate-pulse' : ''"
                         style="background:rgba(23,162,184,0.92);">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        Principale
                    </div>

                    {{-- Hover overlay: "Définir comme principale" for non-cover images --}}
                    <div x-show="cover !== 'existing:{{ $media->id }}'"
                         x-transition:enter="transition-opacity duration-150"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                         style="background:rgba(0,0,0,0.42);">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" style="color:white;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        <span class="text-[9px] font-bold" style="color:white;">Définir principale</span>
                    </div>
                </div>

                {{-- Delete button: instant removal --}}
                <button type="button"
                        @click.stop="if (!removed) { removed = true; $nextTick(() => handleExistingRemoval({{ $media->id }})); }"
                        class="absolute -top-2 -right-2 w-8 h-8 sm:w-7 sm:h-7 rounded-full flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-110 z-10"
                        style="background:#E74C3C; color:white;"
                        title="Supprimer cette photo">
                    <svg class="w-4 h-4 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                {{-- Blur button for existing photo --}}
                <button type="button"
                        @click.stop="$dispatch('open-blur-existing', { id: {{ $media->id }}, url: '{{ $media->url }}' })"
                        class="absolute bottom-1.5 right-1 w-6 h-6 rounded-full flex items-center justify-center transition-all duration-200 active:scale-95 opacity-100 sm:opacity-0 group-hover:opacity-100"
                        style="background:rgba(27,79,114,0.92); color:white; box-shadow:0 2px 8px rgba(0,0,0,0.35);"
                        title="Flouter une zone privée">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.536-6.536a2.5 2.5 0 013.536 3.536L12 15H9v-3l.232-.232z"/>
                    </svg>
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <div x-show="slotsLeft > 0" class="h-px w-full mb-5" style="background:linear-gradient(90deg, transparent, #E0E6ED, transparent);"></div>
    <p x-show="slotsLeft > 0" class="text-xs font-semibold uppercase tracking-wider mb-3" style="color:#6B7B8D;">
        Ajouter de nouvelles photos
        <span class="ml-1 font-normal normal-case" style="color:#9BA8B7;">
            (<span x-text="slotsLeft"></span> slot<span x-show="slotsLeft > 1">s</span> disponible<span x-show="slotsLeft > 1">s</span>)
        </span>
    </p>
    @endif

    {{-- ─── Upload zone ──────────────────────────────────────────────── --}}
    <div x-show="slotsLeft > 0 || files.length > 0">
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
                <p class="text-[11px] mt-1" style="color:#9BA8B7;">JPEG, PNG, WebP, HEIC · Max 15 Mo · <span x-text="maxFiles"></span> photo<span x-show="maxFiles > 1">s</span> max</p>
                <p
                    x-show="!supportsManagedFiles"
                    x-cloak
                    class="text-[11px] mt-1.5"
                    style="color:#F39C12;"
                >
                    Sur certains navigateurs mobiles, ajoutez toutes vos photos en une seule sélection.
                </p>
            </div>

            {{-- Filled state — preview grid + add more button --}}
            <div x-show="files.length > 0">
                {{-- Counter bar --}}
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold" style="color:#1B2A4A;">
                        <span x-text="files.length"></span> / <span x-text="maxFiles"></span> photo<span x-show="files.length > 1">s</span>
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

                {{-- Astuce ordre des photos --}}
                <p
                    x-show="supportsManagedFiles && files.length > 1"
                    class="text-[11px] mb-2 flex items-start gap-1.5 rounded-lg px-2.5 py-2"
                    style="background:rgba(23,162,184,0.08); color:#1B4F72; border:1px solid rgba(23,162,184,0.18);"
                >
                    <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24" style="color:#17A2B8;">
                        <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span><strong>Photo principale :</strong> touchez ou cliquez une autre vignette pour la mettre en première position (badge « Principale »).</span>
                </p>

                {{-- Previews --}}
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2.5 mb-3">
                    <template x-for="(file, index) in files" :key="file.id">
                        <div
                            class="relative group"
                            @click.stop
                        >
                            <div
                                class="aspect-square rounded-xl overflow-hidden transition-all duration-200 relative"
                                :class="(index === 0 && cover === 'new:0')
                                    ? 'ring-2 ring-[#17A2B8] ring-offset-1 shadow-[0_0_0_1px_rgba(23,162,184,0.35)]'
                                    : 'ring-1 ring-[#E0E6ED] group-hover:ring-[#17A2B8]/70 cursor-pointer'"
                                @click="moveToFront(index); setCoverNew(0)"
                                :title="(index === 0 && cover === 'new:0') ? 'Photo principale de l\'annonce' : 'Définir comme photo principale'"
                            >
                                <img :src="file.preview" class="w-full h-full object-cover pointer-events-none" alt="">

                                {{-- Tap / click hint on non-cover thumbnails --}}
                                <div
                                    x-show="!(index === 0 && cover === 'new:0')"
                                    class="absolute inset-0 flex flex-col items-center justify-end pb-2 px-1 bg-gradient-to-t from-black/55 via-black/10 to-transparent opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
                                >
                                    <span class="text-[8px] font-bold text-white text-center leading-tight drop-shadow-sm">
                                        Principale
                                    </span>
                                </div>
                            </div>

                            {{-- "Principale" badge — only on first new file when cover === 'new:0' --}}
                            <div
                                x-show="index === 0 && cover === 'new:0'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-75"
                                x-transition:enter-end="opacity-100 scale-100"
                                class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded-lg text-[9px] font-bold text-white"
                                :class="coverPulse ? 'animate-pulse' : ''"
                                style="background:rgba(23,162,184,0.9); backdrop-filter: blur(4px);"
                            >Principale</div>

                            {{-- Remove button --}}
                            <button
                                type="button"
                                @click.stop="removeFile(index); handleNewRemoval(index)"
                                class="absolute top-1.5 right-1.5 w-7 h-7 sm:w-5 sm:h-5 rounded-full flex items-center justify-center sm:opacity-0 sm:group-hover:opacity-100 transition-all duration-200 hover:scale-110"
                                style="background:rgba(231,76,60,0.95); color:white;"
                                title="Supprimer"
                            >
                                <svg class="w-3.5 h-3.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            {{-- Blur/Pixelate button — always visible mobile, hover desktop --}}
                            <button
                                type="button"
                                @click.stop="openBlurEditor(index)"
                                class="absolute bottom-5 right-1 w-6 h-6 rounded-full flex items-center justify-center transition-all duration-200 active:scale-95 sm:opacity-0 sm:group-hover:opacity-100"
                                style="background:rgba(27,79,114,0.92); color:white; box-shadow:0 2px 8px rgba(0,0,0,0.35);"
                                title="Flouter une zone privée"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.536-6.536a2.5 2.5 0 013.536 3.536L12 15H9v-3l.232-.232z"/>
                                </svg>
                            </button>

                            {{-- "Flou" badge when blurred --}}
                            <div
                                x-show="file.blurred"
                                x-cloak
                                class="absolute bottom-5 left-1.5 px-1.5 py-0.5 rounded-lg text-[9px] font-bold text-white"
                                style="background:rgba(27,79,114,0.9); backdrop-filter:blur(4px);"
                            >🔒 Flou</div>

                            {{-- File size --}}
                            <p class="text-[9px] text-center mt-0.5 truncate px-0.5" style="color:#9BA8B7;" x-text="formatSize(file.size)"></p>
                        </div>
                    </template>

                    {{-- Add more slot --}}
                    <template x-if="supportsManagedFiles ? slotsLeft > 0 : true">
                        <div
                            class="aspect-square rounded-xl border-2 border-dashed flex flex-col items-center justify-center cursor-pointer transition-all duration-200 hover:border-[#17A2B8] hover:bg-[#17A2B8]/5"
                            style="border-color:#E0E6ED;"
                            @click.stop="$refs.fileInput.click()"
                        >
                            <svg class="w-5 h-5 mb-1" style="color:#9BA8B7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span
                                class="text-[9px] font-medium"
                                style="color:#9BA8B7;"
                                x-text="supportsManagedFiles ? 'Ajouter' : 'Remplacer'"
                            ></span>
                        </div>
                    </template>
                </div>

                {{-- Drag hint --}}
                <p class="text-[10px] text-center" style="color:#C5D0DB;">
                    <span x-show="supportsManagedFiles">La vignette en première position est la photo principale — réorganisez en touchant une autre image.</span>
                    <span x-show="!supportsManagedFiles" x-cloak>La première photo de votre sélection sera la principale. Pour en changer, sélectionnez à nouveau les fichiers dans l’ordre souhaité.</span>
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

        {{-- Processing overlay --}}
        <div x-show="isProcessing" x-transition class="mt-3 rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #F8FBFF, #EDF5FF); border: 1.5px solid rgba(23,162,184,0.2);">
            <div class="px-4 py-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="relative w-10 h-10 flex-shrink-0">
                        <svg class="w-10 h-10 animate-spin" viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="20" r="17" stroke="#E0E6ED" stroke-width="3"/>
                            <circle cx="20" cy="20" r="17" stroke="url(#prog-grad)" stroke-width="3" stroke-linecap="round"
                                    :stroke-dasharray="'107'" :stroke-dashoffset="107 - (107 * progressPercent / 100)"/>
                            <defs><linearGradient id="prog-grad" x1="0" y1="0" x2="40" y2="40"><stop stop-color="#1B4F72"/><stop offset="1" stop-color="#17A2B8"/></linearGradient></defs>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-[9px] font-bold" style="color:#1B4F72;" x-text="progressPercent + '%'"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold" style="color:#1B2A4A;">Optimisation des photos...</p>
                        <p class="text-xs mt-0.5" style="color:#6B7B8D;">
                            <span x-text="processedCount"></span> / <span x-text="totalToProcess"></span> photos traitees
                        </p>
                    </div>
                </div>
                <div class="w-full h-2 rounded-full overflow-hidden" style="background:#E0E6ED;">
                    <div class="h-full rounded-full transition-all duration-300 ease-out"
                         :style="'width:' + progressPercent + '%; background: linear-gradient(90deg, #1B4F72, #17A2B8);'"></div>
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
            accept="image/jpeg,image/png,image/webp,image/jpg,image/heic,image/heif,.heic,.heif"
            class="sr-only"
            @change="handleSelect($event)"
        >
    </div>

{{-- ─── Blur / Pixelate Editor Modal ──────────────────────────────────── --}}
<div
    x-show="blurOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[9999] flex flex-col select-none"
    style="background:rgba(8,12,22,0.97); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);"
    @keydown.escape.window="blurOpen = false"
>
    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.07);">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#1B4F72,#17A2B8);">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.536-6.536a2.5 2.5 0 013.536 3.536L12 15H9v-3l.232-.232z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-white font-semibold text-sm leading-tight">Protéger une zone</p>
                <p class="text-[11px] leading-tight" style="color:#6B8CA8;">Peignez sur l’immatriculation ou info privée</p>
            </div>
        </div>
        <button type="button" @click="blurOpen = false"
                class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 ml-2"
                style="background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.6);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Canvas --}}
    <div class="flex-1 flex items-center justify-center overflow-hidden" style="padding:10px;">
        <canvas
            x-ref="blurCanvas"
            class="rounded-2xl max-w-full max-h-full"
            style="touch-action:none;display:block;box-shadow:0 0 0 1px rgba(255,255,255,0.07);cursor:crosshair;"
            @mousedown="blurDown($event)"
            @mousemove="blurMove($event)"
            @mouseup="blurUp($event)"
            @mouseleave="blurUp($event)"
            @touchstart.prevent="blurDown($event)"
            @touchmove.prevent="blurMove($event)"
            @touchend.prevent="blurUp($event)"
            @touchcancel.prevent="blurUp($event)"
        ></canvas>
    </div>

    {{-- Toolbar --}}
    <div class="flex-shrink-0 px-4" style="border-top:1px solid rgba(255,255,255,0.07);padding-bottom:max(16px,env(safe-area-inset-bottom));">
        {{-- Brush size --}}
        <div class="flex items-center gap-2 py-3">
            <span class="text-[11px] font-medium flex-shrink-0" style="color:#6B8CA8;">Pinceau :</span>
            <button type="button" @click="blurBrushLevel=1"
                    class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs font-semibold transition-all active:scale-95"
                    :style="blurBrushLevel===1?'background:linear-gradient(135deg,#1B4F72,#17A2B8);color:white;':'background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.5);'">
                <span class="inline-block rounded-full" style="width:7px;height:7px;background:currentColor;"></span>Petit
            </button>
            <button type="button" @click="blurBrushLevel=2"
                    class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs font-semibold transition-all active:scale-95"
                    :style="blurBrushLevel===2?'background:linear-gradient(135deg,#1B4F72,#17A2B8);color:white;':'background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.5);'">
                <span class="inline-block rounded-full" style="width:11px;height:11px;background:currentColor;"></span>Moyen
            </button>
            <button type="button" @click="blurBrushLevel=3"
                    class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs font-semibold transition-all active:scale-95"
                    :style="blurBrushLevel===3?'background:linear-gradient(135deg,#1B4F72,#17A2B8);color:white;':'background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.5);'">
                <span class="inline-block rounded-full" style="width:16px;height:16px;background:currentColor;"></span>Grand
            </button>
        </div>
        {{-- Actions --}}
        <div class="flex items-center gap-2 pb-3">
            <button type="button" @click="blurUndo()"
                    class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-medium transition-all active:scale-95"
                    style="background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.65);"
                    :class="blurHistory.length<=1?'opacity-30 pointer-events-none':''">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>Annuler
            </button>
            <button type="button" @click="blurReset()"
                    class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-medium transition-all active:scale-95"
                    style="background:rgba(255,255,255,0.07);color:rgba(255,255,255,0.65);"
                    :class="blurHistory.length<=1?'opacity-30 pointer-events-none':''">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>Reset
            </button>
            <div class="flex-1"></div>
            <span x-show="blurHistory.length>1" x-text="(blurHistory.length-1)+' trait'+(blurHistory.length>2?'s':'')"
                  class="text-[10px] font-semibold px-2 py-1 rounded-lg flex-shrink-0"
                  style="background:rgba(23,162,184,0.15);color:#17A2B8;"></span>
            <button type="button" @click="blurSave()"
                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white transition-all active:scale-95"
                    style="background:linear-gradient(135deg,#1B4F72,#17A2B8);box-shadow:0 4px 14px rgba(23,162,184,0.35);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>Enregistrer
            </button>
        </div>
    </div>
</div>

</div>


{{-- ─── Alpine Component ─────────────────────────────────────────────── --}}
<script>
if (typeof photoUploader === 'undefined') {
    function photoUploader(absoluteMax, existingCount = 0, isRequired = false, persistKey = null) {
        return {
            files: [],
            isDragging: false,
            errors: [],
            absoluteMax: absoluteMax,
            existingCount: existingCount,
            removedExistingCount: 0,
            isRequired: isRequired,
            persistKey: persistKey,
            supportsManagedFiles: false,
            isProcessing: false,
            processedCount: 0,
            totalToProcess: 0,
            _heic2anyPromise: null,

            // Dynamic limit: total cap minus existing photos that haven't been
            // removed in this session. Recomputed every render so deleting an
            // existing photo on mobile immediately frees up an upload slot.
            get maxFiles() {
                const remainingExisting = Math.max(0, this.existingCount - this.removedExistingCount);
                return Math.max(0, this.absoluteMax - remainingExisting);
            },

            // ── Unified Principale (cover) state ──
            // null | "existing:<id>" | "new:<index>"
            cover: null,
            coverPulse: false,
            _coverPulseTimer: null,

            // ── Blur / Pixelate editor ──
            blurOpen: false,
            blurIdx: null,
            blurExistingId: null,
            blurExistingObjectUrl: null,
            blurCanvas: null,
            blurCtx: null,
            blurImg: null,
            blurHistory: [],
            blurDragging: false,
            blurLastX: 0, blurLastY: 0,
            blurBrushLevel: 2,

            get slotsLeft() {
                return Math.max(0, this.maxFiles - this.files.length);
            },

            // ── Cover (Principale) helpers ──────────────────────────────
            get coverImageIdValue() {
                return (this.cover && this.cover.startsWith('existing:'))
                    ? this.cover.split(':')[1]
                    : '';
            },

            get coverNewIndexValue() {
                return (this.cover && this.cover.startsWith('new:'))
                    ? this.cover.split(':')[1]
                    : '';
            },

            pulseCover() {
                if (this._coverPulseTimer) {
                    clearTimeout(this._coverPulseTimer);
                }
                this.coverPulse = true;
                this._coverPulseTimer = setTimeout(() => {
                    this.coverPulse = false;
                    this._coverPulseTimer = null;
                }, 600);
            },

            setCoverExisting(id) {
                this.cover = `existing:${id}`;
                this.pulseCover();
            },

            setCoverNew(index) {
                this.cover = `new:${index}`;
                this.pulseCover();
            },

            initCover(firstExistingId) {
                if (firstExistingId !== null && firstExistingId !== undefined && firstExistingId !== '') {
                    this.cover = `existing:${firstExistingId}`;
                } else if (this.files.length > 0) {
                    this.cover = 'new:0';
                } else {
                    this.cover = null;
                }
            },

            handleExistingRemoval(removedId) {
                // Free up an upload slot so the user can replace the deleted
                // photo with a new one (especially when the listing was at the
                // 20-photo cap).
                this.removedExistingCount++;

                if (this.cover === `existing:${removedId}`) {
                    const remainingIds = Array.from(this.$el.querySelectorAll('[data-existing-id]'))
                        .map(el => el.dataset.existingId)
                        .filter(id => id !== String(removedId));
                    if (remainingIds.length > 0) {
                        this.cover = `existing:${remainingIds[0]}`;
                    } else if (this.files.length > 0) {
                        this.cover = 'new:0';
                    } else {
                        this.cover = null;
                    }
                }
            },

            handleNewRemoval(removedIndex) {
                // Called AFTER removeFile() has already spliced out the index.
                if (this.cover === `new:${removedIndex}`) {
                    if (this.files.length > 0) {
                        this.cover = 'new:0';
                    } else {
                        // Fall back to the first remaining existing photo if any
                        const remainingIds = Array.from(this.$el.querySelectorAll('[data-existing-id]'))
                            .map(el => el.dataset.existingId);
                        this.cover = remainingIds.length > 0 ? `existing:${remainingIds[0]}` : null;
                    }
                } else if (this.cover && this.cover.startsWith('new:')) {
                    const idx = parseInt(this.cover.split(':')[1], 10);
                    if (idx > removedIndex) {
                        this.cover = `new:${idx - 1}`;
                    }
                }
            },

            init() {
                this.$el._albaborPhotoUploader = this;
                this.supportsManagedFiles = this.detectManagedFileSupport() && !this.prefersNativeSelection();

                // Initialize cover state from the first existing photo (edit mode).
                // In create mode, falls back to null until files arrive.
                this.initCover(@json($existingMedia->first()?->id));

                // Only validate the required state on submit.
                // Rewriting input.files during submit is brittle on mobile browsers.
                this.$nextTick(() => {
                    const form = this.$el.closest('form');
                    if (form) {
                        form.addEventListener('submit', (e) => {
                            // Toujours compter this.files : c'est ce que
                            // getFilesForSubmit() envoie réellement. Compter le
                            // champ natif laissait passer le cas où les photos
                            // avaient été écartées à la préparation — l'envoi
                            // partait alors sans aucune image, et le serveur
                            // répondait « le champ images est obligatoire ».
                            const selectedCount = this.files.length;

                            // Manual required validation (browser native check runs before submit event)
                            if (this.isRequired && selectedCount === 0) {
                                e.preventDefault();
                                e.stopImmediatePropagation();
                                this.errors = ['Veuillez ajouter au moins une photo.'];
                                this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, { capture: true });
                    }
                });

                this.$nextTick(async () => {
                    await this.restorePersistedFiles();
                    // After restoring persisted files, if no cover is set yet but we have files,
                    // default the cover to the first new photo.
                    if (this.cover === null && this.files.length > 0) {
                        this.cover = 'new:0';
                    }
                });

                // Listen for blur requests from existing photos
                this.$el.addEventListener('open-blur-existing', (e) => {
                    this.openBlurEditorExisting(e.detail.id, e.detail.url);
                });

                // Clean up previews on page unload
                window.addEventListener('beforeunload', () => {
                    this.files.forEach(f => URL.revokeObjectURL(f.preview));
                    if (this.blurExistingObjectUrl) URL.revokeObjectURL(this.blurExistingObjectUrl);
                });
            },

            async openPersistDb() {
                if (!this.persistKey || typeof indexedDB === 'undefined') {
                    return null;
                }

                return await new Promise((resolve, reject) => {
                    const request = indexedDB.open('albabor-photo-uploader', 1);

                    request.onupgradeneeded = () => {
                        const db = request.result;
                        if (!db.objectStoreNames.contains('drafts')) {
                            db.createObjectStore('drafts');
                        }
                    };

                    request.onsuccess = () => resolve(request.result);
                    request.onerror = () => reject(request.error);
                });
            },

            getPersistKey() {
                if (!this.persistKey) {
                    return null;
                }

                try {
                    const sessionKey = 'albabor_listing_draft_session';
                    let sessionId = sessionStorage.getItem(sessionKey);
                    if (!sessionId) {
                        sessionId = (crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random());
                        sessionStorage.setItem(sessionKey, sessionId);
                    }

                    return `${this.persistKey}:${sessionId}`;
                } catch (e) {
                    return this.persistKey;
                }
            },

            async persistFiles() {
                if (!this.persistKey) {
                    return;
                }

                const key = this.getPersistKey();
                if (!key) {
                    return;
                }

                try {
                    const db = await this.openPersistDb();
                    if (!db) {
                        return;
                    }

                    await new Promise((resolve, reject) => {
                        const tx = db.transaction('drafts', 'readwrite');
                        const store = tx.objectStore('drafts');
                        const payload = this.files.map(file => ({
                            id: file.id,
                            name: file.name,
                            file: file.file,
                        }));

                        store.put(payload, key);
                        tx.oncomplete = () => resolve();
                        tx.onerror = () => reject(tx.error);
                        tx.onabort = () => reject(tx.error);
                    });

                    db.close();
                } catch (e) {
                    console.warn('[PhotoUploader] Persisting files failed:', e);
                }
            },

            async clearPersistedFiles() {
                if (!this.persistKey) {
                    return;
                }

                const key = this.getPersistKey();
                if (!key) {
                    return;
                }

                try {
                    const db = await this.openPersistDb();
                    if (!db) {
                        return;
                    }

                    await new Promise((resolve, reject) => {
                        const tx = db.transaction('drafts', 'readwrite');
                        tx.objectStore('drafts').delete(key);
                        tx.oncomplete = () => resolve();
                        tx.onerror = () => reject(tx.error);
                        tx.onabort = () => reject(tx.error);
                    });

                    db.close();
                } catch (e) {
                    console.warn('[PhotoUploader] Clearing persisted files failed:', e);
                }
            },

            async restorePersistedFiles() {
                if (!this.persistKey || this.files.length > 0) {
                    return;
                }

                const key = this.getPersistKey();
                if (!key) {
                    return;
                }

                try {
                    const db = await this.openPersistDb();
                    if (!db) {
                        return;
                    }

                    const payload = await new Promise((resolve, reject) => {
                        const tx = db.transaction('drafts', 'readonly');
                        const request = tx.objectStore('drafts').get(key);
                        request.onsuccess = () => resolve(request.result);
                        request.onerror = () => reject(request.error);
                    });

                    db.close();

                    if (!Array.isArray(payload) || payload.length === 0) {
                        return;
                    }

                    this.revokePreviews();
                    this.files = payload
                        .filter(item => item?.file instanceof Blob)
                        .slice(0, this.maxFiles)
                        .map(item => {
                            const restoredFile = item.file instanceof File
                                ? item.file
                                : new File([item.file], item.name || 'photo.jpg', {
                                    type: item.file?.type || 'image/jpeg',
                                    lastModified: Date.now(),
                                });

                            return {
                                id: item.id || (crypto.randomUUID ? crypto.randomUUID() : (Date.now() + Math.random())),
                                file: restoredFile,
                                preview: URL.createObjectURL(restoredFile),
                                size: restoredFile.size,
                                name: item.name || restoredFile.name,
                            };
                        });

                    if (this.supportsManagedFiles) {
                        this.syncInput();
                    }
                } catch (e) {
                    console.warn('[PhotoUploader] Restoring files failed:', e);
                }
            },

            handleSelect(e) {
                const selectedFiles = Array.from(e.target.files);

                if (this.supportsManagedFiles) {
                    this.addFiles(selectedFiles);
                    // Reset so same file can be re-selected
                    e.target.value = '';
                    return;
                }

                this.replaceNativeSelection(selectedFiles);
            },

            handleDrop(e) {
                this.isDragging = false;
                const dropped = Array.from(e.dataTransfer.files)
                    .filter(f => f.type.startsWith('image/'));

                if (!this.supportsManagedFiles) {
                    this.errors = ['Veuillez utiliser le sélecteur de photos sur ce navigateur mobile.'];
                    return;
                }

                this.addFiles(dropped);
            },

            // ── HEIC / HEIF support ────────────────────────────────────
            // iPhone photos are often HEIC. Chrome/Android browsers can't
            // decode HEIC, so the <img>/canvas pipeline fails (onerror) and
            // the raw HEIC reaches the server — which rejects it with
            // "doit être une image" (the `image` rule) and can't process it
            // with GD anyway. We convert HEIC → JPEG in the browser so the
            // server always receives a JPEG (also fixes the broken preview).
            isHeic(file) {
                const t = (file.type || '').toLowerCase();
                if (t === 'image/heic' || t === 'image/heif') return true;
                // Some browsers report an empty MIME type for HEIC — use the extension.
                return /\.(heic|heif)$/i.test(file.name || '');
            },

            loadHeic2any() {
                if (window.heic2any) return Promise.resolve();
                if (!this._heic2anyPromise) {
                    this._heic2anyPromise = new Promise((resolve, reject) => {
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js';
                        s.onload = () => resolve();
                        s.onerror = () => { this._heic2anyPromise = null; reject(new Error('heic2any load failed')); };
                        document.head.appendChild(s);
                    });
                }
                return this._heic2anyPromise;
            },

            async convertHeicIfNeeded(file) {
                if (!this.isHeic(file)) return file;
                await this.loadHeic2any();
                const out = await window.heic2any({ blob: file, toType: 'image/jpeg', quality: 0.9 });
                const blob = Array.isArray(out) ? out[0] : out;
                const baseName = (file.name || 'photo').replace(/\.\w+$/, '');
                return new File([blob], baseName + '.jpg', { type: 'image/jpeg' });
            },

            // Compress image client-side before upload (max 1600px, JPEG 82%).
            //
            // Le serveur ramène de toute façon les photos à 1200px : envoyer
            // plus grand ne gagne aucun détail, mais 20 photos de 2 Mo font une
            // requête de 40 Mo qui expire côté passerelle sur réseau mobile —
            // l'annonce est enregistrée et l'utilisateur voit une erreur.
            compressImage(file) {
                return new Promise((resolve) => {
                    // Skip small files (< 400 Ko) — already light enough
                    if (file.size < 400 * 1024) { resolve(file); return; }

                    const img = new Image();
                    const url = URL.createObjectURL(file);
                    img.onload = () => {
                        URL.revokeObjectURL(url);
                        const MAX = 1600;
                        let w = img.width, h = img.height;
                        if (w > MAX || h > MAX) {
                            if (w > h) { h = Math.round(h * MAX / w); w = MAX; }
                            else { w = Math.round(w * MAX / h); h = MAX; }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = w; canvas.height = h;
                        canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                        canvas.toBlob((blob) => {
                            if (blob && blob.size < file.size) {
                                const compressed = new File([blob], file.name.replace(/\.\w+$/, '.jpg'), { type: 'image/jpeg' });
                                resolve(compressed);
                            } else {
                                resolve(file); // Original is smaller, keep it
                            }
                        }, 'image/jpeg', 0.82);
                    };
                    img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
                    img.src = url;
                });
            },

            /**
             * Préparation commune aux deux sélecteurs de photos.
             *
             * Le sélecteur natif (tous les mobiles, cf. prefersNativeSelection)
             * ne passait par AUCUNE de ces étapes : pas de conversion HEIC, pas
             * de compression, et une seule photo refusée effaçait la sélection
             * entière. Résultat côté téléphone : soit un envoi de 100 Mo rejeté
             * par la passerelle avant même d'atteindre PHP (donc invisible dans
             * les journaux), soit un HEIC refusé par la règle `image`, soit un
             * encadré de photos brutalement vidé. Les deux chemins partagent
             * désormais exactement la même préparation.
             *
             * Attention : ne jamais écrire deux accolades ouvrantes de suite
             * dans ce fichier, même en commentaire — Blade les compile en
             * `echo`. Un type JSDoc écrit de cette façon a provoqué une erreur
             * 500 sur toute la page de publication.
             *
             * @param incoming  les fichiers choisis (File[])
             * @param options   objet avec `replace` : vrai pour le sélecteur
             *                  natif, qui remplace la sélection au lieu de
             *                  l'agrandir.
             */
            async processSelection(incoming, { replace }) {
                this.errors = [];

                const capacity = replace ? this.maxFiles : this.maxFiles - this.files.length;
                const accepted = [];
                let limitNotice = null;

                for (const file of incoming) {
                    if (accepted.length >= capacity) {
                        // Le sélecteur natif ne peut pas être plafonné depuis le web :
                        // on garde les premières plutôt que de tout refuser.
                        limitNotice = replace
                            ? `Vous avez sélectionné ${incoming.length} photos. Seules les ${this.maxFiles} premières ont été conservées.`
                            : `Limite de ${this.maxFiles} photo(s) atteinte.`;
                        break;
                    }
                    if (file.size > 15 * 1024 * 1024) {
                        // `continue` et non un abandon global : une photo trop
                        // lourde ne doit pas emporter les 19 autres.
                        this.errors.push(`"${file.name}" dépasse la limite de 15 Mo.`);
                        continue;
                    }
                    const allowed = ['image/jpeg','image/jpg','image/png','image/webp','image/heic','image/heif',''];
                    if (!allowed.includes(file.type) && !this.isHeic(file)) {
                        this.errors.push(`"${file.name}" : format non supporté.`);
                        continue;
                    }
                    accepted.push(file);
                }

                if (accepted.length === 0) {
                    if (limitNotice) {
                        this.errors.push(limitNotice);
                    }
                    return;
                }

                this.isProcessing = true;
                this.processedCount = 0;
                this.totalToProcess = accepted.length;
                this.$dispatch('photos-processing');

                const prepared = [];

                for (const file of accepted) {
                    // HEIC → JPEG d'abord : le navigateur peut alors afficher un
                    // aperçu, et le serveur reçoit une image qu'il sait traiter.
                    let working = file;
                    try {
                        working = await this.convertHeicIfNeeded(file);
                    } catch (e) {
                        this.errors.push(`"${file.name}" : conversion HEIC impossible. Réessayez en JPEG.`);
                        this.processedCount++;
                        continue;
                    }
                    const optimized = await this.compressImage(working);
                    prepared.push({
                        id: crypto.randomUUID ? crypto.randomUUID() : (Date.now() + Math.random()),
                        file: optimized,
                        preview: URL.createObjectURL(optimized),
                        size: optimized.size,
                        name: working.name,
                    });
                    this.processedCount++;
                }

                if (replace) {
                    this.revokePreviews();
                    this.files = prepared;
                    // Ne pas écraser une couverture choisie parmi les photos déjà
                    // enregistrées (mode édition).
                    if (this.cover === null || String(this.cover).startsWith('new:')) {
                        this.cover = prepared.length > 0 ? 'new:0' : null;
                    }
                } else {
                    this.files.push(...prepared);
                }

                this.isProcessing = false;

                if (this.cover === null && this.files.length > 0) {
                    this.cover = 'new:0';
                }

                if (limitNotice) {
                    this.errors.push(limitNotice);
                }

                this.$dispatch('photos-ready');

                if (this.supportsManagedFiles) {
                    this.syncInput();
                }

                await this.persistFiles();
            },

            addFiles(newFiles) {
                return this.processSelection(newFiles, { replace: false });
            },

            get progressPercent() {
                if (this.totalToProcess === 0) return 0;
                return Math.round((this.processedCount / this.totalToProcess) * 100);
            },

            removeFile(index) {
                if (index < 0 || index >= this.files.length) return;
                URL.revokeObjectURL(this.files[index].preview);
                this.files.splice(index, 1);
                // syncInput uses DataTransfer which is unreliable on mobile;
                // submission goes through getFilesForSubmit() (FormData) anyway.
                if (this.supportsManagedFiles) {
                    this.syncInput();
                }
                this.persistFiles();
            },

            /** Met la vignette choisie en tête de liste (= photo principale à l'enregistrement). */
            moveToFront(index) {
                if (index <= 0 || index >= this.files.length) {
                    return;
                }
                const [item] = this.files.splice(index, 1);
                this.files.unshift(item);
                if (this.supportsManagedFiles) {
                    this.syncInput();
                }
                this.persistFiles();
            },

            detectManagedFileSupport() {
                try {
                    if (typeof DataTransfer === 'undefined') {
                        return false;
                    }

                    const input = document.createElement('input');
                    input.type = 'file';

                    const dt = new DataTransfer();
                    input.files = dt.files;

                    return input.files !== null;
                } catch (e) {
                    return false;
                }
            },

            prefersNativeSelection() {
                try {
                    const ua = navigator.userAgent || '';
                    const touchPoints = navigator.maxTouchPoints || 0;
                    const isiPadOs = /Macintosh/i.test(ua) && touchPoints > 1;

                    return /Android|iPhone|iPad|iPod|Mobile|CriOS|FxiOS/i.test(ua) || isiPadOs;
                } catch (e) {
                    return false;
                }
            },

            shouldUseAjaxSubmit() {
                // Always prefer AJAX submission when the component is in use.
                // Native form submission + DataTransfer-populated file inputs is
                // unreliable across Safari/iOS and some Android browsers, so we
                // send a clean FormData via fetch() instead of relying on the
                // browser to serialize file inputs correctly.
                return true;
            },

            getFilesForSubmit() {
                return this.files.map(entry => entry.file).filter(file => file instanceof Blob);
            },

            /**
             * Sélecteur natif (mobile) : la sélection remplace la précédente,
             * mais elle est préparée exactement comme sur ordinateur.
             */
            replaceNativeSelection(selectedFiles) {
                if (selectedFiles.length === 0) {
                    this.errors = [];
                    this.clearNativeSelection();
                    return Promise.resolve();
                }

                return this.processSelection(selectedFiles, { replace: true });
            },

            clearNativeSelection() {
                this.revokePreviews();
                this.files = [];
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
                // If the current cover pointed to a new file, drop it back to first existing or null.
                if (this.cover && this.cover.startsWith('new:')) {
                    const remainingIds = Array.from(this.$el.querySelectorAll('[data-existing-id]'))
                        .map(el => el.dataset.existingId);
                    this.cover = remainingIds.length > 0 ? `existing:${remainingIds[0]}` : null;
                }
                this.clearPersistedFiles();
            },

            revokePreviews() {
                this.files.forEach(file => {
                    if (file.preview) {
                        URL.revokeObjectURL(file.preview);
                    }
                });
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
            },

            // ── Blur / Pixelate editor ──────────────────────────────────────

            openBlurEditor(index) {
                this.blurIdx = index;
                this.blurExistingId = null;
                this.blurHistory = [];
                this.blurDragging = false;
                this.blurOpen = true;
                this.$nextTick(() => this.blurInit());
            },

            async openBlurEditorExisting(mediaId, imageUrl) {
                this.blurIdx = null;
                this.blurExistingId = mediaId;
                this.blurHistory = [];
                this.blurDragging = false;
                // Fetch image as blob to bypass CORS canvas restrictions
                try {
                    const resp = await fetch(imageUrl, { credentials: 'same-origin' });
                    if (!resp.ok) throw new Error('fetch failed');
                    const blob = await resp.blob();
                    if (this.blurExistingObjectUrl) URL.revokeObjectURL(this.blurExistingObjectUrl);
                    this.blurExistingObjectUrl = URL.createObjectURL(blob);
                } catch (e) {
                    this.blurExistingObjectUrl = imageUrl;
                }
                this.blurOpen = true;
                this.$nextTick(() => this.blurInitFromUrl(this.blurExistingObjectUrl));
            },

            blurInit() {
                const canvas = this.$refs.blurCanvas;
                if (!canvas) return;
                this.blurCanvas = canvas;
                this.blurCtx = canvas.getContext('2d');
                const f = this.files[this.blurIdx];
                if (!f) return;
                const img = new Image();
                const url = URL.createObjectURL(f.file);
                img.onload = () => {
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    this.blurCtx.drawImage(img, 0, 0);
                    this.blurImg = img;
                    this.blurHistory = [this.blurCtx.getImageData(0, 0, canvas.width, canvas.height)];
                    URL.revokeObjectURL(url);
                };
                img.src = url;
            },

            blurInitFromUrl(url) {
                const canvas = this.$refs.blurCanvas;
                if (!canvas) return;
                this.blurCanvas = canvas;
                this.blurCtx = canvas.getContext('2d');
                const img = new Image();
                img.onload = () => {
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    this.blurCtx.drawImage(img, 0, 0);
                    this.blurImg = img;
                    this.blurHistory = [this.blurCtx.getImageData(0, 0, canvas.width, canvas.height)];
                };
                img.onerror = () => {
                    this.blurOpen = false;
                    alert('Impossible de charger cette photo pour l\'édition. Veuillez réessayer.');
                };
                img.src = url;
            },

            blurCoords(e) {
                const canvas = this.blurCanvas;
                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                const src = e.touches ? e.touches[0] : e;
                return {
                    x: (src.clientX - rect.left) * scaleX,
                    y: (src.clientY - rect.top) * scaleY,
                };
            },

            blurRadius() {
                if (!this.blurCanvas) return 50;
                const base = Math.min(this.blurCanvas.width, this.blurCanvas.height);
                return Math.round(base * [0.04, 0.07, 0.12][this.blurBrushLevel - 1]);
            },

            blurDown(e) {
                e.preventDefault();
                if (!this.blurCanvas) return;
                this.blurDragging = true;
                const {x, y} = this.blurCoords(e);
                this.blurLastX = x; this.blurLastY = y;
                this.blurApplyBrush(x, y);
            },

            blurMove(e) {
                if (!this.blurDragging) return;
                e.preventDefault();
                const {x, y} = this.blurCoords(e);
                const r = this.blurRadius();
                const dx = x - this.blurLastX;
                const dy = y - this.blurLastY;
                const dist = Math.sqrt(dx * dx + dy * dy);
                const step = Math.max(1, r * 0.35);
                const count = Math.ceil(dist / step);
                for (let i = 1; i <= count; i++) {
                    const t = i / count;
                    this.blurApplyBrush(this.blurLastX + dx * t, this.blurLastY + dy * t);
                }
                this.blurLastX = x;
                this.blurLastY = y;
            },

            blurUp(e) {
                if (!this.blurDragging) return;
                this.blurDragging = false;
                // Snapshot post-stroke for undo (original + max 4 strokes)
                const snap = this.blurCtx.getImageData(0, 0, this.blurCanvas.width, this.blurCanvas.height);
                if (this.blurHistory.length >= 6) this.blurHistory.splice(1, 1);
                this.blurHistory.push(snap);
            },

            blurApplyBrush(cx, cy) {
                const ctx = this.blurCtx;
                const canvas = this.blurCanvas;
                const r = this.blurRadius();
                const x  = Math.max(0, Math.floor(cx - r));
                const y  = Math.max(0, Math.floor(cy - r));
                const x2 = Math.min(canvas.width,  Math.ceil(cx + r));
                const y2 = Math.min(canvas.height, Math.ceil(cy + r));
                const w = x2 - x, h = y2 - y;
                if (w <= 0 || h <= 0) return;
                const ps = Math.max(7, Math.floor(r / 4));
                const imgData = ctx.getImageData(x, y, w, h);
                const d = imgData.data;
                const rSq = r * r;
                for (let py = 0; py < h; py += ps) {
                    for (let px = 0; px < w; px += ps) {
                        const bcx = px + ps / 2, bcy = py + ps / 2;
                        if ((x + bcx - cx) ** 2 + (y + bcy - cy) ** 2 > rSq) continue;
                        let rv = 0, gv = 0, bv = 0, n = 0;
                        const bh = Math.min(ps, h - py), bw = Math.min(ps, w - px);
                        for (let dy = 0; dy < bh; dy++) {
                            for (let dx2 = 0; dx2 < bw; dx2++) {
                                const i = ((py + dy) * w + (px + dx2)) * 4;
                                rv += d[i]; gv += d[i+1]; bv += d[i+2]; n++;
                            }
                        }
                        if (!n) continue;
                        rv = Math.round(rv/n); gv = Math.round(gv/n); bv = Math.round(bv/n);
                        for (let dy = 0; dy < bh; dy++) {
                            for (let dx2 = 0; dx2 < bw; dx2++) {
                                const i = ((py + dy) * w + (px + dx2)) * 4;
                                d[i] = rv; d[i+1] = gv; d[i+2] = bv; d[i+3] = 255;
                            }
                        }
                    }
                }
                ctx.putImageData(imgData, x, y);
            },

            blurUndo() {
                if (this.blurHistory.length <= 1) return;
                this.blurHistory.pop();
                this.blurCtx.putImageData(this.blurHistory[this.blurHistory.length - 1], 0, 0);
            },

            blurReset() {
                if (!this.blurHistory.length) return;
                this.blurHistory = [this.blurHistory[0]];
                this.blurCtx.putImageData(this.blurHistory[0], 0, 0);
            },

            blurSave() {
                const canvas = this.blurCanvas;
                canvas.toBlob((blob) => {
                    if (!blob) { this.blurOpen = false; return; }

                    if (this.blurExistingId !== null) {
                        // ── Existing photo: mark for deletion, add blurred version as new file ──
                        const mediaId = this.blurExistingId;
                        const wasCover = this.cover === `existing:${mediaId}`;
                        const newFile = new File([blob], `blurred_${mediaId}.jpg`, {type: 'image/jpeg', lastModified: Date.now()});
                        const id = crypto.randomUUID ? crypto.randomUUID() : (Date.now() + Math.random());
                        const preview = URL.createObjectURL(newFile);
                        const fileObj = { id, file: newFile, preview, size: newFile.size, name: newFile.name, blurred: true };

                        if (wasCover) {
                            this.files.unshift(fileObj);
                            this.cover = 'new:0';
                        } else {
                            this.files.push(fileObj);
                        }

                        // Trigger tile removal: enables delete_images[] input and hides the tile
                        const tile = this.$el.querySelector(`[data-existing-id="${mediaId}"]`);
                        if (tile) {
                            const deleteBtn = tile.querySelector('button[title="Supprimer cette photo"]');
                            if (deleteBtn) {
                                deleteBtn.click();
                            } else {
                                const d = tile._x_dataStack && tile._x_dataStack[0];
                                if (d) d.removed = true;
                                const inp = tile.querySelector('input[name="delete_images[]"]');
                                if (inp) inp.disabled = false;
                            }
                        }

                        this.syncInput();
                        this.persistFiles();

                        if (this.blurExistingObjectUrl && this.blurExistingObjectUrl.startsWith('blob:')) {
                            URL.revokeObjectURL(this.blurExistingObjectUrl);
                            this.blurExistingObjectUrl = null;
                        }
                    } else {
                        // ── New photo: replace in-place ──
                        const orig = this.files[this.blurIdx];
                        URL.revokeObjectURL(orig.preview);
                        const newFile = new File([blob], orig.name, {type: 'image/jpeg', lastModified: Date.now()});
                        this.files[this.blurIdx] = {
                            ...orig,
                            file: newFile,
                            preview: URL.createObjectURL(newFile),
                            size: newFile.size,
                            blurred: true,
                        };
                        this.syncInput();
                        this.persistFiles();
                    }

                    this.blurOpen = false;
                }, 'image/jpeg', 0.92);
            },
        };
    }
}
</script>