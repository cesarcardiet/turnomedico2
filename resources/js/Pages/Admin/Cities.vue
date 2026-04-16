<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cities: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    is_active: true
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (city) => {
    isEditing.value = true;
    editingId.value = city.id;
    form.name = city.name;
    form.is_active = !!city.is_active;
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.cities.update', editingId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.cities.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteCity = (id) => {
    if (confirm('¿Estás seguro de eliminar esta ciudad?')) {
        form.delete(route('admin.cities.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestión de Ciudades" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Gestión de Ciudades</h2>
                </div>
                <button 
                    @click="openCreateModal"
                    class="px-8 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-indigo-900/20 hover:bg-indigo-700 transition-all active:scale-95"
                >
                    Nueva Ciudad
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6">
                <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-white/5 shadow-2xl overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-white/5">
                                <th class="px-8 py-6">Nombre</th>
                                <th class="px-8 py-6">Estado</th>
                                <th class="px-8 py-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="city in cities" :key="city.id" class="group hover:bg-indigo-50/30 dark:hover:bg-indigo-900/5 transition-colors">
                                <td class="px-8 py-5 font-bold text-gray-800 dark:text-gray-200">{{ city.name }}</td>
                                <td class="px-8 py-5">
                                    <span 
                                        :class="city.is_active ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500'"
                                        class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border"
                                        :style="{ borderColor: city.is_active ? 'rgba(16, 185, 129, 0.2)' : 'rgba(244, 63, 94, 0.2)' }"
                                    >
                                        {{ city.is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right space-x-4">
                                    <button @click="openEditModal(city)" class="text-indigo-600 hover:text-indigo-800 font-black text-sm transition-colors">Editar</button>
                                    <button @click="deleteCity(city.id)" class="text-rose-500 hover:text-rose-700 font-black text-sm transition-colors">Eliminar</button>
                                </td>
                            </tr>
                            <tr v-if="cities.length === 0">
                                <td colspan="3" class="px-8 py-12 text-center text-gray-400 italic">No hay ciudades registradas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in duration-300 border border-white/5">
                <div class="p-10">
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-8 flex items-center gap-3 italic uppercase tracking-tighter">
                         <div class="w-1.5 h-6 bg-indigo-600 rounded-full"></div>
                        {{ isEditing ? 'Editar Ciudad' : 'Nueva Ciudad' }}
                    </h3>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Nombre de la Ciudad</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-4 font-bold text-sm"
                                placeholder="Ej. San Juan"
                                required
                            />
                            <div v-if="form.errors.name" class="mt-2 text-[10px] text-rose-500 font-black uppercase tracking-widest">{{ form.errors.name }}</div>
                        </div>

                        <div v-if="isEditing" class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-white/5">
                            <input 
                                v-model="form.is_active" 
                                type="checkbox" 
                                class="w-5 h-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                id="is_active"
                            />
                            <label for="is_active" class="text-xs font-black text-gray-500 uppercase tracking-widest cursor-pointer">Ciudad Activa</label>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button 
                                type="button" 
                                @click="showModal = false"
                                class="flex-1 py-4 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 font-black rounded-2xl hover:bg-gray-100 transition-all uppercase text-[10px] tracking-widest"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="flex-1 py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-900/20 uppercase text-[10px] tracking-widest active:scale-95 disabled:opacity-50"
                            >
                                {{ isEditing ? 'Guardar Cambios' : 'Crear' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
