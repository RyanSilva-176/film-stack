<template>
    <div
        v-if="visible"
        class="relative max-w-sm rounded-lg bg-gray-900 p-4 text-white shadow-2xl border border-gray-700 cursor-pointer"
        :class="toastClasses"
        @click="close"
        @mouseenter="pauseTimer"
        @mouseleave="resumeTimer"
    >
        <div class="flex items-center gap-3">
            <!-- Icon -->
            <div class="flex-shrink-0">
                <FontAwesomeIcon 
                    :icon="iconName" 
                    :class="iconClasses"
                    class="h-5 w-5"
                />
            </div>
            
            <!-- Content -->
            <div class="flex-1 min-w-0">
                <h4 v-if="title" class="font-semibold text-sm">{{ title }}</h4>
                <p class="text-sm" :class="{ 'mt-1': title }">{{ message }}</p>
            </div>
            
            <!-- Close Button -->
            <button
                @click.stop="close"
                class="flex-shrink-0 text-gray-400 hover:text-white transition-colors ml-2 rounded-full p-1 hover:bg-gray-700"
                aria-label="Fechar notificação"
            >
                <FontAwesomeIcon icon="times" class="h-4 w-4" />
            </button>
        </div>
        
        <!-- Progress Bar -->
        <div
            v-if="showProgress && duration > 0"
            class="absolute left-0 bottom-0 h-1 w-full overflow-hidden rounded-b-lg bg-gray-800"
        >
            <div
                class="h-full transition-all duration-100 ease-linear"
                :class="progressBarClass"
                :style="{ width: progressWidth + '%', transition: isPaused ? 'none' : undefined }"
            ></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

type ToastType = 'success' | 'error' | 'warning' | 'info';

interface Props {
    type?: ToastType;
    title?: string;
    message: string;
    duration?: number;
    visible?: boolean;
    showProgress?: boolean;
    autoClose?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'info',
    duration: 5000,
    visible: true,
    showProgress: true,
    autoClose: true,
});

const emit = defineEmits<{
    close: [];
}>();

const progressWidth = ref(100);
const isPaused = ref(false);
const startTime = ref<number>(0);
const pausedTime = ref<number>(0);
const totalPausedTime = ref<number>(0);

let timeoutId: number | null = null;
let animationFrameId: number | null = null;

const iconName = computed(() => {
    switch (props.type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'warning': return 'exclamation-triangle';
        case 'info': 
        default: return 'info-circle';
    }
});

const iconClasses = computed(() => {
    switch (props.type) {
        case 'success': return 'text-green-400';
        case 'error': return 'text-red-400';
        case 'warning': return 'text-yellow-400';
        case 'info':
        default: return 'text-blue-400';
    }
});

const toastClasses = computed(() => {
    const base = 'transform transition-all duration-300 hover:scale-105';
    switch (props.type) {
        case 'success': return `${base} border-green-500/30 hover:border-green-500/50`;
        case 'error': return `${base} border-red-500/30 hover:border-red-500/50`;
        case 'warning': return `${base} border-yellow-500/30 hover:border-yellow-500/50`;
        case 'info':
        default: return `${base} border-blue-500/30 hover:border-blue-500/50`;
    }
});

const progressBarClass = computed(() => {
    switch (props.type) {
        case 'success': return 'bg-gradient-to-r from-green-400 to-green-500';
        case 'error': return 'bg-gradient-to-r from-red-400 to-red-500';
        case 'warning': return 'bg-gradient-to-r from-yellow-400 to-yellow-500';
        case 'info':
        default: return 'bg-gradient-to-r from-blue-400 to-blue-500';
    }
});

const close = () => {
    clearTimers();
    emit('close');
};

const clearTimers = () => {
    if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = null;
    }
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
        animationFrameId = null;
    }
};

const pauseTimer = () => {
    if (!props.autoClose || props.duration <= 0) return;
    
    isPaused.value = true;
    pausedTime.value = Date.now();
    
    clearTimers();
};

const resumeTimer = () => {
    if (!props.autoClose || props.duration <= 0 || !isPaused.value) return;
    
    isPaused.value = false;
    totalPausedTime.value += Date.now() - pausedTime.value;
    
    startProgressAnimation();
    scheduleAutoClose();
};

const startProgressAnimation = () => {
    if (!props.showProgress || props.duration <= 0) return;
    
    const animate = () => {
        if (!startTime.value || isPaused.value) return;
        
        const elapsed = Date.now() - startTime.value - totalPausedTime.value;
        const percent = Math.max(0, 100 - (elapsed / props.duration) * 100);
        
        progressWidth.value = percent;
        
        if (percent > 0 && props.visible) {
            animationFrameId = requestAnimationFrame(animate);
        }
    };
    
    animationFrameId = requestAnimationFrame(animate);
};

const scheduleAutoClose = () => {
    if (!props.autoClose || props.duration <= 0) return;
    
    const remainingTime = props.duration - (Date.now() - startTime.value - totalPausedTime.value);
    
    if (remainingTime > 0) {
        timeoutId = setTimeout(close, remainingTime);
    } else {
        close();
    }
};

const initializeTimer = () => {
    if (!props.visible) return;
    
    startTime.value = Date.now();
    totalPausedTime.value = 0;
    progressWidth.value = 100;
    
    if (props.autoClose && props.duration > 0) {
        scheduleAutoClose();
    }
    
    if (props.showProgress) {
        startProgressAnimation();
    }
};

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            initializeTimer();
        } else {
            clearTimers();
        }
    },
    { immediate: true }
);

onMounted(() => {
    if (props.visible) {
        initializeTimer();
    }
});

onUnmounted(() => {
    clearTimers();
});
</script>
