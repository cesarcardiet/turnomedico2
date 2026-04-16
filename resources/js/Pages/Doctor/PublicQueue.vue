<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    doctor: Object,
    appointments: Array,
    today: String
});

const localAppointments = ref(props.appointments);
const currentTime = ref(new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }));
const audio = ref(null);

// Polling for updates
let pollInterval = null;

const playNotification = () => {
    if (audio.value) {
        audio.value.currentTime = 0;
        audio.value.play().catch(e => console.warn('TV Audio blocked:', e));
    }
};

const fetchUpdates = async () => {
    try {
        const response = await fetch(route('patient.api.public.queue', props.doctor.id));
        const data = await response.json();
        
        // Detect changes to play sound
        const newCalling = data.appointments.find(a => a.status === 'in_consultation');
        const oldCalling = localAppointments.value.find(a => a.status === 'in_consultation');
        
        const newWaitingCount = data.appointments.filter(a => a.status === 'accepted').length;
        const oldWaitingCount = localAppointments.value.filter(a => a.status === 'accepted').length;

        // Sound if someone new is called OR if someone new is added to the verified list
        if ((newCalling && (!oldCalling || newCalling.id !== oldCalling.id)) || (newWaitingCount > oldWaitingCount)) {
            playNotification();
        }

        localAppointments.value = data.appointments;
        currentTime.value = new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    } catch (e) {
        console.error('Error fetching queue updates:', e);
    }
};

