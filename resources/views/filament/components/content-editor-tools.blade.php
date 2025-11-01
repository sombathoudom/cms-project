@php
    $recordResolver = $getRecord ?? null;
    $recordModel = is_callable($recordResolver) ? $recordResolver() : null;
    $recordKey = $recordModel?->getKey();
@endphp

<div
    x-data="contentEditorTools({ interval: {{ $interval ?? 60000 }}, contentId: @js($recordKey) })"
    class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span x-text="statusLabel()" class="font-medium"></span>
            <span x-show="status === 'saving'" class="h-2 w-2 animate-pulse rounded-full bg-amber-400"></span>
            <span x-show="status === 'error'" class="h-2 w-2 rounded-full bg-red-500"></span>
        </div>
        <div class="flex items-center gap-3">
            <div x-show="lastSavedAt" x-text="`Saved at ${new Date(lastSavedAt).toLocaleTimeString()}`"></div>
            <button
                type="button"
                class="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700"
                @click="openMediaPicker"
                :disabled="!contentId"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path d="M4 3a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H4Zm3 3a1 1 0 0 1 1-1h4a1 1 0 0 1 .78.375l2.5 3a1 1 0 0 1 .22.625V13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6h3Zm4 1H9.414l2.293 2.293a1 1 0 0 0 1.414-1.414L11 7Zm-5 5a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                </svg>
                <span>Insert media</span>
            </button>
        </div>
    </div>
    <div class="text-xs text-gray-500 dark:text-gray-400">Auto-save runs every {{ ($interval ?? 60000) / 1000 }} seconds and stores revisions for review.</div>

    <template x-if="!contentId">
        <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-700 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-200">
            Save your draft at least once before embedding media so uploads can be linked to this content.
        </div>
    </template>

    <div
        x-cloak
        x-show="pickerOpen"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @keydown.escape.window="closeMediaPicker"
    >
        <div class="w-full max-w-4xl overflow-hidden rounded-lg bg-white shadow-xl dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Media library</h2>
                <button type="button" class="rounded-full p-1 text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800" @click="closeMediaPicker">
                    <span class="sr-only">Close</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5" />
                    </svg>
                </button>
            </div>
            <div class="grid gap-4 p-4 md:grid-cols-[2fr,1fr]">
                <div class="space-y-3">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div class="flex w-full items-center gap-2">
                            <input
                                type="search"
                                x-model.debounce.500ms="searchTerm"
                                @input="loadMedia"
                                placeholder="Search by name or type"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            />
                            <select
                                x-model="typeFilter"
                                @change="loadMedia"
                                class="rounded-md border border-gray-300 px-2 py-2 text-xs text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            >
                                <option value="">All types</option>
                                <option value="image/">Images</option>
                                <option value="video/">Videos</option>
                                <option value="application/">Documents</option>
                            </select>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            @click="$refs.uploadInput.click()"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path d="M5 3a2 2 0 0 0-2 2v3a1 1 0 1 0 2 0V5h10v3a1 1 0 1 0 2 0V5a2 2 0 0 0-2-2H5Z" />
                                <path d="M9 7a1 1 0 0 1 2 0v5.586l1.293-1.293a1 1 0 1 1 1.414 1.414l-3.001 3a1 1 0 0 1-1.414 0l-3-3a1 1 0 0 1 1.414-1.414L9 12.586V7Z" />
                            </svg>
                            Upload
                        </button>
                        <input type="file" x-ref="uploadInput" class="hidden" @change="uploadSelectedFile">
                    </div>
                    <div class="min-h-[16rem] rounded-md border border-dashed border-gray-200 p-3 dark:border-gray-700">
                        <template x-if="isLoading">
                            <div class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">Loading media…</div>
                        </template>
                        <template x-if="!isLoading && mediaItems.length === 0">
                            <div class="flex h-full items-center justify-center text-sm text-gray-500 dark:text-gray-400">No media assets found.</div>
                        </template>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3" x-show="!isLoading && mediaItems.length > 0">
                            <template x-for="item in mediaItems" :key="item.id">
                                <button
                                    type="button"
                                    @click="selectMedia(item)"
                                    :class="selectedMedia && selectedMedia.id === item.id ? 'ring-2 ring-indigo-500' : 'ring-1 ring-transparent'"
                                    class="flex flex-col overflow-hidden rounded-md border border-gray-200 text-left text-xs shadow-sm transition hover:border-indigo-400 hover:shadow dark:border-gray-700 dark:hover:border-indigo-500"
                                >
                                    <div class="aspect-video bg-gray-100 dark:bg-gray-800">
                                        <template x-if="item.mime_type.startsWith('image/')">
                                            <img :src="item.url" :alt="item.original_name" class="h-full w-full object-cover" loading="lazy" />
                                        </template>
                                        <template x-if="!item.mime_type.startsWith('image/')">
                                            <div class="flex h-full w-full items-center justify-center text-gray-500 dark:text-gray-300">
                                                <span x-text="item.mime_type"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex flex-col gap-1 p-2">
                                        <span class="truncate font-medium text-gray-900 dark:text-gray-100" x-text="item.original_name"></span>
                                        <span class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400" x-text="item.mime_type"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex h-full flex-col gap-3 rounded-md border border-gray-200 p-3 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Embed details</h3>
                    <template x-if="!selectedMedia">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Select or upload a media asset to configure embed options.</p>
                    </template>
                    <template x-if="selectedMedia">
                        <div class="flex flex-col gap-3 text-xs">
                            <div>
                                <span class="font-semibold text-gray-700 dark:text-gray-200" x-text="selectedMedia.original_name"></span>
                                <div class="text-gray-500 dark:text-gray-400" x-text="selectedMedia.mime_type"></div>
                            </div>
                            <label class="flex flex-col gap-1">
                                <span class="font-medium text-gray-700 dark:text-gray-200">Alt text</span>
                                <input type="text" x-model="altText" maxlength="255" class="rounded-md border border-gray-300 px-2 py-2 text-sm text-gray-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" placeholder="Describe the media for accessibility" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="font-medium text-gray-700 dark:text-gray-200">Display order</span>
                                <input type="number" min="0" max="1000" x-model.number="position" class="w-24 rounded-md border border-gray-300 px-2 py-2 text-sm text-gray-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                            </label>
                            <div class="mt-auto flex items-center justify-between">
                                <button type="button" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" @click="clearSelection">Clear</button>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-60"
                                    :disabled="isSubmitting"
                                    @click="insertSelected"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                        <path d="M10.75 4a.75.75 0 1 0-1.5 0v5.25H4a.75.75 0 0 0 0 1.5h5.25V16a.75.75 0 0 0 1.5 0v-5.25H16a.75.75 0 0 0 0-1.5h-5.25V4Z" />
                                    </svg>
                                    Insert media
                                </button>
                            </div>
                            <template x-if="errorMessage">
                                <p class="rounded-md border border-red-200 bg-red-50 p-2 text-xs text-red-600 dark:border-red-500/50 dark:bg-red-500/10 dark:text-red-200" x-text="errorMessage"></p>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('contentEditorTools', ({ interval, contentId }) => ({
                status: 'idle',
                lastSavedAt: null,
                timer: null,
                contentId,
                pickerOpen: false,
                mediaItems: [],
                selectedMedia: null,
                altText: '',
                position: 0,
                searchTerm: '',
                typeFilter: '',
                isLoading: false,
                isSubmitting: false,
                errorMessage: '',
                init() {
                    this.timer = setInterval(async () => {
                        this.status = 'saving';
                        try {
                            const result = await $wire.autoSaveDraft();
                            if (result && result.skipped) {
                                this.status = 'idle';

                                return;
                            }

                            this.status = 'saved';
                            this.lastSavedAt = result?.saved_at ?? new Date().toISOString();
                        } catch (error) {
                            console.error('Auto-save failed', error);
                            this.status = 'error';
                        }
                    }, interval);
                },
                destroy() {
                    if (this.timer) {
                        clearInterval(this.timer);
                    }
                },
                statusLabel() {
                    switch (this.status) {
                        case 'saving':
                            return 'Auto-saving…';
                        case 'saved':
                            return 'Changes saved';
                        case 'error':
                            return 'Auto-save failed';
                        default:
                            return 'Draft idle';
                    }
                },
                async openMediaPicker() {
                    if (! this.contentId) {
                        this.errorMessage = 'Save the record before embedding media.';

                        return;
                    }

                    this.pickerOpen = true;
                    await this.loadMedia();
                },
                closeMediaPicker() {
                    this.pickerOpen = false;
                    this.clearSelection();
                    this.errorMessage = '';
                },
                async loadMedia() {
                    this.isLoading = true;
                    this.errorMessage = '';

                    const params = new URLSearchParams();

                    if (this.searchTerm) {
                        params.set('search', this.searchTerm);
                    }

                    if (this.typeFilter) {
                        params.set('type', this.typeFilter);
                    }

                    const response = await fetch(`/api/v1/admin/media?${params.toString()}`, {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                        },
                    });

                    const payload = await response.json();

                    if (! response.ok) {
                        this.errorMessage = payload?.error?.message ?? 'Unable to load media assets.';
                        this.mediaItems = [];
                        this.isLoading = false;

                        return;
                    }

                    this.mediaItems = payload?.data ?? [];
                    this.isLoading = false;
                },
                selectMedia(item) {
                    this.selectedMedia = item;
                    this.altText = item.alt_text ?? '';
                    this.position = 0;
                    this.errorMessage = '';
                },
                clearSelection() {
                    this.selectedMedia = null;
                    this.altText = '';
                    this.position = 0;
                },
                async uploadSelectedFile(event) {
                    if (! event.target.files || event.target.files.length === 0) {
                        return;
                    }

                    const file = event.target.files[0];
                    const formData = new FormData();
                    formData.append('file', file);

                    this.isLoading = true;
                    this.errorMessage = '';

                    const response = await fetch('/api/v1/admin/media', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                        },
                    });

                    const payload = await response.json();

                    if (! response.ok) {
                        this.errorMessage = payload?.error?.message ?? 'Upload failed. Please check file type and size.';
                        this.isLoading = false;

                        return;
                    }

                    if (payload?.data) {
                        this.mediaItems.unshift(payload.data);
                        this.selectMedia(payload.data);
                    }

                    this.isLoading = false;
                    event.target.value = '';
                },
                async insertSelected() {
                    if (! this.selectedMedia || ! this.contentId) {
                        return;
                    }

                    this.isSubmitting = true;
                    this.errorMessage = '';

                    const response = await fetch(`/api/v1/admin/content/${this.contentId}/media`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                            Accept: 'application/json',
                        },
                        body: JSON.stringify({
                            media_id: this.selectedMedia.id,
                            alt_text: this.altText,
                            position: this.position,
                        }),
                    });

                    const payload = await response.json();

                    if (! response.ok) {
                        this.errorMessage = payload?.error?.message ?? 'Unable to embed media.';
                        this.isSubmitting = false;

                        return;
                    }

                    if (payload?.data?.markup) {
                        window.Livewire.dispatch('content-editor-insert-media', { markup: payload.data.markup });
                    }

                    this.isSubmitting = false;
                    this.closeMediaPicker();
                },
            }));
        });

        window.addEventListener('content-preview.open', (event) => {
            if (event.detail?.url) {
                window.open(event.detail.url, '_blank', 'noopener');
            }
        });
    </script>
@endonce
