<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
    logo_url: String
});

const activeTab = ref('general');
const showSuccessMessage = ref(false);

const generalForm = useForm({
    group: 'general',
    site_logo: null,
    google_maps_api_key: props.settings.general?.google_maps_api_key || '',
});

const mailForm = useForm({
    group: 'mail',
    mail_host: props.settings.mail.mail_host || '',
    mail_port: props.settings.mail.mail_port || '',
    mail_username: props.settings.mail.mail_username || '',
    mail_password: props.settings.mail.mail_password || '',
    mail_encryption: props.settings.mail.mail_encryption || 'tls',
    mail_from_address: props.settings.mail.mail_from_address || '',
    mail_from_name: props.settings.mail.mail_from_name || '',
});

const firebaseForm = useForm({
    group: 'firebase',
    firebase_service_account: props.settings.firebase.firebase_service_account || '',
    firebase_api_key: props.settings.firebase.firebase_api_key || '',
    firebase_auth_domain: props.settings.firebase.firebase_auth_domain || '',
    firebase_project_id: props.settings.firebase.firebase_project_id || '',
    firebase_storage_bucket: props.settings.firebase.firebase_storage_bucket || '',
    firebase_messaging_sender_id: props.settings.firebase.firebase_messaging_sender_id || '',
    firebase_app_id: props.settings.firebase.firebase_app_id || '',
    firebase_measurement_id: props.settings.firebase.firebase_measurement_id || '',
});

const paymentForm = useForm({
    group: 'payment',
    bank_name: props.settings.payment.bank_name || '',
    account_number: props.settings.payment.account_number || '',
    account_type: props.settings.payment.account_type || '',
    account_holder: props.settings.payment.account_holder || '',
    document_id: props.settings.payment.document_id || '',
});

const updateSettings = (form) => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            if (form.site_logo) form.reset('site_logo');
            showSuccessMessage.value = true;
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 3000);
        }
    });
};

const onLogoChange = (e) => {
    generalForm.site_logo = e.target.files[0];
};
</script>

