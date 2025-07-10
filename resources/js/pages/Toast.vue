<template>
    <div class="min-h-screen bg-gray-900 p-4">
        <div class="mx-auto max-w-md space-y-4">
            <h1 class="mb-6 text-2xl font-bold text-white">Toast Test - Mobile Layout</h1>

            <div class="grid grid-cols-1 gap-4">
                <button @click="showSuccessToast" class="rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700">Show Success Toast</button>

                <button @click="showErrorToast" class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700">
                    Show Error Toast (Long Message)
                </button>

                <button @click="showWarningToast" class="rounded-lg bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700">
                    Show Warning Toast
                </button>

                <button @click="showInfoToast" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Show Info Toast (With Title)
                </button>

                <button @click="showMultipleToasts" class="rounded-lg bg-purple-600 px-4 py-2 text-white hover:bg-purple-700">
                    Show Multiple Toasts
                </button>
            </div>

            <div class="mt-8 rounded-lg bg-gray-800 p-4">
                <h2 class="mb-2 text-lg font-semibold text-white">Instructions:</h2>
                <ul class="space-y-1 text-sm text-gray-300">
                    <li>• Test on mobile screen size (640px)</li>
                    <li>• Verify close button is visible and clickable</li>
                    <li>• Check that text doesn't overflow</li>
                    <li>• Ensure toasts fit within screen boundaries</li>
                    <li>• Test with long messages and titles</li>
                </ul>
            </div>
        </div>

        <!-- Toast Container -->
        <ToastContainer />
    </div>
</template>

<script setup lang="ts">
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useToast } from '@/composables/useToastSystem';

const { success, error, warning, info } = useToast();

const showSuccessToast = () => {
    success('Filme adicionado à lista!');
};

const showErrorToast = () => {
    error('Erro ao salvar o filme na lista. Verifique sua conexão com a internet e tente novamente.');
};

const showWarningToast = () => {
    warning('Lista quase cheia', 'Você tem apenas 5 espaços restantes nesta lista.');
};

const showInfoToast = () => {
    info('Nova funcionalidade', 'Agora você pode arrastar e soltar filmes para reordenar suas listas!');
};

const showMultipleToasts = () => {
    success('Primeiro toast');
    setTimeout(() => error('Segundo toast com mensagem mais longa'), 500);
    setTimeout(() => warning('Terceiro toast', 'Com título'), 1000);
    setTimeout(() => info('Quarto toast para testar múltiplos'), 1500);
};
</script>
