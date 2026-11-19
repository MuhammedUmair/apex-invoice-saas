<div x-data="{
    notifications: [],
    add(message, type = 'info', title = '') {
        if (!message) return;
        const id = Date.now() + Math.random().toString(36).substring(2, 5);
        this.notifications.push({ id, message, type, title });
        setTimeout(() => this.remove(id), 5500);
    },
    remove(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    }
}"
@notify.window="add($event.detail.message || $event.detail, $event.detail.type || 'info', $event.detail.title || '')"
x-init="
    @if(session('success')) add(@js(session('success')), 'success', 'Success'); @endif
    @if(session('message')) add(@js(session('message')), 'info', 'Notice'); @endif
    @if(session('error')) add(@js(session('error')), 'error', 'Error'); @endif
    @if(session('status')) add(@js(session('status')), 'info', 'Status'); @endif
"
class="fixed top-5 right-5 z-[99999] flex flex-col gap-2.5 max-w-sm sm:max-w-md w-full pointer-events-none p-4 sm:p-0"
dir="ltr"
style="direction: ltr !important; position: fixed !important; top: 20px !important; right: 20px !important; left: auto !important; z-index: 99999;">
    <template x-for="item in notifications" :key="item.id">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-[-16px] scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-[-12px] scale-95"
             class="pointer-events-auto flex items-start gap-3 p-4 rounded-2xl shadow-xl border backdrop-blur-md transition"
             :class="{
                 'bg-emerald-50/95 border-emerald-200 text-emerald-900 shadow-emerald-500/10': item.type === 'success',
                 'bg-rose-50/95 border-rose-200 text-rose-900 shadow-rose-500/10': item.type === 'error',
                 'bg-amber-50/95 border-amber-200 text-amber-900 shadow-amber-500/10': item.type === 'warning',
                 'bg-sky-50/95 border-sky-200 text-sky-900 shadow-sky-500/10': item.type === 'info'
             }">
            <div class="shrink-0 mt-0.5">
                <template x-if="item.type === 'success'">
                    <i class="ph-fill ph-check-circle text-xl text-emerald-600"></i>
                </template>
                <template x-if="item.type === 'error'">
                    <i class="ph-fill ph-warning-circle text-xl text-rose-600"></i>
                </template>
                <template x-if="item.type === 'warning'">
                    <i class="ph-fill ph-warning text-xl text-amber-600"></i>
                </template>
                <template x-if="item.type === 'info'">
                    <i class="ph-fill ph-info text-xl text-sky-600"></i>
                </template>
            </div>
            <div class="flex-1 min-w-0">
                <template x-if="item.title">
                    <h5 class="text-xs font-bold uppercase tracking-wider mb-0.5 opacity-80" x-text="item.title"></h5>
                </template>
                <p class="text-xs font-semibold leading-relaxed break-words" dir="auto" x-text="item.message"></p>
            </div>
            <button @click="remove(item.id)" type="button" 
                    class="shrink-0 text-slate-400 hover:text-slate-700 transition p-1 -mr-1 -mt-1 rounded-lg hover:bg-black/5"
                    title="Dismiss">
                <i class="ph ph-x text-sm"></i>
            </button>
        </div>
    </template>
</div>
