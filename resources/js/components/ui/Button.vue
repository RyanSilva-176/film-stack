<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="buttonClasses"
    @click="$emit('click', $event)"
    v-bind="$attrs"
  >
    <!-- Leading Icon -->
    <FontAwesomeIcon
      v-if="icon && iconPosition === 'left'"
      :icon="icon"
      :class="iconClasses"
    />
    
    <!-- Button Content -->
    <span v-if="$slots.default || label" :class="labelClasses">
      <slot>{{ label }}</slot>
    </span>
    
    <!-- Trailing Icon -->
    <FontAwesomeIcon
      v-if="icon && iconPosition === 'right'"
      :icon="icon"
      :class="iconClasses"
    />
    
    <!-- Loading Spinner -->
    <div
      v-if="loading"
      class="absolute inset-0 flex items-center justify-center"
    >
      <div class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
    </div>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

interface Props {
  label?: string;
  icon?: string;
  iconPosition?: 'left' | 'right';
  
  type?: 'button' | 'submit' | 'reset';
  disabled?: boolean;
  loading?: boolean;
  
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'destructive' | 'success' | 'glass';
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  fullWidth?: boolean;
  rounded?: 'sm' | 'md' | 'lg' | 'full';
}

const props = withDefaults(defineProps<Props>(), {
  type: 'button',
  variant: 'primary',
  size: 'md',
  iconPosition: 'left',
  disabled: false,
  loading: false,
  fullWidth: false,
  rounded: 'lg',
});

defineEmits<{
  click: [event: MouseEvent];
}>();
const baseClasses = 'inline-flex items-center justify-center font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-black disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden cursor-pointer';

const variantClasses = computed(() => {
  const variants = {
    primary: 'bg-gradient-to-r from-red-500 via-red-600 to-red-700 text-white shadow-lg ring-1 ring-red-700/30 hover:scale-105 hover:from-red-600 hover:to-red-800 focus:ring-red-400',
    
    secondary: 'bg-gradient-to-r from-gray-800 via-gray-700 to-gray-900 text-gray-200 shadow-lg ring-1 ring-gray-700/30 hover:scale-105 hover:from-gray-700 hover:to-gray-800 focus:ring-gray-400',
    
    outline: 'border-2 border-red-600 text-red-600 bg-transparent hover:bg-red-600 hover:text-white hover:scale-105 focus:ring-red-400',
    
    ghost: 'text-gray-300 bg-transparent hover:bg-white/10 hover:text-white hover:scale-105 focus:ring-white/50',
    
    destructive: 'bg-gradient-to-r from-red-700 via-red-800 to-red-900 text-white shadow-lg ring-1 ring-red-900/30 hover:scale-105 hover:from-red-800 hover:to-red-950 focus:ring-red-500',
    
    success: 'bg-gradient-to-r from-green-600 via-green-700 to-green-800 text-white shadow-lg ring-1 ring-green-800/30 hover:scale-105 hover:from-green-700 hover:to-green-900 focus:ring-green-400',
    
    glass: 'bg-white/20 text-white backdrop-blur-sm shadow-lg ring-1 ring-white/20 hover:scale-105 hover:bg-white/30 focus:ring-white'
  };
  
  return variants[props.variant];
});

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'px-2 py-1 text-xs gap-1',
    sm: 'px-3 py-1.5 text-sm gap-1.5',
    md: 'px-4 py-2.5 text-sm gap-2',
    lg: 'px-6 py-3 text-base gap-2',
    xl: 'px-8 py-4 text-lg gap-3'
  };
  
  return sizes[props.size];
});
const roundedClasses = computed(() => {
  const rounded = {
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full'
  };
  
  return rounded[props.rounded];
});

const widthClasses = computed(() => {
  return props.fullWidth ? 'w-full' : '';
});

const stateClasses = computed(() => {
  if (props.loading) return 'cursor-wait';
  if (props.disabled) return 'cursor-not-allowed';
  return '';
});

const buttonClasses = computed(() => {
  return [
    baseClasses,
    variantClasses.value,
    sizeClasses.value,
    roundedClasses.value,
    widthClasses.value,
    stateClasses.value
  ].join(' ');
});

const iconClasses = computed(() => {
  const iconSizes = {
    xs: 'h-3 w-3',
    sm: 'h-3.5 w-3.5',
    md: 'h-4 w-4',
    lg: 'h-5 w-5',
    xl: 'h-6 w-6'
  };
  
  return iconSizes[props.size];
});

const labelClasses = computed(() => {
  return props.loading ? 'opacity-0' : '';
});
</script>

<style scoped>
.glass-effect {
  position: relative;
}

.glass-effect::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.glass-effect:hover::before {
  left: 100%;
}

@keyframes ripple {
  0% {
    transform: scale(0);
    opacity: 1;
  }
  100% {
    transform: scale(4);
    opacity: 0;
  }
}

.ripple-effect {
  position: relative;
  overflow: hidden;
}

.ripple-effect::after {
  content: '';
  display: block;
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  pointer-events: none;
  background-image: radial-gradient(circle, rgba(255, 255, 255, 0.3) 10%, transparent 10.01%);
  background-repeat: no-repeat;
  background-position: 50%;
  transform: scale(10, 10);
  opacity: 0;
  transition: transform 0.5s, opacity 1s;
}

.ripple-effect:active::after {
  transform: scale(0, 0);
  opacity: 0.3;
  transition: 0s;
}
</style>
