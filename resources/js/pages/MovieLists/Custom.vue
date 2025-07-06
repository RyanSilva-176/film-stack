<template>
    <Head title="Listas Personalizadas" />

    <AppLayout>
        <div class="min-h-screen bg-gray-950">
            <!-- Page Header -->
            <div class="border-b border-purple-500/30 bg-gradient-to-r from-purple-600/20 to-purple-500/20">
                <div class="container mx-auto px-4 py-8 md:py-12">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="rounded-full bg-purple-600 p-3">
                                <font-awesome-icon :icon="['fas', 'folder-plus']" class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white md:text-3xl">Listas Personalizadas</h1>
                                <p class="mt-1 text-gray-300">Organize seus filmes em listas customizadas</p>
                            </div>
                        </div>

                        <button
                            @click="showCreateListModal = true"
                            class="flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition-colors hover:bg-purple-700"
                        >
                            <font-awesome-icon :icon="['fas', 'plus']" class="h-4 w-4" />
                            Nova Lista
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="container mx-auto px-4 py-8 md:py-12">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center">
                        <div class="mx-auto h-16 w-16 animate-spin rounded-full border-4 border-purple-500 border-t-transparent"></div>
                        <p class="text-gray-400">Carregando listas personalizadas...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center">
                        <div class="text-6xl text-red-500">⚠️</div>
                        <p class="text-gray-400">Erro ao carregar listas personalizadas</p>
                        <button @click="loadCustomLists" class="rounded-lg bg-purple-600 px-4 py-2 text-white transition-colors hover:bg-purple-700">
                            Tentar novamente
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="!customLists.length" class="flex items-center justify-center py-12">
                    <div class="max-w-md space-y-4 text-center">
                        <div class="text-6xl text-gray-500">📁</div>
                        <h2 class="text-xl font-semibold text-white">Nenhuma lista personalizada ainda</h2>
                        <p class="text-gray-400">Crie listas personalizadas para organizar seus filmes por tema, gênero ou qualquer critério!</p>
                        <button
                            @click="showCreateListModal = true"
                            class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition-colors hover:bg-purple-700"
                        >
                            <font-awesome-icon :icon="['fas', 'plus']" class="h-4 w-4" />
                            Criar Primeira Lista
                        </button>
                    </div>
                </div>

                <!-- Custom Lists Grid -->
                <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="list in customLists"
                        :key="list.id"
                        class="hover:bg-gray-750 cursor-pointer rounded-lg bg-gray-800 p-6 transition-colors"
                        @click="viewList(list)"
                    >
                        <div class="mb-4 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-full bg-purple-600 p-2">
                                    <font-awesome-icon :icon="['fas', 'folder']" class="h-4 w-4 text-white" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">{{ list.name }}</h3>
                                    <p class="text-sm text-gray-400">{{ list.movies_count }} filmes</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span v-if="list.is_public" class="rounded-full bg-green-600/20 px-2 py-1 text-xs text-green-400"> Pública </span>
                                <button @click.stop="deleteList(list)" class="p-1 text-gray-400 transition-colors hover:text-red-400">
                                    <font-awesome-icon :icon="['fas', 'trash']" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <p v-if="list.description" class="mb-4 text-sm text-gray-300">
                            {{ list.description }}
                        </p>

                        <div class="text-xs text-gray-500">Criada em {{ formatDate(list.created_at) }}</div>
                    </div>
                </div>
            </main>

            <!-- Create List Modal -->
            <div v-if="showCreateListModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="showCreateListModal = false" />
                <div class="relative w-full max-w-md rounded-lg bg-gray-800 p-6">
                    <h2 class="mb-4 text-xl font-bold text-white">Criar Nova Lista</h2>

                    <form @submit.prevent="createList" class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Nome da Lista</label>
                            <input
                                v-model="newList.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-600 bg-gray-700 px-3 py-2 text-white focus:border-purple-500 focus:outline-none"
                                placeholder="Ex: Filmes de Natal"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Descrição (opcional)</label>
                            <textarea
                                v-model="newList.description"
                                rows="3"
                                class="w-full rounded-lg border border-gray-600 bg-gray-700 px-3 py-2 text-white focus:border-purple-500 focus:outline-none"
                                placeholder="Descreva o tema ou critério da lista..."
                            />
                        </div>

                        <div class="flex items-center">
                            <input v-model="newList.isPublic" type="checkbox" id="isPublic" class="mr-2" />
                            <label for="isPublic" class="text-sm text-gray-300"> Tornar lista pública (outros usuários podem ver) </label>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="showCreateListModal = false"
                                class="flex-1 rounded-lg bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-700"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="!newList.name.trim()"
                                class="flex-1 rounded-lg bg-purple-600 px-4 py-2 text-white transition-colors hover:bg-purple-700 disabled:opacity-50"
                            >
                                Criar Lista
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Toast Container -->
            <ToastContainer />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import type { MovieList } from '@/stores/userLists';
import { useUserListsStore } from '@/stores/userLists';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faFolder, faFolderPlus, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

library.add(faFolderPlus, faPlus, faFolder, faTrash);

const userListsStore = useUserListsStore();

const showCreateListModal = ref(false);
const newList = ref({
    name: '',
    description: '',
    isPublic: false,
});

const loading = computed(() => userListsStore.loading);
const error = computed(() => userListsStore.error);
const customLists = computed(() => userListsStore.getCustomLists);

const formatDate = (dateString: string): string => {
    try {
        return new Date(dateString).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    } catch {
        return dateString;
    }
};

const loadCustomLists = async () => {
    await userListsStore.fetchUserLists();
};

const createList = async () => {
    try {
        await userListsStore.createCustomList(newList.value.name, newList.value.description || undefined, newList.value.isPublic);
        useToast().success('Lista criada com sucesso!');
        newList.value = {
            name: '',
            description: '',
            isPublic: false,
        };

        showCreateListModal.value = false;
    } catch (error) {
        console.error('Error creating list:', error);
    }
};

const viewList = (list: MovieList) => {
    useToast().info(`Visualizando lista: ${list.name}`);
};

const deleteList = async (list: MovieList) => {
    if (confirm(`Tem certeza que deseja deletar a lista "${list.name}"?`)) {
        try {
            await userListsStore.deleteCustomList(list.id);
        } catch (error) {
            console.error('Error deleting list:', error);
        }
    }
};

onMounted(async () => {
    await loadCustomLists();
});
</script>
