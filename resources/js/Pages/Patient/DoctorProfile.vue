<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Calendar from '@/Components/Calendar.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    doctor: Object
});

const page = usePage();

const form = useForm({
    doctor_profile_id: props.doctor.id,
    time_slot_id: '',
    problem_description: '',
    payment_proof: null
});

const currentStep = ref(1);
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const selectedSlot = ref(null);
const isCalendarOpen = ref(false);

const submit = () => {
    form.post(route('patient.appointments.store'), {
        preserveScroll: true,
        onError: (errors) => {
            console.error('Error al agendar:', errors);
        }
    });
};

// Agrupar horarios por fecha
const slotsByDate = computed(() => {
    const slots = props.doctor.time_slots || props.doctor.timeSlots || [];
    
    const grouped = {};
    slots.forEach(slot => {
        // Normalizar la fecha a YYYY-MM-DD
        let dateStr = slot.date;
        if (typeof dateStr === 'string' && dateStr.includes('T')) {
            dateStr = dateStr.split('T')[0];
        } else if (dateStr && typeof dateStr === 'object') {
            // Si es un objeto Date o similar de Carbon
            const d = new Date(dateStr);
            dateStr = !isNaN(d.getTime()) ? d.toISOString().split('T')[0] : dateStr;
        }

        if (!grouped[dateStr]) {
            grouped[dateStr] = [];
        }
        grouped[dateStr].push(slot);
    });
    return grouped;
});

const availableDates = computed(() => Object.keys(slotsByDate.value).sort());

// Bloques para mostrar: Mañana, Tarde, Noche (en vez de horas)
const BLOCKS = [
    { id: 'morning', label: 'Mañana' },
    { id: 'afternoon', label: 'Tarde' },
    { id: 'night', label: 'Noche' },
];

const slotsForSelectedDate = computed(() => {
    if (!selectedDate.value) return [];
    return slotsByDate.value[selectedDate.value] || [];
});

