<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subscriptions: Array
});

const showImageModal = ref(false);
const selectedImageUrl = ref(null);
const imageLoadError = ref(false);
const imageLoading = ref(true);

const getProofUrl = (sub) => {
    if (sub.payment_proof_url) return sub.payment_proof_url;
    if (sub.payment_proof) return (typeof window !== 'undefined' ? window.location.origin : '') + '/storage/' + sub.payment_proof;
    return null;
};

const openProof = (sub) => {
    const url = typeof sub === 'string' ? sub : getProofUrl(sub);
    if (!url) return;
    selectedImageUrl.value = url;
    imageLoadError.value = false;
    imageLoading.value = true;
    showImageModal.value = true;
};

const onImageError = () => {
    imageLoadError.value = true;
    imageLoading.value = false;
};

const onImageLoad = () => {
    imageLoadError.value = false;
    imageLoading.value = false;
};

const closeModal = () => {
    showImageModal.value = false;
    selectedImageUrl.value = null;
    imageLoadError.value = false;
};

const approve = (id) => {
    if (confirm('¿Deseas confirmar la recepción del pago y activar esta suscripción?')) {
        router.post(route('admin.subscriptions.approve', id));
    }
};

const reject = (id) => {
    if (confirm('¿Deseas rechazar esta suscripción?')) {
        router.post(route('admin.subscriptions.reject', id));
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Pagos de Suscripciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Pagos de Suscripciones</h2>
                </div>
                <div class="flex gap-4">
                    <button class="px-6 py-2 bg-indigo-500/10 text-indigo-500 rounded-xl text-[10px] font-black uppercase tracking-widest border border-indigo-500/20">Aprobados</button>
                    <button class="px-6 py-2 bg-rose-500/10 text-rose-500 rounded-xl text-[10px] font-black uppercase tracking-widest border border-rose-500/20">Rechazados</button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Analytics Summary (Optional but matches UI spirit) -->
                <div v-if="subscriptions.filter(s => s.payment_status === 'pending').length > 0" class="mb-10 bg-indigo-500/10 border border-indigo-500/20 rounded-2xl p-6 flex items-center gap-4">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Atención Requerida</p>
                        <!-- Cambiamos el texto a verde para mejor contraste (como los montos) -->
                        <p class="text-xs font-bold text-emerald-400">Hay {{ subscriptions.filter(s => s.payment_status === 'pending').length }} pagos pendientes de verificación.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-white/5">
                                    <th class="px-10 py-8">ID</th>
                                    <th class="px-6 py-8">Doctor</th>
                                    <th class="px-6 py-8">Plan</th>
                                    <th class="px-6 py-8">Monto</th>
                                    <th class="px-6 py-8">Referencia</th>
                                    <th class="px-6 py-8">Fecha</th>
                                    <th class="px-6 py-8 text-center">Comprobante</th>
                                    <th class="px-10 py-8 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <tr v-for="sub in subscriptions" :key="sub.id" class="group hover:bg-white/[0.02] transition-colors">
                                    <td class="px-10 py-8">
                                        <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 rounded-lg text-[10px] font-black">#{{ sub.id }}</span>
                                    </td>
                                    <td class="px-6 py-8">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-white hover:text-indigo-400 transition cursor-pointer">{{ sub.user.name }}</span>
                                            <span class="text-[10px] font-bold text-gray-500 truncate w-32">{{ sub.user.email }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-8">
                                        <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                            {{ Math.round(sub.membership_plan.duration_days / 30) }} {{ Math.round(sub.membership_plan.duration_days / 30) == 1 ? 'mes' : 'meses' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-8 font-black text-indigo-400 leading-tight">
                                        <div class="flex flex-col" v-if="Number(sub.membership_plan.price) === 0">
                                            <span class="text-sm font-black text-gray-500 dark:text-gray-400">Plan gratuito</span>
                                        </div>
                                        <div v-else class="flex flex-col">
                                            <span class="text-lg font-black text-emerald-400">${{ Number(sub.membership_plan.price).toLocaleString('es-ES') }}</span>
                                            <span class="text-[9px] text-gray-500 font-bold uppercase tracking-widest">${{ Number(sub.membership_plan.price / (sub.membership_plan.duration_days / 30)).toFixed(0) }} p/m</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-8 font-mono text-indigo-400 text-xs font-bold">{{ sub.reference_number }}</td>
                                    <td class="px-6 py-8 text-[11px] font-bold text-gray-400">{{ formatDate(sub.created_at) }}</td>
                                    <td class="px-6 py-8 text-center">
                                        <template v-if="Number(sub.membership_plan?.price) === 0">
                                            <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest">—</span>
                                        </template>
                                        <template v-else-if="sub.payment_proof || sub.payment_proof_url">
                                            <button type="button" @click.prevent="openProof(sub)" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-100 hover:bg-indigo-200 border border-indigo-200 rounded-xl text-indigo-700 transition group/proof">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                <span class="text-[9px] font-black uppercase tracking-widest">Ver comprobante (aquí)</span>
                                            </button>
                                        </template>
                                        <span v-else class="text-[9px] font-black text-gray-500 uppercase tracking-widest">N/A</span>
                                    </td>
                                    <td class="px-10 py-8 text-right">
                                        <div v-if="sub.payment_status === 'pending'" class="flex items-center justify-end gap-2">
                                            <button @click="approve(sub.id)" class="w-10 h-10 bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 rounded-xl flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </button>
                                            <button @click="reject(sub.id)" class="w-10 h-10 bg-rose-500/10 text-rose-500 border border-rose-500/20 rounded-xl flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        <div v-else class="flex flex-col items-end">
                                            <span :class="sub.payment_status === 'approved' ? 'text-emerald-500' : 'text-rose-500'" class="text-[9px] font-black uppercase tracking-[0.2em]">
                                                {{ sub.payment_status === 'approved' ? 'Aprobado' : 'Rechazado' }}
                                            </span>
                                            <p class="text-[8px] font-black text-gray-700 uppercase tracking-widest mt-1">Acción Finalizada</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal ver comprobante (en la misma página) -->
        <Teleport to="body">
            <div v-if="showImageModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-6 bg-black/85" @click.self="closeModal">
                <div class="relative max-w-4xl w-full min-h-[200px] max-h-full flex flex-col items-center justify-center bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="absolute top-4 right-4 flex items-center gap-2 z-10">
                        <a v-if="selectedImageUrl" :href="selectedImageUrl" target="_blank" rel="noopener" class="px-4 py-2 bg-gray-100 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-black uppercase">Abrir en nueva pestaña</a>
                        <button type="button" @click="closeModal" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-full text-gray-800" aria-label="Cerrar">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <p class="absolute top-4 left-4 text-sm font-black text-gray-800">Comprobante</p>
                    <div class="flex-1 flex items-center justify-center p-12 pt-16 w-full relative">
                        <div v-if="imageLoading && !imageLoadError" class="absolute inset-0 flex items-center justify-center bg-white/90 z-[1]">
                            <div class="animate-pulse text-gray-500 font-bold uppercase text-sm">Cargando imagen...</div>
                        </div>
                        <div v-else-if="imageLoadError" class="text-center">
                            <p class="text-rose-600 font-bold mb-4">No se pudo cargar la imagen.</p>
                            <p class="text-gray-500 text-xs mb-4">En el servidor ejecuta: php artisan storage:link</p>
                            <a :href="selectedImageUrl" target="_blank" rel="noopener" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-xl font-black uppercase text-sm hover:bg-indigo-700">Abrir enlace directo</a>
                        </div>
                        <img
                            v-show="!imageLoadError && selectedImageUrl"
                            :src="selectedImageUrl"
                            alt="Comprobante de pago"
                            class="max-w-full max-h-[75vh] w-auto h-auto object-contain rounded-lg"
                            @load="onImageLoad"
                            @error="onImageError"
                        />
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
