<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

defineProps({
    appointments: Array
});

const appointmentData = computed(() => usePage().props.flash?.appointment);
const showSuccessModal = ref(false);

watch(appointmentData, (newVal) => {
    if (newVal) {
        showSuccessModal.value = true;
    }
}, { immediate: true });

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

const getStatusClass = (status) => {
    switch (status) {
        case 'pending': return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
        case 'accepted': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
        case 'completed': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        case 'rejected': return 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400';
        case 'in_consultation': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400';
        default: return 'bg-gray-100 text-gray-500';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Pendiente de Pago';
        case 'accepted': return 'Verificado / Pagado';
        case 'completed': return 'Finalizado';
        case 'rejected': return 'Rechazada';
        case 'in_consultation': return 'En Consulta';
        default: return status;
    }
};

const getInitial = (name) => name ? name.charAt(0).toUpperCase() : 'U';
</script>

<template>
    <Head title="Historial de Citas" />

    <PatientLayout>
        <!-- Success Modal (Alta Gama) -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 scale-90" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-90">
                <div v-if="showSuccessModal" class="fixed inset-0 z-[110] flex items-center justify-center p-6">
                    <div @click="showSuccessModal = false" class="fixed inset-0 bg-[#0f1115]/95 backdrop-blur-xl"></div>
                    
                    <div class="relative bg-white dark:bg-gray-900 rounded-[3rem] shadow-[0_0_100px_rgba(79,70,229,0.3)] border border-white/10 overflow-hidden w-full max-w-md p-10 text-center animate-in fade-in zoom-in duration-500">
                        <!-- Success Icon -->
                        <div class="w-24 h-24 bg-emerald-500/10 rounded-[2rem] flex items-center justify-center text-emerald-500 mx-auto mb-8 relative">
                            <div class="absolute inset-0 bg-emerald-500/20 rounded-[2rem] animate-ping duration-1000"></div>
                            <svg class="w-12 h-12 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>

                        <h3 class="text-3xl font-black text-gray-800 dark:text-white uppercase tracking-tight mb-4">¡Cita Agendada!</h3>
                        <p v-if="appointmentData" class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-8 px-4">Tu solicitud ha sido enviada con éxito al <span class="text-indigo-600 dark:text-indigo-400">Dr. {{ appointmentData?.doctor_name }}</span>.</p>

                        <!-- Turn Number Card -->
                        <div v-if="appointmentData" class="bg-indigo-50 dark:bg-indigo-900/20 p-8 rounded-[2rem] border border-indigo-100 dark:border-indigo-500/10 mb-10">
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em] mb-2">Tu número de turno</p>
                            <p class="text-6xl font-black text-indigo-600 dark:text-indigo-400 italic">#{{ appointmentData?.turn_number }}</p>
                        </div>

                        <button @click="showSuccessModal = false" class="w-full h-16 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-lg rounded-[1.5rem] transition-all shadow-xl shadow-indigo-500/20 uppercase tracking-widest active:scale-95">
                            Entendido
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
        <!-- Header Banner -->
        <template #header>
            <div class="bg-[#1e293b] dark:bg-[#161920] py-12 px-8 mb-8 relative overflow-hidden">
                <div class="max-w-7xl mx-auto relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 mb-2">Inicio > Citas</p>
                    <h1 class="text-4xl font-black text-white tracking-tight uppercase">Historial de Citas</h1>
                </div>
                <div class="absolute right-0 top-0 w-1/3 h-full bg-gradient-to-l from-emerald-500/10 to-transparent"></div>
            </div>
        </template>

        <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-white/5 overflow-hidden">
            <div class="p-8 border-b border-gray-50 dark:border-white/5 flex items-center justify-between">
                <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Todas mis citas</h3>
                <Link :href="route('patient.search')" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-lg shadow-indigo-500/20">
                    Nueva Cita
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-black/20 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                            <th class="px-8 py-5">Médico</th>
                            <th class="px-8 py-5 text-center">Turno</th>
                            <th class="px-8 py-5">Fecha / Hora</th>
                            <th class="px-8 py-5">Estado Pago</th>
                            <th class="px-8 py-5">Estado Cita</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5 text-sm">
                        <tr v-for="apt in appointments" :key="apt.id" class="hover:bg-gray-50/80 dark:hover:bg-white/5 transition-colors">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-black text-indigo-500 overflow-hidden shrink-0">
                                        <img v-if="apt.doctor_profile.user.profile_photo_url" :src="apt.doctor_profile.user.profile_photo_url" class="w-full h-full object-cover" />
                                        <span v-else>{{ getInitial(apt.doctor_profile.user.name) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-black text-gray-800 dark:text-white uppercase tracking-tight text-xs">{{ apt.doctor_profile.user.name }}</p>
                                        <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest">{{ apt.doctor_profile.speciality?.name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div v-if="apt.status === 'accepted' || apt.status === 'pending'" class="inline-block px-3 py-1 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-white/5 font-black text-xs text-gray-600 dark:text-gray-300">
                                    #{{ apt.turn_number }}
                                </div>
                                <div v-else class="text-[9px] font-black text-gray-300 uppercase italic">-</div>
                            </td>
                            <td class="px-8 py-6">
                                <p class="font-black text-gray-800 dark:text-white text-xs whitespace-nowrap">{{ formatDate(apt.time_slot?.date) }}</p>
                                <p class="text-xs font-bold text-gray-400 tabular-nums">{{ formatTime(apt.time_slot?.start_time) }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <span v-if="apt.payment_status === 'verified'" class="text-emerald-500 font-bold text-[10px] uppercase tracking-wider flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Verificado
                                </span>
                                <span v-else class="text-amber-500 font-bold text-[10px] uppercase tracking-wider flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Pendiente
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <span :class="getStatusClass(apt.status)" class="px-4 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest block text-center w-max">
                                    {{ getStatusLabel(apt.status) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="appointments.length === 0">
                            <td colspan="5" class="px-8 py-20 text-center">
                                <p class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">No tienes citas registradas</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </PatientLayout>
</template>
