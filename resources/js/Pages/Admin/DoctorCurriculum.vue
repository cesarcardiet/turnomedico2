<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    doctor: Object
});

const approve = () => {
    if (confirm('¿Aprobar a este médico? Podrá aparecer en la búsqueda pública.')) {
        router.post(route('admin.doctors.approve', props.doctor.id), {}, {
            onSuccess: () => router.visit(route('admin.doctors.index'))
        });
    }
};
</script>

<template>
    <Head :title="'Currículum – ' + (doctor?.user?.name || 'Médico')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.doctors.index')" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </Link>
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Currículum del médico</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Revisión para aprobación</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.doctors.index')" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        Volver a lista
                    </Link>
                    <button
                        v-if="!doctor.is_approved"
                        @click="approve"
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-900/20 transition"
                    >
                        Aprobar médico
                    </button>
                    <span v-else class="px-4 py-2 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-sm font-black">
                        Aprobado
                    </span>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-6">
                <div class="bg-white dark:bg-[#161920] rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-xl overflow-hidden">
                    <!-- Cabecera tipo CV -->
                    <div class="p-10 border-b border-gray-100 dark:border-white/5 bg-gradient-to-br from-indigo-50/80 to-white dark:from-indigo-900/10 dark:to-transparent">
                        <div class="flex flex-col sm:flex-row gap-8 items-start">
                            <div class="w-32 h-32 rounded-2xl bg-gray-200 dark:bg-gray-700 overflow-hidden shrink-0 border-4 border-white dark:border-gray-800 shadow-lg">
                                <img v-if="doctor.user?.profile_photo_url" :src="doctor.user.profile_photo_url" class="w-full h-full object-cover" :alt="doctor.user.name" />
                                <div v-else class="w-full h-full flex items-center justify-center text-4xl font-black text-gray-400 dark:text-gray-500">
                                    {{ doctor.user?.name?.charAt(0) || 'M' }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <h1 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight mb-1">{{ doctor.user?.name }}</h1>
                                <p class="text-indigo-600 dark:text-indigo-400 font-bold text-sm uppercase tracking-widest mb-4">{{ doctor.speciality?.name || 'Especialidad no asignada' }}</p>
                                <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <span>{{ doctor.user?.email }}</span>
                                    <span v-if="doctor.phone_number">{{ doctor.phone_number }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Secciones del currículum -->
                    <div class="p-10 space-y-10">
                        <section v-if="doctor.about" class="space-y-2">
                            <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-white/5 pb-2">Sobre el médico</h3>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ doctor.about }}</p>
                        </section>

                        <section class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div v-if="doctor.consultation_fee != null && doctor.consultation_fee !== ''" class="space-y-1">
                                <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Tarifa de consulta</h3>
                                <p class="text-gray-800 dark:text-gray-200 font-bold">${{ Number(doctor.consultation_fee).toLocaleString('es') }}</p>
                            </div>
                            <div v-if="doctor.working_hours" class="space-y-1">
                                <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Horario de trabajo</h3>
                                <p class="text-gray-700 dark:text-gray-300">{{ doctor.working_hours }}</p>
                            </div>
                            <div v-if="doctor.city" class="space-y-1">
                                <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Ciudad</h3>
                                <p class="text-gray-700 dark:text-gray-300">{{ doctor.city }}</p>
                            </div>
                        </section>

                        <section v-if="doctor.clinic_address" class="space-y-2">
                            <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-white/5 pb-2">Dirección de la clínica</h3>
                            <p class="text-gray-700 dark:text-gray-300">{{ doctor.clinic_address }}</p>
                        </section>

                        <section v-if="doctor.services_description" class="space-y-2">
                            <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-white/5 pb-2">Servicios</h3>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ doctor.services_description }}</p>
                        </section>

                        <section v-if="doctor.health_care_info" class="space-y-2">
                            <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-white/5 pb-2">Cuidado de la salud</h3>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ doctor.health_care_info }}</p>
                        </section>

                        <section v-if="doctor.bank_name || doctor.account_holder" class="space-y-3 pt-4 border-t border-gray-100 dark:border-white/5">
                            <h3 class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Datos bancarios (para pagos)</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div v-if="doctor.bank_name"><span class="text-gray-500">Banco:</span> <span class="text-gray-800 dark:text-gray-200 font-medium">{{ doctor.bank_name }}</span></div>
                                <div v-if="doctor.account_holder"><span class="text-gray-500">Titular:</span> <span class="text-gray-800 dark:text-gray-200 font-medium">{{ doctor.account_holder }}</span></div>
                                <div v-if="doctor.account_number"><span class="text-gray-500">Cuenta:</span> <span class="text-gray-800 dark:text-gray-200 font-medium">{{ doctor.account_number }}</span></div>
                                <div v-if="doctor.bank_swift_ifsc"><span class="text-gray-500">SWIFT/IFSC:</span> <span class="text-gray-800 dark:text-gray-200 font-medium">{{ doctor.bank_swift_ifsc }}</span></div>
                            </div>
                        </section>
                    </div>

                    <!-- Pie con acciones -->
                    <div class="p-10 bg-gray-50/50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Estado: <span :class="doctor.is_approved ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-amber-600 dark:text-amber-400 font-bold'">{{ doctor.is_approved ? 'Aprobado' : 'Pendiente de aprobación' }}</span>
                        </p>
                        <div class="flex items-center gap-3">
                            <Link :href="route('admin.doctors.index')" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 font-bold text-sm hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
                                Volver a lista de médicos
                            </Link>
                            <button
                                v-if="!doctor.is_approved"
                                @click="approve"
                                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-sm shadow-lg shadow-indigo-900/20 transition"
                            >
                                Aprobar médico
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
