<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stats: Object,
    appointments_by_date: Object,
    next_appointment: Object,
    all_appointments: Array
});

const activeTab = ref('upcoming');

const getStatusClass = (status) => {
    switch (status) {
        case 'pending': return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
        case 'accepted': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400';
        case 'completed': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
        case 'rejected': return 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400';
        default: return 'bg-gray-100 text-gray-500';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Pendiente';
        case 'accepted': return 'Verificado / Pagado';
        case 'completed': return 'Finalizado';
        case 'rejected': return 'Rechazada';
        case 'in_consultation': return 'En Consulta';
        default: return status;
    }
};

const getInitial = (name) => name ? name.charAt(0).toUpperCase() : 'U';

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        return date.toLocaleDateString('es-ES', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
        }).replace('.', '');
    } catch (e) {
        return dateStr;
    }
};
const formatTime = (timeStr) => {
    if (!timeStr) return '';
    return timeStr.substring(0, 5);
};
</script>

<template>
    <Head title="Tablero del Paciente" />

    <PatientLayout>
        <template #header>
            <div class="bg-[#1e293b] dark:bg-[#161920] py-12 px-8 mb-8 relative overflow-hidden">
                <div class="max-w-7xl mx-auto relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 mb-2">Inicio > Tablero del paciente</p>
                    <h1 class="text-4xl font-black text-white tracking-tight uppercase">Tablero del paciente</h1>
                </div>
                <div class="absolute right-0 top-0 w-1/3 h-full bg-gradient-to-l from-indigo-500/10 to-transparent"></div>
            </div>
        </template>

        <div class="pb-24">
            <div class="space-y-10">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-[#161920] p-6 rounded-[2rem] shadow-sm border border-gray-100 dark:border-white/5 flex items-center justify-between group hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-500">
                        <div>
                            <p class="text-3xl font-black text-gray-800 dark:text-white mb-1">{{ stats.total }}</p>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Cita</p>
                        </div>
                        <div class="w-16 h-16 rounded-[1.5rem] bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600 transition-transform group-hover:scale-110">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#161920] p-6 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-white/5 flex items-center justify-between group hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-500">
                        <div>
                            <p class="text-3xl font-black text-gray-800 dark:text-white mb-1">{{ stats.completed }}</p>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Completado Cita</p>
                        </div>
                        <div class="w-16 h-16 rounded-[1.5rem] bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600 transition-transform group-hover:scale-110">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#161920] p-6 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-white/5 flex items-center justify-between group hover:shadow-xl hover:shadow-amber-500/10 transition-all duration-500">
                        <div>
                            <p class="text-3xl font-black text-gray-800 dark:text-white mb-1">{{ stats.pending }}</p>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pendiente Cita</p>
                        </div>
                        <div class="w-16 h-16 rounded-[1.5rem] bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 transition-transform group-hover:scale-110">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.175 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                        </div>
                    </div>

                    <!-- New: Next Appointment Card -->
                    <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 p-6 rounded-[2rem] shadow-xl border border-indigo-500 flex items-center justify-between group hover:scale-[1.02] transition-all duration-500">
                        <div>
                            <p class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em] mb-3">Próxima Cita</p>
                            <div v-if="next_appointment">
                                <p class="text-xl font-black text-white mb-1 uppercase tracking-tight">{{ formatDate(next_appointment.time_slot.date) }}</p>
                                <p class="text-xs font-bold text-indigo-100 uppercase tracking-widest opacity-80">{{ formatTime(next_appointment.time_slot.start_time) }} - {{ next_appointment.doctor_profile.user.name }}</p>
                            </div>
                            <div v-else>
                                <p class="text-xl font-black text-white mb-1 uppercase">Sin citas</p>
                                <p class="text-[10px] font-bold text-indigo-200 uppercase">Reserva una nueva</p>
                            </div>
                        </div>
                        <div class="w-16 h-16 rounded-[1.5rem] bg-white/10 flex items-center justify-center text-white">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Main Table Section -->
                <section class="bg-white dark:bg-[#161920] rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-white/5 overflow-hidden">
                    <div class="p-8 border-b border-gray-50 dark:border-white/5">
                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight mb-8">Citas con doctores</h3>

                        <!-- Tabs -->
                        <div class="flex gap-2">
                            <button
                                @click="activeTab = 'past'"
                                class="px-8 py-2.5 rounded-full text-[10px] font-black uppercase tracking-widest transition-all"
                                :class="activeTab === 'past' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'"
                            >Pasado</button>
                            <button
                                @click="activeTab = 'today'"
                                class="px-8 py-2.5 rounded-full text-[10px] font-black uppercase tracking-widest transition-all"
                                :class="activeTab === 'today' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'"
                            >Hoy</button>
                            <button
                                @click="activeTab = 'upcoming'"
                                class="px-8 py-2.5 rounded-full text-[10px] font-black uppercase tracking-widest transition-all"
                                :class="activeTab === 'upcoming' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/20' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'"
                            >Próximas</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-black/20 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                    <th class="px-8 py-5">Nombre del Médico</th>
                                    <th class="px-8 py-5 text-center">Número de Cola</th>
                                    <th class="px-8 py-5">Teléfono</th>
                                    <th class="px-8 py-5">Fecha</th>
                                    <th class="px-8 py-5">Estado</th>
                                    <th class="px-8 py-5 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-white/5 text-sm">
                                <tr v-for="apt in appointments_by_date[activeTab]" :key="apt.id" class="hover:bg-gray-50/80 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-black text-indigo-500 overflow-hidden shrink-0">
                                                <img v-if="apt.doctor_profile.user.profile_photo_url" :src="apt.doctor_profile.user.profile_photo_url" class="w-full h-full object-cover" />
                                                <span v-else>{{ getInitial(apt.doctor_profile.user.name) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-black text-gray-800 dark:text-white uppercase tracking-tight text-xs">{{ apt.doctor_profile.user.name }}</p>
                                                <p class="text-[9px] font-black font-bold text-indigo-400 uppercase tracking-widest">{{ apt.doctor_profile.speciality?.name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <div v-if="apt.status === 'accepted' || apt.status === 'pending'" class="inline-block p-1 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-white/5 shadow-inner">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white font-black text-sm">
                                                {{ apt.turn_number }}
                                            </div>
                                            <p class="text-[8px] font-black text-gray-400 mt-1 uppercase tracking-tighter">En espera</p>
                                        </div>
                                        <div v-else class="text-[9px] font-black text-gray-300 uppercase italic">Sin asignar</div>
                                    </td>
                                    <td class="px-8 py-6 font-bold text-gray-500 dark:text-gray-400 text-xs tabular-nums tracking-wider">
                                        {{ apt.doctor_profile.phone_number || '809-XXX-XXXX' }}
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="font-black text-gray-800 dark:text-white text-xs whitespace-nowrap">{{ formatDate(apt.time_slot.date) }}</p>
                                        <p class="text-xs font-bold text-gray-400 tabular-nums">{{ formatTime(apt.time_slot.start_time) }}</p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span :class="getStatusClass(apt.status)" class="px-4 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest block text-center">
                                            {{ getStatusLabel(apt.status) }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <div class="px-4 py-2 bg-emerald-500 text-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 whitespace-nowrap cursor-default inline-block">
                                            Mi Turno #{{ apt.turn_number }}
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="appointments_by_date[activeTab].length === 0">
                                    <td colspan="6" class="px-8 py-20 text-center">
                                        <div class="max-w-xs mx-auto opacity-20 dark:opacity-10 mb-6">
                                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <p class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">No hay citas en esta sección</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mock Pagination -->
                    <div class="p-8 border-t border-gray-50 dark:border-white/5 flex justify-center">
                        <div class="flex gap-2">
                            <button class="w-10 h-10 rounded-xl border border-gray-100 dark:border-white/5 flex items-center justify-center text-xs font-black text-gray-400 hover:bg-gray-50 transition">&lt;</button>
                            <button class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-lg shadow-indigo-500/20 transition">1</button>
                            <button class="w-10 h-10 rounded-xl border border-gray-100 dark:border-white/5 flex items-center justify-center text-xs font-black text-gray-500 hover:bg-gray-50 transition">2</button>
                            <button class="w-10 h-10 rounded-xl border border-gray-100 dark:border-white/5 flex items-center justify-center text-xs font-black text-gray-400 hover:bg-gray-50 transition">&gt;</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </PatientLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1e293b;
}
</style>
