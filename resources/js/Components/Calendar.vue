<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    availableDates: {
        type: Array,
        default: () => []
    },
    modelValue: String
});

const emit = defineEmits(['update:modelValue', 'select']);

const currentDate = ref(new Date());
const daysOfWeek = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
const months = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const calendarDays = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const firstDayOfMonth = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    const days = [];
    
    // Previous month padding
    for (let i = 0; i < firstDayOfMonth; i++) {
        days.push({ day: null, date: null });
    }
    
    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        const dateObj = new Date(year, month, i);
        const dateStr = dateObj.toISOString().split('T')[0];
        const isAvailable = props.availableDates.includes(dateStr);
        
        // Today logic in local time
        const today = new Date();
        const localTodayStr = today.getFullYear() + '-' + 
                              String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                              String(today.getDate()).padStart(2, '0');
        
        const isToday = localTodayStr === dateStr;
        
        days.push({
            day: i,
            date: dateStr,
            isAvailable,
            isToday,
            isPast: dateObj < new Date(today.setHours(0,0,0,0))
        });
    }
    
    return days;
});

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
};

const prevMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
};

const selectDate = (date) => {
    if (!date) return;
    emit('update:modelValue', date);
    emit('select', date);
};
</script>

<template>
    <div class="calendar-container border border-gray-100 dark:border-white/5 bg-white dark:bg-[#161920] rounded-[2rem] p-6 shadow-2xl w-full max-w-[320px] mx-auto transition-all duration-500">
        <!-- Calendar Header -->
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-[0.2em]">
                {{ months[currentDate.getMonth()] }} <span class="text-indigo-500">{{ currentDate.getFullYear() }}</span>
            </h4>
            <div class="flex gap-2">
                <button @click="prevMonth" class="p-2 bg-white/5 hover:bg-white/10 rounded-xl transition text-gray-400 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button @click="nextMonth" class="p-2 bg-white/5 hover:bg-white/10 rounded-xl transition text-gray-400 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>

        <!-- Days of Week -->
        <div class="grid grid-cols-7 gap-2 mb-4">
            <div v-for="day in daysOfWeek" :key="day" class="text-center text-[9px] font-black text-gray-500 uppercase tracking-widest">
                {{ day }}
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-2">
            <div v-for="(day, index) in calendarDays" :key="index" class="aspect-square">
                <button
                    v-if="day.day"
                    @click="selectDate(day.date)"
                    :disabled="day.isPast && !day.isAvailable"
                    :class="[
                        'w-full h-full rounded-2xl flex flex-col items-center justify-center transition-all relative group border',
                        day.date === modelValue ? 'bg-indigo-600 text-white border-indigo-500 shadow-xl shadow-indigo-500/20 scale-105 z-10' : 
                        day.isAvailable ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20 hover:bg-indigo-500/20 font-black' : 
                        'bg-white text-gray-700 dark:bg-white/[0.02] dark:text-gray-400 border-gray-100 dark:border-transparent hover:border-gray-200 dark:hover:border-white/10',
                        day.isToday && day.date !== modelValue ? 'ring-2 ring-indigo-500 ring-offset-4 ring-offset-white dark:ring-offset-[#161920]' : '',
                        day.isPast && !day.isAvailable ? 'opacity-10 cursor-not-allowed grayscale' : ''
                    ]"
                >
                    <span class="text-[11px] font-black tracking-tighter">{{ day.day }}</span>
                    <!-- Dot for availability -->
                    <span v-if="day.isAvailable && day.date !== modelValue" class="absolute bottom-1.5 w-1 h-1 bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.8)]"></span>
                </button>
            </div>
        </div>
    </div>
</template>
