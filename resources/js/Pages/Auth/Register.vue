<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'patient', // Default to patient
});

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const roleParam = params.get('role');
    if (roleParam === 'doctor' || roleParam === 'patient') {
        form.role = roleParam;
    }
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <PublicLayout>
        <Head title="Registrarse" />

        <div class="py-20 px-6">
            <div class="max-w-xl mx-auto">
                <div class="text-center mb-10">
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">Crear Cuenta Nueva</h1>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">
                        Únete a Turno Médico como <span class="text-indigo-600 font-black uppercase tracking-widest text-sm">{{ form.role === 'doctor' ? 'Médico' : 'Paciente' }}</span>
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-8 md:p-12 rounded-[2.5rem] shadow-2xl shadow-indigo-100 dark:shadow-none border border-gray-100 dark:border-gray-700">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Nombre Completo" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3" />
                            <TextInput
                                id="name"
                                type="text"
                                class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-4 font-bold"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Ej. Juan Pérez"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Correo Electrónico" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3" />
                            <TextInput
                                id="email"
                                type="email"
                                class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-4 font-bold"
                                v-model="form.email"
                                required
                                autocomplete="username"
                                placeholder="juan@ejemplo.com"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="password" value="Contraseña" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-4 font-bold"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div>
                                <InputLabel
                                    for="password_confirmation"
                                    value="Confirmar Contraseña"
                                    class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3"
                                />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-4 font-bold"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.password_confirmation"
                                />
                            </div>
                        </div>

                        <div class="pt-4 space-y-4">
                            <button
                                type="submit"
                                class="w-full py-5 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 dark:shadow-none uppercase tracking-widest"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Procesando...' : 'Crear mi Cuenta' }}
                            </button>

                            <div class="text-center">
                                <Link
                                    :href="route('login')"
                                    class="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition"
                                >
                                    ¿Ya tienes una cuenta? Ingresa aquí
                                </Link>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Footer note for doctors -->
                <div v-if="form.role === 'doctor'" class="mt-8 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900 p-6 rounded-2xl">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-amber-900 dark:text-amber-400 uppercase tracking-widest mb-2">Nota Importante</h4>
                            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                                Los perfiles médicos deben ser aprobados por el administrador antes de ser públicos. Recibirás una notificación cuando tu perfil sea verificado.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
