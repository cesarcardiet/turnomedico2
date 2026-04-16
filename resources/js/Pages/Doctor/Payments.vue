<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    pendingPayments: Array
});

const showModal = ref(false);
const selectedProof = ref(null);

const openProof = (path) => {
    selectedProof.value = path;
    showModal.value = true;
};

const approve = (id) => {
    if (confirm('¿Confirmas que has recibido el dinero en tu cuenta bancaria?')) {
        router.post(route('doctor.payments.approve', id));
    }
};

const reject = (id) => {
    if (confirm('¿Deseas rechazar este pago? El turno será liberado.')) {
        router.post(route('doctor.payments.reject', id));
    }
};
</script>

<template>
    <Head title="Verificación de Pagos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Verificación de pagos</h2>
            </div>
        </template>

        <div class="py-12 bg-[#fdf8f5] dark:bg-[#0c0e12] min-h-screen">
            <div class="max-w-7xl mx-auto px-6">
                
                <div v-if="pendingPayments.length === 0" class="bg-white dark:bg-[#161920] rounded-[2.5rem] p-20 border border-white/5 shadow-2xl text-center">
                    <div class="w-20 h-20 bg-emerald-500/10 rounded-3xl flex items-center justify-center text-emerald-500 mx-auto mb-8 shadow-xl">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">No hay pagos pendientes</h3>
                    <p class="text-gray-500 font-bold text-sm uppercase tracking-widest text-[10px]">Todos los pagos de tus pacientes están al día.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="payment in pendingPayments" :key="payment.id" 
                        class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-white/5 shadow-2xl p-8 hover:scale-[1.02] transition-all duration-500 relative overflow-hidden group">
                        
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 blur-3xl -mr-16 -mt-16"></div>

                        <div class="flex items-center justify-between mb-8">
                            <span class="bg-amber-500/10 text-amber-500 border border-amber-500/20 px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest">
                                Pago por verficar
                            </span>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">T#{{ payment.turn_number || payment.id }}</span>
                        </div>

                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 font-black text-lg shadow-lg">
                                {{ payment.user.name.charAt(0) }}
                            </div>
                            <div>
                                <h4 class="text-lg font-black text-gray-900 dark:text-white leading-tight uppercase tracking-tight">{{ payment.user.name }}</h4>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Cita: {{ payment.time_slot.date }}</p>
                            </div>
                        </div>

                        <!-- Proof Preview -->
                        <div class="mb-8 relative rounded-2xl overflow-hidden aspect-video bg-gray-100 border border-gray-200 cursor-pointer group/img" @click="openProof(payment.payment_proof_url || ('/storage/' + payment.payment_proof))">
                            <img :src="payment.payment_proof_url || ('/storage/' + payment.payment_proof)" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="Comprobante" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="bg-white text-black text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-widest">Ver Comprobante</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button @click="approve(payment.id)" class="bg-emerald-500 text-white font-black text-[10px] uppercase tracking-widest py-4 rounded-xl hover:bg-emerald-600 transition shadow-xl shadow-emerald-900/20">Aprobar</button>
                            <button @click="reject(payment.id)" class="bg-rose-500/10 text-rose-500 border border-rose-500/20 font-black text-[10px] uppercase tracking-widest py-4 rounded-xl hover:bg-rose-500 hover:text-white transition">Rechazar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-black/90 backdrop-blur-sm" @click="showModal = false">
            <div class="relative max-w-4xl w-full h-full flex items-center justify-center" @click.stop>
                <img :src="selectedProof" class="max-w-full max-h-full rounded-2xl shadow-2xl object-contain" alt="Comprobante" />
                <button type="button" @click="showModal = false" class="absolute top-4 right-4 p-2 bg-white rounded-full text-gray-800 shadow-lg hover:bg-gray-100">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
