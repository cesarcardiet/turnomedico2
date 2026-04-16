<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    specialities: Array,
    featured_doctors: Array
});

const searchQuery = ref('');
const showLoginSelector = ref(false);
const showRegisterSelector = ref(false);
const imageErrors = ref(new Set());

const handleImageError = (id, type) => {
    imageErrors.value.add(`${type}_${id}`);
};

const hasImageError = (id, type) => {
    return imageErrors.value.has(`${type}_${id}`);
};
const showAutocomplete = ref(false);
const autocompleteResults = ref([]);
const isSearching = ref(false);

const handleSearch = () => {
    router.get(route('patient.search'), { search: searchQuery.value });
};

const performAutocomplete = async () => {
    if (searchQuery.value.length < 2) {
        autocompleteResults.value = [];
        showAutocomplete.value = false;
        return;
    }

    isSearching.value = true;
    try {
        const response = await fetch(route('patient.autocomplete', { q: searchQuery.value }));
        const data = await response.json();
        autocompleteResults.value = data;
        showAutocomplete.value = data.length > 0;
    } catch (e) {
        console.error('Autocomplete failed', e);
    } finally {
        isSearching.value = false;
    }
};

const selectDoctor = (doctor) => {
    searchQuery.value = doctor.name;
    showAutocomplete.value = false;
    router.get(route('patient.doctor.profile', doctor.id));
};

const handleLoginToggle = () => {
    showLoginSelector.value = !showLoginSelector.value;
};

const handleRegisterToggle = () => {
    showRegisterSelector.value = !showRegisterSelector.value;
};

const formatDoctorName = (name) => {
    if (!name) return '';
    const nameLower = name.toLowerCase();
    if (nameLower.startsWith('dr') || nameLower.startsWith('dra')) {
        return name;
    }
    return 'Dr. ' + name;
};

const toggleFavorite = (doctor) => {
    if (!props.canLogin || !props.canRegister) return; // Not handled here but just in case
    
    // Check if user is logged in
    // This is handled via Inertia, if not authenticated it might redirect or we could check props.auth
    
    router.post(route('patient.favorites.store'), {
        doctor_profile_id: doctor.id
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Success notification or logic if needed
        },
        onError: () => {
            // Handle error (e.g. redirect to login if not authenticated)
        }
    });
};
</script>

