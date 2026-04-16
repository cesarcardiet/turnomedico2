<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// Detect role from URL parameter
onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const roleParam = params.get('role');
    // Store role for display purposes if needed
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <PublicLayout>
        <Head title="Iniciar Sesión" />

        <div class="py-20 px-6">
            <div class="max-w-md mx-auto">
                <div class="text-center mb-10">
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">Iniciar Sesión</h1>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">Ingresa a tu cuenta de Turno Médico</p>
                </div>

                <div v-if="status" class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-100 dark:border-green-900 rounded-2xl">
                    <p class="text-sm font-bold text-green-600 dark:text-green-400">{{ status }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-8 md:p-10 rounded-[2.5rem] shadow-2xl shadow-indigo-100 dark:shadow-none border border-gray-100 dark:border-gray-700">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="email" value="Correo Electrónico" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3" />
                            <TextInput
                                id="email"
                                type="email"
                                class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-4 font-bold"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="tu@email.com"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Contraseña" class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3" />
                            <TextInput
                                id="password"
                                type="password"
                                class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-4 font-bold"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center cursor-pointer">
                                <Checkbox name="remember" v-model:checked="form.remember" class="rounded" />
                                <span class="ms-3 text-sm font-bold text-gray-600 dark:text-gray-400">Recordarme</span>
                            </label>
                        </div>

                        <div class="space-y-4">
                            <button
                                type="submit"
                                class="w-full py-5 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 transition shadow-xl shadow-indigo-100 dark:shadow-none uppercase tracking-widest"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Ingresando...' : 'Ingresar' }}
                            </button>

                            <div class="flex flex-col gap-3 text-center">
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition"
                                >
                                    ¿Olvidaste tu contraseña?
                                </Link>
                                
                                <Link
                                    :href="route('register')"
                                    class="text-xs font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition"
                                >
                                    ¿No tienes cuenta? Regístrate aquí
                                </Link>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
