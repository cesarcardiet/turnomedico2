<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subscription: Object,
    history: Array,
    payment_details: Object
});

const showCopyMessage = ref(false);
const lastCopiedLabel = ref('');

const copyToClipboard = (text, label) => {
    navigator.clipboard.writeText(text);
    lastCopiedLabel.value = label;
    showCopyMessage.value = true;
    setTimeout(() => {
        showCopyMessage.value = false;
    }, 2000);
};

const getStatusColor = (status) => {
    switch(status) {
        case 'pending': return 'bg-amber-500 text-white';
        case 'approved': return 'bg-emerald-500 text-white';
        case 'rejected': return 'bg-rose-500 text-white';
        default: return 'bg-gray-500 text-white';
    }
};

const getStatusLabel = (status) => {
    switch(status) {
        case 'pending': return 'Pendiente';
        case 'approved': return 'Activa';
        case 'rejected': return 'Rechazada';
        default: return status;
    }
};
</script>

<template>
    <Head title="Estado de Suscripción" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Estado de Suscripción</h2>
            </div>
        </template>

        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto space-y-10">
                <!-- Notification for Copy (New) -->
                <div v-if="showCopyMessage" class="fixed top-24 left-1/2 -translate-x-1/2 z-[60] animate-in slide-in-from-top-full duration-300">
                    <div class="bg-emerald-500 text-white px-6 py-2 rounded-full text-[9px] font-black uppercase tracking-widest shadow-xl flex items-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ lastCopiedLabel }} copiado
                    </div>
                </div>

                <!-- Current Status Card -->
                <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden animate-in fade-in duration-700">
                    <div class="bg-indigo-600 px-10 py-6 flex items-center justify-between">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Estado Actual
                        </h3>
                    </div>

                    <div class="p-10 text-center">
                        <div v-if="subscription.payment_status === 'pending'" class="bg-amber-50 dark:bg-amber-500/10 rounded-2xl p-8 mb-8 border border-amber-100 dark:border-amber-500/20">
                            <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center text-amber-500 mx-auto mb-6 shadow-xl">
                                <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h4 class="text-xl font-black text-amber-900 dark:text-amber-400 mb-2 uppercase tracking-tight">Suscripción Pendiente de Aprobación</h4>
                            <p class="text-amber-700 dark:text-amber-500/60 text-sm font-bold">Tu pago está siendo verificado. El tiempo de procesamiento es de 24-48 horas hábiles.</p>
                            
                            <!-- Payment Details if pending -->
                            <div v-if="payment_details && payment_details.bank_name" class="mt-8 p-8 bg-white dark:bg-[#1c2128] rounded-[2.5rem] border border-dashed border-indigo-500/30 text-left max-w-2xl mx-auto shadow-sm">
                                <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.2em] mb-6 text-center">Datos para Transferencia Bancaria</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div v-for="(value, key) in {
                                        'Banco': payment_details.bank_name,
                                        'Nro. de Cuenta': payment_details.account_number,
                                        'Tipo de Cuenta': payment_details.account_type,
                                        'Titular': payment_details.account_holder,
                                        'RNC / Cédula': payment_details.document_id,
                                        'Monto a Pagar': `$ ${Number(subscription.membership_plan.price).toLocaleString('es-ES')}`
                                    }" :key="key" class="flex items-center justify-between group/status-item relative">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ key }}</span>
                                            <span class="text-xs font-black" :class="key === 'Monto a Pagar' ? 'text-indigo-600' : 'text-gray-800 dark:text-gray-200'">{{ value }}</span>
                                        </div>
                                        <button 
                                            v-if="['Nro. de Cuenta', 'RNC / Cédula', 'Monto a Pagar'].includes(key)"
                                            type="button"
                                            @click="copyToClipboard(key === 'Monto a Pagar' ? subscription.membership_plan.price : value, key)"
                                            class="p-2 rounded-xl bg-indigo-500/10 text-indigo-500 transition-all hover:bg-indigo-600 hover:text-white shrink-0"
                                            title="Copiar"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H15m0 0l3-3m-3 3l3 3"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="subscription.payment_status === 'approved'" class="bg-emerald-50 dark:bg-emerald-500/10 rounded-2xl p-8 mb-8 border border-emerald-100 dark:border-emerald-500/20">
                            <div class="w-16 h-16 bg-white dark:bg-[#1c2128] rounded-2xl flex items-center justify-center text-emerald-500 mx-auto mb-6 shadow-xl">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h4 class="text-xl font-black text-emerald-900 dark:text-emerald-400 mb-2 uppercase tracking-tight">Suscripción Activa</h4>
                            <p class="text-emerald-700 dark:text-emerald-500/60 text-sm font-bold">¡Todo listo! Tienes acceso completo al panel de médicos.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-8 text-left max-w-2xl mx-auto">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Plan contratado</p>
                                <p class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ subscription.membership_plan.name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Fecha de pago</p>
                                <p class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ new Date(subscription.created_at).toLocaleDateString() }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Monto</p>
                                <p class="text-sm font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-tight">${{ subscription.membership_plan.price }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Referencia</p>
                                <p class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ subscription.reference_number }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Table -->
                <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-white/5 shadow-sm overflow-hidden animate-in slide-in-from-bottom duration-1000">
                    <div class="bg-[#1c2128] px-10 py-6 border-b border-white/5">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Historial de Suscripciones
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-white/5">
                                    <th class="px-10 py-6">Plan</th>
                                    <th class="px-6 py-6">Monto</th>
                                    <th class="px-6 py-6">Fecha</th>
                                    <th class="px-6 py-6">Estado</th>
                                    <th class="px-10 py-6">Referencia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="item in history" :key="item.id" class="text-sm text-gray-700 dark:text-gray-300">
                                    <td class="px-10 py-6 font-bold">{{ item.membership_plan.name }}</td>
                                    <td class="px-6 py-6 font-black text-indigo-600 dark:text-indigo-400">${{ item.membership_plan.price }}</td>
                                    <td class="px-6 py-6 font-medium">{{ new Date(item.created_at).toLocaleDateString() }}</td>
                                    <td class="px-6 py-6">
                                        <span :class="getStatusColor(item.payment_status)" class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                            {{ getStatusLabel(item.payment_status) }}
                                        </span>
                                    </td>
                                    <td class="px-10 py-6 font-mono text-xs opacity-50">{{ item.reference_number }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer CTAs -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center py-8">
                    <Link
                        v-if="subscription.payment_status === 'approved'"
                        :href="route('doctor.dashboard')"
                        class="px-8 py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-900/20 hover:bg-indigo-700 transition"
                    >
                        Ir al Panel de Control
                    </Link>
                    <Link
                        :href="route('welcome')"
                        class="px-8 py-4 bg-[#161920] border border-white/5 text-gray-400 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:text-white transition"
                    >
                        Volver al Inicio
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-delay: 0.1s;
}
</style>
