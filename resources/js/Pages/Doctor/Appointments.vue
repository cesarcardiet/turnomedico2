<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    appointments: Array,
    server_today: String
});

const filterType = ref('hoy');
const searchQuery = ref('');

// Normalizar fecha desde el backend (puede venir como ISO con \"T\")
const normalizeDate = (val) => {
    if (!val) return '';
    const s = String(val);
    if (s.includes('T')) return s.split('T')[0];
    return s.trim();
};

const filteredAppointments = computed(() => {
    let list = props.appointments;
    const now = new Date();
    // Usar fecha del servidor si está disponible, si no la local
    const today = props.server_today || `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    
    // Calcular mañana en base a 'today'
    const refDate = new Date(today + 'T12:00:00'); // mediodía para evitar problemas de zona horaria
    const tomorrowDate = new Date(refDate);
    tomorrowDate.setDate(refDate.getDate() + 1);
    const tomorrow = `${tomorrowDate.getFullYear()}-${String(tomorrowDate.getMonth() + 1).padStart(2, '0')}-${String(tomorrowDate.getDate()).padStart(2, '0')}`;

    // Filtrar por fecha
    if (filterType.value === 'hoy') {
        list = list.filter(a => normalizeDate(a.time_slot.date) === today);
    } else if (filterType.value === 'mañana') {
        list = list.filter(a => normalizeDate(a.time_slot.date) === tomorrow);
    } else if (filterType.value === 'proximas') {
        list = list.filter(a => normalizeDate(a.time_slot.date) > today);
    }

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        list = list.filter(a => 
            a.user.name.toLowerCase().includes(query) || 
            (a.turn_number && a.turn_number.toString().includes(query))
        );
    }

    return list;
});

const updateStatus = (id, status) => {
    router.post(route('doctor.appointments.update-status', id), { status });
};

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
    <Head title="Gestión de Citas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Gestión de Citas</h2>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <!-- Search -->
                    <div class="relative w-full sm:w-64">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Buscar paciente..."
                            class="w-full bg-white dark:bg-[#161920] border-gray-100 dark:border-white/5 rounded-2xl px-6 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-gray-400"
                        />
                        <svg class="w-5 h-5 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="flex bg-gray-100 dark:bg-[#161920] p-1.5 rounded-2xl w-full sm:w-auto">
                        <button 
                            v-for="f in ['hoy', 'mañana', 'proximas', 'todas']" 
                            :key="f"
                            @click="filterType = f"
                            :class="filterType === f ? 'bg-white dark:bg-indigo-600 text-indigo-600 dark:text-white shadow-lg' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all capitalize"
                        >
                            {{ f }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6">
                
                <div v-if="filteredAppointments.length === 0" class="bg-white dark:bg-[#161920] rounded-[2.5rem] p-20 border border-white/5 shadow-2xl text-center">
                    <div class="w-20 h-20 bg-gray-500/10 rounded-3xl flex items-center justify-center text-gray-500 mx-auto mb-8 shadow-xl">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2 uppercase tracking-widest">No hay citas en esta vista</h3>
                    <p class="text-gray-500 font-bold text-sm uppercase tracking-widest text-[10px]">Prueba cambiando el filtro o la búsqueda.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="appointment in filteredAppointments" :key="appointment.id" 
                        class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-white/5 shadow-2xl p-8 hover:scale-[1.02] transition-all duration-500 relative overflow-hidden group">
                        
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 blur-3xl -mr-16 -mt-16"></div>

                        <!-- Header -->
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex flex-col gap-2">
                                <span :class="getStatusClass(appointment.status)" class="px-4 py-1.5 border rounded-full text-[9px] font-black uppercase tracking-widest">
                                    {{ getStatusLabel(appointment.status) }}
                                </span>
                                <span class="px-3 py-1 bg-indigo-500/10 text-indigo-500 rounded-lg text-[9px] font-black uppercase tracking-widest text-center">
                                    Turno #{{ appointment.turn_number }}
                                </span>
                            </div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">#{{ appointment.id }}</span>
                        </div>

                        <!-- Patient Info -->
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 font-black text-lg shadow-lg group-hover:rotate-3 transition">
                                {{ appointment.user.name.charAt(0) }}
                            </div>
                            <div>
                                <h4 class="text-lg font-black text-gray-900 dark:text-white leading-tight uppercase tracking-tight">{{ appointment.user.name }}</h4>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ appointment.user.email }}</p>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center gap-3 text-gray-500">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-xs font-bold">
                                    {{
                                        (() => {
                                            const dStr = normalizeDate(appointment.time_slot.date);
                                            if (!dStr) return '';
                                            const d = new Date(dStr + 'T12:00:00');
                                            if (isNaN(d.getTime())) return dStr;
                                            return d.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' });
                                        })()
                                    }}
                                </span>
                            </div>
                            <div v-if="appointment.time_slot?.start_time || appointment.time_slot?.end_time" class="flex items-center gap-3 text-gray-500">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-xs font-bold">{{ appointment.time_slot.start_time ? appointment.time_slot.start_time.substring(0,5) : '--' }} - {{ appointment.time_slot.end_time ? appointment.time_slot.end_time.substring(0,5) : '--' }}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div v-if="appointment.problem_description" class="mb-8 p-4 bg-gray-50 dark:bg-[#1c2128] rounded-2xl border border-white/5">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Motivo de consulta:</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 italic line-clamp-2">"{{ appointment.problem_description }}"</p>
                        </div>

                        <!-- Actions -->
                        <div class="grid grid-cols-2 gap-3 pt-4 border-t border-white/5">
                            <template v-if="appointment.status === 'pending'">
                                <button @click="updateStatus(appointment.id, 'accepted')" class="bg-emerald-500 text-white font-black text-[10px] uppercase tracking-widest py-3 rounded-xl hover:bg-emerald-600 transition shadow-lg shadow-emerald-900/20">Aceptar</button>
                                <button @click="updateStatus(appointment.id, 'rejected')" class="bg-rose-500 text-white font-black text-[10px] uppercase tracking-widest py-3 rounded-xl hover:bg-rose-600 transition shadow-lg shadow-rose-900/20">Rechazar</button>
                            </template>
                            <template v-else-if="appointment.status === 'accepted'">
                                <div class="col-span-2 grid grid-cols-2 gap-3">
                                    <button @click="updateStatus(appointment.id, 'in_consultation')" class="bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest py-3 rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-900/40 flex items-center justify-center gap-2">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path></svg>
                                        Llamar
                                    </button>
                                    <button @click="updateStatus(appointment.id, 'completed')" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-black text-[10px] uppercase tracking-widest py-3 rounded-xl hover:bg-gray-200 transition">Finalizar</button>
                                </div>
                            </template>
                            <template v-else-if="appointment.status === 'in_consultation'">
                                <button @click="updateStatus(appointment.id, 'completed')" class="col-span-2 bg-emerald-600 text-white font-black text-[10px] uppercase tracking-widest py-3 rounded-xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/40">Finalizar Consulta</button>
                            </template>
                            <template v-else>
                                <div class="col-span-2 text-center py-2 text-[9px] font-black text-gray-500 uppercase tracking-widest">
                                    Cita {{ getStatusLabel(appointment.status) }}
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
