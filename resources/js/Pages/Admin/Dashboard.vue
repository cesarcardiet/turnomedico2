<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    latest_appointments: Array
});
</script>

<template>
    <Head title="Panel de Administración" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Panel de Control</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                    <!-- Total Citas -->
                    <div class="bg-indigo-50/50 dark:bg-indigo-500/5 rounded-[2.5rem] p-8 border border-indigo-100 dark:border-indigo-500/10 flex items-center gap-6 group hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-900/10 group-hover:rotate-6 transition">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[32px] font-black text-gray-900 dark:text-white leading-none">{{ stats.total_appointments }}</p>
                            <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mt-1">Total Citas</p>
                        </div>
                    </div>

                    <!-- Médicos Activos -->
                    <div class="bg-emerald-50/50 dark:bg-emerald-500/5 rounded-[2.5rem] p-8 border border-emerald-100 dark:border-emerald-500/10 flex items-center gap-6 group hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center shadow-xl shadow-emerald-900/10 group-hover:rotate-6 transition">
                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[32px] font-black text-gray-900 dark:text-white leading-none">{{ stats.total_doctors }}</p>
                            <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mt-1">Médicos</p>
                        </div>
                    </div>

                    <!-- Pacientes -->
                    <div class="bg-purple-50/50 dark:bg-purple-500/5 rounded-[2.5rem] p-8 border border-purple-100 dark:border-purple-500/10 flex items-center gap-6 group hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center shadow-xl shadow-purple-900/10 group-hover:rotate-6 transition">
                            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[32px] font-black text-gray-900 dark:text-white leading-none">{{ stats.total_patients }}</p>
                            <p class="text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-widest mt-1">Pacientes</p>
                        </div>
                    </div>

                    <!-- Doctores Pendientes -->
                    <div class="bg-amber-50/50 dark:bg-amber-500/5 rounded-[2.5rem] p-8 border border-amber-100 dark:border-amber-500/10 flex items-center gap-6 group hover:scale-[1.02] transition-all duration-500 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center shadow-xl shadow-amber-900/10 group-hover:rotate-6 transition">
                            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[32px] font-black text-gray-900 dark:text-white leading-none">{{ stats.pending_doctors }}</p>
                            <p class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest mt-1">Validar Médicos</p>
                        </div>
                    </div>
                </div>

                <!-- Latest Activity Table -->
                <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden">
                    <div class="p-10 border-b border-white/5">
                        <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                            Últimas Citas Registradas
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-white/5">
                                    <th class="px-10 py-8">Paciente</th>
                                    <th class="px-6 py-8">Médico</th>
                                    <th class="px-6 py-8">Fecha</th>
                                    <th class="px-6 py-8">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="apt in latest_appointments" :key="apt.id" class="group hover:bg-white/[0.02] transition-colors">
                                    <td class="px-10 py-8">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-500 font-black">
                                                {{ apt.user.name.charAt(0) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-black text-gray-900 dark:text-white">{{ apt.user.name }}</span>
                                                <span class="text-[10px] font-bold text-gray-500">{{ apt.user.email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-8">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Dr. {{ apt.doctor_profile?.user.name }}</span>
                                    </td>
                                    <td class="px-6 py-8">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-gray-700 dark:text-gray-300">{{ new Date(apt.created_at).toLocaleDateString() }}</span>
                                            <span class="text-[10px] font-bold text-gray-500">{{ new Date(apt.created_at).toLocaleTimeString().substring(0,5) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-8">
                                        <span :class="{
                                            'px-4 py-1.5 border rounded-full text-[9px] font-black uppercase tracking-widest': true,
                                            'bg-amber-500/10 text-amber-500 border-amber-500/20': apt.status === 'pending',
                                            'bg-emerald-500/10 text-emerald-500 border-emerald-500/20': apt.status === 'accepted',
                                            'bg-indigo-500/10 text-indigo-500 border-indigo-500/20': apt.status === 'completed',
                                            'bg-rose-500/10 text-rose-500 border-rose-500/20': apt.status === 'rejected'
                                        }">
                                            {{ apt.status === 'pending' ? 'Pendiente' : (apt.status === 'accepted' ? 'Aprobada' : (apt.status === 'completed' ? 'Completada' : 'Rechazada')) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="latest_appointments.length === 0">
                                    <td colspan="4" class="px-10 py-20 text-center">
                                        <div class="w-16 h-16 bg-gray-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6 text-gray-500">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">No hay citas registradas recientemente</p>
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
