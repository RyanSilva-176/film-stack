<template>
  <Teleport to="body">
    <div class="fixed top-4 right-2 left-2 sm:right-4 sm:left-auto z-[9999] space-y-2 max-w-xs sm:max-w-sm w-full sm:w-auto pointer-events-none">
      <TransitionGroup
        name="toast"
        tag="div"
        class="space-y-2 pointer-events-auto"
      >
        <Toast
          v-for="toast in toasts"
          :key="toast.id"
          :type="toast.type"
          :title="toast.title"
          :message="toast.message"
          :duration="toast.duration"
          :auto-close="toast.autoClose"
          :show-progress="toast.showProgress"
          @close="removeToast(toast.id)"
        />
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import Toast from './Toast.vue';
import { useToast } from '../../composables/useToastSystem';

const { toasts, removeToast } = useToast();
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
