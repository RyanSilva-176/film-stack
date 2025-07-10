<template>
    <Dialog :open="isOpen" @update:open="emit('update:open', $event)">
        <DialogContent class="border-sidebar-border bg-sidebar text-sidebar-foreground sm:max-w-md">
            <DialogHeader>
                <div class="flex items-center gap-3">
                    <!-- Icon based on variant -->
                    <div class="flex h-10 w-10 items-center justify-center rounded-full" :class="iconBackgroundClass">
                        <FontAwesomeIcon :icon="iconName" :class="iconClass" />
                    </div>

                    <div>
                        <DialogTitle class="text-lg font-semibold" :class="titleClass">
                            {{ title }}
                        </DialogTitle>
                        <DialogDescription v-if="description" class="text-sm text-sidebar-accent-foreground">
                            {{ description }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div v-if="message" class="py-4">
                <p class="text-sidebar-foreground" v-html="message"></p>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <Button variant="ghost" @click="handleCancel" :disabled="loading" :label="cancelLabel" />
                <Button :variant="confirmButtonVariant" :loading="loading" @click="handleConfirm" :label="confirmLabel" :icon="confirmIcon" />
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed } from 'vue';

type ConfirmVariant = 'danger' | 'warning' | 'info' | 'success';

interface Props {
    isOpen: boolean;
    variant?: ConfirmVariant;
    title: string;
    description?: string;
    message?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    confirmIcon?: string;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'info',
    confirmLabel: 'Confirmar',
    cancelLabel: 'Cancelar',
    loading: false,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
    cancel: [];
}>();

const iconName = computed(() => {
    switch (props.variant) {
        case 'danger':
            return 'exclamation-triangle';
        case 'warning':
            return 'exclamation-circle';
        case 'success':
            return 'check-circle';
        case 'info':
        default:
            return 'info-circle';
    }
});

const iconClass = computed(() => {
    switch (props.variant) {
        case 'danger':
            return 'text-red-600';
        case 'warning':
            return 'text-yellow-600';
        case 'success':
            return 'text-green-600';
        case 'info':
        default:
            return 'text-blue-600';
    }
});

const iconBackgroundClass = computed(() => {
    switch (props.variant) {
        case 'danger':
            return 'bg-red-100';
        case 'warning':
            return 'bg-yellow-100';
        case 'success':
            return 'bg-green-100';
        case 'info':
        default:
            return 'bg-blue-100';
    }
});

const titleClass = computed(() => {
    switch (props.variant) {
        case 'danger':
            return 'text-red-600';
        case 'warning':
            return 'text-yellow-600';
        case 'success':
            return 'text-green-600';
        case 'info':
        default:
            return 'text-sidebar-primary';
    }
});

const confirmButtonVariant = computed(() => {
    switch (props.variant) {
        case 'danger':
            return 'destructive';
        case 'warning':
            return 'secondary';
        case 'success':
            return 'success';
        case 'info':
        default:
            return 'primary';
    }
});

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('cancel');
    emit('update:open', false);
};
</script>
