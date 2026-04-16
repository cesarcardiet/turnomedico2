<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    favorites: Array
});

const removeFavorite = (id) => {
    router.delete(route('patient.favorites.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
            // Toast notification handled by layout
        }
    });
};

const getInitial = (name) => name ? name.charAt(0).toUpperCase() : 'U';
</script>

<template>
    <Head title="Médicos Favoritos" />

    <PatientLayout>
        <!-- Header Banner -->
        <template #header>
            <div class="bg-[#1e293b] dark:bg-[#161920] py-12 px-8 mb-8 relative overflow-hidden">
                <div class="max-w-7xl mx-auto relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 mb-2">Inicio > Favoritos</p>
                    <h1 class="text-4xl font-black text-white tracking-tight uppercase">Mis Médicos Favoritos</h1>
                </div>
                <div class="absolute right-0 top-0 w-1/3 h-full bg-gradient-to-l from-pink-500/10 to-transparent"></div>
            </div>
        </template>

        <!-- Search/Results Grid -->
        <div v-if="favorites.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="doctor in favorites" :key="doctor.id" class="bg-white dark:bg-[#161920] rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-white/5 flex flex-col gap-6 group hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 relative overflow-hidden">
                
                <!-- Remove Button -->
                <button @click="removeFavorite(doctor.id)" class="absolute top-4 right-4 text-rose-400 hover:text-rose-600 transition z-10 p-2 bg-white/50 dark:bg-black/20 rounded-full backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                </button>

                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-[1.5rem] bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center font-black text-2xl text-indigo-600 overflow-hidden shrink-0 border-2 border-white dark:border-gray-800 shadow-lg">
                        <img v-if="doctor.user.profile_photo_url" :src="doctor.user.profile_photo_url" class="w-full h-full object-cover" />
                        <span v-else>{{ getInitial(doctor.user.name) }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-white uppercase tracking-tight leading-tight mb-1">{{ doctor.user.name }}</h3>
                        <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 dark:bg-indigo-900/20 px-3 py-1 rounded-full inline-block">{{ doctor.speciality?.name }}</p>
                    </div>
                </div>

                <div class="space-y-3 border-t border-gray-50 dark:border-white/5 pt-4">
                    <div class="flex items-center gap-3 text-xs font-bold text-gray-400">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                        <span>{{ doctor.city || 'Santo Domingo' }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-bold text-gray-400">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Consulta: RD$ {{ doctor.consultation_price || '2000' }}</span>
                    </div>
                </div>

                <Link :href="route('patient.doctor.profile', doctor.id)" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest text-center shadow-lg shadow-indigo-500/20 transition-all mt-auto">
                    Agendar Cita
                </Link>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-24">
            <div class="w-24 h-24 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-300">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight mb-2">No tienes favoritos aún</h3>
            <p class="text-gray-400 text-sm mb-8">Marca como favorito a los doctores para acceder rápidamente a ellos.</p>
            <Link :href="route('patient.search')" class="inline-flex px-8 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/20">
                Buscar Médicos
            </Link>
        </div>
    </PatientLayout>
</template>
