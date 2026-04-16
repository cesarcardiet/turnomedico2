<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plans: Array,
    payment_details: Object,
});

const selectedPlan = ref(null);
const showModal = ref(false);
const showFreePlanModal = ref(false);
const showCopyMessage = ref(false);
const lastCopiedLabel = ref('');
const currentStep = ref(1);

const form = useForm({
    membership_plan_id: '',
    reference_number: '',
    payment_proof: null,
    notes: ''
});

const isFreePlan = (plan) => plan && Number(plan.price) === 0;

const openPaymentModal = (plan) => {
    selectedPlan.value = plan;
    form.membership_plan_id = plan.id;
    form.reference_number = '';
    form.payment_proof = null;
    form.notes = '';
    currentStep.value = 1;
    if (isFreePlan(plan)) {
        showFreePlanModal.value = true;
        showModal.value = false;
    } else {
        showModal.value = true;
        showFreePlanModal.value = false;
    }
};

const submitFreePlan = () => {
    form.transform((data) => ({ membership_plan_id: data.membership_plan_id }))
        .post(route('doctor.membership.subscribe'), {
            onSuccess: () => {
                showFreePlanModal.value = false;
                form.reset();
            }
        });
};

const copyToClipboard = (text, label) => {
    navigator.clipboard.writeText(text);
    lastCopiedLabel.value = label;
    showCopyMessage.value = true;
    setTimeout(() => {
        showCopyMessage.value = false;
    }, 2000);
};

