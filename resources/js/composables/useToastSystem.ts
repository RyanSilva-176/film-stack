import { ref } from 'vue';

export interface ToastItem {
    id: string;
    type: 'success' | 'error' | 'warning' | 'info';
    title?: string;
    message: string;
    duration?: number;
    showProgress?: boolean;
    autoClose?: boolean;
    createdAt: number;
}

const toasts = ref<ToastItem[]>([]);
const maxToasts = 5;

let toastIdCounter = 0;

const generateId = (): string => {
    return `toast-${++toastIdCounter}-${Date.now()}`;
};

const addToast = (toast: Omit<ToastItem, 'id' | 'createdAt'>): string => {
    const id = generateId();
    const newToast: ToastItem = {
        id,
        createdAt: Date.now(),
        duration: 5000,
        showProgress: true,
        autoClose: true,
        ...toast,
    };

    if (toasts.value.length >= maxToasts) {
        toasts.value.shift();
    }

    toasts.value.push(newToast);

    return id;
};

const removeToast = (id: string): void => {
    const index = toasts.value.findIndex(toast => toast.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
};

const clearAllToasts = (): void => {
    toasts.value = [];
};

const pauseAllToasts = (): void => {
    console.log('Pausando todas as notificações');
    // TODO: Implementar lógica para pausar todas as notificações
};

const resumeAllToasts = (): void => {
    console.log('Retomando todas as notificações');
    // TODO: Implementar lógica para retomar todas as notificações
};

export const useToast = () => {
    const success = (title: string, message?: string, duration?: number): string => {
        return addToast({
            type: 'success',
            title,
            message: message || '',
            duration: duration || 4000,
        });
    };

    const error = (title: string, message?: string, duration?: number): string => {
        return addToast({
            type: 'error',
            title,
            message: message || '',
            duration: duration || 6000,
        });
    };

    const warning = (title: string, message?: string, duration?: number): string => {
        return addToast({
            type: 'warning',
            title,
            message: message || '',
            duration: duration || 5000,
        });
    };

    const info = (title: string, message?: string, duration?: number): string => {
        return addToast({
            type: 'info',
            title,
            message: message || '',
            duration: duration || 4000,
        });
    };

    const custom = (toast: Omit<ToastItem, 'id' | 'createdAt'>): string => {
        return addToast(toast);
    };

    const persistent = (type: ToastItem['type'], title: string, message?: string): string => {
        return addToast({
            type,
            title,
            message: message || '',
            duration: 0,
            autoClose: false,
            showProgress: false,
        });
    };

    return {
        toasts,
        success,
        error,
        warning,
        info,
        custom,
        persistent,
        removeToast,
        clearAllToasts,
        pauseAllToasts,
        resumeAllToasts,

        getToastCount: () => toasts.value.length,
        hasToasts: () => toasts.value.length > 0,
    };
};
