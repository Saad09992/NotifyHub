@php
    $initialToasts = [];
    if (session('success')) {
        $initialToasts[] = ['type' => 'success', 'msg' => session('success')];
    }
    if (session('error')) {
        $initialToasts[] = ['type' => 'error', 'msg' => session('error')];
    }
    foreach ($errors->all() as $err) {
        $initialToasts[] = ['type' => 'error', 'msg' => $err];
    }
@endphp

<div
    x-data="{
        nextId: 1,
        toasts: [],
        push(type, msg) {
            const id = this.nextId++;
            this.toasts.push({ id, type, msg });
            setTimeout(() => this.remove(id), 4500);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
    }"
    x-init="
        @foreach ($initialToasts as $t)
            push(@js($t['type']), @js($t['msg']));
        @endforeach
        window.addEventListener('toast', e => push(e.detail.type || 'success', e.detail.msg));
    "
    class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-80 pointer-events-none"
    role="status"
    aria-live="polite"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="t.type === 'success'
                ? 'bg-emerald-600 text-white border-emerald-700'
                : t.type === 'error'
                    ? 'bg-rose-600 text-white border-rose-700'
                    : 'bg-gray-800 text-white border-gray-900'"
            class="pointer-events-auto rounded-md shadow-lg border px-4 py-3 flex items-start gap-3"
        >
            <svg x-show="t.type === 'success'" class="w-5 h-5 flex-none mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg x-show="t.type === 'error'" class="w-5 h-5 flex-none mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="flex-1 text-sm leading-snug" x-text="t.msg"></span>
            <button @click="remove(t.id)" type="button" class="opacity-80 hover:opacity-100 text-lg leading-none" aria-label="Dismiss">&times;</button>
        </div>
    </template>
</div>
