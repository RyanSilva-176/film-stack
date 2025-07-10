<template>
    <Dialog :open="isOpen" @update:open="$emit('update:open', $event)">
        <DialogContent class="border-sidebar-border bg-sidebar text-sidebar-foreground sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold text-sidebar-primary">
                    <font-awesome-icon :icon="['fas', iconName]" :class="iconColor" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription class="text-sm text-sidebar-accent-foreground">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div class="py-4">
                <p class="text-sm text-sidebar-foreground">
                    {{ message }}
                </p>

                <!-- Item details if provided -->
                <div v-if="itemDetails" class="mt-4 rounded-lg bg-sidebar-accent p-3">
                    <div class="flex items-center gap-3">
                        <img v-if="itemDetails.image" :src="itemDetails.image" :alt="itemDetails.title" class="h-16 w-12 rounded object-cover" />
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-sidebar-foreground">{{ itemDetails.title }}</h4>
                            <p v-if="itemDetails.subtitle" class="text-xs text-sidebar-accent-foreground">
                                {{ itemDetails.subtitle }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button
                    variant="outline"
                    @click="handleCancel"
                    :disabled="loading"
                    class="flex-1 border-sidebar-border text-sidebar-foreground hover:bg-sidebar-accent sm:flex-initial"
                >
                    {{ cancelText }}
                </Button>
                <Button
                    :variant="confirmVariant"
                    @click="handleConfirm"
                    :loading="loading"
                    class="flex-1 sm:flex-initial"
                    :class="
                        confirmVariant === 'destructive'
                            ? 'bg-red-600 hover:bg-red-700'
                            : 'bg-sidebar-primary text-sidebar-primary-foreground hover:bg-sidebar-primary/90'
                    "
                >
                    <font-awesome-icon v-if="!loading" :icon="['fas', confirmIcon]" class="mr-2" />
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { computed } from 'vue';

interface ItemDetails {
    title: string;
    subtitle?: string;
    image?: string;
}

interface Props {
    isOpen: boolean;
    title: string;
    description?: string;
    message: string;
    type?: 'warning' | 'danger' | 'info';
    confirmText?: string;
    cancelText?: string;
    confirmIcon?: string;
    loading?: boolean;
    itemDetails?: ItemDetails;
}

const props = withDefaults(defineProps<Props>(), {
    description: '',
    type: 'warning',
    confirmText: 'Confirmar',
    cancelText: 'Cancelar',
    confirmIcon: 'check',
    loading: false,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
    cancel: [];
}>();

const iconName = computed(() => {
    switch (props.type) {
        case 'danger':
            return 'exclamation-triangle';
        case 'info':
            return 'info-circle';
        default:
            return 'exclamation-triangle';
    }
});

const iconColor = computed(() => {
    switch (props.type) {
        case 'danger':
            return 'text-red-500';
        case 'info':
            return 'text-blue-500';
        default:
            return 'text-yellow-500';
    }
});

const confirmVariant = computed(() => {
    switch (props.type) {
        case 'danger':
            return 'destructive';
        case 'info':
            return 'primary';
        default:
            return 'secondary';
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
