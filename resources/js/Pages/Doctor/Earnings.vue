<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    totalEarnings: Number,
    projectedEarnings: Number,
    transactions: Array
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP' }).format(amount);
};
</script>

<template>
    <Head title="Informes de Ganancias" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Informes de ganancias</h2>
            </div>
        </template>

        <div class="py-12 bg-[#fdf8f5] dark:bg-[#0c0e12] min-h-screen">
            <div class="max-w-6xl mx-auto px-6 space-y-8">
                
                <!-- Stats Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] p-10 border border-emerald-500/10 shadow-sm relative overflow-hidden group transition-all hover:scale-[1.02]">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 block">Ingresos Totales</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black text-gray-900 dark:text-white">{{ formatCurrency(totalEarnings) }}</span>
                            <span class="text-xs font-bold text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded-lg">Realizado</span>
                        </div>
                        <p class="text-xs text-gray-400 font-bold mt-4 leading-relaxed">Suma de todas las consultas aceptadas y completadas vinculadas a pagos aprobados.</p>
                    </div>

                    <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] p-10 border border-blue-500/10 shadow-sm relative overflow-hidden group transition-all hover:scale-[1.02]">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 block">Ingresos Proyectados</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black text-gray-900 dark:text-white">{{ formatCurrency(projectedEarnings) }}</span>
                            <span class="text-xs font-bold text-blue-500 bg-blue-500/10 px-2 py-1 rounded-lg">Por Recaudar</span>
                        </div>
                        <p class="text-xs text-gray-400 font-bold mt-4 leading-relaxed">Total de consultas con pagos ya aprobados que aún no se han completado.</p>
                    </div>
                </div>

                <!-- Transaction History -->
                <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] overflow-hidden border border-gray-100 dark:border-white/5 shadow-sm">
                    <div class="p-8 border-b border-gray-100 dark:border-white/5 flex items-center justify-between">
                        <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Historial de Transacciones</h4>
                        <div class="p-2 bg-gray-50 dark:bg-white/5 rounded-xl text-xs font-black text-gray-400 uppercase tracking-widest px-4">
                            Citas aprobadas
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Paciente</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha / Turno</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Monto</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                <tr v-for="tx in transactions" :key="tx.id" class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/5 flex items-center justify-center font-black text-gray-400 text-xs">
                                                {{ tx.patient_name.substring(0,2).toUpperCase() }}
                                            </div>
                                            <span class="text-sm font-black text-gray-900 dark:text-white">{{ tx.patient_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ tx.date }}</span>
                                            <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Turno #{{ tx.turn_number }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <span class="text-sm font-black text-gray-900 dark:text-white">{{ formatCurrency(tx.amount) }}</span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span :class="{
                                            'bg-emerald-500/10 text-emerald-500': tx.status === 'completed',
                                            'bg-blue-500/10 text-blue-500': tx.status === 'accepted'
                                        }" class="text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest">
                                            {{ tx.status === 'completed' ? 'Realizado' : 'Aceptado' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div v-if="transactions.length === 0" class="p-20 text-center">
                        <div class="w-16 h-16 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                             <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">No hay transacciones aprobadas aún</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
