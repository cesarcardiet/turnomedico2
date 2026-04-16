<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    modelValue: String,
    label: String
});

const emit = defineEmits(['update:modelValue']);

// Parse initial value (HH:mm)
const parseInitial = () => {
    if (!props.modelValue) return { h: '09', m: '00', p: 'a.m.' };
    const parts = props.modelValue.split(':');
    if (parts.length < 2) return { h: '09', m: '00', p: 'a.m.' };
    
    let h = parseInt(parts[0]);
    const m = parts[1];
    const p = h >= 12 ? 'p.m.' : 'a.m.';
    
    if (h > 12) h -= 12;
    if (h === 0) h = 12;
    
    return { h: h.toString().padStart(2, '0'), m, p };
};

const initial = parseInitial();
const selectedHour = ref(initial.h);
const selectedMinute = ref(initial.m);
const selectedPeriod = ref(initial.p);

const hours = Array.from({ length: 12 }, (_, i) => (i + 1).toString().padStart(2, '0'));
const minutes = Array.from({ length: 60 }, (_, i) => i.toString().padStart(2, '0'));
const periods = ['a.m.', 'p.m.'];

const isOpen = ref(false);

const updateValue = () => {
    let hh = parseInt(selectedHour.value);
    if (selectedPeriod.value === 'p.m.' && hh < 12) hh += 12;
    if (selectedPeriod.value === 'a.m.' && hh === 12) hh = 0;
    
    const formattedTime = `${hh.toString().padStart(2, '0')}:${selectedMinute.value}:00`;
    emit('update:modelValue', formattedTime);
};

watch([selectedHour, selectedMinute, selectedPeriod], updateValue);

const toggle = (e) => {
    e.stopPropagation();
    isOpen.value = !isOpen.value;
};

// Close on outside click
onMounted(() => {
    window.addEventListener('click', () => {
        isOpen.value = false;
    });
});
</script>

<template>
    <div class="time-picker-container relative w-full">
        <div 
            @click="toggle"
            class="bg-[#f3ebe4] dark:bg-[#252a33] rounded-2xl p-5 flex items-center justify-between cursor-pointer border border-transparent dark:border-white/5 hover:border-blue-500/30 transition-all shadow-sm"
        >
            <span class="text-gray-800 dark:text-gray-100 font-black text-sm uppercase tracking-wider">
                {{ selectedHour }}:{{ selectedMinute }} {{ selectedPeriod }}
            </span>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <!-- Custom Dropdown -->
        <div 
            v-if="isOpen"
            @click.stop
            class="absolute top-full left-0 mt-3 bg-white dark:bg-[#1c2128] border border-gray-100 dark:border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.2)] rounded-[2rem] z-[999] p-3 flex gap-1 w-full min-w-[280px] animate-in fade-in slide-in-from-top-2 duration-300"
        >
            <!-- Hours Column -->
            <div class="flex-1 max-h-[280px] overflow-y-auto custom-scrollbar p-1">
                <div class="text-[9px] font-black text-gray-300 uppercase tracking-widest text-center mb-2">Hrs</div>
                <div 
                    v-for="h in hours" 
                    :key="h"
                    @click="selectedHour = h"
                    :class="selectedHour === h ? 'bg-[#0052cc] text-white shadow-lg shadow-blue-500/30 font-black scale-105' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'"
                    class="py-4 px-2 text-center text-xs cursor-pointer rounded-xl transition-all duration-200 mb-1"
                >
                    {{ h }}
                </div>
            </div>

            <!-- Minutes Column -->
            <div class="flex-1 max-h-[280px] overflow-y-auto custom-scrollbar p-1 border-x border-gray-100 dark:border-white/5">
                <div class="text-[9px] font-black text-gray-300 uppercase tracking-widest text-center mb-2">Min</div>
                <div 
                    v-for="m in minutes" 
                    :key="m"
                    @click="selectedMinute = m"
                    :class="selectedMinute === m ? 'bg-[#0052cc] text-white shadow-lg shadow-blue-500/30 font-black scale-105' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'"
                    class="py-4 px-2 text-center text-xs cursor-pointer rounded-xl transition-all duration-200 mb-1"
                >
                    {{ m }}
                </div>
            </div>

            <!-- Period Column -->
            <div class="flex-1 max-h-[280px] overflow-y-auto custom-scrollbar p-1">
                <div class="text-[9px] font-black text-gray-300 uppercase tracking-widest text-center mb-2">AM/PM</div>
                <div 
                    v-for="p in periods" 
                    :key="p"
                    @click="selectedPeriod = p"
                    :class="selectedPeriod === p ? 'bg-[#0052cc] text-white shadow-lg shadow-blue-500/30 font-black scale-105' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'"
                    class="py-4 px-2 text-center text-[10px] cursor-pointer rounded-xl transition-all duration-200 mb-1 uppercase"
                >
                    {{ p }}
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 82, 204, 0.2) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0, 82, 204, 0.1);
    border-radius: 20px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.03);
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 82, 204, 0.3);
}

/* Scroll Snap could be nice but can be annoying if not implemented with a library for desktop */
</style>
