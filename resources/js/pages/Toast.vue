<template>
    <div class="min-h-screen bg-gray-900 p-4">
        <div class="max-w-md mx-auto space-y-4">
            <h1 class="text-2xl font-bold text-white mb-6">Toast Test - Mobile Layout</h1>
            
            <div class="grid grid-cols-1 gap-4">
                <button 
                    @click="showSuccessToast"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg"
                >
                    Show Success Toast
                </button>
                
                <button 
                    @click="showErrorToast"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg"
                >
                    Show Error Toast (Long Message)
                </button>
                
                <button 
                    @click="showWarningToast"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg"
                >
                    Show Warning Toast
                </button>
                
                <button 
                    @click="showInfoToast"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                >
                    Show Info Toast (With Title)
                </button>
                
                <button 
                    @click="showMultipleToasts"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg"
                >
                    Show Multiple Toasts
                </button>
            </div>
            
            <div class="mt-8 p-4 bg-gray-800 rounded-lg">
                <h2 class="text-lg font-semibold text-white mb-2">Instructions:</h2>
                <ul class="text-gray-300 text-sm space-y-1">
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
