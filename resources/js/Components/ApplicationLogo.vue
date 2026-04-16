<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
const props = defineProps({
    showText: {
        type: Boolean,
        default: false
    },
    compact: {
        type: Boolean,
        default: false
    }
});
const siteLogo = computed(() => usePage().props.site_logo);
const logoUrl = computed(() => siteLogo.value || '/images/logo.png');
const useImageLogo = computed(() => !!logoUrl.value);
// Tamaños: versión compacta pensada para la barra superior (similar al alto de la nav)
const boxSize = computed(() => (props.compact ? 'w-14 h-14' : 'w-24 h-24'));
const iconSize = computed(() => (props.compact ? 'w-6 h-6' : 'w-8 h-8'));
</script>

<template>
    <div class="flex items-center gap-3 shrink-0">
        <!-- Contenedor del logo: solo el icono (recorte superior para ocultar texto en la imagen) -->
        <div
            class="shrink-0 overflow-hidden rounded-2xl bg-white dark:bg-gray-800/80 shadow-md flex items-center justify-center"
            :class="useImageLogo ? boxSize : (props.compact ? 'w-14 h-14 bg-brand-teal' : 'w-24 h-24 bg-brand-teal')"
        >
            <template v-if="useImageLogo">
                <img
                    :src="logoUrl"
                    alt="Logo"
                    :class="[
                        'w-full h-full',
                        compact ? 'object-contain' : 'object-cover object-top'
                    ]"
                />
            </template>
            <template v-else>
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" :class="iconSize">
                    <circle cx="50" cy="50" r="45" stroke="white" stroke-width="8" stroke-linecap="round"/>
                    <rect x="42" y="25" width="16" height="50" rx="4" fill="white"/>
                    <rect x="25" y="42" width="50" height="16" rx="4" fill="white"/>
                    <path d="M20 50H32L38 35L47 65L53 50H80" stroke="rgba(255,255,255,0.4)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </template>
        </div>
        <span v-if="showText" class="font-black text-xl tracking-tight text-gray-800 dark:text-white uppercase whitespace-nowrap">
            Turno<span class="text-brand-teal">Médico</span>
        </span>
    </div>
</template>
