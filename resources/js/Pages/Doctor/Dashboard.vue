<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: Object,
    appointments: Array
});

const filter = ref('hoy');

const filteredAppointments = computed(() => {
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    if (filter.value === 'hoy') {
        return props.appointments.filter(a => a.time_slot.date === today);
    } else if (filter.value === 'pasado') {
        return props.appointments.filter(a => a.time_slot.date < today);
    } else if (filter.value === 'proximos') {
        return props.appointments.filter(a => a.time_slot.date > today);
    }
    return props.appointments;
});

const getStatusClass = (status) => {
    switch (status) {
        case 'pending': return 'bg-amber-500/10 text-amber-500 border-amber-500/20';
        case 'accepted': return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
        case 'rejected': return 'bg-rose-500/10 text-rose-500 border-rose-500/20';
        case 'completed': return 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20';
        case 'in_consultation': return 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-500/50 animate-pulse';
        default: return 'bg-gray-500/10 text-gray-500 border-gray-500/20';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Pendiente';
        case 'accepted': return 'Aceptada';
        case 'rejected': return 'Rechazada';
        case 'completed': return 'Completada';
        case 'in_consultation': return 'En Consulta';
        default: return status;
    }
};
</script>

<template>
    <Head title="Tablero del Doctor" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Tablero del doctor</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6">
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <!-- Total Citas -->
                    <div class="bg-emerald-50/50 dark:bg-emerald-500/5 rounded-[2.5rem] p-8 border border-emerald-100 dark:border-emerald-500/10 flex items-center gap-6 group hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center shadow-xl shadow-emerald-900/10 group-hover:rotate-6 transition">
                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[32px] font-black text-gray-900 dark:text-white leading-none">{{ stats.total_appointments }}</p>
                            <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mt-1">Total de citas</p>
                        </div>
                    </div>

                    <!-- Total Reseñas -->
                    <div class="bg-rose-50/50 dark:bg-rose-500/5 rounded-[2.5rem] p-8 border border-rose-100 dark:border-rose-500/10 flex items-center gap-6 group hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center shadow-xl shadow-rose-900/10 group-hover:rotate-6 transition">
                            <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[32px] font-black text-gray-900 dark:text-white leading-none">{{ stats.total_reviews }}</p>
                            <p class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest mt-1">Total de reseñas</p>
                        </div>
                    </div>

                    <!-- Nuevas Citas -->
                    <div class="bg-indigo-50/50 dark:bg-indigo-500/5 rounded-[2.5rem] p-8 border border-indigo-100 dark:border-indigo-500/10 flex items-center gap-6 group hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-900/10 group-hover:rotate-6 transition">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[32px] font-black text-gray-900 dark:text-white leading-none">{{ stats.new_appointments }}</p>
                            <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mt-1">Nuevas citas</p>
                        </div>
                    </div>
                    
                    <!-- Turn Monitor (TV View) -->
                    <a v-if="$page.props.auth.user.doctor_profile?.id" :href="route('patient.public.queue', $page.props.auth.user.doctor_profile.id)" target="_blank" class="bg-violet-50/50 dark:bg-violet-500/5 rounded-[2.5rem] p-8 border border-violet-100 dark:border-violet-500/10 flex items-center gap-6 group hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-violet-500/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center shadow-xl shadow-violet-900/10 group-hover:rotate-6 transition">
                            <svg class="w-8 h-8 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[14px] font-black text-gray-900 dark:text-white leading-tight">Monitor de TV</p>
                            <p class="text-[10px] font-black text-violet-600 dark:text-violet-400 uppercase tracking-widest mt-1">Abrir pantalla</p>
                        </div>
                    </a>
                    
                    <!-- Aprobar Pagos -->
                    <Link :href="route('doctor.payments.index')" class="bg-amber-50/50 dark:bg-amber-500/5 rounded-[2.5rem] p-8 border border-amber-100 dark:border-amber-500/10 flex items-center gap-6 group hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center shadow-xl shadow-amber-900/10 group-hover:rotate-6 transition">
                            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[32px] font-black text-gray-900 dark:text-white leading-none">Verificar</p>
                            <p class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest mt-1">Aprobar Pagos</p>
                        </div>
                    </Link>
                </div>

                <!-- Appointments Table -->
                <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden">
                    <div class="p-10 border-b border-white/5 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Citas de pacientes</h3>
                        
                        <div class="flex bg-gray-100 dark:bg-[#1c2128] p-1.5 rounded-2xl">
                            <button 
                                @click="filter = 'pasado'"
                                :class="filter === 'pasado' ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-lg' : 'text-gray-500'"
                                class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                            >
                                Pasado
                            </button>
                            <button 
                                @click="filter = 'hoy'"
                                :class="filter === 'hoy' ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-lg' : 'text-gray-500'"
                                class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                            >
                                Hoy
                            </button>
                            <button 
                                @click="filter = 'proximos'"
                                :class="filter === 'proximos' ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-lg' : 'text-gray-500'"
                                class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                            >
                                Próximos
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-white/5">
                                    <th class="px-10 py-8">Nombre del Paciente</th>
                                    <th class="px-6 py-8">Turno #</th>
                                    <th class="px-6 py-8">Fecha</th>
                                    <th class="px-6 py-8">Teléfono</th>
                                    <th class="px-6 py-8">Comprobante</th>
                                    <th class="px-6 py-8">Estado</th>
                                    <th class="px-10 py-8 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="appointment in filteredAppointments" :key="appointment.id" class="group hover:bg-white/[0.02] transition-colors">
                                    <td class="px-10 py-8">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 font-black overflow-hidden ring-1 ring-white/10 shadow-inner">
                                                <img v-if="appointment.user.profile_photo_url" :src="appointment.user.profile_photo_url" class="w-full h-full object-cover" />
                                                <span v-else>{{ appointment.user.name.charAt(0) }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-black text-gray-900 dark:text-white">{{ appointment.user.name }}</span>
                                                <span class="text-[10px] font-bold text-gray-500">{{ appointment.user.email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-8">
                                        <div class="px-4 py-1.5 bg-indigo-500/10 text-indigo-500 rounded-lg text-xs font-black inline-block uppercase tracking-widest">
                                            Turno #{{ appointment.turn_number }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-8">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-gray-700 dark:text-gray-300">{{ new Date(appointment.time_slot.date).toLocaleDateString() }}</span>
                                            <span class="text-[10px] font-bold text-gray-500">{{ appointment.time_slot.start_time.substring(0,5) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-8">
                                        <span class="text-xs font-bold text-gray-500">{{ appointment.user.phone || 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-8">
                                        <div v-if="appointment.payment_proof" class="relative group/proof w-16 h-16 rounded-xl overflow-hidden border border-white/10">
                                            <img :src="appointment.payment_proof" class="w-full h-full object-cover" />
                                            <a :href="appointment.payment_proof" target="_blank" class="absolute inset-0 bg-black/50 opacity-0 group-hover/proof:opacity-100 flex items-center justify-center transition text-white">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                        </div>
                                        <span v-else class="text-xs text-gray-400">Sin comprobante</span>
                                    </td>
                                    <td class="px-6 py-8">
                                        <div class="flex flex-col gap-1">
                                            <span 
                                                :class="getStatusClass(appointment.status)"
                                                class="px-4 py-1.5 border rounded-full text-[9px] font-black uppercase tracking-widest text-center"
                                            >
                                                {{ getStatusLabel(appointment.status) }}
                                            </span>
                                            <span v-if="appointment.payment_status === 'verified'" class="text-[8px] font-black text-emerald-500 uppercase tracking-tighter text-center">
                                                ✓ PAGO VERIFICADO
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-right">
                                        <div class="flex items-center justify-end gap-2" v-if="appointment.status === 'pending'">
                                            <Link 
                                                :href="route('doctor.appointments.update-status', { id: appointment.id, status: 'accepted' })" 
                                                method="post" as="button"
                                                class="px-4 py-2 bg-emerald-500/10 text-emerald-600 rounded-lg hover:bg-emerald-500 hover:text-white transition text-[10px] font-black uppercase tracking-widest"
                                            >
                                                Aprobar
                                            </Link>
                                            <Link 
                                                :href="route('doctor.appointments.update-status', { id: appointment.id, status: 'rejected' })" 
                                                method="post" as="button"
                                                class="px-4 py-2 bg-rose-500/10 text-rose-600 rounded-lg hover:bg-rose-500 hover:text-white transition text-[10px] font-black uppercase tracking-widest"
                                            >
                                                Rechazar
                                            </Link>
                                        </div>
                                        <Link 
                                            v-else
                                            :href="route('doctor.appointments.index')"
                                            class="px-6 py-2 bg-[#1c2128] border border-white/5 rounded-xl text-indigo-400 hover:bg-indigo-600 hover:text-white transition group/btn"
                                        >
                                            <span class="text-[10px] font-black uppercase tracking-widest">Ver Detalles</span>
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="filteredAppointments.length === 0">
                                    <td colspan="5" class="px-10 py-20 text-center">
                                        <div class="w-16 h-16 bg-gray-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6 text-gray-500">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">No se encontró ningún dato</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
