<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';

const props = defineProps({
    profile: Object,
    specialities: Array,
    cities: Array,
    auth: Object,
    google_maps_api_key: String
});

const form = useForm({
    speciality_id: props.profile?.speciality_id || '',
    about: props.profile?.about || '',
    clinic_address: props.profile?.clinic_address || '',
    phone_number: props.profile?.phone_number || '',
    working_hours: props.profile?.working_hours || '',
    consultation_fee: props.profile?.consultation_fee || '',
    services_description: props.profile?.services_description || '',
    health_care_info: props.profile?.health_care_info || '',
    city: props.profile?.city || '',
    latitude: props.profile?.latitude ?? '',
    longitude: props.profile?.longitude ?? '',
    bank_name: props.profile?.bank_name || '',
    account_number: props.profile?.account_number || '',
    account_holder: props.profile?.account_holder || '',
    bank_swift_ifsc: props.profile?.bank_swift_ifsc || '',
    address_search: props.profile?.address_search || '',
    profile_photo: null,
});

const photoInput = ref(null);
const photoPreview = ref(null);
const clinicAddressInput = ref(null);
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);
const errorMessage = ref('');
let successTimeout = null;
let errorTimeout = null;
let placesAutocomplete = null;

const page = usePage();
const profileMissingAfterSave = computed(() => page.props.flash?.profile_missing_after_save || []);