onMounted(() => {
    audio.value = new Audio('/sounds/notification.mp3');
    pollInterval = setInterval(fetchUpdates, 5000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

// Logic categories
const callingNow = computed(() => {
    return localAppointments.value.find(a => a.status === 'in_consultation');
});

const waitingList = computed(() => {
    return localAppointments.value.filter(a => a.status === 'accepted');
});

const finishedList = computed(() => {
    return localAppointments.value.filter(a => a.status === 'completed').slice(0, 5);
});

const getInitial = (name) => name ? name.charAt(0).toUpperCase() : 'P';
</script>

<template>
    <Head :title="'Sala de Espera - Dr. ' + doctor.user.name" />

    <div class="min-h-screen bg-[#0a0c10] text-white overflow-hidden font-sans relative">
        <!-- Background Ambient Glows -->
        <div class="absolute top-0 left-0 w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[120px] -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-indigo-900/10 rounded-full blur-[150px] translate-x-1/3 translate-y-1/3"></div>

        <!-- Header TV Style -->
        <header class="h-16 bg-[#111419]/95 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 shadow-2xl relative z-50">
            <div class="flex items-center gap-4">
                <div class="relative shrink-0 flex items-center">
                    <div class="absolute inset-0 bg-indigo-500 blur-md opacity-20"></div>
                    <!-- Icono SVG Puro para evitar problemas de componentes/props -->
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center relative z-10">
                        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                            <circle cx="50" cy="50" r="45" stroke="white" stroke-width="8" stroke-linecap="round"/>
                            <rect x="42" y="25" width="16" height="50" rx="4" fill="white"/>
                            <rect x="25" y="42" width="50" height="16" rx="4" fill="white"/>
                            <path d="M20 50H32L38 35L47 65L53 50H80" stroke="rgba(255,255,255,0.4)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <div class="h-6 w-[1px] bg-white/10 mx-1"></div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-bold uppercase tracking-tight text-white leading-none">SALA DE ESPERA</h1>
                    <p class="text-[8px] font-bold text-indigo-400 uppercase tracking-[0.2em] mt-1 opacity-70">En vivo</p>
                </div>
            </div>

            <div class="flex items-center gap-8">
                <div class="text-right">
                    <p class="text-3xl font-black tracking-tight tabular-nums text-white leading-none">{{ currentTime }}</p>
                    <p class="text-[8px] font-bold text-gray-500 uppercase tracking-widest mt-1 flex items-center justify-end gap-2">
                        <span class="w-1 h-1 bg-indigo-500 rounded-full animate-pulse"></span>
                        {{ today }}
                    </p>
                </div>
            </div>
        </header>

        <main class="h-[calc(100vh-7rem)] p-6 grid grid-cols-12 gap-6 relative z-10">
            <!-- Left Side: Calling Now (The Star) -->
            <div class="col-span-12 lg:col-span-7 flex flex-col gap-10">
                <div class="flex-1 bg-[#161920]/40 rounded-[4rem] border border-white/5 flex flex-col items-center justify-center text-center shadow-[0_32px_64px_-16px_rgba(0,0,0,0.5)] relative overflow-hidden group p-12">
                    <!-- Glassmorphism Background -->
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-transparent"></div>
                    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] bg-indigo-500/10 rounded-full blur-[100px] animate-pulse"></div>
                    
                    <div class="relative z-10 w-full animate-float">
                        <div class="inline-flex items-center gap-3 px-8 py-3 bg-white/5 backdrop-blur-xl rounded-full border border-white/10 text-[11px] font-black uppercase tracking-[0.5em] mb-12 shadow-xl shadow-black/20">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping"></span>
                            Llamando ahora
                        </div>
                        
                        <div v-if="callingNow" class="animate-in fade-in zoom-in-sm duration-1000">
                            <div class="relative inline-block mb-6">
                                <p class="text-[16rem] leading-none font-black tracking-tighter drop-shadow-[0_20px_50px_rgba(79,70,229,0.4)] text-white italic">
                                    #{{ callingNow.turn_number }}
                                </p>
                                <div class="absolute inset-0 bg-indigo-500/20 blur-[80px] -z-10 animate-pulse"></div>
                            </div>
                            
                            <h2 class="text-6xl font-black uppercase tracking-tight mb-4 text-white">
                                {{ callingNow.patient_name }}
                            </h2>
                            <div class="h-[2px] w-24 bg-indigo-500/50 mx-auto mb-6 rounded-full"></div>
                            <p class="text-sm font-bold text-indigo-400 uppercase tracking-[0.5em] opacity-80 flex items-center justify-center gap-4">
                                <span class="w-8 h-[1px] bg-indigo-500/30"></span>
                                Dr. {{ doctor.user.name }}
                                <span class="w-8 h-[1px] bg-indigo-500/30"></span>
                            </p>
                        </div>
                        <div v-else class="py-24 opacity-30 flex flex-col items-center gap-8">
                            <div class="w-32 h-32 rounded-full border-4 border-dashed border-indigo-500/20 flex items-center justify-center animate-spin-slow">
                                <svg class="w-12 h-12 text-indigo-500/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-4xl font-black uppercase tracking-[0.4em]">Esperando paciente</p>
                                <p class="text-[11px] font-bold mt-4 uppercase tracking-[0.6em] text-indigo-400">Próximos turnos en la lista lateral</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doctor Mini Info Box -->
                <div class="bg-[#111419]/60 backdrop-blur-xl rounded-[3rem] p-8 border border-white/5 flex items-center justify-between shadow-xl">
                    <div class="flex items-center gap-8">
                        <div class="relative">
                            <div class="absolute inset-0 bg-indigo-500/20 blur-lg rounded-2xl"></div>
                            <div class="w-24 h-24 rounded-[2rem] bg-indigo-500/10 border border-white/10 flex items-center justify-center text-4xl font-black text-indigo-400 relative z-10 overflow-hidden">
                                <img v-if="doctor.user.profile_photo_url" :src="doctor.user.profile_photo_url" class="w-full h-full object-cover" />
                                <span v-else>{{ getInitial(doctor.user.name) }}</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-3xl font-black uppercase tracking-tight text-white mb-1">Dr. {{ doctor.user.name }}</h3>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 bg-indigo-500/10 rounded-full text-[10px] font-black text-indigo-400 uppercase tracking-widest border border-indigo-500/20">
                                    {{ doctor.speciality?.name }}
                                </span>
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">• Consultorio habilitado</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em] mb-1">SALA DE ESPERA</p>
                        <p class="text-2xl font-black text-white/20 uppercase tracking-tighter">VIP MEDICAL</p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Secondary Queues -->
            <div class="col-span-12 lg:col-span-5 flex flex-col gap-10">
                <!-- Upcoming -->
                <div class="flex-1 bg-[#111419]/40 backdrop-blur-3xl rounded-[4rem] p-10 border border-white/5 shadow-2xl flex flex-col overflow-hidden">
                    <div class="flex items-center justify-between mb-10">
                        <h3 class="text-[11px] font-black text-gray-500 uppercase tracking-[0.5em] flex items-center gap-4">
                            <span class="w-2.5 h-2.5 bg-amber-500 rounded-full animate-ping"></span>
                            Próximos en cola
                        </h3>
                        <span class="text-[10px] font-bold text-indigo-400/50 uppercase tracking-widest">{{ waitingList.length }} pacientes</span>
                    </div>
                    
                    <div class="space-y-5 overflow-y-auto custom-scrollbar pr-3 flex-1">
                        <div v-for="(apt, index) in waitingList" :key="apt.id" 
                             class="flex items-center justify-between p-7 bg-white/[0.03] hover:bg-white/[0.07] rounded-[2.5rem] border border-white/5 transition-all duration-500 group animate-stagger"
                             :style="{ '--delay': (index * 0.1) + 's' }">
                            <div class="flex items-center gap-8">
                                <div class="w-16 h-16 rounded-3xl bg-indigo-500/5 border border-white/5 flex items-center justify-center text-3xl font-black text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white transition-all duration-500">
                                    #{{ apt.turn_number }}
                                </div>
                                <div>
                                    <span class="text-xl font-black uppercase tracking-tight text-white/90 group-hover:text-white transition-colors">{{ apt.patient_name }}</span>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase mt-1 tracking-widest">Turno confirmado</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[11px] font-black text-indigo-400 tabular-nums">{{ apt.time }}</span>
                                <div class="w-8 h-1 bg-white/5 rounded-full overflow-hidden">
                                    <div class="w-1/3 h-full bg-indigo-500 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                        <div v-if="waitingList.length === 0" class="h-full flex flex-col items-center justify-center gap-6 border-2 border-dashed border-white/[0.03] rounded-[3rem] opacity-30">
                            <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <p class="text-[11px] font-black uppercase tracking-[0.4em]">Sin turnos pendientes</p>
                        </div>
                    </div>
                </div>

                <!-- Recently Finished -->
                <div class="h-1/3 bg-[#111419]/20 backdrop-blur-xl rounded-[4rem] p-10 border border-white/5 shadow-inner">
                    <h3 class="text-[11px] font-black text-gray-500 uppercase tracking-[0.5em] mb-8">Atendidos recientemente</h3>
                    <div class="flex flex-wrap gap-4">
                        <div v-for="apt in finishedList" :key="apt.id" class="pl-4 pr-6 py-3 bg-indigo-500/[0.03] rounded-full border border-indigo-500/10 flex items-center gap-4 hover:bg-indigo-500/5 transition-colors">
                            <span class="w-8 h-8 rounded-full bg-indigo-500 text-white font-black text-[10px] flex items-center justify-center shadow-lg shadow-indigo-500/20">
                                #{{ apt.turn_number }}
                            </span>
                            <span class="text-[11px] font-black uppercase tracking-tight text-gray-400">{{ apt.patient_name }}</span>
                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer Ticker -->
        <footer class="h-12 bg-indigo-600 fixed bottom-0 w-full flex items-center overflow-hidden border-t border-white/10 shadow-[0_-10px_40px_rgba(79,70,229,0.3)] z-50">
            <div class="whitespace-nowrap animate-ticker flex items-center text-xs font-black uppercase tracking-[0.2em] px-16 gap-32">
                <span class="flex items-center gap-4"><span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span> Sistema de turnos activo</span>
                <span class="text-indigo-100 opacity-80 flex items-center gap-4">Bienvenidos a TurnoMédico - Dr. {{ doctor.user.name }}</span>
                <span class="flex items-center gap-4">Favor de confirmar su llegada en recepción</span>
                <span class="text-indigo-100 opacity-80">Por favor, mantenga su ticket a mano</span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

@keyframes ticker {
    0% { transform: translateX(30%); }
    100% { transform: translateX(-150%); }
}
.animate-ticker {
    animation: ticker 35s linear infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) scale(1) rotate(0deg); }
    50% { transform: translateY(-15px) scale(1.01) rotate(0.3deg); }
}
.animate-float {
    animation: float 10s ease-in-out infinite;
}

@keyframes stagger {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-stagger {
    animation: stagger 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) both;
    animation-delay: var(--delay);
}

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 15s linear infinite;
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes zoom-in-sm {
    from { opacity: 0; transform: scale(0.97); }
    to { opacity: 1; transform: scale(1); }
}

.animate-in {
    animation-fill-mode: both;
}
.duration-1000 { animation-duration: 0.8s; }
.fade-in { animation-name: fade-in; }
.zoom-in-sm { animation-name: zoom-in-sm; }
</style>
