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
    x-data="photoUploader({{ $maxNew }}, {{ $required ? 'true' : 'false' }}, @js($persistKey))"
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

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
            @foreach($existingMedia as $media)
            <div x-data="{ marked: false }" class="relative group">
                {{-- Hidden delete input (only submitted when marked) --}}
                <input type="hidden" name="delete_images[]" value="{{ $media->id }}" :disabled="!marked">

                {{-- Image tile --}}
                <div class="aspect-square rounded-xl overflow-hidden relative transition-all duration-200"
                     :class="marked ? 'ring-2 ring-red-400 ring-offset-1 opacity-40' : 'ring-1 ring-[#E0E6ED]'">
                    <img src="{{ $media->thumbnail_url ?? $media->url }}" alt=""
                         class="w-full h-full object-cover" loading="lazy">

                    @if($loop->first)
                    <span x-show="!marked" class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded-lg text-[9px] font-bold text-white backdrop-blur-sm"
                          style="background:rgba(23,162,184,0.85);">Principale</span>
                    @endif
                </div>

                {{-- Delete / Undo button --}}
                <button type="button" @click="marked = !marked"
                        class="absolute -top-2 -right-2 w-7 h-7 rounded-full flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-110 z-10"
                        :style="marked ? 'background:#27AE60; color:white;' : 'background:#E74C3C; color:white;'">
                    <svg x-show="!marked" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <svg x-show="marked" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </button>

                <p x-show="marked" class="text-[9px] text-center mt-1 font-semibold" style="color:#E74C3C;">A supprimer</p>
            </div>
            @endforeach
        </div>
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
                <p class="text-[11px] mt-1" style="color:#9BA8B7;">JPEG, PNG, WebP, HEIC · Max 15 Mo · {{ $hasExisting ? $maxNew : $max }} photos max</p>
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
                                x-show="supportsManagedFiles"
                                x-cloak
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
                    <span x-show="supportsManagedFiles">La première photo sera la photo principale</span>
                    <span x-show="!supportsManagedFiles" x-cloak>La première photo sera principale. Pour modifier la sélection, choisissez à nouveau toutes les photos.</span>
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
    @endif

</div>

{{-- ─── Alpine Component ─────────────────────────────────────────────── --}}
<script>
if (typeof photoUploader === 'undefined') {
    function photoUploader(maxFiles, isRequired = false, persistKey = null) {
        return {
            files: [],
            isDragging: false,
            errors: [],
            maxFiles: maxFiles,
            isRequired: isRequired,
            persistKey: persistKey,
            supportsManagedFiles: false,
            isProcessing: false,
            processedCount: 0,
            totalToProcess: 0,

            get slotsLeft() {
                return Math.max(0, this.maxFiles - this.files.length);
            },

            init() {
                this.$el._albaborPhotoUploader = this;
                this.supportsManagedFiles = this.detectManagedFileSupport() && !this.prefersNativeSelection();

                // Only validate the required state on submit.
                // Rewriting input.files during submit is brittle on mobile browsers.
                this.$nextTick(() => {
                    const form = this.$el.closest('form');
                    if (form) {
                        form.addEventListener('submit', (e) => {
                            const selectedCount = this.supportsManagedFiles
                                ? this.files.length
                                : (this.$refs.fileInput?.files?.length || 0);

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
                });

                // Clean up previews on page unload
                window.addEventListener('beforeunload', () => {
                    this.files.forEach(f => URL.revokeObjectURL(f.preview));
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

            // Compress image client-side before upload (max 2000px, JPEG 85%)
            compressImage(file) {
                return new Promise((resolve) => {
                    // Skip small files (< 1MB) — no need to compress
                    if (file.size < 1024 * 1024) { resolve(file); return; }

                    const img = new Image();
                    const url = URL.createObjectURL(file);
                    img.onload = () => {
                        URL.revokeObjectURL(url);
                        const MAX = 2000;
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
                        }, 'image/jpeg', 0.85);
                    };
                    img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
                    img.src = url;
                });
            },

            async addFiles(newFiles) {
                this.errors = [];
                const available = this.maxFiles - this.files.length;
                let toProcess = [];

                for (const file of newFiles) {
                    if (toProcess.length >= available) {
                        this.errors.push(`Limite de ${this.maxFiles} photo(s) atteinte.`);
                        break;
                    }
                    if (file.size > 15 * 1024 * 1024) {
                        this.errors.push(`"${file.name}" dépasse la limite de 15 Mo.`);
                        continue;
                    }
                    const allowed = ['image/jpeg','image/jpg','image/png','image/webp','image/heic','image/heif',''];
                    if (!allowed.includes(file.type)) {
                        this.errors.push(`"${file.name}" : format non supporté.`);
                        continue;
                    }
                    toProcess.push(file);
                }

                if (toProcess.length === 0) return;

                this.isProcessing = true;
                this.processedCount = 0;
                this.totalToProcess = toProcess.length;
                this.$dispatch('photos-processing');

                for (const file of toProcess) {
                    const optimized = await this.compressImage(file);
                    const id = crypto.randomUUID ? crypto.randomUUID() : (Date.now() + Math.random());
                    const preview = URL.createObjectURL(optimized);
                    this.files.push({ id, file: optimized, preview, size: optimized.size, name: file.name });
                    this.processedCount++;
                }
                this.isProcessing = false;
                this.$dispatch('photos-ready');
                this.syncInput();
                await this.persistFiles();
            },

            get progressPercent() {
                if (this.totalToProcess === 0) return 0;
                return Math.round((this.processedCount / this.totalToProcess) * 100);
            },

            removeFile(index) {
                if (!this.supportsManagedFiles) {
                    return;
                }
                URL.revokeObjectURL(this.files[index].preview);
                this.files.splice(index, 1);
                this.syncInput();
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

            replaceNativeSelection(selectedFiles) {
                this.errors = [];

                if (selectedFiles.length === 0) {
                    this.clearNativeSelection();
                    return;
                }

                if (selectedFiles.length > this.maxFiles) {
                    this.errors = [`Veuillez sélectionner au maximum ${this.maxFiles} photo(s) à la fois.`];
                    this.clearNativeSelection();
                    return;
                }

                const normalizedFiles = [];

                for (const file of selectedFiles) {
                    if (file.size > 15 * 1024 * 1024) {
                        this.errors.push(`"${file.name}" dépasse la limite de 15 Mo.`);
                    }

                    const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/heic', 'image/heif', ''];
                    if (!allowed.includes(file.type)) {
                        this.errors.push(`"${file.name}" : format non supporté.`);
                    }

                    normalizedFiles.push(file);
                }

                if (this.errors.length > 0) {
                    this.clearNativeSelection();
                    return;
                }

                this.revokePreviews();
                this.files = normalizedFiles.map(file => ({
                    id: crypto.randomUUID ? crypto.randomUUID() : (Date.now() + Math.random()),
                    file,
                    preview: URL.createObjectURL(file),
                    size: file.size,
                    name: file.name,
                }));
                this.persistFiles();
            },

            clearNativeSelection() {
                this.revokePreviews();
                this.files = [];
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
                this.clearPersistedFiles();
            },

            syncFilesFromInput() {
                if (!this.$refs.fileInput) {
                    return;
                }

                const selectedFiles = Array.from(this.$refs.fileInput.files || []);
                if (selectedFiles.length === this.files.length) {
                    return;
                }

                this.replaceNativeSelection(selectedFiles);
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
            }
        };
    }
}
</script>
