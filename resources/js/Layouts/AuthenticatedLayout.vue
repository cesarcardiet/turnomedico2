<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationToast from '@/Components/NotificationToast.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { initializeFCM } from '@/firebase';

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(true);
const toastRef = ref(null);
const latestNotification = ref<Record<string, any> | undefined>(undefined);

const page = usePage();
const rolesRaw = (page.props.auth.user as any)?.roles || [];
// roles como array de strings para includes() (por si el backend envía objetos)
const roles = computed(() => rolesRaw.map((r: any) => typeof r === 'string' ? r : (r?.name ?? r?.NAME ?? '')).filter(Boolean));
// Etiqueta legible para mostrar en sidebar/header (nunca mostrar objeto/JSON)
const roleLabel = computed(() => {
    const first = roles.value[0] || '';
    const labels: Record<string, string> = { doctor: 'Doctor', admin: 'Administrador', patient: 'Paciente' };
    return labels[first.toLowerCase()] || (first ? String(first).charAt(0).toUpperCase() + String(first).slice(1).toLowerCase() : '');
});

const pendingSubscriptionsCount = computed(() => {
    return (page.props.auth as any)?.pending_subscriptions_count || 0;
});

const isSubscribed = computed(() => (page.props.auth.user as any)?.is_subscribed ?? true);
const hasPendingSubscription = computed(() => (page.props.auth.user as any)?.has_pending_subscription ?? false);
const showRestrictionModal = ref(false);

// Recordatorio: activar notificaciones web (solo si el navegador lo soporta y no se ha cerrado)
const showNotificationReminder = ref(false);
const dismissNotificationReminder = () => {
    try { localStorage.setItem('turnomedico_notification_reminder_dismissed', '1'); } catch (_) {}
    showNotificationReminder.value = false;
};
const requestNotificationPermission = () => {
    if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
        Notification.requestPermission().then(() => dismissNotificationReminder());
    } else { dismissNotificationReminder(); }
};

// Cálculo robusto para saber si el perfil del doctor está incompleto
const isDoctorProfileIncomplete = computed(() => {
    const user: any = page.props.auth.user;
    if (!user) return false;
    const hasDoctorRole = roles.value.includes('doctor');
    if (!hasDoctorRole) return false;

    // Si el backend ya dice que está completo, no mostramos el banner
    if (user.is_profile_complete) return false;

    const profile: any = user.doctor_profile;
    if (!profile) return true;

    const aboutOk = !!(profile.about && String(profile.about).trim() !== '');
    const addressOk = !!(profile.clinic_address && String(profile.clinic_address).trim() !== '');
    const phoneOk = !!(profile.phone_number && String(profile.phone_number).trim() !== '');
    const specialityOk = !!(profile.speciality_id && Number(profile.speciality_id) > 0);

    return !(aboutOk && addressOk && phoneOk && specialityOk);
});

const missingFieldsLabels: Record<string, string> = {
    about: 'Sobre nosotros',
    clinic_address: 'Dirección de la clínica',
    phone_number: 'Teléfono',
    speciality: 'Especialidad',
};
const missingFieldsLabel = computed(() => {
    const missing = (page.props.auth.user as any)?.profile_missing_fields || [];
    if (missing.length) {
        return missing.map((key: string) => missingFieldsLabels[key] || key).join(', ');
    }
    return 'Sobre nosotros, Dirección de la clínica, Teléfono, Especialidad';
});

const doctorQueueMonitorUrl = computed(() => {
    const profile = (page.props.auth.user as any)?.doctor_profile;
    const id = profile?.id;
    return id ? route('patient.public.queue', id) : null;
});

let unregisterRouterHook: (() => void) | null = null;

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

// Polling for new notifications
let pollInterval: ReturnType<typeof setInterval> | null = null;

const checkNotifications = async () => {
    // Only poll if user is authenticated and we're not in the middle of a logout/login transition
    if (!page.props.auth.user) return;
    
    try {
        const response = await window.axios.get(route('notifications.unread-count'));
        const data = response.data;
        const currentCount = data.count;
        const prevCount = localStorage.getItem('last_notif_count');
        
        if (prevCount !== null && currentCount > parseInt(prevCount)) {
            const notifResponse = await window.axios.get(route('notifications.latest'));
            const notifData = notifResponse.data;
            const notif = notifData.notification;
            if (notif) {
                latestNotification.value = notif;
                if (toastRef.value) {
                    (toastRef.value as any).show();
                }
            }
        }
        localStorage.setItem('last_notif_count', currentCount.toString());
    } catch (e: any) {
        // Silently fail to avoid breaking the UI during transitions
        if (e.response?.status === 401) {
            if (pollInterval) clearInterval(pollInterval);
        }
    }
};

