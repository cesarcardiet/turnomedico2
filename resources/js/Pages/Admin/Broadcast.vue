<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: Array
});

const form = useForm({
    role: 'all',
    title: '',
    message: '',
});

const submit = () => {
    form.post(route('admin.broadcast.send'), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <Head title="Difusión de Notificaciones Masivas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-2xl text-gray-800 dark:text-gray-200 leading-tight tracking-tight">
                Difusión de Mensajes Masivos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-10 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <form @submit.prevent="submit" class="space-y-8">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Audiencia Objetivo</label>
                            <select v-model="form.role" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-4 font-bold">
                                <option v-for="role in roles" :key="role" :value="role">
                                    {{ role === 'all' ? 'Todos los Usuarios' : (role === 'doctor' ? 'Médicos' : (role === 'patient' ? 'Pacientes' : 'Administradores')) }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Título de la Notificación</label>
                            <input v-model="form.title" type="text" placeholder="Ej. Mantenimiento programado del sistema" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-4 font-bold" />
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Contenido del Mensaje</label>
                            <textarea v-model="form.message" rows="6" placeholder="Escribe aquí el anuncio importante para la comunidad..." class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 p-5 font-medium leading-relaxed"></textarea>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full py-5 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 transition shadow-xl shadow-indigo-200 dark:shadow-none uppercase tracking-widest"
                        >
                            {{ form.processing ? 'Enviando...' : 'Enviar Notificación Masiva' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
