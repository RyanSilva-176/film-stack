<template>
    <Head title="Listas Personalizadas" />

    <AppLayout>
        <div class="min-h-screen bg-gray-950">
            <!-- Page Header -->
            <div class="border-b border-purple-500/30 bg-gradient-to-r from-purple-600/20 to-purple-500/20">
                <div class="container mx-auto px-4 py-6 md:py-8 lg:py-12">
                    <!-- Mobile Layout -->
                    <div class="block md:hidden">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="rounded-full bg-purple-600 p-2">
                                <font-awesome-icon :icon="['fas', 'folder-plus']" class="h-4 w-4 text-white" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h1 class="text-xl font-bold text-white truncate">Listas Personalizadas</h1>
                                <p class="text-sm text-gray-300">Organize seus filmes em listas customizadas</p>
                            </div>
                        </div>
                        
                        <button
                            @click="openCreateModal"
                            class="w-full flex cursor-pointer items-center justify-center gap-2 rounded-lg bg-purple-600 px-4 py-3 text-white transition-colors hover:bg-purple-700"
                        >
                            <font-awesome-icon :icon="['fas', 'plus']" class="h-4 w-4" />
                            Nova Lista
                        </button>
                    </div>

                    <!-- Desktop Layout -->
                    <div class="hidden md:flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="rounded-full bg-purple-600 p-3">
                                <font-awesome-icon :icon="['fas', 'folder-plus']" class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white lg:text-3xl">Listas Personalizadas</h1>
                                <p class="mt-1 text-gray-300">Organize seus filmes em listas customizadas</p>
                            </div>
                        </div>

                        <button
                            @click="openCreateModal"
                            class="flex cursor-pointer items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition-colors hover:bg-purple-700"
                        >
                            <font-awesome-icon :icon="['fas', 'plus']" class="h-4 w-4" />
                            Nova Lista
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="container mx-auto px-4 py-6 md:py-8 lg:py-12">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center">
                        <div class="mx-auto h-12 w-12 md:h-16 md:w-16 animate-spin rounded-full border-4 border-purple-500 border-t-transparent"></div>
                        <p class="text-sm md:text-base text-gray-400">Carregando listas personalizadas...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center px-4">
                        <div class="text-4xl md:text-6xl text-red-500">⚠️</div>
                        <p class="text-sm md:text-base text-gray-400">Erro ao carregar listas personalizadas</p>
                        <button @click="loadCustomLists" class="rounded-lg bg-purple-600 px-4 py-2 text-sm md:text-base text-white transition-colors hover:bg-purple-700">
                            Tentar novamente
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="!customLists.length" class="flex items-center justify-center py-12">
                    <div class="max-w-sm md:max-w-md space-y-4 text-center px-4">
                        <div class="text-4xl md:text-6xl text-gray-500">📁</div>
                        <h2 class="text-lg md:text-xl font-semibold text-white">Nenhuma lista personalizada ainda</h2>
                        <p class="text-sm md:text-base text-gray-400">Crie listas personalizadas para organizar seus filmes por tema, gênero ou qualquer critério!</p>
                        <button
                            @click="openCreateModal"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm md:text-base text-white transition-colors hover:bg-purple-700"
                        >
                            <font-awesome-icon :icon="['fas', 'plus']" class="h-4 w-4" />
                            Criar Primeira Lista
                        </button>
                    </div>
                </div>

                <!-- Custom Lists Grid -->
                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="list in customLists"
                        :key="list.id"
                        class="cursor-pointer rounded-lg bg-gray-800 p-4 md:p-6 transition-colors hover:bg-gray-750"
                        @click="viewList(list)"
                    >
                        <!-- Mobile Layout -->
                        <div class="block md:hidden">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="rounded-full bg-purple-600 p-2 flex-shrink-0">
                                        <font-awesome-icon :icon="['fas', 'folder']" class="h-3 w-3 text-white" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-base font-semibold text-white truncate">{{ list.name }}</h3>
                                        <p class="text-xs text-gray-400">{{ list.movies_count }} filmes</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-1 ml-2">
                                    <span v-if="list.is_public" class="rounded-full bg-green-600/20 px-2 py-0.5 text-xs text-green-400 whitespace-nowrap"> 
                                        Pública 
                                    </span>
                                </div>
                            </div>

                            <p v-if="list.description" class="mb-3 text-sm text-gray-300 overflow-hidden text-ellipsis" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-clamp: 2;">
                                {{ list.description }}
                            </p>

                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    Criada em {{ formatDate(list.created_at) }}
                                </div>
                                
                                <div class="flex items-center gap-1">
                                    <button
                                        @click.stop="openEditModal(list)"
                                        class="p-1.5 text-gray-400 transition-colors hover:text-purple-400 rounded"
                                        title="Editar lista"
                                    >
                                        <font-awesome-icon :icon="['fas', 'edit']" class="h-3 w-3" />
                                    </button>
                                    <button
                                        @click.stop="deleteList(list)"
                                        class="p-1.5 text-gray-400 transition-colors hover:text-red-400 rounded"
                                        title="Deletar lista"
                                    >
                                        <font-awesome-icon :icon="['fas', 'trash']" class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Layout -->
                        <div class="hidden md:block">
                            <div class="mb-4 flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full bg-purple-600 px-3 py-2">
                                        <font-awesome-icon :icon="['fas', 'folder']" class="h-4 w-4 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-white">{{ list.name }}</h3>
                                        <p class="text-sm text-gray-400">{{ list.movies_count }} filmes</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span v-if="list.is_public" class="rounded-full bg-green-600/20 px-2 py-1 text-xs text-green-400"> Pública </span>
                                    <button
                                        @click.stop="openEditModal(list)"
                                        class="p-1 text-gray-400 transition-colors hover:text-purple-400"
                                        title="Editar lista"
                                    >
                                        <font-awesome-icon :icon="['fas', 'edit']" class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click.stop="deleteList(list)"
                                        class="p-1 text-gray-400 transition-colors hover:text-red-400"
                                        title="Deletar lista"
                                    >
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
                </div>
            </main>

            <!-- Create/Edit List Modal -->
            <CreateEditListModal
                :is-open="showCreateListModal"
                :list="editingList"
                @update:open="showCreateListModal = $event"
                @success="handleListCreated"
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import CreateEditListModal from '@/components/modals/CreateEditListModal.vue';
import { useToast } from '@/composables/useToastSystem';
import AppLayout from '@/layouts/AppLayout.vue';
import type { MovieList } from '@/stores/userLists';
import { useUserListsStore } from '@/stores/userLists';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faEdit, faFolder, faFolderPlus, faPlus, faTrash } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

library.add(faFolderPlus, faPlus, faFolder, faTrash, faEdit);

const userListsStore = useUserListsStore();
const { success } = useToast();

const showCreateListModal = ref(false);
const editingList = ref<MovieList | null>(null);

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

const handleListCreated = () => {
    loadCustomLists();
};

const openCreateModal = () => {
    editingList.value = null;
    showCreateListModal.value = true;
};

const openEditModal = (list: MovieList) => {
    editingList.value = list;
    showCreateListModal.value = true;
};

const viewList = (list: MovieList) => {
    window.location.href = `/lists/${list.id}`;
};

const deleteList = async (list: MovieList) => {
    if (confirm(`Tem certeza que deseja deletar a lista "${list.name}"?`)) {
        try {
            await userListsStore.deleteCustomList(list.id);
            success('Lista Deletada!', `A lista "${list.name}" foi deletada com sucesso.`);
        } catch (error) {
            console.error('Error deleting list:', error);
        }
    }
};

onMounted(async () => {
    await loadCustomLists();
});
</script>
