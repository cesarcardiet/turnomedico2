<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    doctors: Array,
    specialities: Array
});

const showEditModal = ref(false);
const editingDoctor = ref(null);

const editForm = useForm({
    name: '',
    speciality_id: '',
    clinic_address: '',
    phone_number: '',
    is_active: true,
    latitude: '',
    longitude: '',
});

const approve = (id) => {
    if (confirm('¿Deseas aprobar a este médico?')) {
        router.post(route('admin.doctors.approve', id));
    }
};

const openEditModal = (doctor) => {
    editingDoctor.value = doctor;
    editForm.name = doctor.user.name;
    editForm.speciality_id = doctor.speciality_id;
    editForm.clinic_address = doctor.clinic_address || '';
    editForm.phone_number = doctor.phone_number || '';
    editForm.is_active = !!doctor.is_active;
    editForm.latitude = doctor.latitude ?? '';
    editForm.longitude = doctor.longitude ?? '';
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('admin.doctors.update', editingDoctor.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
        }
    });
};

const deleteDoctor = (id) => {
    if (confirm('¿Estás seguro de que deseas eliminar permanentemente a este médico y su cuenta de usuario?')) {
        router.delete(route('admin.doctors.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestionar Médicos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-black text-2xl text-gray-800 dark:text-gray-200 leading-tight tracking-tight">
                Gestión de Médicos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-8">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 dark:bg-gray-700/50 text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700">
                                    <th class="px-6 py-5">Médico</th>
                                    <th class="px-6 py-5">Especialidad</th>
                                    <th class="px-6 py-5">Estado Aprobación</th>
                                    <th class="px-6 py-5">Estado Activo</th>
                                    <th class="px-6 py-5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                <tr v-for="doctor in doctors" :key="doctor.id" class="group hover:bg-indigo-50/30 dark:hover:bg-indigo-900/5 transition-colors">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black">
                                                {{ doctor.user.name.charAt(0) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-800 dark:text-gray-200">{{ doctor.user.name }}</div>
                                                <div class="text-xs text-gray-400">{{ doctor.user.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-sm text-gray-500 dark:text-gray-400 font-medium">
                                        {{ doctor.speciality?.name || 'No asignada' }}
                                    </td>
                                    <td class="px-6 py-5 text-sm">
                                        <div class="flex items-center gap-2">
                                            <span :class="doctor.is_approved ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-500' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-500'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                                                {{ doctor.is_approved ? 'Aprobado' : 'Pendiente' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-sm">
                                        <span :class="doctor.is_active ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-500' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                                            {{ doctor.is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link 
                                                :href="route('admin.doctors.show', doctor.id)"
                                                class="inline-flex items-center justify-center w-11 h-11 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-900/30 transition-all hover:scale-105"
                                                :title="doctor.is_profile_complete ? 'Ver currículum del médico' : 'Ver datos del médico (perfil incompleto)'"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </Link>
                                            <button v-if="!doctor.is_approved" @click="approve(doctor.id)" class="px-4 py-2 rounded-full text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 font-black text-xs transition-colors">Aprobar</button>
                                            <button @click="openEditModal(doctor)" class="px-4 py-2 rounded-full text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 font-black text-xs transition-colors">Editar</button>
                                            <button @click="deleteDoctor(doctor.id)" class="px-4 py-2 rounded-full text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 font-black text-xs transition-colors">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="doctors.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic font-medium">No se encontraron médicos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden animate-in zoom-in duration-300">
                <div class="p-8">
                    <h3 class="text-xl font-black text-gray-800 dark:text-white mb-8">Editar Perfil de Médico</h3>
                    
                    <form @submit.prevent="submitEdit" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-1">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Nombre Completo</label>
                                <input v-model="editForm.name" type="text" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-3" required />
                            </div>

                            <div class="col-span-1">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Especialidad</label>
                                <select v-model="editForm.speciality_id" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-3" required>
                                    <option v-for="spec in specialities" :key="spec.id" :value="spec.id">{{ spec.name }}</option>
                                </select>
                            </div>

                            <div class="col-span-1">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Teléfono</label>
                                <input v-model="editForm.phone_number" type="text" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-3" />
                            </div>

                            <div class="col-span-1">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Estado Activo</label>
                                <div class="flex items-center mt-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" v-model="editForm.is_active" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                        <span class="ml-3 text-sm font-bold text-gray-500 dark:text-gray-400">{{ editForm.is_active ? 'Activo' : 'Inactivo' }}</span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Dirección Clínica</label>
                                <textarea v-model="editForm.clinic_address" rows="2" class="w-full rounded-2xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-3" placeholder="Ej. Av. Principal, Caracas o Santo Domingo, RD"></textarea>
                                <p class="text-[10px] text-gray-500 mt-1">Coordenadas para el mapa (opcional; el doctor puede configurarlas en su perfil con Google):</p>
                                <div class="grid grid-cols-2 gap-3 mt-2">
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-500 uppercase mb-1">Latitud</label>
                                        <input v-model="editForm.latitude" type="text" placeholder="18.4861" class="w-full rounded-xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white py-2 px-3 text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-black text-gray-500 uppercase mb-1">Longitud</label>
                                        <input v-model="editForm.longitude" type="text" placeholder="-69.9312" class="w-full rounded-xl border-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white py-2 px-3 text-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button type="button" @click="showEditModal = false" class="flex-1 py-4 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 font-black rounded-2xl hover:bg-gray-100 transition-all">Cancelar</button>
                            <button type="submit" :disabled="editForm.processing" class="flex-1 py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
