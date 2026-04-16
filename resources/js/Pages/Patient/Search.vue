<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, nextTick } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    doctors: Array,
    specialities: Array,
    cities: Array,
    filters: Object,
    google_maps_api_key: String
});

const search = ref(props.filters?.search || '');
const speciality = ref(props.filters?.speciality || '');
const city = ref(props.filters?.city || '');
const useLocation = ref(!!(props.filters?.lat && props.filters?.lng));
const radiusKm = ref(props.filters?.radius_km || 25);
const userLat = ref(props.filters?.lat || null);
const userLng = ref(props.filters?.lng || null);
const locationLoading = ref(false);
const locationError = ref(null);
const showMap = ref(false);
const mapRef = ref(null);
let mapInstance = null;
let markers = [];

const performSearch = debounce(() => {
    const payload = {
        search: search.value,
        speciality: speciality.value,
        city: city.value || undefined
    };
    if (useLocation.value && userLat.value != null && userLng.value != null) {
        payload.lat = userLat.value;
        payload.lng = userLng.value;
        payload.radius_km = radiusKm.value;
    }
    router.get(route('patient.search'), payload, { preserveState: true, replace: true });
}, 300);

watch([search, speciality, city, useLocation, userLat, userLng, radiusKm], performSearch);

const getMyLocation = () => {
    locationError.value = null;
    if (!navigator.geolocation) {
        locationError.value = 'Tu navegador no soporta geolocalización.';
        return;
    }
    locationLoading.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            userLat.value = pos.coords.latitude;
            userLng.value = pos.coords.longitude;
            useLocation.value = true;
            locationLoading.value = false;
        },
        () => {
            locationError.value = 'No se pudo obtener tu ubicación. Revisa permisos o conexión.';
            locationLoading.value = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 }
    );
};

const clearLocation = () => {
    useLocation.value = false;
    userLat.value = null;
    userLng.value = null;
    locationError.value = null;
};

const doctorsWithCoords = () => (props.doctors || []).filter(d => d.latitude != null && d.longitude != null);

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

function initMap() {
    if (!mapRef.value || !window.google?.maps || !props.google_maps_api_key) return;
    const withCoords = doctorsWithCoords();
    const centerLat = userLat.value ?? (withCoords[0]?.latitude ? parseFloat(withCoords[0].latitude) : 18.4861);
    const centerLng = userLng.value ?? (withCoords[0]?.longitude ? parseFloat(withCoords[0].longitude) : -69.9312);
    mapInstance = new window.google.maps.Map(mapRef.value, {
        center: { lat: centerLat, lng: centerLng },
        zoom: 10,
        minZoom: 8,
        maxZoom: 16,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true,
        zoomControl: true
    });
    markers.forEach(m => m.setMap(null));
    markers = [];
    withCoords.forEach(d => {
        const lat = parseFloat(d.latitude);
        const lng = parseFloat(d.longitude);
        const m = new window.google.maps.Marker({
            position: { lat, lng },
            map: mapInstance,
            title: d.user?.name || 'Doctor',
            label: { text: '●', color: '#818cf8', fontSize: '16px' }
        });
        m.addListener('click', () => window.location.href = route('patient.doctor.profile', d.id));
        markers.push(m);
    });
    if (userLat.value != null && userLng.value != null) {
        const me = new window.google.maps.Marker({
            position: { lat: userLat.value, lng: userLng.value },
            map: mapInstance,
            icon: { path: window.google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: '#22c55e', fillOpacity: 1, strokeColor: '#fff', strokeWeight: 2 },
            title: 'Tu ubicación'
        });
        markers.push(me);
    }
    const bounds = new window.google.maps.LatLngBounds();
    withCoords.forEach(d => bounds.extend({ lat: parseFloat(d.latitude), lng: parseFloat(d.longitude) }));
    if (userLat.value != null && userLng.value != null) bounds.extend({ lat: userLat.value, lng: userLng.value });
    if (bounds.getNorthEast && bounds.getSouthWest()) {
        mapInstance.fitBounds(bounds, 60);
        // Limitar zoom para que el mapa no quede muy cerca (más alejado = ver más área y puntos de doctores)
        const listener = window.google.maps.event.addListenerOnce(mapInstance, 'idle', () => {
            const z = mapInstance.getZoom();
            if (z > 11) mapInstance.setZoom(11);
        });
    }
    // Forzar redibujado del mapa cuando el contenedor ya tiene tamaño (evita mapa en azul vacío)
    setTimeout(() => {
        if (mapInstance && window.google?.maps?.event) {
            window.google.maps.event.trigger(mapInstance, 'resize');
            mapInstance.setCenter({ lat: centerLat, lng: centerLng });
            if (bounds.getNorthEast && bounds.getSouthWest()) {
                mapInstance.fitBounds(bounds, 60);
                const z = mapInstance.getZoom();
                if (z > 11) mapInstance.setZoom(11);
            }
        }
    }, 150);
}

