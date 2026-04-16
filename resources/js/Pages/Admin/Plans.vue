<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plans: Array
});

const isEditing = ref(false);
const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    description: '',
    price: '',
    duration_days: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (plan) => {
    isEditing.value = true;
    editingId.value = plan.id;
    form.name = plan.name;
    form.description = plan.description;
    form.price = plan.price;
    form.duration_days = plan.duration_days;
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.plans.update', editingId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.plans.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deletePlan = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar este plan?')) {
        form.delete(route('admin.plans.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestionar Planes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-2xl text-gray-800 dark:text-gray-200 leading-tight tracking-tight">
                    Planes de Membresía
                </h2>
                <button 
                    @click="openCreateModal"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-indigo-200 dark:shadow-none flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo Plan
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="plan in plans" :key="plan.id" class="bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700 shadow-sm relative group overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="flex gap-2">
                                <button @click="openEditModal(plan)" class="p-2 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-100 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button @click="deletePlan(plan.id)" class="p-2 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-xl font-black text-gray-800 dark:text-white mb-2">{{ plan.name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ plan.description }}</p>
                        </div>

                        <div class="flex items-baseline gap-1 mb-8">
                            <span class="text-4xl font-black text-indigo-600 dark:text-indigo-400">${{ plan.price }}</span>
                            <span class="text-gray-400 font-bold text-sm">/ {{ plan.duration_days }} días</span>
                        </div>

                        <div class="pt-6 border-t border-gray-50 dark:border-gray-700/50 flex items-center justify-between text-xs font-black text-gray-400 uppercase tracking-widest">
                            <span>Estado: Activo</span>
                            <div class="flex gap-1">
                                <div v-for="i in 3" :key="i" class="w-1.5 h-1.5 rounded-full bg-indigo-200 dark:bg-indigo-900"></div>
                            </div>
                        </div>
                    </div>

                    <div v-if="plans.length === 0" class="col-span-full py-24 bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700 text-center">
                        <p class="text-gray-400 font-bold">No hay planes configurados.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in duration-300">
                <div class="p-8">
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-8">
                        {{ isEditing ? 'Editar Plan' : 'Nuevo Plan de Membresía' }}
                    </h3>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nombre del Plan</label>
                                <input v-model="form.name" type="text" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-3" placeholder="Ej. Plan Diamante" required />
                                <div v-if="form.errors.name" class="mt-1 text-xs text-rose-500 font-bold">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Precio ($)</label>
                                <input v-model="form.price" type="number" step="0.01" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-3" placeholder="0.00" required />
                                <div v-if="form.errors.price" class="mt-1 text-xs text-rose-500 font-bold">{{ form.errors.price }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Duración (Días)</label>
                                <input v-model="form.duration_days" type="number" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-3" placeholder="30" required />
                                <div v-if="form.errors.duration_days" class="mt-1 text-xs text-rose-500 font-bold">{{ form.errors.duration_days }}</div>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Descripción</label>
                                <textarea v-model="form.description" rows="4" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-3" placeholder="Describe los beneficios del plan..." required></textarea>
                                <div v-if="form.errors.description" class="mt-1 text-xs text-rose-500 font-bold">{{ form.errors.description }}</div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button type="button" @click="showModal = false" class="flex-1 py-4 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 font-black rounded-2xl hover:bg-gray-100 transition-all">Cancelar</button>
                            <button type="submit" :disabled="form.processing" class="flex-1 py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none">
                                {{ isEditing ? 'Actualizar Plan' : 'Crear Plan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
