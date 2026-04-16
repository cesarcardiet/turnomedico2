<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    doctor: Object,
    currentTurn: Object,
    upcomingTurns: Array,
    date: String
});

// Auto-refresh every 30 seconds
let refreshInterval = null;

onMounted(() => {
    refreshInterval = setInterval(() => {
        router.reload({ only: ['currentTurn', 'upcomingTurns'] });
    }, 30000); // 30 seconds
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});

const getInitial = (name) => name ? name.charAt(0).toUpperCase() : 'P';
</script>

<template>
    <Head :title="`Monitor de Turnos - ${doctor.user.name}`" />

    <div class="min-h-screen bg-gradient-to-br from-[#0f1115] via-[#1a1d24] to-[#0f1115] text-white font-sans overflow-hidden">
        <!-- Header -->
        <header class="bg-[#1e293b]/80 backdrop-blur-md border-b border-white/10 py-8 px-12">
            <div class="max-w-[1920px] mx-auto flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-indigo-500/30">
                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-4xl font-black uppercase tracking-tight">Monitor de Turnos</h1>
                        <p class="text-xl font-bold text-indigo-400 mt-1">{{ doctor.user.name }} - {{ doctor.speciality?.name }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-black uppercase tracking-wider text-gray-400">{{ date }}</p>
                    <p class="text-lg font-bold text-gray-500 mt-1">Actualización automática cada 30s</p>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-[1920px] mx-auto px-12 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Current Turn (Now Serving) -->
                <section class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-[3rem] p-16 shadow-2xl border-4 border-emerald-400/30 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-5xl font-black uppercase tracking-tight">Atendiendo Ahora</h2>
                        </div>

                        <div v-if="currentTurn" class="text-center py-12">
                            <div class="inline-block bg-white/20 backdrop-blur-md rounded-3xl px-16 py-8 mb-8 border-4 border-white/30">
                                <p class="text-9xl font-black tabular-nums tracking-tighter">{{ currentTurn.turn_number }}</p>
                            </div>
                            <p class="text-3xl font-bold opacity-90">Turno en progreso</p>
                        </div>

                        <div v-else class="text-center py-20">
                            <div class="w-32 h-32 bg-white/10 rounded-full mx-auto mb-8 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-4xl font-black uppercase opacity-60">Sin turno activo</p>
                            <p class="text-2xl font-bold opacity-40 mt-4">Esperando próximo paciente</p>
                        </div>
                    </div>
                </section>

                <!-- Upcoming Turns (Queue) -->
                <section class="bg-[#1e293b]/60 backdrop-blur-md rounded-[3rem] p-16 shadow-2xl border border-white/10">
                    <div class="flex items-center gap-4 mb-12">
                        <div class="w-16 h-16 bg-indigo-600/20 rounded-2xl flex items-center justify-center">
                            <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-5xl font-black uppercase tracking-tight">Próximos Turnos</h2>
                    </div>

                    <div v-if="upcomingTurns.length > 0" class="space-y-6">
                        <div 
                            v-for="(turn, index) in upcomingTurns" 
                            :key="turn.id"
                            class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10 hover:bg-white/10 transition-all duration-300"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-6">
                                    <div class="w-20 h-20 bg-indigo-600/20 rounded-xl flex items-center justify-center border-2 border-indigo-500/30">
                                        <p class="text-4xl font-black text-indigo-400 tabular-nums">{{ turn.turn_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-black uppercase tracking-tight">Turno {{ turn.turn_number }}</p>
                                        <p class="text-xl font-bold text-gray-400 mt-1">{{ turn.time_slot.start_time }}</p>
                                    </div>
                                </div>
                                <div class="px-6 py-3 bg-amber-500/20 rounded-xl border border-amber-500/30">
                                    <p class="text-lg font-black uppercase text-amber-400">En Espera</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-20">
                        <div class="w-32 h-32 bg-white/5 rounded-full mx-auto mb-8 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-black uppercase opacity-40">No hay turnos pendientes</p>
                    </div>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <footer class="fixed bottom-0 left-0 right-0 bg-[#1e293b]/80 backdrop-blur-md border-t border-white/10 py-6 px-12 w-full">
            <div class="flex items-center justify-between">
                <p class="text-lg font-bold text-gray-400">Sistema de Gestión de Turnos - Turno Médico</p>
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                    <p class="text-lg font-bold text-emerald-400">En Vivo</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
