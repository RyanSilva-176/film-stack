import { toast } from 'vue-sonner';

export const useToast = () => {
    const success = (title: string, message?: string) => {
        if (message) {
            toast.success(title, { description: message });
        } else {
            toast.success(title);
        }
    };

    const error = (title: string, message?: string) => {
        if (message) {
            toast.error(title, { description: message });
        } else {
            toast.error(title);
        }
    };

    const info = (title: string, message?: string) => {
        if (message) {
            toast.info(title, { description: message });
        } else {
            toast.info(title);
        }
    };

    const warning = (title: string, message?: string) => {
        if (message) {
            toast.warning(title, { description: message });
        } else {
            toast.warning(title);
        }
    };

    const loading = (title: string, message?: string) => {
        if (message) {
            return toast.loading(title, { description: message });
        } else {
            return toast.loading(title);
        }
    };

    const dismiss = (toastId?: string | number) => {
        toast.dismiss(toastId);
    };

    return {
        success,
        error,
        info,
        warning,
        loading,
        dismiss,
    };
};
