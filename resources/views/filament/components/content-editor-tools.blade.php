<div
    x-data="contentEditorTools({ interval: {{ $interval ?? 60000 }} })"
    class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
>
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span x-text="statusLabel()" class="font-medium"></span>
            <span x-show="status === 'saving'" class="h-2 w-2 animate-pulse rounded-full bg-amber-400"></span>
            <span x-show="status === 'error'" class="h-2 w-2 rounded-full bg-red-500"></span>
        </div>
        <div x-show="lastSavedAt" x-text="`Saved at ${new Date(lastSavedAt).toLocaleTimeString()}`"></div>
    </div>
    <div class="text-xs text-gray-500 dark:text-gray-400">Auto-save runs every {{ ($interval ?? 60000) / 1000 }} seconds and stores revisions for review.</div>
</div>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('contentEditorTools', ({ interval }) => ({
                status: 'idle',
                lastSavedAt: null,
                timer: null,
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
            }));
        });

        window.addEventListener('content-preview.open', (event) => {
            if (event.detail?.url) {
                window.open(event.detail.url, '_blank', 'noopener');
            }
        });
    </script>
@endonce