onMounted(() => {
    if (props.filters?.lat != null) userLat.value = parseFloat(props.filters.lat);
    if (props.filters?.lng != null) userLng.value = parseFloat(props.filters.lng);
});

watch(showMap, async (visible) => {
    if (visible && props.google_maps_api_key) {
        await nextTick();
        await loadGoogleMaps();
        await nextTick();
        setTimeout(() => initMap(), 200);
    }
});

watch(() => props.doctors, () => {
    if (showMap.value && mapInstance && window.google?.maps) initMap();
}, { deep: true });

const getInitial = (name) => name ? name.charAt(0).toUpperCase() : 'M';
</script>

<template>
    <Head title="Turno Médico - Buscar Especialistas" />

    <div class="min-h-screen bg-slate-50 text-gray-800 font-sans selection:bg-brand-teal/30">
        <!-- Compact Premium Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-xl border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link :href="route('welcome')" class="hover:opacity-80 transition">
                    <!-- Logo compacto en barra de búsqueda de doctores -->
                    <ApplicationLogo compact class="brightness-0 invert" />
                </Link>
                
                <div class="hidden md:flex items-center gap-6">
                    <Link :href="route('welcome')" class="text-[10px] font-black text-gray-500 uppercase tracking-widest hover:text-white transition">Inicio</Link>
                    <Link :href="route('patient.search')" class="text-[10px] font-black text-brand-teal uppercase tracking-widest">Busca tu Doctor</Link>
                </div>

                <div v-if="!$page.props.auth.user">
                    <Link :href="route('login')" class="px-5 py-2 bg-brand-teal text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-brand-dark transition shadow-lg shadow-brand-teal/20">Unirse</Link>
                </div>
                <div v-else>
                    <Link :href="route('dashboard')" class="text-[10px] font-black text-brand-teal uppercase tracking-widest hover:text-brand-light transition">Mi Panel</Link>
                </div>
            </div>
        </nav>

        <!-- Refined Hero Section -->
        <section class="pt-24 pb-16 bg-white relative border-b border-gray-100">
            <div class="absolute inset-0 bg-gradient-to-b from-white to-slate-50"></div>
            <div class="max-w-5xl mx-auto px-6 relative z-10">
                <div class="flex items-center justify-center gap-2 text-gray-500 text-[9px] font-black uppercase tracking-[0.2em] mb-4">
                    <Link :href="route('welcome')" class="hover:text-brand-dark transition">Inicio</Link>
                    <span class="opacity-20">/</span>
                    <span class="text-brand-teal/80">Resultados</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 text-center uppercase tracking-tight mb-8">Encuentra tu Especialista</h1>
                
                <!-- Compact Search Bar -->
                <div class="max-w-xl mx-auto">
                    <div class="bg-white p-1.5 rounded-2xl shadow-2xl border border-gray-200 flex items-center gap-2">
                        <div class="flex-1 flex items-center pl-4">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Nombre, especialidad..." 
                                class="w-full bg-transparent border-none focus:ring-0 text-sm font-bold text-gray-900 placeholder-gray-400 py-3"
                            >
                        </div>
                        <button class="px-6 py-3 bg-brand-teal text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-brand-dark transition">Buscar</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="py-12 px-6">
            <div class="max-w-7xl mx-auto">
                <!-- Toolbar -->
                <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6">
                    <div>
                        <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-1">Resultados Found</h2>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Mostrando <span class="text-brand-teal">{{ doctors.length }}</span> doctores disponibles</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="bg-white border border-gray-200 rounded-xl px-4 py-1 flex items-center">
                            <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest mr-3">Filtro:</span>
                            <select v-model="speciality" class="bg-transparent border-none focus:ring-0 text-[11px] font-bold text-gray-300 py-2 cursor-pointer">
                                <option value="">Especialidad</option>
                                <option v-for="spec in specialities" :key="spec.id" :value="spec.id" class="bg-[#161920]">{{ spec.name }}</option>
                            </select>
                        </div>
                        <div v-if="cities && cities.length" class="bg-white border border-gray-200 rounded-xl px-4 py-1 flex items-center">
                            <select v-model="city" class="bg-transparent border-none focus:ring-0 text-[11px] font-bold text-gray-300 py-2 cursor-pointer">
                                <option value="">Ciudad</option>
                                <option v-for="c in cities" :key="c.id" :value="c.name" class="bg-[#161920]">{{ c.name }}</option>
                            </select>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-xl px-4 py-2 flex items-center gap-2">
                            <div class="flex flex-col">
                                <button 
                                    type="button"
                                    @click="getMyLocation"
                                    :disabled="locationLoading"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition"
                                    :class="useLocation && userLat ? 'bg-brand-teal text-white' : 'bg-slate-100 text-gray-500 hover:bg-slate-200 hover:text-gray-800'"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                    {{ locationLoading ? 'Obteniendo...' : (useLocation && userLat ? 'Ubicación activa' : 'Usar mi ubicación') }}
                                </button>
                                <span class="text-[8px] text-gray-500 mt-0.5">El navegador pedirá permiso de ubicación</span>
                            </div>
                            <template v-if="useLocation && userLat">
                                <select v-model="radiusKm" class="bg-white border border-gray-200 focus:ring-0 text-[10px] font-bold text-gray-700 py-1 rounded cursor-pointer">
                                    <option :value="5">5 km</option>
                                    <option :value="10">10 km</option>
                                    <option :value="25">25 km</option>
                                    <option :value="50">50 km</option>
                                </select>
                                <button type="button" @click="clearLocation" class="p-1 text-gray-500 hover:text-rose-400 transition" title="Quitar filtro de ubicación">×</button>
                            </template>
                        </div>
                        <p v-if="locationError" class="text-[10px] text-rose-400 font-bold">{{ locationError }}</p>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Grid -->
                    <div class="flex-1">
                        <div v-if="doctors.length > 0" class="grid sm:grid-cols-2 gap-6">
                            <div v-for="doctor in doctors" :key="doctor.id" class="group bg-white rounded-3xl overflow-hidden border border-gray-100 hover:border-brand-teal/40 transition-all duration-500 flex flex-col shadow-lg shadow-slate-100">
                                <!-- Photo Area -->
                                <div class="relative h-48 bg-slate-100 overflow-hidden">
                                    <div class="absolute inset-0 bg-brand-teal/5 group-hover:bg-transparent transition-colors"></div>
                                    <template v-if="doctor.profile_photo_url">
                                        <img :src="doctor.profile_photo_url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" :alt="doctor.user?.name">
                                    </template>
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <div class="w-16 h-16 bg-slate-200 rounded-2xl flex items-center justify-center text-2xl font-black text-brand-teal">
                                            {{ getInitial(doctor.user?.name) }}
                                        </div>
                                    </div>
                                    <div class="absolute top-4 right-4 px-2 py-1 bg-black/40 backdrop-blur-md rounded-lg flex items-center gap-1 border border-white/10 uppercase font-black text-[9px] tracking-widest text-amber-500">
                                        <span>★</span> <span>{{ doctor.rating || '5.0' }}</span>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="p-6 flex-1 flex flex-col">
                                    <div class="mb-4">
                                        <h3 class="text-base font-black text-gray-900 uppercase tracking-tight group-hover:text-brand-teal transition-colors leading-tight mb-1">{{ doctor.user?.name }}</h3>
                                        <p class="text-[9px] font-black text-brand-teal/80 uppercase tracking-widest">{{ doctor.speciality?.name || 'General' }}</p>
                                    </div>

                                    <div class="space-y-2 mb-6 opacity-60">
                                        <div class="flex items-center gap-2 text-[11px] font-bold">
                                            <svg class="w-3.5 h-3.5 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                            <span class="truncate">{{ doctor.clinic_address || 'Santo Domingo, RD' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] font-bold">
                                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span>Disponible hoy</span>
                                        </div>
                                    </div>

                                    <Link :href="route('patient.doctor.profile', doctor.id)" class="w-full h-11 inline-flex items-center justify-center bg-brand-teal text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-brand-dark transition shadow-lg shadow-brand-teal/20 active:scale-95">
                                        Ver Perfil
                                        <svg class="w-3 h-3 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path></svg>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Empty -->
                        <div v-else class="text-center py-24 bg-white rounded-[2rem] border border-gray-200 border-dashed">
                            <span class="text-4xl block mb-4">🛰️</span>
                            <h3 class="text-sm font-black text-white uppercase tracking-widest mb-1">Sin resultados</h3>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Intenta con otros filtros</p>
                        </div>
                    </div>

                    <!-- Map panel -->
                    <div class="hidden xl:block w-[320px] shrink-0">
                        <div class="sticky top-24 h-[600px] bg-white rounded-[2rem] overflow-hidden border border-gray-200 relative shadow-2xl">
                            <template v-if="google_maps_api_key && showMap">
                                <div ref="mapRef" class="absolute inset-0 w-full h-full min-h-[400px] rounded-[2rem]" style="min-width: 280px;"></div>
                            </template>
                            <template v-else>
                                <div class="absolute inset-0 bg-slate-100 flex items-center justify-center">
                                    <div class="text-center px-4">
                                        <div class="w-16 h-16 rounded-2xl bg-brand-teal/10 flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-8 h-8 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                        </div>
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-4">Mapa de doctores</p>
                                        <button 
                                            v-if="google_maps_api_key"
                                            @click="showMap = true"
                                            class="px-6 py-3 bg-brand-teal text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-2xl hover:bg-brand-dark transition"
                                        >
                                            Visualizar Mapa
                                        </button>
                                        <p v-else class="text-[9px] text-gray-500">Configura la API de Google Maps en Admin para ver el mapa.</p>
                                    </div>
                                </div>
                            </template>
                            <button 
                                v-if="showMap && google_maps_api_key" 
                                @click="showMap = false" 
                                class="absolute top-3 right-3 z-10 w-9 h-9 bg-black/50 hover:bg-black/70 rounded-lg flex items-center justify-center text-white text-lg font-bold transition"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Compact Footer -->
        <footer class="bg-slate-100 pt-16 pb-8 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-2">
                    <ApplicationLogo class="brightness-0 invert mb-6 opacity-40" />
                    <p class="text-[11px] text-gray-600 max-w-xs font-medium leading-relaxed uppercase tracking-tighter">
                        Tecnología médica avanzada para una gestión de citas eficiente y de alta gama en toda RD.
                    </p>
                </div>
                <div>
                    <h4 class="text-[9px] font-black text-brand-teal uppercase tracking-widest mb-6">Enlaces</h4>
                    <ul class="space-y-3 text-[10px] text-gray-500 font-black uppercase tracking-widest">
                        <li><a href="#" class="hover:text-white transition">Médicos</a></li>
                        <li><a href="#" class="hover:text-white transition">Empresas</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[9px] font-black text-brand-teal uppercase tracking-widest mb-6">Soporte</h4>
                    <p class="text-[10px] text-gray-500 font-black uppercase tracking-widest">+1 (829) 555-0100</p>
                </div>
            </div>
            <div class="max-w-7xl mx-auto px-6 pt-8 border-t border-gray-200 text-center text-[8px] font-black uppercase tracking-[0.3em] text-gray-500">
                © 2024 Turno Médico. Precision & Elegance.
            </div>
        </footer>
    </div>
</template>

<style scoped>
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
</style>