// Para la fecha elegida: agrupar por bloque (slot_type) y contar disponibles
const blocksForSelectedDate = computed(() => {
    const slots = slotsForSelectedDate.value;
    const result = [];
    BLOCKS.forEach(({ id, label }) => {
        const ofType = slots.filter(s => (s.slot_type || '').toLowerCase() === id);
        const available = ofType.filter(s => !s.is_booked);
        result.push({
            id,
            label,
            total: ofType.length,
            available: available.length,
            firstAvailableSlot: available[0] || null,
        });
    });
    return result;
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    
    // Normalizar si es objeto o tiene 'T'
    let normalizedDate = dateStr;
    if (typeof normalizedDate === 'object') {
        const d = new Date(normalizedDate);
        normalizedDate = !isNaN(d.getTime()) ? d.toISOString().split('T')[0] : '';
    } else if (typeof normalizedDate === 'string' && normalizedDate.includes('T')) {
        normalizedDate = normalizedDate.split('T')[0];
    }

    if (!normalizedDate) return '';

    const date = new Date(normalizedDate + 'T00:00:00'); 
    const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    if (isNaN(date.getTime())) return normalizedDate;

    return `${days[date.getDay()]}, ${date.getDate()} de ${months[date.getMonth()]}`;
};

const selectTimeSlot = (slot) => {
    selectedSlot.value = slot;
    form.time_slot_id = slot.id;
    currentStep.value = 2;
};

const selectBlock = (block) => {
    if (!block.firstAvailableSlot || block.available === 0) return;
    selectTimeSlot(block.firstAvailableSlot);
};

// Limpiar errores y resetear paso al cambiar fecha
watch(selectedDate, () => {
    currentStep.value = 1;
    selectedSlot.value = null;
    form.clearErrors();
});

const getInitial = (name) => name ? name.charAt(0).toUpperCase() : 'M';

const selectedSlotLabel = computed(() => {
    if (!selectedSlot.value) return '';
    const s = selectedSlot.value;
    const block = BLOCKS.find(b => (s.slot_type || '').toLowerCase() === b.id);
    if (block) return block.label;
    if (s.start_time) return s.start_time.substring(0, 5);
    return 'Turno';
});
</script>

<template>
    <Head :title="(doctor.user?.name.toLowerCase().includes('dr.') ? '' : 'Dr. ') + doctor.user?.name" />

    <div class="min-h-screen bg-slate-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Navbar -->
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link :href="route('welcome')">
                    <ApplicationLogo />
                </Link>
                
                <div class="flex items-center gap-4">
                    <Link :href="route('patient.search')" class="text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-indigo-600 transition">
                        ← Volver
                    </Link>
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                        Mi Panel
                    </Link>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Sidebar del Doctor -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-xl border border-gray-50 dark:border-gray-700 sticky top-24">
                        <!-- Info del Doctor -->
                        <div class="text-center mb-6 pb-6 border-b border-gray-50 dark:border-gray-700">
                            <div class="w-32 h-32 rounded-3xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-5xl mx-auto mb-4 border-4 border-white dark:border-gray-700 shadow-inner overflow-hidden">
                                <img v-if="doctor.profile_photo_url" :src="doctor.profile_photo_url" class="w-full h-full object-cover" />
                                <span v-else>{{ getInitial(doctor.user.name) }}</span>
                            </div>
                            <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-2 uppercase tracking-tight">
                                {{ doctor.user?.name.toLowerCase().includes('dr.') ? '' : 'Dr. ' }}{{ doctor.user?.name }}
                            </h1>
                            <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">{{ doctor.speciality?.name || 'Especialista' }}</p>
                        </div>

                        <!-- Detalles -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Acerca de</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-medium break-words">
                                    {{ doctor.about || 'Profesional médico certificado con amplia experiencia.' }}
                                </p>
                            </div>

                                <div class="grid grid-cols-1 gap-4">
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-2xl flex items-center gap-4">
                                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center text-indigo-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Ubicación</p>
                                        <p class="text-xs font-black text-gray-800 dark:text-white">{{ doctor.location || 'Santiago, RD' }}</p>
                                    </div>
                                </div>
                                <!-- Consulta / precio oculto: planes gratis sin pago por ahora -->
                                <!--
                                <div class="p-4 bg-emerald-50 ...">Consulta RD$</div>
                                -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Reserva Compacta -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <!-- Header Oscuro -->
                        <div class="bg-[#1c2128] p-8 text-white">
                            <h2 class="text-3xl font-black uppercase tracking-tight mb-2">Reservar cita</h2>
                            <p class="text-sm font-bold text-indigo-400 uppercase tracking-widest">
                                DISPONIBILIDAD: {{ doctor.availability_text || 'De lunes a viernes' }}
                            </p>
                        </div>

                        <div class="p-8 bg-[#fdfaf5] dark:bg-gray-900/50">
                            <!-- Selector de Fecha Compacto -->
                            <div class="mb-8 relative">
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Selecciona una fecha</p>
                                
                                <div @click="isCalendarOpen = true" class="cursor-pointer bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between group hover:border-indigo-500 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-[9px] font-black text-gray-400 uppercase mb-1">Día de la cita</p>
                                            <p class="text-sm font-black text-gray-800 dark:text-white">{{ formatDate(selectedDate) }}</p>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>

                                <!-- Modal de Calendario (Alta Gama) -->
                                <Teleport to="body">
                                    <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                                        <div v-if="isCalendarOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                                            <!-- Backdrop -->
                                            <div @click="isCalendarOpen = false" class="fixed inset-0 bg-[#0f1115]/90 backdrop-blur-md"></div>
                                            
                                            <!-- Modal Content -->
                                            <div class="relative bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-2xl border border-white/10 overflow-hidden w-full max-w-sm animate-in fade-in zoom-in duration-300">
                                                <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-slate-50/50 dark:bg-gray-800/50">
                                                    <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.2em]">Elige tu día</h3>
                                                    <button @click="isCalendarOpen = false" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>
                                                <div class="p-4">
                                                    <Calendar 
                                                        v-model="selectedDate"
                                                        :availableDates="availableDates"
                                                        @select="() => { isCalendarOpen = false; currentStep = 1; form.clearErrors(); }"
                                                        class="!shadow-none !border-none !p-0 mx-auto"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </Transition>
                                </Teleport>
                            </div>

                            <!-- Opciones por bloque: Mañana, Tarde, Noche (no horas) -->
                            <div v-if="currentStep === 1" class="animate-in fade-in duration-500">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Elige turno disponible</p>
                                <div v-if="slotsForSelectedDate.length > 0" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <button
                                        v-for="block in blocksForSelectedDate"
                                        :key="block.id"
                                        @click="selectBlock(block)"
                                        :disabled="block.available === 0"
                                        :class="block.available === 0
                                            ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-gray-200 dark:border-gray-700 cursor-not-allowed'
                                            : 'bg-white dark:bg-gray-800 hover:bg-indigo-600 dark:hover:bg-indigo-600 text-gray-800 dark:text-white hover:text-white border-gray-100 dark:border-gray-700'"
                                        class="px-6 py-5 rounded-2xl text-left border shadow-sm transition-all active:scale-95 disabled:opacity-70"
                                    >
                                        <span class="block text-sm font-black uppercase tracking-tight">{{ block.label }}</span>
                                        <span v-if="block.available === 0" class="block text-xs font-bold text-rose-500 mt-1">Agotado</span>
                                        <span v-else class="block text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ block.available }} {{ block.available === 1 ? 'turno disponible' : 'turnos disponibles' }}</span>
                                    </button>
                                </div>
                                <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
                                    <span class="text-4xl block mb-2">🗓️</span>
                                    <p class="text-sm font-bold text-gray-500 uppercase">Sin disponibilidad para este día</p>
                                </div>
                            </div>

                            <!-- PASO 2: Confirmación y Detalles -->
                            <div v-else-if="currentStep === 2" class="animate-in fade-in duration-300">
                                <div class="flex items-center justify-between mb-8">
                                    <div>
                                        <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase">Confirmar Reserva</h3>
                                        <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Para el {{ formatDate(selectedSlot?.date) }} — {{ selectedSlotLabel }}</p>
                                    </div>
                                    <button @click="currentStep = 1" class="text-[10px] font-black text-gray-400 uppercase hover:text-indigo-600 transition tracking-widest">← Cambiar</button>
                                </div>

                                <!-- Datos bancarios y resumen de pago ocultos: planes gratis / sin pago por ahora -->
                                <!--
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <div class="p-6 ...">Datos Bancarios del Médico (Banco, Cuenta, Titular)</div>
                                    <div class="p-6 bg-indigo-600 ...">Resumen de Pago RD$</div>
                                </div>
                                -->

                                <!-- Formulario Compacto -->
                                <form @submit.prevent="submit" class="space-y-6">
                                    <!-- Global Error Alert -->
                                    <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-2xl flex items-start gap-4 animate-in fade-in duration-300">
                                        <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center shrink-0 text-rose-600 dark:text-rose-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-rose-900 dark:text-rose-400 uppercase mb-1">Error al agendar</p>
                                            <ul class="text-[10px] font-bold text-rose-700 dark:text-rose-500 list-disc list-inside">
                                                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div>
                                        <textarea
                                            v-model="form.problem_description"
                                            class="w-full bg-white dark:bg-gray-800 rounded-2xl border-none focus:ring-2 focus:ring-indigo-500 p-5 font-bold text-sm placeholder:text-gray-400 shadow-sm"
                                            :class="{'ring-2 ring-rose-500': form.errors.problem_description}"
                                            rows="3"
                                            placeholder="¿Cuál es el motivo de tu consulta?"
                                            required
                                        ></textarea>
                                        <p v-if="form.errors.problem_description" class="text-[10px] font-black text-rose-500 uppercase mt-2 ml-2">{{ form.errors.problem_description }}</p>
                                    </div>

                                    <!-- Pago deshabilitado: reserva sin comprobante (solo asignación de turno por el médico) -->
                                    <div v-if="false" class="relative group">
                                        <input
                                            type="file"
                                            @input="form.payment_proof = $event.target.files[0]"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                            accept="image/*"
                                        />
                                        <div class="w-full py-6 bg-white dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl flex items-center justify-center gap-4 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/10 transition-all" :class="{'border-rose-500 bg-rose-50/10': form.errors.payment_proof}">
                                            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span class="text-xs font-black text-gray-500 uppercase tracking-widest">
                                                {{ form.payment_proof ? form.payment_proof.name : 'Subir Comprobante' }}
                                            </span>
                                        </div>
                                        <p v-if="form.errors.payment_proof" class="text-[10px] font-black text-rose-500 uppercase mt-2 ml-2 text-center">{{ form.errors.payment_proof }}</p>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="w-full h-16 bg-[#1c2128] text-white font-black text-lg rounded-2xl hover:bg-black transition-all shadow-xl disabled:opacity-50 active:scale-95 flex items-center justify-center gap-3 uppercase tracking-widest"
                                    >
                                        <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ form.processing ? 'Procesando...' : 'Confirmar y Agendar' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        </div>


    </template>
<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-in {
    animation: fade-in 0.3s ease-out;
}
</style>
