<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    timeSlots: Array,
    weeklySchedules: Array,
    maxTurnsPerDay: Number,
    salesStoppedDates: { type: Array, default: () => [] }
});

// ——— Agregar turnos: modo "Solo un día" o "Varios días" ———
const BLOCKS = [
    { id: 'morning', label: 'Mañana' },
    { id: 'afternoon', label: 'Tarde' },
    { id: 'night', label: 'Noche' },
];

const mode = ref('single'); // 'single' = solo un día, 'range' = varios días
const selectedBlocks = ref(['morning', 'afternoon']);
const byDayForm = useForm({
    date_from: '',
    date_to: '',
    blocks: [],
    turns_per_block: 5,
});

const toggleBlock = (id) => {
    const i = selectedBlocks.value.indexOf(id);
    if (i === -1) selectedBlocks.value.push(id);
    else selectedBlocks.value.splice(i, 1);
};

const addByDayRange = () => {
    if (mode.value === 'single') {
        if (!byDayForm.date_from) {
            alert('Elige la fecha.');
            return;
        }
        byDayForm.date_to = byDayForm.date_from;
    } else {
        if (!byDayForm.date_from || !byDayForm.date_to) {
            alert('Elige fecha desde y fecha hasta.');
            return;
        }
        if (byDayForm.date_to < byDayForm.date_from) {
            alert('La fecha hasta debe ser igual o posterior a la fecha desde.');
            return;
        }
    }
    byDayForm.blocks = [...selectedBlocks.value];
    if (byDayForm.blocks.length === 0) {
        alert('Marca al menos uno: Mañana, Tarde o Noche.');
        return;
    }
    byDayForm.post(route('doctor.schedule.by-day-range'), {
        onSuccess: () => {},
        preserveScroll: true,
    });
};

// ——— Detener venta ———
const stopSalesForm = useForm({ date: '' });

const stopSales = () => {
    if (!stopSalesForm.date) return;
    stopSalesForm.post(route('doctor.schedule.stop-sales'), { onSuccess: () => stopSalesForm.reset('date') });
};

const resumeSales = (date) => {
    if (!confirm('¿Reanudar la venta de turnos para esta fecha?')) return;
    router.post(route('doctor.schedule.resume-sales'), { date }, { preserveScroll: true });
};

const deleteSlot = (id) => {
    if (confirm('¿Eliminar este turno? (Solo si no está reservado)')) {
        router.delete(route('doctor.schedule.destroy', id), { preserveScroll: true });
    }
};

// ——— Helpers ———
const toDateKey = (val) => {
    const s = String(val ?? '');
    return s.includes('T') ? s.split('T')[0] : s.trim();
};

const slotsByDate = computed(() => {
    const grouped = {};
    (props.timeSlots || []).forEach(slot => {
        const key = toDateKey(slot.date);
        if (!key) return;
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(slot);
    });
    return Object.keys(grouped).sort().reduce((acc, date) => {
        acc[date] = grouped[date];
        return acc;
    }, {});
});

const formatDateHuman = (dateStr) => {
    if (!dateStr) return 'Fecha';
    const raw = String(dateStr);
    const dateOnly = raw.includes('T') ? raw.split('T')[0] : raw.trim();
    if (!dateOnly || !/^\d{4}-\d{2}-\d{2}$/.test(dateOnly)) return dateOnly || 'Fecha';
    const date = new Date(dateOnly + 'T12:00:00');
    if (isNaN(date.getTime())) return dateOnly;
    const formatted = date.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' });
    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
};

const slotTypeLabel = (type) => {
    const t = BLOCKS.find(b => b.id === type);
    return t ? t.label : type || '—';
};

const formatSlotDisplay = (slot) => {
    if (slot.start_time) return slot.start_time.substring(0, 5);
    if (slot.slot_type) return slotTypeLabel(slot.slot_type);
    return '—';
};

// Agrupar slots de un día por tipo para mostrar "Mañana (3), Tarde (2)" etc.
const slotsByTypeForDate = (slots) => {
    const byType = {};
    slots.forEach(s => {
        const t = s.slot_type || 'other';
        if (!byType[t]) byType[t] = [];
        byType[t].push(s);
    });
    return byType;
};
</script>

