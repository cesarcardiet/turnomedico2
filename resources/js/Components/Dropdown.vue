<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        align?: 'left' | 'right';
        width?: '48';
        contentClasses?: string;
        closeOnClick?: boolean;
    }>(),
    {
        align: 'right',
        width: '48',
        contentClasses: 'py-1 bg-white dark:bg-gray-700',
        closeOnClick: true,
    },
);

const open = ref(false);
const triggerRef = ref<HTMLElement | null>(null);
const panelRef = ref<HTMLElement | null>(null);

const closeOnEscape = (e: KeyboardEvent) => {
    if (open.value && e.key === 'Escape') open.value = false;
};

const closeOnClickOutside = (e: MouseEvent) => {
    const target = e.target as Node;
    if (!open.value) return;
    if (triggerRef.value?.contains(target)) return;
    if (panelRef.value?.contains(target)) return;
    open.value = false;
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    document.addEventListener('click', closeOnClickOutside, true);
});
onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.removeEventListener('click', closeOnClickOutside, true);
});

const widthClass = computed(() => ({
    '48': 'w-48',
    '80': 'w-80',
    '96': 'w-96',
}[props.width.toString()] || props.width));

const alignmentClasses = computed(() => {
    if (props.align === 'left') return 'ltr:origin-top-left rtl:origin-top-right start-0';
    if (props.align === 'right') return 'ltr:origin-top-right rtl:origin-top-left end-0';
    return 'origin-top';
});
</script>

<template>
    <div class="relative">
        <div ref="triggerRef" @click.stop="open = !open">
            <slot name="trigger" />
        </div>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="open"
                ref="panelRef"
                class="absolute z-[9999] mt-2 rounded-md shadow-lg bg-white dark:bg-gray-800"
                :class="[widthClass, alignmentClasses]"
                @click.stop="closeOnClick && (open = false)"
            >
                <div
                    class="rounded-md ring-1 ring-black ring-opacity-5 overflow-hidden"
                    :class="contentClasses"
                >
                    <slot name="content" :close="() => (open = false)" />
                </div>
            </div>
        </Transition>
    </div>
</template>
