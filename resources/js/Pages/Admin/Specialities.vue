<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    specialities: Array
});

const isEditing = ref(false);
const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    icon: 'stethoscope',
    image: null,
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (speciality) => {
    isEditing.value = true;
    editingId.value = speciality.id;
    form.name = speciality.name;
    form.icon = speciality.icon;
    form.image = null;
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.post(route('admin.specialities.update', editingId.value), {
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('admin.specialities.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteSpeciality = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta especialidad?')) {
        form.delete(route('admin.specialities.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestionar Especialidades" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-black text-2xl text-gray-800 dark:text-gray-200 leading-tight tracking-tight">
                    Especialidades Médicas
                </h2>
                <button 
                    @click="openCreateModal"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-indigo-200 dark:shadow-none flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Añadir Especialidad
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-700/50 text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700">
                                <th class="px-8 py-5">Nombre</th>
                                <th class="px-8 py-5">Icono</th>
                                <th class="px-8 py-5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            <tr v-for="spec in specialities" :key="spec.id" class="group hover:bg-indigo-50/30 dark:hover:bg-indigo-900/5 transition-colors">
                                <td class="px-8 py-5 font-bold text-gray-800 dark:text-gray-200">{{ spec.name }}</td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden border border-gray-100 dark:border-white/10 shadow-sm">
                                            <img v-if="spec.image_url" :src="spec.image_url" class="w-full h-full object-cover" />
                                            <span v-else class="text-indigo-600 dark:text-indigo-400 font-black text-xl">{{ spec.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">ID Icono</div>
                                            <span class="px-3 py-1 bg-gray-50 dark:bg-gray-900 rounded-lg text-[10px] text-gray-400 font-mono border border-gray-100 dark:border-white/5">{{ spec.icon }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right space-x-4">
                                    <button @click="openEditModal(spec)" class="text-indigo-600 hover:text-indigo-800 font-black text-sm transition-colors">Editar</button>
                                    <button @click="deleteSpeciality(spec.id)" class="text-rose-500 hover:text-rose-700 font-black text-sm transition-colors">Eliminar</button>
                                </td>
                            </tr>
                            <tr v-if="specialities.length === 0">
                                <td colspan="3" class="px-8 py-12 text-center text-gray-400 italic">No hay especialidades registradas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Specialty Modal -->
        <div v-if="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-[#161920] rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden animate-in zoom-in duration-300 border border-white/5 relative">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
                
                <button @click="showModal = false" class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="p-10">
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-8 flex items-center gap-3 italic uppercase tracking-tighter">
                        <div class="w-1.5 h-7 bg-indigo-600 rounded-full"></div>
                        {{ isEditing ? 'Editar Especialidad' : 'Nueva Especialidad' }}
                    </h3>

                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Icon Input (Simplified for elegance) -->
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Identificador de Icono</label>
                            <input 
                                v-model="form.icon" 
                                type="text" 
                                class="w-full bg-white dark:bg-gray-900 border-gray-100 dark:border-white/5 rounded-2xl p-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500/20 transition-all"
                                placeholder="Ej. heart, stethoscope, etc."
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Nombre de la Especialidad</label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    class="w-full bg-white dark:bg-gray-900 border-gray-100 dark:border-white/5 rounded-2xl p-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500/20 transition-all placeholder:text-gray-400"
                                    placeholder="Ej. Cardiología"
                                    required
                                />
                                <div v-if="form.errors.name" class="mt-2 text-[10px] text-rose-500 font-black uppercase tracking-widest">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Imagen Representativa</label>
                                <div class="flex items-center gap-5 p-5 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-200 dark:border-white/10 group hover:border-indigo-500/50 transition-colors">
                                    <div class="w-16 h-16 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden border border-gray-100 dark:border-white/5">
                                        <img v-if="form.image_url" :src="form.image_url" class="w-full h-full object-cover" />
                                        <svg v-else class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="flex-1">
                                        <input 
                                            type="file" 
                                            @input="form.image = $event.target.files[0]"
                                            class="block w-full text-[10px] text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer"
                                            accept="image/*"
                                        />
                                        <p class="mt-2 text-[9px] text-gray-500 font-bold uppercase tracking-tight">Recomendado: 400x400px (Máx 2MB)</p>
                                    </div>
                                </div>
                                <div v-if="form.errors.image" class="mt-2 text-[10px] text-rose-500 font-black uppercase tracking-widest">{{ form.errors.image }}</div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button 
                                type="button" 
                                @click="showModal = false"
                                class="flex-1 py-4 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 font-black rounded-2xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-all uppercase text-[10px] tracking-widest border border-gray-100 dark:border-white/5"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="flex-1 py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-900/30 uppercase text-[10px] tracking-widest active:scale-95 disabled:opacity-50"
                            >
                                {{ isEditing ? 'Guardar Cambios' : 'Crear Especialidad' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