<template>
    <Head title="Horario de Citas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Horario de citas</h2>
        </template>

        <div class="py-8 bg-[#fdf8f5] dark:bg-[#0c0e12] min-h-screen">
            <div class="max-w-4xl mx-auto px-6 space-y-8">
                <!-- Mensaje flash -->
                <div v-if="$page.props.flash?.message" class="p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm font-bold">
                    {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.errors?.error" class="p-4 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 rounded-2xl text-rose-700 dark:text-rose-300 text-sm font-bold">
                    {{ $page.props.errors.error }}
                </div>

                <!-- 1) Agregar turnos: instrucciones y dos modos claros -->
                <div class="bg-white dark:bg-[#161920] rounded-2xl border border-gray-100 dark:border-white/5 p-8 shadow-sm">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">¿Cómo agendo?</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Elige si es <strong>un solo día</strong> (ej: un viernes solo hasta mediodía) o <strong>varios días seguidos</strong>. Luego marca en qué bloque(s) atiendes: <strong>Mañana</strong>, <strong>Tarde</strong> o <strong>Noche</strong>.</p>

                    <!-- Ejemplos en caja -->
                    <div class="mb-8 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-500/20">
                        <p class="text-xs font-black text-indigo-800 dark:text-indigo-300 uppercase tracking-wider mb-2">Ejemplos</p>
                        <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                            <li>• <strong>Solo viernes hasta mediodía:</strong> elige "Solo un día", fecha = ese viernes, marca solo <strong>Mañana</strong>, Agregar.</li>
                            <li>• <strong>Lunes a viernes toda la semana:</strong> elige "Varios días", desde = lunes, hasta = viernes, marca Mañana y Tarde, Agregar.</li>
                            <li>• <strong>Un día completo:</strong> "Solo un día", elige la fecha, marca Mañana + Tarde + Noche, Agregar.</li>
                        </ul>
                    </div>

                    <!-- Modo: Solo un día / Varios días -->
                    <div class="flex gap-2 mb-6">
                        <button
                            type="button"
                            @click="mode = 'single'"
                            :class="mode === 'single' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
                            class="px-5 py-2.5 rounded-xl font-black text-sm uppercase tracking-wider transition"
                        >
                            Solo un día
                        </button>
                        <button
                            type="button"
                            @click="mode = 'range'"
                            :class="mode === 'range' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
                            class="px-5 py-2.5 rounded-xl font-black text-sm uppercase tracking-wider transition"
                        >
                            Varios días
                        </button>
                    </div>

                    <div class="space-y-6">
                        <!-- Fecha(s) -->
                        <div class="flex flex-wrap items-end gap-6">
                            <div v-if="mode === 'single'">
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">¿Qué día trabajas?</label>
                                <input
                                    v-model="byDayForm.date_from"
                                    type="date"
                                    :min="new Date().toISOString().split('T')[0]"
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1c2128] px-4 py-3 text-sm font-bold text-gray-800 dark:text-white"
                                />
                            </div>
                            <template v-else>
                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Desde</label>
                                    <input
                                        v-model="byDayForm.date_from"
                                        type="date"
                                        :min="new Date().toISOString().split('T')[0]"
                                        class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1c2128] px-4 py-3 text-sm font-bold text-gray-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Hasta</label>
                                    <input
                                        v-model="byDayForm.date_to"
                                        type="date"
                                        :min="byDayForm.date_from || new Date().toISOString().split('T')[0]"
                                        class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1c2128] px-4 py-3 text-sm font-bold text-gray-800 dark:text-white"
                                    />
                                </div>
                            </template>
                        </div>

                        <!-- Bloques -->
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">{{ mode === 'single' ? 'Ese día atiendo en:' : 'En todos esos días atiendo en:' }}</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="b in BLOCKS"
                                    :key="b.id"
                                    type="button"
                                    @click="toggleBlock(b.id)"
                                    :class="selectedBlocks.includes(b.id)
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-white/10'"
                                    class="px-4 py-2.5 rounded-xl border text-sm font-black uppercase tracking-wider transition"
                                >
                                    {{ b.label }}
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">Marca solo los que apliquen (ej: solo Mañana = hasta mediodía)</p>
                        </div>

                        <!-- Turnos por bloque + botón -->
                        <div class="flex flex-wrap items-end gap-6">
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Cuántos turnos por bloque</label>
                                <input
                                    v-model.number="byDayForm.turns_per_block"
                                    type="number"
                                    min="1"
                                    max="50"
                                    class="w-20 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1c2128] px-4 py-3 text-sm font-bold text-gray-800 dark:text-white text-center"
                                />
                            </div>
                            <button
                                type="button"
                                @click="addByDayRange"
                                :disabled="(mode === 'range' ? (!byDayForm.date_from || !byDayForm.date_to) : !byDayForm.date_from) || selectedBlocks.length === 0 || byDayForm.processing"
                                class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-black text-sm uppercase tracking-wider hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                {{ byDayForm.processing ? 'Agregando…' : (mode === 'single' ? 'Agregar este día' : 'Agregar estos días') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2) Detener venta -->
                <div class="bg-white dark:bg-[#161920] rounded-2xl border border-gray-100 dark:border-white/5 p-8 shadow-sm">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">Detener venta de turnos</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Si detienes la venta para una fecha, los pacientes no podrán reservar ese día.</p>
                    <div class="flex flex-wrap items-end gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Fecha</label>
                            <input v-model="stopSalesForm.date" type="date" :min="new Date().toISOString().split('T')[0]" class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1c2128] px-4 py-3 text-sm font-bold text-gray-800 dark:text-white" />
                        </div>
                        <button type="button" @click="stopSales" :disabled="!stopSalesForm.date || stopSalesForm.processing" class="px-5 py-3 bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 rounded-xl font-black text-xs uppercase tracking-wider hover:bg-rose-500 hover:text-white transition disabled:opacity-50">
                            Detener venta
                        </button>
                    </div>
                    <div v-if="salesStoppedDates?.length > 0" class="flex flex-wrap gap-2">
                        <span class="text-xs font-black text-gray-500 uppercase tracking-wider mr-2">Venta detenida:</span>
                        <template v-for="d in salesStoppedDates" :key="d">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold">
                                {{ formatDateHuman(d) }}
                                <button type="button" @click="resumeSales(d)" class="hover:bg-rose-500/20 rounded-lg p-0.5" title="Reanudar">×</button>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- 3) Turnos publicados (por día, mostrando Mañana/Tarde/Noche) -->
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white mb-4">Tus turnos publicados</h3>

                    <div v-if="Object.keys(slotsByDate).length === 0" class="bg-white dark:bg-[#161920] p-10 rounded-2xl border border-dashed border-gray-200 dark:border-white/10 text-center">
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-bold">Aún no hay turnos. Usa la sección de arriba para agregar turnos por día (Mañana, Tarde, Noche).</p>
                    </div>

                    <div v-else class="space-y-6">
                        <div v-for="(slots, date) in slotsByDate" :key="date" class="bg-white dark:bg-[#161920] rounded-2xl p-6 border border-gray-100 dark:border-white/5 shadow-sm">
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-4 mb-4">
                                <h4 class="text-base font-black text-gray-900 dark:text-white capitalize">{{ formatDateHuman(date) }}</h4>
                                <span class="px-3 py-1 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-black">{{ slots.length }} turnos</span>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <div
                                    v-for="slot in slots"
                                    :key="slot.id"
                                    class="group relative"
                                >
                                    <div
                                        :class="slot.is_booked ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20' : 'bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-white/10'"
                                        class="pl-4 pr-8 py-2 rounded-xl text-sm font-bold border flex items-center gap-2"
                                    >
                                        {{ formatSlotDisplay(slot) }}
                                        <span v-if="slot.is_booked" class="w-1.5 h-1.5 bg-emerald-500 rounded-full" title="Reservado"></span>
                                        <button
                                            v-if="!slot.is_booked"
                                            @click="deleteSlot(slot.id)"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 w-5 h-5 bg-rose-500 text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-xs"
                                            title="Eliminar"
                                        >×</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
