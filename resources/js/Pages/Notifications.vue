<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    notifications: Object
});

const markAsRead = (id) => {
    router.post(route('notifications.read', id));
};

const markAllAsRead = () => {
    router.post(route('notifications.read.all'));
};
</script>

<template>
    <Head title="Mis Notificaciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center px-4">
                <h2 class="font-black text-2xl text-gray-800 dark:text-gray-200 leading-tight tracking-tight">
                    Centro de Notificaciones
                </h2>
                <button
                    v-if="notifications.data.some(n => !n.read_at)"
                    @click="markAllAsRead"
                    class="text-xs font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 hover:underline"
                >
                    Marcar todo como leído
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <div v-for="notif in notifications.data" :key="notif.id" :class="{
                        'p-8 rounded-[2.5rem] border transition-all duration-300 shadow-sm': true,
                        'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700': notif.read_at,
                        'bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-900/30 ring-1 ring-indigo-100 dark:ring-indigo-900/50': !notif.read_at
                    }">
                        <div class="flex items-start gap-6">
                            <div class="w-14 h-14 rounded-2xl bg-white dark:bg-gray-700 shadow-sm flex items-center justify-center text-indigo-600">
                                <svg v-if="notif.data.type === 'appointment_booked'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <svg v-else-if="notif.data.type === 'appointment_status_updated'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg v-else class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-black text-lg text-gray-800 dark:text-gray-100 uppercase tracking-tight">
                                        {{ notif.data.title || 'Notificación del Sistema' }}
                                    </h4>
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-900 px-2 py-1 rounded-lg">
                                        {{ new Date(notif.created_at).toLocaleDateString() }}
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 leading-relaxed">{{ notif.data.message }}</p>
                                <div class="mt-6 flex gap-6 items-center">
                                    <button 
                                        v-if="!notif.read_at"
                                        @click="markAsRead(notif.id)"
                                        class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest hover:bg-indigo-50 dark:hover:bg-indigo-900/40 px-3 py-1.5 rounded-xl transition"
                                    >
                                        Marcar como leída
                                    </button>
                                    <Link :href="notif.data.action_url" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition flex items-center gap-2 group">
                                        Ver detalles
                                        <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="notifications.data.length === 0" class="text-center py-24 bg-white dark:bg-gray-800 rounded-[3rem] border border-dashed border-gray-200 dark:border-gray-700">
                        <svg class="w-16 h-16 text-gray-200 dark:text-gray-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <p class="text-gray-400 font-bold uppercase tracking-widest text-xs italic">Aún no tienes notificaciones.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
