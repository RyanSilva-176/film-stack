<template>
    <footer class="bg-gray-900 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <!-- Brand -->
                <div class="md:col-span-2">
                    <h3 class="text-2xl font-bold text-white">Film<span class="text-red-500">Stack</span></h3>
                    <p class="mt-4 text-gray-400">
                        A plataforma definitiva para organizar e descobrir filmes. Baseado na API do TMDB para dados sempre atualizados.
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="font-semibold text-white">Recursos</h4>
                    <ul class="mt-4 space-y-2 text-gray-400">
                        <li>
                            <a
                                href="#"
                                class="transition-colors duration-200 hover:text-white hover:underline"
                                @click.prevent="handleLinkClick('Listas Personalizadas')"
                            >
                                Listas Personalizadas
                            </a>
                        </li>
                        <li>
                            <a
                                href="#"
                                class="transition-colors duration-200 hover:text-white hover:underline"
                                @click.prevent="handleLinkClick('Recomendações')"
                            >
                                Recomendações
                            </a>
                        </li>
                        <li>
                            <a
                                href="#"
                                class="transition-colors duration-200 hover:text-white hover:underline"
                                @click.prevent="handleLinkClick('Avaliações')"
                            >
                                Avaliações
                            </a>
                        </li>
                        <li>
                            <a
                                href="#"
                                class="transition-colors duration-200 hover:text-white hover:underline"
                                @click.prevent="handleLinkClick('Estatísticas')"
                            >
                                Estatísticas
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="font-semibold text-white">Suporte</h4>
                    <ul class="mt-4 space-y-2 text-gray-400">
                        <li>
                            <a
                                href="#"
                                class="transition-colors duration-200 hover:text-white hover:underline"
                                @click.prevent="handleLinkClick('Central de Ajuda')"
                            >
                                Central de Ajuda
                            </a>
                        </li>
                        <li>
                            <a
                                href="#"
                                class="transition-colors duration-200 hover:text-white hover:underline"
                                @click.prevent="handleLinkClick('Contato')"
                            >
                                Contato
                            </a>
                        </li>
                        <li>
                            <a
                                href="#"
                                class="transition-colors duration-200 hover:text-white hover:underline"
                                @click.prevent="handleLinkClick('Privacidade')"
                            >
                                Privacidade
                            </a>
                        </li>
                        <li>
                            <a
                                href="#"
                                class="transition-colors duration-200 hover:text-white hover:underline"
                                @click.prevent="handleLinkClick('Termos')"
                            >
                                Termos
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; {{ currentYear }} FilmStack. Todos os direitos reservados. Dados fornecidos por TMDB.</p>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToastSystem';
import { computed } from 'vue';

interface Props {
    showToastOnClick?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showToastOnClick: true,
});

const emit = defineEmits<{
    linkClick: [section: string];
}>();

const { info } = useToast();

const currentYear = computed(() => new Date().getFullYear());

const handleLinkClick = (section: string) => {
    emit('linkClick', section);
    if (props.showToastOnClick) {
        info('Em desenvolvimento', `A seção "${section}" estará disponível em breve!`);
    }
};
</script>
