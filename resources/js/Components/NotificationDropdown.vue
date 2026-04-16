<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
    unreadCount: Number
});

const notifications = ref([]);
const loading = ref(false);

const fetchRecent = async () => {
    loading.value = true;
    try {
        const response = await fetch(route('notifications.recent'));
        const data = await response.json();
        notifications.value = data.notifications;
    } catch (e) {
        console.error('Failed to fetch notifications', e);
    } finally {
        loading.value = false;
    }
};

const markAsRead = (id) => {
    router.post(route('notifications.read', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            notifications.value = notifications.value.map(n => 
                n.id === id ? { ...n, read_at: new Date() } : n
            );
        }
    });
};

onMounted(() => {
    // Register event listener for new notifications via poll if needed
});
</script>

<template>
    <Dropdown align="right" width="80" :close-on-click="true">
        <template #trigger>
            <button @click="fetchRecent" class="relative p-2 text-gray-400 hover:text-brand-teal dark:hover:text-brand-light transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span v-if="unreadCount > 0" class="absolute top-2 right-2 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-teal opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-teal"></span>
                </span>
            </button>
        </template>

        <template #content="{ close }">
            <div class="w-80 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden ring-1 ring-black/5 dark:ring-white/10">
                <!-- Header con botón cerrar -->
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                    <span class="text-xs font-black uppercase tracking-widest text-gray-800 dark:text-white">Notificaciones</span>
                    <div class="flex items-center gap-2">
                        <Link :href="route('notifications.index')" class="text-[10px] font-black text-brand-teal dark:text-brand-light uppercase tracking-widest hover:underline">Ver todo</Link>
                        <button type="button" @click="close" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-200 hover:text-gray-800 transition-colors" aria-label="Cerrar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
                    <div v-if="loading" class="p-8 text-center">
                        <div class="animate-spin w-5 h-5 border-2 border-brand-teal border-t-transparent rounded-full mx-auto"></div>
                    </div>

                    <template v-else-if="notifications.length > 0">
                        <Link v-for="notif in notifications" :key="notif.id" 
                             :href="notif.data.action_url || '#'"
                             @click="markAsRead(notif.id)"
                             class="group block relative px-4 py-4 border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all cursor-pointer"
                             :class="{ 'bg-brand-teal/10 dark:bg-brand-teal/20': !notif.read_at }">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-indigo-600 shrink-0 ring-1 ring-gray-100 dark:ring-gray-700">
                                    <svg v-if="notif.data.type === 'appointment_booked'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <svg v-else-if="notif.data.type === 'appointment_status_updated'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <p class="text-[11px] font-black text-gray-800 dark:text-white uppercase tracking-tight truncate pr-4">
                                            {{ notif.data.title || 'Sistema' }}
                                        </p>
                                        <div v-if="!notif.read_at" class="w-2 h-2 rounded-full bg-brand-teal flex-shrink-0"></div>
                                    </div>
                                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 line-clamp-2 mt-0.5 leading-tight">
                                        {{ notif.data.message }}
                                    </p>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="text-[8px] font-black text-gray-400 uppercase tracking-widest">{{ new Date(notif.created_at).toLocaleDateString() }}</span>
                                        <span class="text-[8px] font-black text-brand-teal uppercase tracking-widest group-hover:underline">Ver detalles</span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </template>

                    <div v-else class="p-12 text-center">
                        <svg class="w-10 h-10 text-gray-200 dark:text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Sin notificaciones</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 text-center border-t border-gray-100 dark:border-gray-700">
                    <Link :href="route('notifications.index')" class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest hover:text-brand-teal transition-colors">Centro de Notificaciones</Link>
                </div>
            </div>
        </template>
    </Dropdown>
</template>