function loadGoogleMaps() {
    if (!props.google_maps_api_key || window.google?.maps) return Promise.resolve();
    return new Promise((resolve) => {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${props.google_maps_api_key}&libraries=places`;
        script.async = true;
        script.defer = true;
        script.onload = resolve;
        document.head.appendChild(script);
    });
}

function initAddressAutocomplete() {
    if (!window.google?.maps?.places || !clinicAddressInput.value || !props.google_maps_api_key) return;
    placesAutocomplete = new window.google.maps.places.Autocomplete(clinicAddressInput.value, {
        types: ['address'],
        fields: ['formatted_address', 'geometry', 'address_components']
    });
    placesAutocomplete.addListener('place_changed', () => {
        const place = placesAutocomplete.getPlace();
        if (place.formatted_address) form.clinic_address = place.formatted_address;
        if (place.geometry?.location) {
            form.latitude = place.geometry.location.lat();
            form.longitude = place.geometry.location.lng();
        }
    });
}

onMounted(() => {
    if (props.google_maps_api_key) {
        loadGoogleMaps().then(() => {
            setTimeout(() => initAddressAutocomplete(), 200);
        });
    }
});

watch(clinicAddressInput, (el) => {
    if (el && props.google_maps_api_key && window.google?.maps?.places) initAddressAutocomplete();
});

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];
    if (!photo) return;
    form.profile_photo = photo;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
};

const submit = () => {
    showSuccessMessage.value = false;
    showErrorMessage.value = false;
    form.post(route('doctor.profile.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            showSuccessMessage.value = true;
            if (successTimeout) clearTimeout(successTimeout);
            successTimeout = setTimeout(() => { showSuccessMessage.value = false; }, 5000);
            // Recarga total del navegador para que el layout reciba auth actualizado desde el servidor
            window.location.reload();
        },
        onError: () => {
            const firstError = Object.values(form.errors)[0];
            errorMessage.value = firstError ? String(firstError) : 'Revisa los datos e intenta de nuevo.';
            showErrorMessage.value = true;
            if (errorTimeout) clearTimeout(errorTimeout);
            errorTimeout = setTimeout(() => { showErrorMessage.value = false; }, 6000);
        }
    });
};
</script>

<template>
    <Head title="Mi Perfil Profesional" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-8 bg-[#0052cc] rounded-full"></div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Mi perfil</h2>
            </div>
        </template>

        <div class="py-12 bg-[#fdf8f5] dark:bg-[#0c0e12] min-h-screen">
            <div class="max-w-5xl mx-auto px-6">
                <!-- Toast éxito (fijo, siempre visible) -->
                <Transition name="toast">
                    <div v-if="showSuccessMessage" class="fixed bottom-6 right-6 left-6 sm:left-auto sm:max-w-md z-50 p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-500/30 flex items-center gap-4 shadow-xl animate-in fade-in duration-300">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-emerald-800 dark:text-emerald-200">Perfil actualizado correctamente.</p>
                        <button @click="showSuccessMessage = false" class="ml-auto p-2 rounded-lg text-emerald-600 hover:bg-emerald-500/20 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </Transition>
                <!-- Toast error (fijo, siempre visible) -->
                <Transition name="toast">
                    <div v-if="showErrorMessage" class="fixed bottom-6 right-6 left-6 sm:left-auto sm:max-w-md z-50 p-5 rounded-2xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-500/30 flex items-center gap-4 shadow-xl animate-in fade-in duration-300">
                        <div class="w-12 h-12 rounded-xl bg-rose-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-rose-800 dark:text-rose-200">{{ errorMessage }}</p>
                        <button @click="showErrorMessage = false" class="ml-auto p-2 rounded-lg text-rose-600 hover:bg-rose-500/20 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </Transition>
                <!-- Qué hay que completar (todos editables en esta página) -->
                <div class="mb-8 p-5 rounded-2xl bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-500/30">
                    <p class="text-sm font-bold text-sky-800 dark:text-sky-200">Para aparecer en búsquedas, completa y guarda estos 4 campos (todos se pueden editar en esta página):</p>
                    <ul class="mt-2 text-sm text-sky-700 dark:text-sky-300 list-disc list-inside space-y-0.5">
                        <li><strong>Especialista</strong> (selector arriba)</li>
                        <li><strong>Teléfono No</strong></li>
                        <li><strong>Sobre nosotros</strong></li>
                        <li><strong>Dirección de la clínica</strong> (más abajo, en Datos bancarios)</li>
                    </ul>
                    <p class="text-xs mt-2 text-sky-600 dark:text-sky-400">Nombre y correo no se pueden editar aquí; no son necesarios para el perfil profesional. Después de llenar los 4 campos, haz clic en <strong>«Guardar cambios»</strong>.</p>
                </div>
                <!-- Aviso: tras guardar el sistema aún detecta campos faltantes -->
                <div v-if="profileMissingAfterSave.length" class="mb-8 p-5 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-500/30 flex items-start gap-4">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <p class="text-sm font-bold text-amber-800 dark:text-amber-200">Después de guardar, el sistema aún detecta que faltan estos campos obligatorios:</p>
                        <p class="text-sm font-bold text-amber-700 dark:text-amber-300 mt-1">{{ profileMissingAfterSave.join(', ') }}</p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">Comprueba que estén llenos (sin espacios en blanco) y vuelve a hacer clic en «Guardar cambios».</p>
                    </div>
                </div>
                <!-- Profile Image & Name Card -->
                <div class="bg-white dark:bg-[#161920] rounded-[2rem] p-10 mb-8 border border-gray-100 dark:border-white/5 shadow-sm text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
                    
                    <div class="relative inline-block mb-6">
                        <div class="w-40 h-40 rounded-[2.5rem] bg-gray-100 dark:bg-[#1c2128] border-4 border-white dark:border-[#161920] shadow-2xl flex items-center justify-center overflow-hidden group">
                             <!-- Preview for new photo -->
                             <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover animate-in fade-in duration-300" />
                             <!-- Existing photo -->
                             <img v-else-if="auth.user.profile_photo_url" :src="auth.user.profile_photo_url" class="w-full h-full object-cover" />
                             <!-- Placeholder -->
                             <svg v-else class="w-20 h-20 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                             
                             <div v-if="photoPreview" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                <span class="text-white text-[10px] font-black uppercase tracking-widest">Nueva foto</span>
                             </div>
                        </div>
                        <input 
                            type="file" 
                            class="hidden" 
                            ref="photoInput" 
                            @change="updatePhotoPreview"
                        >
                        <button 
                            type="button"
                            @click="selectNewPhoto"
                            class="absolute bottom-2 right-2 bg-[#0052cc] text-white p-3 rounded-2xl shadow-xl hover:bg-blue-700 transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </button>
                    </div>
                    <InputError :message="form.errors.profile_photo" class="mt-2 text-center" />
                    
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ auth.user.name }}</h1>
                    <p class="text-gray-400 font-bold text-sm mt-1 uppercase tracking-widest">{{ props.profile?.speciality?.name || 'Médico Profesional' }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- General Information Group -->
                    <div class="bg-white dark:bg-[#161920] rounded-[2rem] p-10 border border-gray-100 dark:border-white/5 shadow-sm space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Nombre</label>
                                <input :value="auth.user.name" readonly disabled class="w-full bg-[#f3ebe4] dark:bg-[#1c2128] border-none rounded-2xl text-gray-600 dark:text-gray-300 font-black text-sm p-5 opacity-70 cursor-not-allowed" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1 text-[#0052cc]">Especialista *</label>
                                <select v-model="form.speciality_id" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition cursor-pointer">
                                    <option value="">Seleccionar especialidad</option>
                                    <option v-for="spec in specialities" :key="spec.id" :value="spec.id">{{ spec.name }}</option>
                                </select>
                                <InputError :message="form.errors.speciality_id" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Correo Electrónico</label>
                                <input :value="auth.user.email" readonly disabled class="w-full bg-[#f3ebe4] dark:bg-[#1c2128] border-none rounded-2xl text-gray-600 dark:text-gray-300 font-black text-sm p-5 opacity-70 cursor-not-allowed" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Teléfono No</label>
                                <input v-model="form.phone_number" type="text" placeholder="0419999999" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1 min-w-[300px] text-rose-500">Tarifa de consulta *</label>
                                <input v-model="form.consultation_fee" type="number" step="0.01" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition" />
                                <InputError :message="form.errors.consultation_fee" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Horario de Trabajo</label>
                                <input v-model="form.working_hours" type="text" placeholder="Ingrese el Horario de Trabajo" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Sobre nosotros</label>
                            <textarea v-model="form.about" rows="3" placeholder="Ingrese Acerca del Medico" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Servicios</label>
                            <textarea v-model="form.services_description" rows="2" placeholder="Ingrese la descripción sobre los servicios" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1 text-gray-400">Cuidado de la Salud</label>
                            <textarea v-model="form.health_care_info" rows="2" placeholder="Ingrese Cuidado de la Salud" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-1 gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Ciudad</label>
                                <select v-model="form.city" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition cursor-pointer">
                                    <option value="">Seleccionar Ciudad</option>
                                    <option v-for="city in cities" :key="city.id" :value="city.name">{{ city.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Banking Information Group -->
                    <div class="bg-white dark:bg-[#161920] rounded-[2rem] p-10 border border-gray-100 dark:border-white/5 shadow-sm space-y-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-blue-50 dark:bg-blue-500/10 rounded-lg text-[#0052cc]">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.382 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Datos Bancarios para Pagos</h3>
                        </div>
                        <p class="text-[11px] text-gray-400 font-bold -mt-4 mb-2">Configure la información bancaria para recibir pagos de consultas</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Nombre del Banco *</label>
                                <input v-model="form.bank_name" type="text" placeholder="Ej. Banco Nacional, Banco de Venezuela" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition" />
                                <InputError :message="form.errors.bank_name" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Código IFSC / SWIFT *</label>
                                <input v-model="form.bank_swift_ifsc" type="text" placeholder="Ej: BANKVE 22, 001000001" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition" />
                                <InputError :message="form.errors.bank_swift_ifsc" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Número de Cuenta *</label>
                                <input v-model="form.account_number" type="text" placeholder="Número de cuenta bancaria" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition" />
                                <InputError :message="form.errors.account_number" class="mt-2" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1">Nombre del Titular *</label>
                                <input v-model="form.account_holder" type="text" placeholder="Nombre completo del titular de la cuenta" class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition" />
                                <InputError :message="form.errors.account_holder" class="mt-2" />
                            </div>
                        </div>

                        <!-- Alert Box -->
                        <div class="bg-cyan-50 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-500/20 rounded-2xl p-6 flex items-start gap-4 mt-6">
                            <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <p class="text-xs font-bold text-cyan-800 dark:text-cyan-300 leading-relaxed">
                                <span class="font-black uppercase tracking-widest">Importante:</span> Esta información se utilizará para procesar los pagos de tus consultas médicas. Asegúrate de que los datos sean correctos y estén actualizados.
                            </p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pl-1 text-rose-500">Dirección de la clínica *</label>
                            <input 
                                ref="clinicAddressInput"
                                v-model="form.clinic_address" 
                                type="text" 
                                placeholder="Escribe y elige una dirección (ej. Caracas, Santo Domingo, República Dominicana...)" 
                                class="w-full bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/5 rounded-2xl text-gray-800 dark:text-white font-black text-sm p-5 focus:ring-2 focus:ring-blue-500/20 transition" 
                            />
                            <p v-if="google_maps_api_key" class="text-[9px] text-gray-500 mt-1.5 flex items-center gap-1">
                                <span>Usa la búsqueda de Google para una dirección exacta; se guardará la ubicación para el mapa.</span>
                            </p>
                            <InputError :message="form.errors.clinic_address" class="mt-2" />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-4 pt-4">
                        <button type="submit" :disabled="form.processing" class="flex-1 max-w-[240px] py-4 bg-[#0052cc] text-white font-black rounded-full hover:bg-blue-700 transition shadow-2xl shadow-blue-900/20 uppercase tracking-widest text-[10px] flex items-center justify-center gap-3">
                            <span>Guardar cambios</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                        <button type="button" @click="form.reset()" class="px-10 py-4 bg-white dark:bg-[#161920] border border-gray-100 dark:border-white/5 text-gray-500 font-black rounded-full hover:bg-gray-50 dark:hover:bg-white/5 transition uppercase tracking-widest text-[10px]">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Toast enter/leave */
.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>