<template>
    <Head title="Turno Médico - Encuentra a tu doctor ideal" />

    <div class="min-h-screen bg-slate-50 dark:bg-gray-900 font-sans selection:bg-brand-teal/20 dark:selection:bg-brand-teal/30">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link :href="route('welcome')">
                    <!-- Versión compacta del logo para que no sobresalga de la barra -->
                    <ApplicationLogo compact />
                </Link>
                
                <div class="hidden md:flex items-center gap-8">
                    <a href="#inicio" class="text-sm font-bold text-gray-500 hover:text-brand-teal dark:text-gray-400 dark:hover:text-brand-light transition-colors">Inicio</a>
                    <a href="#especialidades" class="text-sm font-bold text-gray-500 hover:text-brand-teal dark:text-gray-400 dark:hover:text-brand-light transition-colors">Especialidades</a>
                    <Link :href="route('patient.search')" class="text-sm font-bold text-brand-teal dark:text-brand-light hover:text-brand-dark dark:hover:text-brand-light transition-colors">Buscar doctores</Link>
                </div>

                <div v-if="canLogin" class="flex items-center gap-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm font-black text-brand-teal dark:text-brand-light uppercase tracking-widest">Mi Panel</Link>
                    <template v-else>
                        <button @click="handleLoginToggle" class="text-sm font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest hover:text-brand-teal transition-colors">Ingresar</button>
                        <button @click="handleRegisterToggle" class="px-6 py-3 bg-brand-teal text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-brand-dark transition shadow-lg shadow-brand-teal/20 dark:shadow-none">Registrarse</button>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section id="inicio" class="pt-40 pb-20 px-6">
            <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
                <div class="animate-in fade-in slide-in-from-left duration-1000">
                    <span class="inline-block px-4 py-1.5 bg-brand-teal/10 dark:bg-brand-teal/20 text-brand-teal dark:text-brand-light rounded-full text-[10px] font-black uppercase tracking-widest mb-6">Salud al alcance de un clic</span>
                    <h1 class="text-5xl md:text-7xl font-black text-gray-900 dark:text-white leading-[1.1] mb-8">
                        ¡Encuentra a <span class="text-brand-teal">tu doctor</span> de confianza!
                    </h1>
                    <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 leading-relaxed max-w-lg">
                        Reserva citas con los mejores especialistas en segundos. Sin esperas, sin complicaciones.
                    </p>
                    
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-[2.5rem] shadow-2xl shadow-brand-teal/20 dark:shadow-none border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row gap-4 relative">
                        <div class="flex-1 relative">
                            <svg class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input v-model="searchQuery" @input="performAutocomplete" type="text" placeholder="¿Qué especialista buscas?" class="w-full pl-14 pr-6 py-5 bg-transparent border-none focus:ring-0 text-gray-800 dark:text-white font-bold" @keyup.enter="handleSearch">
                            
                            <!-- Autocomplete Dropdown -->
                            <div v-if="showAutocomplete" class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-[60]">
                                <div v-for="doctor in autocompleteResults" :key="doctor.id" @click="selectDoctor(doctor)" class="px-8 py-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer flex items-center justify-between border-b border-gray-50 dark:border-gray-700 last:border-none">
                                    <div>
                                        <p class="text-sm font-black text-gray-900 dark:text-white">Dr. {{ doctor.name }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ doctor.speciality }}</p>
                                    </div>
                                    <svg class="w-4 h-4 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <button @click="handleSearch" class="px-10 py-5 bg-brand-teal text-white rounded-[1.8rem] font-black uppercase tracking-widest hover:bg-brand-dark transition shadow-lg shadow-brand-teal/30">
                            Buscar Ahora
                        </button>
                    </div>
                </div>
                
                <div class="relative animate-in fade-in slide-in-from-right duration-1000">
                    <div class="absolute -inset-4 bg-brand-teal rounded-[3rem] opacity-10 blur-3xl"></div>
                    <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?q=80&w=2070&auto=format&fit=crop" alt="Doctor" class="relative rounded-[3rem] shadow-2xl w-full h-[550px] object-cover grayscale-[0.2] hover:grayscale-0 transition duration-700">
                    <div class="absolute -bottom-6 -left-6 bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-xl flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <div class="text-sm font-black dark:text-white">500+ Doctores</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase">Verificados Hoy</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Specialities Section -->
        <section id="especialidades" class="py-24 bg-white dark:bg-gray-800/50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <span class="text-xs font-black text-brand-teal uppercase tracking-widest">Servicios</span>
                    <h2 class="text-4xl font-black text-gray-900 dark:text-white mt-4">Buscar por Especialidad</h2>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-8">
                    <Link 
                        v-for="spec in specialities" 
                        :key="spec.id" 
                        :href="route('patient.search', { speciality: spec.id })" 
                        class="group cursor-pointer"
                    >
                        <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-[2.5rem] text-center border border-transparent group-hover:border-brand-teal/30 dark:group-hover:border-brand-teal/20 group-hover:bg-white dark:group-hover:bg-gray-900 group-hover:shadow-2xl transition-all duration-300 h-full flex flex-col items-center justify-center">
                            <div class="w-24 h-24 mx-auto bg-white dark:bg-gray-700 rounded-3xl flex items-center justify-center text-brand-teal dark:text-brand-light mb-6 shadow-md group-hover:scale-110 transition-transform overflow-hidden border border-gray-100 dark:border-white/5">
                                <img v-if="spec.image_url && !hasImageError(spec.id, 'spec')" 
                                    :src="spec.image_url" 
                                    @error="handleImageError(spec.id, 'spec')"
                                    class="w-full h-full object-cover" />
                                <span v-else class="font-black text-3xl">{{ spec.name.charAt(0) }}</span>
                            </div>
                            <h4 class="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 group-hover:text-brand-teal transition-colors">{{ spec.name }}</h4>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Doctors Grid -->
        <section id="doctores" class="py-24 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-4">
                    <div>
                        <span class="text-xs font-black text-brand-teal uppercase tracking-widest">Recomendados</span>
                        <h2 class="text-4xl font-black text-gray-900 dark:text-white mt-4">Especialistas Mejor Valorados</h2>
                    </div>
                    <Link :href="route('patient.search')" class="text-sm font-black text-brand-teal dark:text-brand-light uppercase tracking-widest flex items-center gap-2 group">
                        Ver Todos
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </Link>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10">
                    <div v-for="doctor in featured_doctors" :key="doctor.id" class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700 hover:shadow-2xl transition-all duration-500 group overflow-hidden flex flex-col shadow-sm">
                        <!-- Image Header -->
                        <div class="relative h-64 overflow-hidden">
                            <Link :href="route('patient.doctor.profile', doctor.id)" class="block w-full h-full">
                                <img v-if="(doctor.profile_photo_url || doctor.user?.profile_photo_url) && !hasImageError(doctor.id, 'doc')" 
                                    :src="doctor.profile_photo_url || doctor.user?.profile_photo_url" 
                                    @error="handleImageError(doctor.id, 'doc')"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                                <div v-else class="w-full h-full bg-brand-teal/10 dark:bg-brand-teal/20 flex items-center justify-center text-brand-teal dark:text-brand-light font-black text-4xl">
                                    {{ doctor.user?.name?.charAt(0) }}
                                </div>
                            </Link>
                            
                            <!-- Favorite Icon -->
                            <button 
                                @click.stop="toggleFavorite(doctor)"
                                class="absolute top-6 right-6 w-12 h-12 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg transition-all transform hover:scale-110"
                                :class="doctor.is_favorited ? 'text-rose-500 bg-rose-50' : 'text-gray-400 hover:text-rose-500'"
                            >
                                <svg class="w-6 h-6" :class="{ 'fill-current': doctor.is_favorited }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="p-8 flex-1 flex flex-col">
                            <Link :href="route('patient.doctor.profile', doctor.id)">
                                <h3 class="text-xl font-black text-gray-900 dark:text-gray-100 mb-1 uppercase tracking-tight hover:text-brand-teal dark:hover:text-brand-light transition-colors">
                                    {{ formatDoctorName(doctor.user?.name) }}
                                </h3>
                            </Link>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">{{ doctor.speciality?.name || 'Médico' }}</p>
                            
                            <div class="flex items-center gap-1 mb-6">
                                <div class="flex text-amber-500">
                                    <svg v-for="i in 5" :key="i" class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                </div>
                                <span class="text-xs font-black text-gray-300 ml-2">(1)</span>
                            </div>

                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mb-8">
                                <svg class="w-4 h-4 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-[10px] font-black uppercase tracking-widest">{{ doctor.city || 'La Vega, República Dominicana' }}</span>
                            </div>

                            <div class="mt-auto pt-8 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between gap-4">
                                <div class="text-[10px] font-black uppercase tracking-widest flex flex-col">
                                    <span class="text-gray-400 dark:text-gray-400 text-[8px] mb-1">Horario</span>
                                    <span class="text-gray-800 dark:text-white font-bold">8:30 AM - 5:30 PM</span>
                                </div>
                                <Link :href="route('patient.doctor.profile', doctor.id)" class="px-6 py-3 bg-brand-teal text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-brand-dark transition-all shadow-lg shadow-brand-teal/20">
                                    Visita ahora
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Process Section -->
        <section class="py-24 bg-brand-teal rounded-[4rem] mx-6">
            <div class="max-w-7xl mx-auto px-10">
                <div class="text-center text-white mb-20">
                    <h2 class="text-4xl font-black mb-4">Proceso de Cita</h2>
                    <!-- Texto en blanco para mejor contraste sobre fondo morado -->
                    <p class="text-white/90 font-medium">Sigue estos simples pasos para atenderte</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-16 relative">
                    <!-- Steps logic -->
                    <div class="text-center group">
                        <div class="w-20 h-20 bg-white/20 backdrop-blur-xl rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-xl group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-white mb-4">Busca tu Médico</h4>
                        <!-- Texto en blanco suave para mejor legibilidad -->
                        <p class="text-white/80 text-sm">Explora entre cientos de profesionales certificados por especialidad.</p>
                    </div>
                    
                    <div class="text-center group">
                        <div class="w-20 h-20 bg-white/20 backdrop-blur-xl rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-xl group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-white mb-4">Elige el Horario</h4>
                        <p class="text-white/80 text-sm">Selecciona el día y la hora que mejor se adapte a tu agenda personal.</p>
                    </div>
                    
                    <div class="text-center group">
                        <div class="w-20 h-20 bg-white/20 backdrop-blur-xl rounded-[2rem] flex items-center justify-center mx-auto mb-8 shadow-xl group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-white mb-4">Confirma tu Cita</h4>
                        <p class="text-white/80 text-sm">¡Listo! Recibirás una notificación y podrás gestionar tu cita en tu panel.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- App Call to Action -->
        <section class="py-24 px-6 overflow-hidden">
            <div class="max-w-7xl mx-auto bg-slate-900 rounded-[3rem] p-12 md:p-20 relative overflow-hidden flex flex-col md:flex-row items-center gap-16">
                <div class="absolute top-0 right-0 w-96 h-96 bg-brand-teal/20 blur-[100px] -mr-48 -mt-48 transition-all"></div>
                
                <div class="relative z-10 flex-1 text-center md:text-left">
                    <h2 class="text-4xl md:text-5xl font-black text-white mb-8 leading-tight">Descarga nuestra <br class="hidden md:block"> App Móvil</h2>
                    <p class="text-gray-400 text-lg mb-10 max-w-md">Lleva tu salud en el bolsillo. Gestiona turnos, recibe recordatorios y comunícate con doctores desde cualquier lugar.</p>
                    
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                        <button class="bg-white px-8 py-4 rounded-2xl flex items-center gap-3 hover:bg-gray-100 transition shadow-xl">
                            <span class="text-left">
                                <span class="block text-[8px] font-black uppercase text-gray-400 tracking-widest">Descargar en</span>
                                <span class="block text-sm font-black text-gray-900 uppercase">App Store</span>
                            </span>
                        </button>
                        <button class="bg-brand-teal px-8 py-4 rounded-2xl flex items-center gap-3 hover:bg-brand-dark transition shadow-xl">
                            <span class="text-left">
                                <span class="block text-[8px] font-black uppercase text-brand-light tracking-widest">Consíguelo en</span>
                                <span class="block text-sm font-black text-white uppercase">Google Play</span>
                            </span>
                        </button>
                    </div>
                </div>
                
                <div class="relative w-full md:w-1/3 animate-bounce duration-[3000ms]">
                    <div class="bg-brand-teal/10 p-8 rounded-[3.5rem] relative">
                        <img src="https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?q=80&w=2070&auto=format&fit=crop" alt="Mobile App" class="rounded-[2.5rem] shadow-2xl">
                    </div>
                </div>
            </div>
        </section>

        <!-- Register CTAs -->
        <section class="py-24 bg-gray-50 dark:bg-gray-900/50">
            <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-10">
                <div class="bg-white dark:bg-gray-800 p-12 rounded-[3rem] border border-gray-100 dark:border-gray-700 group">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-4">¿Eres Médico?</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 font-medium">Únete a nuestra plataforma y gestiona tus pacientes de forma digital. Aumenta tu visibilidad y organiza tu agenda.</p>
                    <Link :href="route('register')" class="inline-block px-10 py-5 bg-brand-teal text-white rounded-[1.8rem] font-black uppercase tracking-widest hover:bg-brand-dark transition shadow-lg shadow-brand-teal/20 dark:shadow-none">Unirse como Doctor</Link>
                </div>
                
                <div class="bg-brand-teal p-12 rounded-[3rem] text-white overflow-hidden relative group">
                    <svg class="absolute -right-20 -bottom-20 w-64 h-64 text-white/5 opacity-10 group-hover:scale-125 transition-transform duration-1000" fill="currentColor" viewBox="0 0 200 200"><path d="M44.7,-76.4C58.2,-69.2,70.1,-58.5,78.9,-45.5C87.8,-32.5,93.6,-17.2,93.3,-2.1C93,13.1,86.6,28.1,77.8,41.9C69,55.7,57.8,68.4,44,76.5C30.2,84.6,13.8,88.1,-2.4,92.3C-18.6,96.5,-34.5,101.5,-48.6,95.5C-62.7,89.5,-75,72.6,-82.1,55.7C-89.2,38.8,-91,21.9,-88.7,5.9C-86.4,-10.1,-80,-25.1,-71.4,-38.3C-62.8,-51.5,-52.1,-62.9,-39.7,-70.8C-27.3,-78.7,-13.7,-83.1,0.7,-84.3C15.1,-85.5,31.2,-83.6,44.7,-76.4Z" transform="translate(100 100)" /></svg>
                    <div class="relative z-10">
                        <h3 class="text-3xl font-black mb-4 text-white">Para Pacientes</h3>
                        <!-- Texto en blanco para mejor contraste sobre fondo morado -->
                        <p class="text-white/85 mb-8 font-medium">La salud es prioridad. Regístrate ahora para agendar tu primera cita y recibir recordatorios inteligentes.</p>
                        <Link :href="route('register')" class="inline-block px-10 py-5 bg-white text-brand-teal rounded-[1.8rem] font-black uppercase tracking-widest hover:bg-gray-100 transition shadow-lg shadow-brand-teal/20">Registrarse Ahora</Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-900 pt-20 pb-10 border-t border-gray-100 dark:border-gray-800 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-4 gap-12 mb-20">
                    <div class="col-span-1 md:col-span-2">
                        <ApplicationLogo class="mb-8" />
                        <p class="text-gray-500 dark:text-gray-400 font-medium max-w-sm leading-relaxed">
                            Turno Médico es la plataforma líder en gestión de citas médicas digitales, conectando a miles de profesionales con pacientes que buscan atención de calidad.
                        </p>
                    </div>
                    
                    <div>
                        <h5 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-8">Enlaces Rápidos</h5>
                        <ul class="space-y-4">
                            <li><a href="#" class="text-sm font-bold text-gray-500 hover:text-brand-teal transition-colors">Buscador</a></li>
                            <li><a href="#" class="text-sm font-bold text-gray-500 hover:text-brand-teal transition-colors">Doctores</a></li>
                            <li><a href="#" class="text-sm font-bold text-gray-500 hover:text-brand-teal transition-colors">Especialidades</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h5 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-8">Contacto</h5>
                        <ul class="space-y-4">
                            <li class="text-sm font-bold text-gray-500">contacto@turnomedico.com</li>
                            <li class="text-sm font-bold text-gray-500">+1 (800) MED-DOCS</li>
                        </ul>
                    </div>
                </div>
                
                <div class="pt-10 border-t border-gray-100 dark:border-gray-800 flex flex-col md:flex-row justify-between items-center gap-6">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">© 2026 Turno Médico. Todos los derechos reservados.</p>
                    <div class="flex gap-6">
                        <a href="#" class="text-gray-400 hover:text-brand-teal transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-brand-teal transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Role Selection Modal -->
        <div v-if="showLoginSelector" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="handleLoginToggle"></div>
            
            <div class="relative bg-white dark:bg-gray-800 w-full max-w-2xl rounded-[3rem] shadow-2xl border border-gray-100 dark:border-gray-700 p-8 md:p-12 animate-in zoom-in duration-300">
                <button @click="handleLoginToggle" class="absolute top-8 right-8 text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="text-center mb-12">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-4 uppercase tracking-tight">Bienvenido de Nuevo</h3>
                    <p class="text-gray-500 dark:text-gray-400 font-medium tracking-wide">Selecciona cómo deseas ingresar a Turno Médico</p>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Patient Path -->
                    <Link :href="route('login', { role: 'patient' })" class="group relative bg-brand-teal/10 dark:bg-brand-teal/20 p-8 rounded-[2rem] text-center hover:bg-brand-teal transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-brand-teal/30">
                        <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-6 text-brand-teal group-hover:scale-110 transition-transform shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h4 class="text-xl font-black text-brand-teal group-hover:text-white mb-2 uppercase tracking-tight">Soy Paciente</h4>
                        <p class="text-brand-teal group-hover:text-brand-teal/90 text-[10px] font-black uppercase tracking-widest">Mis citas y salud</p>
                        <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-[2rem]"></div>
                    </Link>

                    <!-- Doctor Path -->
                    <Link :href="route('login', { role: 'doctor' })" class="group relative bg-emerald-50 dark:bg-emerald-900/30 p-8 rounded-[2rem] text-center hover:bg-emerald-600 transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-emerald-200">
                        <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-6 text-emerald-600 group-hover:scale-110 transition-transform shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.691.34a6 6 0 01-3.86.517l-2.387-.477a2 2 0 00-1.022.547l-1.162 1.163a1 1 0 001.414 1.414l1.163-1.163 2.387.477a4 4 0 002.573-.344l.69-.34a4 4 0 002.574-.344l2.387.477 1.162 1.163a1 1 0 001.414-1.414l-1.162-1.163z"></path></svg>
                        </div>
                        <h4 class="text-xl font-black text-emerald-600 group-hover:text-white mb-2 uppercase tracking-tight">Soy Médico</h4>
                        <p class="text-emerald-400 group-hover:text-emerald-100 text-[10px] font-black uppercase tracking-widest">Mi consultorio digital</p>
                        <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-[2rem]"></div>
                    </Link>
                </div>

                <p class="text-center mt-12 text-gray-400 text-[10px] font-black uppercase tracking-widest">
                    Administrador? <Link :href="route('login')" class="text-brand-teal hover:text-brand-teal transition ml-2">Haz clic aquí</Link>
                </p>
            </div>
        </div>

        <!-- Role Selection Modal (Registration) -->
        <div v-if="showRegisterSelector" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="handleRegisterToggle"></div>
            
            <div class="relative bg-white dark:bg-gray-800 w-full max-w-2xl rounded-[3rem] shadow-2xl border border-gray-100 dark:border-gray-700 p-8 md:p-12 animate-in zoom-in duration-300">
                <button @click="handleRegisterToggle" class="absolute top-8 right-8 text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="text-center mb-12">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-4 uppercase tracking-tight">Únete a Nosotros</h3>
                    <p class="text-gray-500 dark:text-gray-400 font-medium tracking-wide">Crea tu cuenta profesional o de paciente en segundos</p>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Patient Path -->
                    <Link :href="route('register', { role: 'patient' })" class="group relative bg-brand-teal/10 dark:bg-brand-teal/20 p-8 rounded-[2rem] text-center hover:bg-brand-teal transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-brand-teal/30">
                        <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-6 text-brand-teal group-hover:scale-110 transition-transform shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h4 class="text-xl font-black text-brand-teal group-hover:text-white mb-2 uppercase tracking-tight">Soy Paciente</h4>
                        <p class="text-brand-teal group-hover:text-brand-teal/90 text-[10px] font-black uppercase tracking-widest">Agendar mis citas</p>
                        <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-[2rem]"></div>
                    </Link>

                    <!-- Doctor Path -->
                    <Link :href="route('register', { role: 'doctor' })" class="group relative bg-emerald-50 dark:bg-emerald-900/30 p-8 rounded-[2rem] text-center hover:bg-emerald-600 transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-emerald-200">
                        <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-6 text-emerald-600 group-hover:scale-110 transition-transform shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.691.34a6 6 0 01-3.86.517l-2.387-.477a2 2 0 00-1.022.547l-1.162 1.163a1 1 0 001.414 1.414l1.163-1.163 2.387.477a4 4 0 002.573-.344l.69-.34a4 4 0 002.574-.344l2.387.477 1.162 1.163a1 1 0 001.414-1.414l-1.162-1.163z"></path></svg>
                        </div>
                        <h4 class="text-xl font-black text-emerald-600 group-hover:text-white mb-2 uppercase tracking-tight">Soy Médico</h4>
                        <p class="text-emerald-400 group-hover:text-emerald-100 text-[10px] font-black uppercase tracking-widest">Gestionar mi agenda</p>
                        <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-[2rem]"></div>
                    </Link>
                </div>

                <p class="text-center mt-12 text-gray-400 text-[10px] font-black uppercase tracking-widest">
                    ¿Ya tienes cuenta? <button @click="() => { handleRegisterToggle(); handleLoginToggle(); }" class="text-brand-teal hover:text-brand-teal transition ml-2">Ingresa aquí</button>
                </p>
            </div>
        </div>
    </div>
</template>

<style>
.bg-brand-teal {
    background-color: #4f46e5;
}
.text-brand-teal {
    color: #4f46e5;
}
.font-black {
    font-weight: 900;
}
.animate-in {
    animation-duration: 1s;
    animation-fill-mode: both;
}
</style>