const submit = () => {
    form.post(route('doctor.membership.subscribe'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Comprar Plan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col items-center text-center py-10">
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-4 italic">Comprar Plan</h1>
                <div class="flex items-center gap-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <span>Inicio</span>
                    <span class="opacity-20">/</span>
                    <span class="text-indigo-600">Comprar Plan</span>
                </div>
            </div>
        </template>

        <div class="py-12 max-w-7xl mx-auto px-6">
            <!-- Pricing Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="plan in plans" :key="plan.id" class="relative group">
                    <div class="bg-white dark:bg-[#161920] rounded-[3rem] p-10 border border-white/5 shadow-xl hover:shadow-indigo-500/10 transition-all duration-500 flex flex-col items-center text-center h-full group-hover:-translate-y-2">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-600/5 rounded-bl-[100%] transition-all group-hover:scale-110"></div>
                        
                        <div class="mb-8">
                            <span class="text-4xl font-black text-gray-900 dark:text-white">$ {{ Number(plan.price).toLocaleString('es-ES') }}</span>
                            <span class="text-gray-400 text-sm font-bold ml-1">/ {{ Math.round(plan.duration_days / 30) }} {{ Math.round(plan.duration_days / 30) == 1 ? 'mes' : 'meses' }}</span>
                        </div>

                        <ul class="space-y-4 mb-10 flex-1">
                            <li v-for="i in 4" :key="i" class="flex items-center gap-2 text-[11px] font-bold text-gray-500 uppercase tracking-tight">
                                <div class="w-4 h-4 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-500">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                {{ plan.description }}
                            </li>
                        </ul>

                        <button 
                            @click="openPaymentModal(plan)"
                            class="w-14 h-14 rounded-full flex items-center justify-center transition-all group/btn"
                            :class="isFreePlan(plan) 
                                ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white' 
                                : 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white'"
                        >
                            <svg class="w-6 h-6 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path></svg>
                        </button>
                        <p v-if="isFreePlan(plan)" class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mt-2">Plan gratuito</p>
                    </div>
                </div>
            </div>

            <!-- Payment Modal -->
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center px-6 bg-slate-900/40 backdrop-blur-sm p-4">
                <div class="relative bg-white dark:bg-[#161920] w-full max-w-xl rounded-[2.5rem] shadow-2xl border border-white/5 overflow-hidden animate-in zoom-in duration-300">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                    
                    <div class="absolute top-6 right-6">
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-10">
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-8 flex items-center gap-3">
                            <div class="w-1.5 h-7 bg-indigo-600 rounded-full"></div>
                            Comprar Plan
                        </h2>
                        
                        <div class="flex items-center justify-between mb-8 p-6 bg-gray-50 dark:bg-[#1c2128] rounded-2xl border border-white/5">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $page.props.auth.user.name }}</p>
                                <p class="text-xs font-bold text-gray-600 dark:text-gray-400">{{ selectedPlan?.name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Detalles del Plan:</p>
                                <p class="text-sm font-black text-indigo-500">${{ Number(selectedPlan?.price).toLocaleString('es-ES') }}/{{ Math.round(selectedPlan?.duration_days / 30) }} {{ Math.round(selectedPlan?.duration_days / 30) == 1 ? 'mes' : 'meses' }}</p>
                            </div>
                        </div>

                        <!-- Step Indicator -->
                        <div class="flex items-center gap-4 mb-8">
                            <div class="flex-1 h-1.5 rounded-full transition-all" :class="currentStep >= 1 ? 'bg-indigo-600' : 'bg-gray-100 dark:bg-gray-800'"></div>
                            <div class="flex-1 h-1.5 rounded-full transition-all" :class="currentStep >= 2 ? 'bg-indigo-600' : 'bg-gray-100 dark:bg-gray-800'"></div>
                        </div>

                        <div class="max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                            <!-- STEP 1: PAYMENT DETAILS -->
                            <div v-if="currentStep === 1" class="space-y-8 animate-in slide-in-from-right-4 duration-500">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 block pl-1">Método de Pago</label>
                                    <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-2xl p-5 flex gap-4 items-center">
                                        <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white shrink-0 shadow-lg shadow-indigo-900/20">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <p class="text-[10px] font-bold text-indigo-400 leading-relaxed uppercase">
                                            <span class="font-black text-gray-900 dark:text-white">Pago Manual:</span> Sigue los pasos para realizar tu transferencia bancaria.
                                        </p>
                                    </div>
                                </div>

                                <!-- Payment Details (Grid) -->
                                <div v-if="payment_details && payment_details.bank_name" class="p-8 bg-slate-50 dark:bg-[#1c2128] rounded-[2.5rem] border border-dashed border-indigo-500/30">
                                    <p class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.2em] mb-8 text-center">Datos Bancarios para Transferencia</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                                        <div v-for="(value, key) in {
                                            'Banco': payment_details.bank_name,
                                            'Nro. de Cuenta': payment_details.account_number,
                                            'Tipo': payment_details.account_type,
                                            'Titular': payment_details.account_holder,
                                            'RNC / Cédula': payment_details.document_id,
                                            'Monto a Pagar': `$ ${Number(selectedPlan?.price).toLocaleString('es-ES')}`
                                        }" :key="key" class="flex flex-col gap-1 group/item relative">
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ key }}</span>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-black" :class="key === 'Monto a Pagar' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-800 dark:text-gray-200'">{{ value }}</span>
                                                <button 
                                                    v-if="['Nro. de Cuenta', 'RNC / Cédula', 'Monto a Pagar'].includes(key)"
                                                    type="button"
                                                    @click="copyToClipboard(key === 'Monto a Pagar' ? selectedPlan?.price : value, key)"
                                                    class="p-2 rounded-xl bg-indigo-500/10 text-indigo-500 transition-all hover:bg-indigo-600 hover:text-white shrink-0"
                                                    title="Copiar"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H15m0 0l3-3m-3 3l3 3"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button 
                                    @click="currentStep = 2"
                                    class="w-full bg-indigo-600 text-white font-black rounded-2xl py-5 flex items-center justify-center gap-3 uppercase text-[10px] tracking-[0.2em] shadow-xl shadow-indigo-900/30 hover:bg-indigo-700 transition active:scale-95"
                                >
                                    Siguiente Paso: Subir Comprobante
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path></svg>
                                </button>
                            </div>

                            <!-- STEP 2: FORM SUBMISSION -->
                            <form v-if="currentStep === 2" @submit.prevent="submit" class="space-y-6 animate-in slide-in-from-right-4 duration-500">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block pl-1">Número de Referencia:</label>
                                    <input 
                                        v-model="form.reference_number"
                                        type="text" 
                                        required
                                        placeholder="Nro. de transferencia o depósito"
                                        class="w-full bg-gray-50 dark:bg-[#1c2128] border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white text-xs font-bold p-5 focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-inner"
                                        :class="{'border-rose-500': form.errors.reference_number}"
                                    >
                                    <div v-if="form.errors.reference_number" class="text-rose-500 text-[9px] font-black mt-2 uppercase tracking-widest pl-1">{{ form.errors.reference_number }}</div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block pl-1">Comprobante de Pago:</label>
                                    <div class="relative group">
                                        <input 
                                            type="file" 
                                            required
                                            @input="form.payment_proof = $event.target.files[0]"
                                            class="w-full bg-gray-50 dark:bg-[#1c2128] border-gray-100 dark:border-white/5 rounded-2xl text-gray-400 text-[10px] font-black p-5 file:bg-indigo-600 file:border-none file:text-white file:px-6 file:py-2 file:rounded-xl file:mr-4 file:uppercase file:tracking-widest file:cursor-pointer shadow-inner"
                                            :class="{'border-rose-500': form.errors.payment_proof}"
                                        >
                                    </div>
                                    <div v-if="form.errors.payment_proof" class="text-rose-500 text-[9px] font-black mt-2 uppercase tracking-widest pl-1">{{ form.errors.payment_proof }}</div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block pl-1">Notas (Opcional):</label>
                                    <textarea 
                                        v-model="form.notes"
                                        rows="3"
                                        placeholder="¿Algún detalle extra sobre tu transferencia?"
                                        class="w-full bg-gray-50 dark:bg-[#1c2128] border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white text-xs font-bold p-5 focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-inner"
                                    ></textarea>
                                </div>

                                <div class="flex gap-4">
                                    <button 
                                        type="button"
                                        @click="currentStep = 1"
                                        class="px-8 bg-gray-100 dark:bg-gray-800 text-gray-500 font-black rounded-2xl uppercase text-[10px] tracking-widest hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                                    >
                                        Atrás
                                    </button>
                                    <button 
                                        type="submit" 
                                        :disabled="form.processing"
                                        class="flex-1 bg-indigo-600 text-white font-black rounded-2xl py-5 flex items-center justify-center gap-3 uppercase text-[10px] tracking-[0.2em] shadow-xl shadow-indigo-900/30 hover:bg-indigo-700 transition active:scale-95 disabled:opacity-50"
                                    >
                                        <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ form.processing ? 'Procesando Envío...' : 'Enviar Comprobante' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal plan gratuito: solo confirmar activación -->
            <div v-if="showFreePlanModal && selectedPlan" class="fixed inset-0 z-[100] flex items-center justify-center px-6 bg-slate-900/40 backdrop-blur-sm">
                <div class="relative bg-white dark:bg-[#161920] w-full max-w-md rounded-[2.5rem] shadow-2xl border border-white/5 p-10">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">Activar plan gratuito</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">{{ selectedPlan.name }} — Un administrador aprobará tu solicitud.</p>
                    <div class="flex gap-4">
                        <button type="button" @click="showFreePlanModal = false" class="flex-1 py-3 rounded-2xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-400 font-bold uppercase text-xs">Cancelar</button>
                        <button type="button" @click="submitFreePlan" :disabled="form.processing" class="flex-1 py-3 bg-emerald-600 text-white rounded-2xl font-black uppercase text-xs hover:bg-emerald-700 disabled:opacity-50">
                            {{ form.processing ? 'Enviando...' : 'Activar plan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
</style>
