<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage();
const roles = (page.props.auth.user as any)?.roles || [];
console.log('User Roles:', roles);
console.log('Is Patient:', roles.includes('patient'));
const isPatient = computed(() => roles.includes('patient'));
const layout = computed(() => isPatient.value ? PatientLayout : AuthenticatedLayout);
</script>

<template>
    <Head title="Perfil" />

    <component :is="layout">
        <template #header v-if="!isPatient">
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Mi Perfil</h2>
            </div>
        </template>

        <div class="py-12" :class="{ 'px-0': isPatient }">
            <div class="max-w-7xl mx-auto px-6 space-y-8" :class="{ 'px-0': isPatient }">
                
                <!-- Profile Header Card -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl shadow-indigo-500/30 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full blur-2xl -ml-24 -mb-24"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                        <div class="w-32 h-32 rounded-[2rem] bg-white p-2 shadow-lg rotate-3 group hover:rotate-0 transition-all duration-300">
                            <div class="w-full h-full bg-gray-100 rounded-[1.5rem] overflow-hidden flex items-center justify-center">
                                <img v-if="($page.props.auth.user as any).profile_photo_url" :src="($page.props.auth.user as any).profile_photo_url" class="w-full h-full object-cover" />
                                <span v-else class="text-4xl font-black text-indigo-600 uppercase">{{ ($page.props.auth.user as any).name.charAt(0) }}</span>
                            </div>
                        </div>
                        <div class="text-center md:text-left">
                            <h3 class="text-3xl font-black uppercase tracking-tight mb-2">Configuración de Cuenta</h3>
                            <p class="text-indigo-100 font-medium max-w-xl text-sm leading-relaxed">
                                Administra tu información personal, seguridad y preferencias de la cuenta. 
                                Mantén tus datos actualizados para una mejor experiencia.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Update Profile Info -->
                    <div class="bg-white dark:bg-[#161920] p-8 md:p-10 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-white/5">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight">Información Personal</h3>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Actualiza tus datos básicos</p>
                            </div>
                        </div>
                        
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="w-full"
                        />
                    </div>

                    <!-- Update Password -->
                    <div class="bg-white dark:bg-[#161920] p-8 md:p-10 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-white/5">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2h2m2 4l2-2m0 0l-2-2m2 2H8m10-6V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight">Seguridad</h3>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Actualizar contraseña</p>
                            </div>
                        </div>

                        <UpdatePasswordForm class="w-full" />
                    </div>

                    <!-- Delete Account -->
                    <div class="lg:col-span-2 bg-rose-50 dark:bg-rose-900/10 p-8 md:p-10 rounded-[2.5rem] border border-rose-100 dark:border-rose-500/10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-rose-900/30 flex items-center justify-center text-rose-600 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight">Zona de Peligro</h3>
                                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Eliminar cuenta permanentemente</p>
                                </div>
                            </div>
                            
                            <div class="w-full md:w-auto">
                                <DeleteUserForm class="w-full" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
