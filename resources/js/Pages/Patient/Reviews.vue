<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    reviews: Array
});

const getInitial = (name) => name ? name.charAt(0).toUpperCase() : 'U';

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Mis Reseñas" />

    <PatientLayout>
        <!-- Header Banner -->
        <template #header>
            <div class="bg-[#1e293b] dark:bg-[#161920] py-12 px-8 mb-8 relative overflow-hidden">
                <div class="max-w-7xl mx-auto relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 mb-2">Inicio > Reseñas</p>
                    <h1 class="text-4xl font-black text-white tracking-tight uppercase">Mis Reseñas</h1>
                </div>
                <div class="absolute right-0 top-0 w-1/3 h-full bg-gradient-to-l from-amber-500/10 to-transparent"></div>
            </div>
        </template>

        <!-- Reviews Grid -->
        <div v-if="reviews.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="review in reviews" :key="review.id" class="bg-white dark:bg-[#161920] rounded-[2rem] p-8 shadow-sm border border-gray-100 dark:border-white/5 flex flex-col gap-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="flex text-amber-400">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'fill-current' : 'text-gray-200 dark:text-gray-700'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                        <span class="text-xs font-black text-gray-400 tabular-nums">{{ review.rating }}/5</span>
                    </div>
                    <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest">{{ formatDate(review.created_at) }}</span>
                </div>

                <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed italic">
                    "{{ review.comment }}"
                </p>

                <div class="mt-auto pt-6 border-t border-gray-50 dark:border-white/5 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-black text-indigo-500 overflow-hidden shrink-0">
                        <img v-if="review.doctor_profile.user.profile_photo_url" :src="review.doctor_profile.user.profile_photo_url" class="w-full h-full object-cover" />
                        <span v-else>{{ getInitial(review.doctor_profile.user.name) }}</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Doctor</p>
                        <p class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-tight">{{ review.doctor_profile.user.name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-24">
            <div class="w-24 h-24 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-300">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.175 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
            </div>
            <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight mb-2">No has escrito reseñas</h3>
            <p class="text-gray-400 text-sm mb-8">Tus opiniones aparecerán aquí cuando califiques tus consultas.</p>
        </div>
    </PatientLayout>
</template>