onMounted(() => {
    // Banner recordatorio notificaciones: mostrar si no está rechazado y el usuario no lo cerró
    try {
        if (page.props.auth.user && typeof Notification !== 'undefined' && Notification.permission === 'default' && !localStorage.getItem('turnomedico_notification_reminder_dismissed')) {
            showNotificationReminder.value = true;
        }
    } catch (_) {}

    // Sincronizar con el servidor en la primera comprobación (no solo con datos de la página)
    const initialCount = (page.props.auth.user as any)?.unread_notifications ?? 0;
    localStorage.setItem('last_notif_count', String(initialCount));

    // Primera comprobación a los 2 s para captar notificaciones recientes (ej. cita recién creada)
    setTimeout(checkNotifications, 2000);
    // Polling cada 5 segundos para notificaciones en tiempo casi real (funciona en localhost)
    pollInterval = setInterval(checkNotifications, 5000);
    
    // Initialize Firebase Cloud Messaging
    initializeFCM();

    // Global navigation guard for restricted doctors
    unregisterRouterHook = router.on('before', (event) => {
        const url = event.detail.visit.url.pathname;
        const isDoctorPath = url.startsWith('/doctor');
        const isMembershipPath = url.startsWith('/doctor/membership');
        const isLogout = url === '/logout';
        
        if (roles.value.includes('doctor') && !isSubscribed.value && isDoctorPath && !isMembershipPath && !isLogout) {
            event.preventDefault();
            showRestrictionModal.value = true;
        }
    });
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (unregisterRouterHook) unregisterRouterHook();
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="hidden lg:flex flex-col fixed inset-y-0 left-0 z-50 bg-white dark:bg-gray-800 border-r border-gray-100 dark:border-gray-700 transition-all duration-300 ease-in-out shadow-xl"
        >
            <div class="flex items-center justify-between h-20 px-6 border-b border-gray-50 dark:border-gray-700/50">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <ApplicationLogo compact />
                </Link>
            </div>

            <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto custom-scrollbar">
                <!-- Admin Links -->
                <template v-if="roles.includes('admin')">
                    <div v-if="sidebarOpen" class="px-3 mb-4 text-xs font-black text-gray-400 uppercase tracking-widest">Menú Principal</div>
                    <Link :href="route('admin.dashboard')" :class="route().current('admin.dashboard') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span v-if="sidebarOpen">Panel de Control</span>
                    </Link>
                    <Link :href="route('admin.doctors.index')" :class="route().current('admin.doctors.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span v-if="sidebarOpen">Gestionar Médicos</span>
                    </Link>
                    <Link :href="route('admin.subscriptions.index')" :class="route().current('admin.subscriptions.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center justify-between px-3 py-3 rounded-2xl font-bold transition-all group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span v-if="sidebarOpen">Suscripciones</span>
                        </div>
                        <span v-if="sidebarOpen && pendingSubscriptionsCount > 0" class="px-2 py-0.5 bg-rose-500 text-white text-[9px] rounded-lg animate-pulse">
                            {{ pendingSubscriptionsCount }}
                        </span>
                    </Link>
                    <Link :href="route('admin.specialities.index')" :class="route().current('admin.specialities.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.691.34a6 6 0 01-3.86.517l-2.387-.477a2 2 0 00-1.022.547l-1.162 1.163a1 1 0 001.414 1.414l1.163-1.163 2.387.477a4 4 0 002.573-.344l.69-.34a4 4 0 002.574-.344l2.387.477 1.162 1.163a1 1 0 001.414-1.414l-1.162-1.163z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11l-3 3m0 0l-3-3m3 3V8M7 5h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2z"></path></svg>
                        <span v-if="sidebarOpen">Especialidades</span>
                    </Link>
                    <Link :href="route('admin.plans.index')" :class="route().current('admin.plans.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                        <span v-if="sidebarOpen">Planes de Membresía</span>
                    </Link>
                    <Link :href="route('admin.cities.index')" :class="route().current('admin.cities.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11l-3 3m0 0l-3-3m3 3V8"></path></svg>
                        <span v-if="sidebarOpen">Ciudades</span>
                    </Link>
                    <Link :href="route('admin.broadcast.index')" :class="route().current('admin.broadcast.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.167H3.3a1.598 1.598 0 01-1.6-1.6V7.7a1.598 1.598 0 011.6-1.6h2.136l2.147-6.167A1.756 1.756 0 0111 5.882z"></path></svg>
                        <span v-if="sidebarOpen">Difusión</span>
                    </Link>
                    <Link :href="route('admin.settings.index')" :class="route().current('admin.settings.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span v-if="sidebarOpen">Configuración</span>
                    </Link>
                </template>

                <!-- Doctor Links -->
                <template v-if="roles.includes('doctor')">
                    <div v-if="sidebarOpen" class="px-3 mb-4 text-xs font-black text-gray-400 uppercase tracking-widest">Consultorio</div>
                    <Link :href="route('doctor.dashboard')" :class="route().current('doctor.dashboard') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span v-if="sidebarOpen">Panel de Control</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('doctor.appointments.index')" :class="route().current('doctor.appointments.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span v-if="sidebarOpen">Citas</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('doctor.payments.index')" :class="route().current('doctor.payments.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <span v-if="sidebarOpen">Aprobar Pagos</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('doctor.schedule.index')" :class="route().current('doctor.schedule.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span v-if="sidebarOpen">Horario de citas</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <a v-if="doctorQueueMonitorUrl" :href="doctorQueueMonitorUrl" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:hover:text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span v-if="sidebarOpen">Pantalla de turnos (TV)</span>
                    </a>
                    <Link :href="route('doctor.reviews.index')" :class="route().current('doctor.reviews.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.381-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        <span v-if="sidebarOpen">Reseñas</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('doctor.holidays.index')" :class="route().current('doctor.holidays.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span v-if="sidebarOpen">Mis vacaciones</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('doctor.profile.edit')" :class="route().current('doctor.profile.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span v-if="sidebarOpen">Mi perfil</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('doctor.earnings.index')" :class="route().current('doctor.earnings.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span v-if="sidebarOpen">Informes de ganancias</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('doctor.membership.index')" :class="route().current('doctor.membership.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all border-t border-gray-100 dark:border-white/5 pt-6 mt-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                        <span v-if="sidebarOpen">Mi suscripción</span>
                    </Link>
                    <Link :href="route('doctor.payments.history')" :class="route().current('doctor.payments.history') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        <span v-if="sidebarOpen">Historial de pagos</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('doctor.bank-details.index')" :class="route().current('doctor.bank-details.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <span v-if="sidebarOpen">Datos Bancarios</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('doctor.password.index')" :class="route().current('doctor.password.*') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light font-black' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all relative group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2h2m2 4l2-2m0 0l-2-2m2 2H8m10-6V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2"></path></svg>
                        <span v-if="sidebarOpen">Cambiar Contraseña</span>
                        <svg v-if="!isSubscribed" class="w-3.5 h-3.5 absolute right-3 text-amber-500 opacity-50 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-3 px-3 py-3 text-gray-500 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/10 dark:hover:text-rose-400 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span v-if="sidebarOpen">Cerrar sesión</span>
                    </Link>
                </template>

                <!-- Patient Links -->
                <template v-if="roles.includes('patient')">
                    <div v-if="sidebarOpen" class="px-3 mb-4 text-xs font-black text-gray-400 uppercase tracking-widest">Actividad</div>
                    <Link :href="route('patient.dashboard')" :class="route().current('patient.dashboard') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span v-if="sidebarOpen">Mis Citas</span>
                    </Link>
                    <Link :href="route('patient.search')" :class="route().current('patient.search') ? 'bg-brand-teal/10 text-brand-teal dark:bg-brand-teal/20 dark:text-brand-light' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 dark:text-gray-400'" class="flex items-center gap-3 px-3 py-3 rounded-2xl font-bold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span v-if="sidebarOpen">Buscar Médico</span>
                    </Link>
                </template>
            </nav>

            <div class="p-4 border-t border-gray-50 dark:border-gray-700/50">
                <button 
                    @click="toggleSidebar"
                    class="w-full flex items-center justify-center py-2 bg-gray-50 dark:bg-gray-700/30 rounded-xl text-gray-400 hover:text-brand-teal dark:hover:text-brand-light transition-colors"
                >
                    <svg v-if="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                </button>
            </div>

            <!-- User Profile at Bottom of Sidebar -->
                        <div v-if="sidebarOpen && ($page.props.auth.user as any)" class="p-4 border-t border-gray-50 dark:border-gray-700/50">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-gray-700/30">
                    <div class="w-10 h-10 rounded-xl bg-brand-teal/10 dark:bg-brand-teal/20 flex items-center justify-center text-brand-teal dark:text-brand-light font-black overflow-hidden ring-2 ring-white dark:ring-gray-800 shrink-0">
                        <img v-if="($page.props.auth.user as any).profile_photo_url" :src="($page.props.auth.user as any).profile_photo_url" class="w-full h-full object-cover" />
                        <span v-else>{{ ($page.props.auth.user as any).name.charAt(0) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-black text-gray-800 dark:text-gray-200 truncate">{{ $page.props.auth.user.name }}</div>
                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest truncate">{{ roleLabel }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-20'" class="transition-all duration-300">
            <!-- Header -->
            <header class="sticky top-0 z-40 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-700">
                <div class="h-20 flex items-center justify-between px-4 sm:px-8">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white lg:hidden">DOCAPP</h2>
                    <div class="flex-1 lg:flex-none"></div>

                    <div class="flex items-center gap-2 sm:gap-4">
                        <!-- Notification Bell (Dropdown) -->
                        <NotificationDropdown 
                            v-if="$page.props.auth.user" 
                            :unread-count="(page.props.auth.user as any)?.unread_notifications" 
                        />

                        <div v-if="$page.props.auth.user" class="h-8 w-[1px] bg-gray-100 dark:bg-gray-700 hidden sm:block"></div>

                        <!-- User Dropdown -->
                        <Dropdown v-if="$page.props.auth.user" align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-3 p-1 rounded-full hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
                                    <div class="w-10 h-10 rounded-full bg-brand-teal/10 dark:bg-brand-teal/20 flex items-center justify-center text-brand-teal dark:text-brand-light font-black overflow-hidden ring-2 ring-white dark:ring-gray-800">
                                        <img v-if="($page.props.auth.user as any).profile_photo_url" :src="($page.props.auth.user as any).profile_photo_url" class="w-full h-full object-cover" />
                                        <span v-else>{{ $page.props.auth.user?.name?.charAt(0) }}</span>
                                    </div>
                                    <div class="hidden sm:block text-left">
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $page.props.auth.user?.name }}</div>
                                        <div class="text-[10px] font-black uppercase tracking-widest text-gray-400">{{ roleLabel }}</div>
                                    </div>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Configuración</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Cerrar Sesión</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Inner Content -->
            <main class="p-6 sm:p-8 animate-in fade-in duration-700">
                <!-- Recordatorio: activar notificaciones web para avisos de turnos -->
                <div v-if="showNotificationReminder" class="mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-500/30 rounded-2xl flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm font-bold text-indigo-800 dark:text-indigo-200">
                        Activa las notificaciones del navegador para recibir avisos de tus turnos y citas.
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="requestNotificationPermission" class="px-4 py-2 bg-indigo-600 text-white text-xs font-black rounded-xl hover:bg-indigo-700 transition uppercase tracking-wider">
                            Activar
                        </button>
                        <button type="button" @click="dismissNotificationReminder" class="p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-800/50 rounded-lg transition" aria-label="Cerrar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Profile Completion Warning -->
                <div v-if="isDoctorProfileIncomplete" class="mb-8 p-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-500/30 rounded-2xl flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 text-amber-800 dark:text-amber-200">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div>
                            <p class="text-sm font-bold">Tu perfil está incompleto. Completa tu información profesional para aparecer en el sistema de búsqueda.</p>
                            <p class="text-sm font-bold mt-2 text-amber-700 dark:text-amber-300">Completa estos campos: {{ missingFieldsLabel }}</p>
                        </div>
                    </div>
                    <Link :href="route('doctor.profile.edit')" class="px-4 py-2 bg-amber-600 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-amber-700 transition-colors shrink-0">Completar Perfil</Link>
                </div>

                <header v-if="$slots.header" class="mb-8">
                    <slot name="header" />
                </header>

                <slot />
            </main>
        </div>

        <!-- Real-time Notification Toast -->
        <NotificationToast ref="toastRef" :notification="latestNotification" />

        <!-- Restricted Navigation Modal -->
        <div v-if="showRestrictionModal" class="fixed inset-0 z-[200] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-md animate-in fade-in duration-300">
            <div class="relative bg-white dark:bg-[#161920] w-full max-w-md rounded-[2.5rem] shadow-2xl border border-white/5 overflow-hidden animate-in zoom-in-95 duration-300">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-amber-400 via-orange-500 to-amber-600"></div>
                
                <div class="p-10 text-center">
                    <div class="w-20 h-20 bg-amber-500/10 rounded-[2rem] flex items-center justify-center text-amber-500 mx-auto mb-8 shadow-xl shadow-amber-500/5 ring-1 ring-amber-500/20">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-4">Acceso Limitado</h2>
                    
                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400 leading-relaxed mb-8">
                        Tu cuenta aún no ha sido aceptada o tu suscripción no está activa. Debes tener una suscripción vigente para acceder a todas las funciones del consultorio.
                    </p>

                    <div class="space-y-4">
                        <Link 
                            :href="route('doctor.membership.index')" 
                            @click="showRestrictionModal = false"
                            class="block w-full py-4 bg-brand-teal text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-brand-teal/30 hover:bg-brand-dark transition active:scale-95"
                        >
                            Ver Mi Suscripción
                        </Link>
                        <button 
                            @click="showRestrictionModal = false"
                            class="block w-full py-4 bg-gray-50 dark:bg-white/5 text-gray-400 dark:hover:text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition hover:bg-gray-100"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