<template>
    <Head title="Configuración del Sistema" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Configuración del Sistema</h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Success Message -->
                <div v-if="showSuccessMessage" class="mb-6 p-4 bg-emerald-500 text-white rounded-2xl flex items-center gap-3 shadow-lg shadow-emerald-500/20 animate-in slide-in-from-top-4 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-xs font-black uppercase tracking-widest">Configuración guardada satisfactoriamente</span>
                </div>

                <!-- Tabs -->
                <div class="flex gap-4 mb-8">
                    <button 
                        @click="activeTab = 'general'"
                        :class="activeTab === 'general' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-gray-500 hover:bg-gray-50'"
                        class="px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all"
                    >
                        General & Logo
                    </button>
                    <button 
                        @click="activeTab = 'mail'"
                        :class="activeTab === 'mail' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-gray-500 hover:bg-gray-50'"
                        class="px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all"
                    >
                        Correo (SMTP)
                    </button>
                    <button 
                        @click="activeTab = 'firebase'"
                        :class="activeTab === 'firebase' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-gray-500 hover:bg-gray-50'"
                        class="px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all"
                    >
                        Firebase & Notificaciones
                    </button>
                    <button 
                        @click="activeTab = 'payment'"
                        :class="activeTab === 'payment' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-gray-500 hover:bg-gray-50'"
                        class="px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all"
                    >
                        Datos de Pago
                    </button>
                </div>

                <!-- General Tab -->
                <div v-if="activeTab === 'general'" class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-gray-100 dark:border-white/5 p-10 shadow-2xl">
                    <form @submit.prevent="updateSettings(generalForm)" class="space-y-8">
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-6">Identidad Visual</h3>
                            <div class="flex items-center gap-10">
                                <div class="w-40 h-40 rounded-[2rem] bg-gray-50 dark:bg-gray-900 flex items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-700 overflow-hidden group relative">
                                    <img v-if="logo_url" :src="logo_url" class="w-full h-full object-contain p-4" />
                                    <div v-else class="text-gray-400 font-bold text-xs uppercase tracking-widest">Logo</div>
                                    <div class="absolute inset-0 bg-indigo-600/90 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <label for="logo-upload" class="cursor-pointer text-white text-[10px] font-black uppercase tracking-widest">Cambiar</label>
                                    </div>
                                    <input id="logo-upload" type="file" @change="onLogoChange" class="hidden" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-2">Logo Principal</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-6 leading-relaxed">Este logo se utilizará en la cabecera de la web pública, los paneles de control y los correos electrónicos.</p>
                                    <button 
                                        type="submit" 
                                        :disabled="generalForm.processing"
                                        class="px-8 py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-xl shadow-indigo-500/20"
                                    >
                                        {{ generalForm.processing ? 'Guardando...' : 'Guardar Cambios' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="pt-8 border-t border-gray-100 dark:border-white/5">
                            <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">Google Maps</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Clave de API para el mapa y filtro por ubicación en la búsqueda de doctores (Maps JavaScript API y Geocoding habilitados).</p>
                            <div class="max-w-xl">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Clave de API de Google Maps</label>
                                <input v-model="generalForm.google_maps_api_key" type="text" placeholder="AIzaSy..." class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Mail Tab -->
                <div v-if="activeTab === 'mail'" class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-gray-100 dark:border-white/5 p-10 shadow-2xl">
                    <form @submit.prevent="updateSettings(mailForm)" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">Configuración SMTP</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-6">Define los parámetros de tu servidor de correo para las notificaciones.</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Servidor (Host)</label>
                            <input v-model="mailForm.mail_host" type="text" placeholder="smtp.mailtrap.io" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Puerto</label>
                            <input v-model="mailForm.mail_port" type="text" placeholder="587" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Usuario</label>
                            <input v-model="mailForm.mail_username" type="text" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Contraseña</label>
                            <input v-model="mailForm.mail_password" type="password" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Remitente (Email)</label>
                            <input v-model="mailForm.mail_from_address" type="email" placeholder="no-reply@turnomedico.com" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Nombre Remitente</label>
                            <input v-model="mailForm.mail_from_name" type="text" placeholder="Turno Médico" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div class="md:col-span-2 flex justify-end">
                            <button 
                                type="submit" 
                                :disabled="mailForm.processing"
                                class="px-8 py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-xl shadow-indigo-500/20"
                            >
                                {{ mailForm.processing ? 'Guardando...' : 'Guardar Configuración de Correo' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Firebase Tab -->
                <div v-if="activeTab === 'firebase'" class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-gray-100 dark:border-white/5 p-10 shadow-2xl">
                    <form @submit.prevent="updateSettings(firebaseForm)" class="space-y-8">
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">Configuración Firebase Cloud Messaging</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-10">Integra notificaciones push para la web y app móvil.</p>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    Contenido de la Cuenta de Servicio (JSON)
                                    <span class="w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[8px] font-black">!</span>
                                </label>
                                <textarea 
                                    v-model="firebaseForm.firebase_service_account" 
                                    rows="6" 
                                    placeholder='{ "type": "service_account", ... }'
                                    class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-mono text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500"
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Clave de API</label>
                                    <input v-model="firebaseForm.firebase_api_key" type="text" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Dominio de Autenticación</label>
                                    <input v-model="firebaseForm.firebase_auth_domain" type="text" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">ID del Proyecto</label>
                                    <input v-model="firebaseForm.firebase_project_id" type="text" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Cubo de Almacenamiento</label>
                                    <input v-model="firebaseForm.firebase_storage_bucket" type="text" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">ID del Remitente de Mensajes</label>
                                    <input v-model="firebaseForm.firebase_messaging_sender_id" type="text" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">ID de Aplicación</label>
                                    <input v-model="firebaseForm.firebase_app_id" type="text" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button 
                                type="submit" 
                                :disabled="firebaseForm.processing"
                                class="px-10 py-5 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-xl shadow-indigo-500/20"
                            >
                                {{ firebaseForm.processing ? 'Sincronizando...' : 'Actualizar Firebase' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Payment Tab -->
                <div v-if="activeTab === 'payment'" class="bg-white dark:bg-[#161920] rounded-[2.5rem] border border-gray-100 dark:border-white/5 p-10 shadow-2xl">
                    <form @submit.prevent="updateSettings(paymentForm)" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2">Datos para Transferencias</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-6">Configura la información bancaria que verán los doctores para pagar sus planes.</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Nombre del Banco</label>
                            <input v-model="paymentForm.bank_name" type="text" placeholder="Ej: Banreservas / Popular" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Número de Cuenta</label>
                            <input v-model="paymentForm.account_number" type="text" placeholder="000-0000000-0" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Tipo de Cuenta</label>
                            <select v-model="paymentForm.account_type" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200">
                                <option value="">Seleccionar...</option>
                                <option value="Ahorros">Ahorros</option>
                                <option value="Corriente">Corriente</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Titular de la Cuenta</label>
                            <input v-model="paymentForm.account_holder" type="text" placeholder="Nombre completo" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">RNC / Cédula</label>
                            <input v-model="paymentForm.document_id" type="text" placeholder="Identificación del titular" class="w-full rounded-2xl bg-gray-50 dark:bg-gray-900 border-none py-4 px-6 font-bold text-sm text-gray-800 dark:text-gray-200" />
                        </div>

                        <div class="md:col-span-2 flex justify-end">
                            <button 
                                type="submit" 
                                :disabled="paymentForm.processing"
                                class="px-8 py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-xl shadow-indigo-500/20"
                            >
                                {{ paymentForm.processing ? 'Guardando...' : 'Guardar Datos de Pago' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
