<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    notification: {
        type: Object,
        default: null
    }
});

const visible = ref(false);
const audio = ref(null);

onMounted(() => {
    audio.value = new Audio('/sounds/notification.mp3');
});

function dismiss() {
    visible.value = false;
    const id = props.notification?.id;
    if (id && typeof window.axios !== 'undefined') {
        window.axios.post(route('notifications.read', id), {}, { headers: { 'Accept': 'application/json' } }).catch(() => {});
    }
}

const show = () => {
    visible.value = true;
    if (audio.value) {
        audio.value.currentTime = 0;
        audio.value.play().catch(() => {});
    }
    setTimeout(dismiss, 8000);
};

defineExpose({ show });
</script>

<template>
    <div v-if="visible" class="fixed bottom-6 right-6 z-[100] max-w-sm w-full animate-in slide-in-from-right-full duration-500">
        <div class="bg-gray-900/95 backdrop-blur-xl border border-white/10 rounded-[2rem] p-6 shadow-2xl flex items-start gap-4 ring-1 ring-white/5">
            <!-- Icon -->
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0 shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </div>

            <!-- Content (Laravel guarda title/message en notification.data) -->
            <div class="flex-1">
                <h4 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-1">{{ (notification?.data?.title || notification?.title) || 'Nueva Notificación' }}</h4>
                <p class="text-[11px] font-bold text-gray-300 leading-tight mb-4 tracking-tight">{{ notification?.data?.message || notification?.message || '' }}</p>
                
                <div class="flex items-center gap-4">
                    <Link :href="(notification?.data?.action_url || notification?.action_url) || route('notifications.index')" @click="dismiss" class="text-[9px] font-black text-white uppercase tracking-widest bg-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-900/40">Ver ahora</Link>
                    <button @click="dismiss" class="text-[9px] font-black text-gray-500 uppercase tracking-widest hover:text-white transition">Cerrar</button>
                </div>
            </div>

            <!-- Close button -->
            <button @click="dismiss" class="text-gray-600 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
</template>
