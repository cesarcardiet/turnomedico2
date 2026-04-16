<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    holidays: Array
});

const form = useForm({
    date: '',
    reason: ''
});

const submit = () => {
    form.post(route('doctor.holidays.store'), {
        onSuccess: () => {
            form.reset();
            alert('Día de vacaciones agregado.');
        }
    });
};

const deleteHoliday = (id) => {
    if (confirm('¿Eliminar este día de vacaciones?')) {
        form.delete(route('doctor.holidays.destroy', id));
    }
};

const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString + 'T00:00:00').toLocaleDateString('es-ES', options);
};
</script>

<template>
    <Head title="Mis Vacaciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-8 bg-amber-500 rounded-full"></div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Mis vacaciones</h2>
            </div>
        </template>

        <div class="py-12 bg-[#fdf8f5] dark:bg-[#0c0e12] min-h-screen">
            <div class="max-w-4xl mx-auto px-6 space-y-8">
                
                <!-- Add Holiday Card -->
                <div class="bg-white dark:bg-[#161920] rounded-[2rem] p-10 border border-gray-100 dark:border-white/5 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2 bg-amber-50 dark:bg-amber-500/10 rounded-lg text-amber-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Programar Vacaciones</h3>
                    </div>

                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Fecha de Inicio/Día</label>
                            <input v-model="form.date" type="date" class="w-full bg-[#f3ebe4] dark:bg-[#1c2128] border-none rounded-2xl text-gray-800 dark:text-white font-black text-sm p-4 focus:ring-2 focus:ring-amber-500/20 transition" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Motivo (Opcional)</label>
                            <input v-model="form.reason" type="text" placeholder="Ej. Congreso médico, viaje..." class="w-full bg-[#f3ebe4] dark:bg-[#1c2128] border-none rounded-2xl text-gray-800 dark:text-white font-black text-sm p-4 focus:ring-2 focus:ring-amber-500/20 transition" />
                        </div>
                        <button type="submit" :disabled="form.processing" class="md:col-span-2 w-full py-4 bg-amber-500 text-white font-black rounded-2xl hover:bg-amber-600 transition shadow-xl shadow-amber-900/20 uppercase tracking-widest text-[11px]">
                            {{ form.processing ? 'Agregando...' : 'Marcar como no laborable' }}
                        </button>
                        <div v-if="form.errors.date" class="md:col-span-2 text-rose-500 text-[10px] font-black uppercase mt-2 text-center">{{ form.errors.date }}</div>
                    </form>
                </div>

                <!-- Holidays List -->
                <div v-if="holidays.length > 0" class="space-y-4">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest pl-2">Días Programados</h4>
                    <div v-for="holiday in holidays" :key="holiday.id" class="bg-white dark:bg-[#161920] rounded-2xl p-6 border border-gray-100 dark:border-white/5 flex items-center justify-between group hover:border-amber-500/30 transition-all">
                        <div class="flex items-center gap-6">
                            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-amber-500 font-black">
                                <span class="text-lg">{{ new Date(holiday.date + 'T00:00:00').getDate() }}</span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-gray-900 dark:text-white capitalize">{{ formatDate(holiday.date) }}</span>
                                <span class="text-[10px] font-black text-gray-400 uppercase mt-1">{{ holiday.reason || 'Sin motivo especificado' }}</span>
                            </div>
                        </div>
                        <button @click="deleteHoliday(holiday.id)" class="bg-rose-500/10 text-rose-500 p-3 rounded-xl hover:bg-rose-500 hover:text-white transition-all opacity-0 group-hover:opacity-100">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>

                <div v-else class="text-center py-20 bg-white dark:bg-[#161920] rounded-[2rem] border border-dashed border-gray-200 dark:border-white/10">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 dark:text-gray-700">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </div>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No tienes vacaciones programadas</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
