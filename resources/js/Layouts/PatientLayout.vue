<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import NotificationToast from '@/Components/NotificationToast.vue';

const page = usePage();
const toastRef = ref(null);
const latestNotification = ref(undefined);

// Polling for new notifications (identical logic to AuthenticatedLayout)
let pollInterval = null;

const checkNotifications = async () => {
    // Only poll if user is authenticated
    if (!page.props.auth.user) return;
    
    try {
        const response = await window.axios.get(route('notifications.unread-count'));
        const data = response.data;
        const currentCount = data.count;
        const prevCount = localStorage.getItem('last_notif_count_patient');
        
        if (prevCount !== null && currentCount > parseInt(prevCount)) {
            // Fetch the latest notification to show in toast
            const notifResponse = await window.axios.get(route('notifications.latest'));
            const notifData = notifResponse.data;
            if (notifData.notification) {
                latestNotification.value = notifData.notification;
                if (toastRef.value) {
                    toastRef.value.show();
                }
            }
        }
        localStorage.setItem('last_notif_count_patient', currentCount.toString());
    } catch (e) {
        // Silently fail or stop polling on 401
        if (e.response?.status === 401) {
            if (pollInterval) clearInterval(pollInterval);
        }
    }
};

onMounted(() => {
    const initialCount = page.props.auth.user?.unread_notifications ?? 0;
    localStorage.setItem('last_notif_count_patient', String(initialCount));

    setTimeout(checkNotifications, 2000);
    pollInterval = setInterval(checkNotifications, 5000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] dark:bg-[#0f1115] font-sans">
        <!-- Top Bar -->
        <div class="bg-[#1e293b] text-white py-2 px-6 hidden md:block">
            <div class="max-w-7xl mx-auto flex justify-between items-center text-[10px] font-black uppercase tracking-[0.2em]">
                <div class="flex gap-8">
                    <span class="flex items-center gap-2">
                        <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        Calle Menorca Pérez #75, Rincón Largo, Stgo RD
                    </span>
                    <span class="flex items-center gap-2">
                        <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        809-555-0100
                    </span>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="hover:text-indigo-400 transition">Cerrar sesión</Link>
            </div>
        </div>

        <!-- Main Nav -->
        <nav class="bg-white dark:bg-[#161920] border-b border-gray-100 dark:border-white/5 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 h-24 flex items-center justify-between gap-8">
                <Link :href="route('welcome')" class="flex items-center shrink-0">
                    <ApplicationLogo />
                </Link>

                <div class="hidden lg:flex items-center gap-10">
                    <Link :href="route('welcome')" class="text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-indigo-600 transition">Inicio</Link>
                    <Link :href="route('patient.search')" class="text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-indigo-600 transition">Médicos</Link>
                    <Link :href="route('patient.search')" class="text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-indigo-600 transition">Especialistas</Link>
                </div>

                <div class="flex items-center gap-6 shrink-0">
                    <NotificationDropdown 
                        v-if="$page.props.auth.user" 
                        :unread-count="page.props.auth.user?.unread_notifications" 
                    />
                    
                    <Link :href="route('patient.dashboard')" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-700 transition shadow-xl shadow-indigo-500/20 whitespace-nowrap">
                        Mi Tablero
                    </Link>
                </div>
            </div>
        </nav>


        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.message" class="max-w-7xl mx-auto px-6 mt-6">
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center gap-3">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                {{ $page.props.flash.message }}
            </div>
        </div>

        <!-- Full Width Header Slot (Banner) - Added overflow-x-hidden to prevent spillover -->
        <div class="overflow-x-hidden">
            <slot name="header" />
        </div>

        <!-- Main Content with Sidebar -->
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Persistent Sidebar -->
                <aside class="md:w-80 shrink-0">
                    <div class="bg-white dark:bg-[#161920] rounded-[2rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-white/5 overflow-hidden sticky top-28">
                        <!-- Profile Section -->
                        <div class="p-8 text-center border-b border-gray-50 dark:border-white/5">
                            <div class="w-24 h-24 rounded-[1.5rem] bg-indigo-50 dark:bg-indigo-900/20 mx-auto mb-4 flex items-center justify-center text-3xl font-black text-indigo-600 shadow-inner overflow-hidden border-4 border-white dark:border-gray-800">
                                <img v-if="$page.props.auth.user.profile_photo_url" :src="$page.props.auth.user.profile_photo_url" class="w-full h-full object-cover" />
                                <span v-else>{{ $page.props.auth.user.name ? $page.props.auth.user.name.charAt(0).toUpperCase() : 'U' }}</span>
                            </div>
                            <h2 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight mb-1">{{ $page.props.auth.user.name }}</h2>
                            <p class="text-[10px] font-bold text-gray-400 truncate">{{ $page.props.auth.user.email }}</p>
                        </div>

                        <!-- Menu -->
                        <nav class="p-4 space-y-1">
                            <Link :href="route('patient.dashboard')" :class="route().current('patient.dashboard') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="flex items-center gap-4 px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                Panel de Control
                            </Link>
                            <Link :href="route('patient.favorites.index')" :class="route().current('patient.favorites.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                Médicos favoritos
                            </Link>
                            <Link :href="route('patient.appointments.index')" :class="route().current('patient.appointments.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Horario de citas
                            </Link>
                            <Link :href="route('patient.reviews.index')" :class="route().current('patient.reviews.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.175 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                Reseña
                            </Link>
                            <Link :href="route('profile.edit')" :class="route().current('profile.edit') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5'" class="flex items-center gap-4 px-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Mi perfil
                            </Link>

                            <Link :href="route('profile.edit')" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5 font-black text-xs uppercase tracking-widest transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2h2m2 4l2-2m0 0l-2-2m2 2H8m10-6V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2"></path></svg>
                                Cambiar Contraseña
                            </Link>
                            
                            <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 font-black text-xs uppercase tracking-widest transition-all text-left">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Cerrar sesión
                            </Link>
                        </nav>
                    </div>
                </aside>

                <!-- Content -->
                <main class="flex-1">
                    <slot />
                </main>
            </div>
        <!-- Footer -->
        <footer class="bg-[#1e293b] dark:bg-[#0a0c10] pt-24 pb-12 overflow-hidden relative mt-auto">
            <div class="max-w-7xl mx-auto px-8 grid grid-cols-1 md:grid-cols-4 gap-12 relative z-10 border-b border-white/5 pb-20 mb-12 text-gray-400">
                <div class="md:col-span-1">
                     <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center mb-6">
                        <ApplicationLogo compact />
                     </div>
                     <p class="text-[11px] font-bold leading-relaxed opacity-60 uppercase tracking-tighter pr-8">Sistema de gestión de salud avanzada. Excelencia médica dominicana a tu alcance.</p>
                </div>
                <div>
                    <h4 class="text-xs font-black text-white uppercase tracking-widest mb-8">Acerca de</h4>
                    <ul class="space-y-4 text-[11px] font-black uppercase tracking-widest opacity-60">
                        <li><Link href="#" class="hover:text-indigo-400 transition">Sobre Nosotros</Link></li>
                        <li><Link href="#" class="hover:text-indigo-400 transition">Especialistas</Link></li>
                        <li><Link href="#" class="hover:text-indigo-400 transition">Política Privacidad</Link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-black text-white uppercase tracking-widest mb-8">Enlaces útiles</h4>
                    <ul class="space-y-4 text-[11px] font-black uppercase tracking-widest opacity-60">
                        <li><Link :href="route('patient.search')" class="hover:text-indigo-400 transition font-black">Buscar Médicos</Link></li>
                        <li><Link href="#" class="hover:text-indigo-400 transition font-black">Unirse como doctor</Link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-black text-white uppercase tracking-widest mb-8">Contacto</h4>
                    <div class="space-y-4 text-[11px] font-black uppercase tracking-widest opacity-60">
                        <p class="flex items-center gap-3">Santo Domingo, RD</p>
                        <p class="flex items-center gap-3">809-555-0100</p>
                        <p class="flex items-center gap-3">turnomedico@gmail.com</p>
                    </div>
                </div>
            </div>
            <div class="text-center text-[9px] font-black uppercase tracking-[0.5em] text-gray-600">
                Sistema de Gestión de Citas © 2024 Todos los derechos reservados
            </div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
        </footer>
        
        <!-- Real-time Notification Toast -->
        <NotificationToast ref="toastRef" :notification="latestNotification" />
    </div>
    </div>
</template>
