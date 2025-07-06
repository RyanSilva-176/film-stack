<template>
    <Head :title="customList?.name || 'Lista Personalizada'" />

    <AppLayout>
        <div class="min-h-screen overflow-x-hidden bg-gray-950">
            <!-- Page Header -->
            <div class="border-b border-purple-500/30 bg-gradient-to-r from-purple-600/20 to-purple-500/20">
                <div class="container mx-auto px-4 py-6 md:py-8 lg:py-12">
                    <!-- Mobile Layout -->
                    <div class="block md:hidden">
                        <!-- Header with icon and title -->
                        <div class="mb-4">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="rounded-full bg-purple-600 p-2">
                                    <font-awesome-icon :icon="['fas', 'list']" class="h-4 w-4 text-white" />
                                </div>
                                <h1 class="text-xl font-bold text-white truncate">
                                    {{ customList?.name || 'Lista Personalizada' }}
                                </h1>
                            </div>
                            
                            <div class="ml-10">
                                <p v-if="customList?.description" class="text-sm text-gray-300 mb-1">
                                    {{ customList.description }}
                                </p>
                                <p v-if="!loading && customList" class="text-xs text-gray-400">
                                    {{ customList.movies_count }} {{ customList.movies_count === 1 ? 'filme' : 'filmes' }}
                                    {{ customList.is_public ? '• Lista Pública' : '• Lista Privada' }}
                                </p>
                            </div>
                        </div>

                        <!-- Mobile Action Buttons - Horizontal scroll -->
                        <div class="flex gap-2 overflow-x-auto pb-2 -mx-1 px-1">
                            <Button 
                                variant="outline" 
                                size="sm" 
                                icon="edit" 
                                @click="handleEditList" 
                                class="text-gray-300 border-gray-600 hover:border-purple-500 hover:text-white whitespace-nowrap flex-shrink-0"
                            >
                                Editar
                            </Button>

                            <Button 
                                variant="outline" 
                                size="sm" 
                                icon="share-alt" 
                                @click="handleShareList" 
                                class="text-gray-300 border-gray-600 hover:border-blue-500 hover:text-white whitespace-nowrap flex-shrink-0"
                            >
                                Compartilhar
                            </Button>

                            <Button 
                                variant="outline" 
                                size="sm" 
                                icon="trash" 
                                @click="handleDeleteList" 
                                class="text-red-400 border-red-600 hover:border-red-500 hover:text-red-300 whitespace-nowrap flex-shrink-0"
                            >
                                Excluir
                            </Button>
                        </div>
                    </div>

                    <!-- Desktop Layout -->
                    <div class="hidden md:flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="rounded-full bg-purple-600 p-3">
                                <font-awesome-icon :icon="['fas', 'list']" class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white lg:text-3xl">
                                    {{ customList?.name || 'Lista Personalizada' }}
                                </h1>
                                <p v-if="customList?.description" class="mt-1 text-gray-300">
                                    {{ customList.description }}
                                </p>
                                <p v-if="!loading && customList" class="mt-1 text-sm text-gray-400">
                                    {{ customList.movies_count }} {{ customList.movies_count === 1 ? 'filme' : 'filmes' }}
                                    {{ customList.is_public ? '• Lista Pública' : '• Lista Privada' }}
                                </p>
                            </div>
                        </div>

                        <!-- Desktop Action Buttons -->
                        <div class="flex items-center gap-2">
                            <Button variant="ghost" size="sm" icon="edit" @click="handleEditList" class="text-gray-400 hover:text-white">
                                Editar
                            </Button>

                            <Button variant="ghost" size="sm" icon="share-alt" @click="handleShareList" class="text-gray-400 hover:text-white">
                                Compartilhar
                            </Button>

                            <Button variant="ghost" size="sm" icon="trash" @click="handleDeleteList" class="text-red-400 hover:text-red-300">
                                Excluir
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <MovieListFilters
                v-model:search="filters.search"
                v-model:genre-filter="filters.genre"
                v-model:sort-by="filters.sortBy"
                v-model:view-mode="viewMode"
                v-model:selection-mode="selectionMode"
                :selected-count="selectedMovies.length"
                :total-count="listMovies.length"
                :available-genres="availableGenres"
                :bulk-loading="bulkLoading"
                :show-results-summary="true"
                :results-summary-text="resultsSummaryText"
                @select-all="selectAllMovies"
                @clear-selection="clearSelection"
                @bulk-mark-watched="handleBulkMarkWatched"
                @bulk-move="handleBulkMove"
                @bulk-remove="handleBulkRemove"
                @filters-changed="handleFiltersChanged"
            />

            <!-- Main Content -->
            <main class="container mx-auto max-w-full px-4 py-6 md:py-8 lg:py-12">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center">
                        <div class="mx-auto h-12 w-12 md:h-16 md:w-16 animate-spin rounded-full border-4 border-purple-500 border-t-transparent"></div>
                        <p class="text-sm md:text-base text-gray-400">Carregando lista...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center px-4">
                        <div class="text-4xl md:text-6xl text-red-500">⚠️</div>
                        <p class="text-sm md:text-base text-gray-400">Erro ao carregar lista</p>
                        <button @click="loadCustomList" class="rounded-lg bg-purple-600 px-4 py-2 text-sm md:text-base text-white transition-colors hover:bg-purple-700">
                            Tentar novamente
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="!listMovies.length" class="flex items-center justify-center py-12">
                    <div class="max-w-sm md:max-w-md space-y-4 text-center px-4">
                        <div class="text-4xl md:text-6xl text-gray-500">📝</div>
                        <h2 class="text-lg md:text-xl font-semibold text-white">Lista vazia</h2>
                        <p class="text-sm md:text-base text-gray-400">Esta lista ainda não possui filmes. Explore a dashboard e adicione filmes à sua lista!</p>
                        <Link
                            href="/dashboard"
                            class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm md:text-base text-white transition-colors hover:bg-purple-700"
                        >
                            <font-awesome-icon :icon="['fas', 'compass']" class="h-4 w-4" />
                            Explorar Filmes
                        </Link>
                    </div>
                </div>

                <!-- Movies Grid/List -->
                <div v-else>
                    <!-- Grid View -->
                    <div
                        v-if="viewMode === 'grid'"
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 md:gap-4 lg:grid-cols-5 lg:gap-6 xl:grid-cols-6"
                    >
                        <MovieListCard
                            v-for="movieItem in listMovies"
                            :key="movieItem.id"
                            :movie="movieItem.movie!"
                            :list-item="movieItem"
                            list-type="custom"
                            :selection-mode="selectionMode"
                            :selected="selectedMovies.includes(movieItem.tmdb_movie_id)"
                            @click="handleMovieClick"
                            @details="handleMovieDetails"
                            @remove-from-list="handleRemoveFromList"
                            @mark-watched="handleMarkWatched"
                            @move-to-list="handleMoveToList"
                            @show-options="handleShowOptions"
                            @selection-change="handleSelectionChange"
                        />
                    </div>

                    <!-- List View -->
                    <div v-else class="space-y-3 md:space-y-4">
                        <div v-for="movieItem in listMovies" :key="movieItem.id" class="flex items-center gap-3 md:gap-4 rounded-lg bg-gray-900 p-3 md:p-4">
                            <div v-if="selectionMode" class="flex-shrink-0">
                                <input
                                    type="checkbox"
                                    :checked="selectedMovies.includes(movieItem.tmdb_movie_id)"
                                    @change="handleSelectionChange(movieItem.movie!, !selectedMovies.includes(movieItem.tmdb_movie_id))"
                                    class="h-4 w-4 md:h-5 md:w-5 rounded border-gray-600 bg-gray-800 text-purple-600 focus:ring-2 focus:ring-purple-500"
                                />
                            </div>

                            <div class="h-16 w-12 md:h-24 md:w-16 flex-shrink-0">
                                <img
                                    v-if="movieItem.movie?.poster_url"
                                    :src="movieItem.movie.poster_url"
                                    :alt="movieItem.movie.title"
                                    class="h-full w-full rounded object-cover"
                                />
                                <div v-else class="flex h-full w-full items-center justify-center rounded bg-gray-800">
                                    <font-awesome-icon icon="film" class="text-gray-400 text-sm md:text-base" />
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-semibold text-white text-sm md:text-base">{{ movieItem.movie?.title }}</h3>
                                <p class="text-xs md:text-sm text-gray-400">
                                    {{ movieItem.movie?.release_date ? new Date(movieItem.movie.release_date).getFullYear() : 'N/A' }}
                                </p>
                                <p class="text-xs md:text-sm text-gray-400">Adicionado em {{ formatDate(movieItem.created_at) }}</p>
                            </div>

                            <!-- Mobile: Show only dots menu -->
                            <div class="flex md:hidden">
                                <Button variant="ghost" size="sm" icon="ellipsis-h" @click="handleShowOptions(movieItem.movie!)" class="text-gray-400" />
                            </div>

                            <!-- Desktop: Show all action buttons -->
                            <div class="hidden md:flex items-center gap-2">
                                <Button variant="ghost" size="sm" icon="info-circle" @click="handleMovieDetails(movieItem.movie!)" />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    icon="check"
                                    @click="handleMarkWatched(movieItem.movie!)"
                                    class="text-green-400 hover:bg-green-400/20"
                                />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    icon="exchange-alt"
                                    @click="handleMoveToList(movieItem.movie!)"
                                    class="text-blue-400 hover:bg-blue-400/20"
                                />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    @click="handleRemoveFromList(movieItem.movie!)"
                                    class="text-red-400 hover:bg-red-400/20"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.total_pages > 1" class="mt-6 md:mt-8 flex justify-center">
                    <div class="flex items-center gap-2">
                        <button
                            @click="loadPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1 || loading"
                            class="rounded-lg bg-gray-800 px-2 py-2 md:px-3 text-white transition-colors hover:bg-gray-700 disabled:opacity-50"
                        >
                            <font-awesome-icon :icon="['fas', 'chevron-left']" class="h-3 w-3 md:h-4 md:w-4" />
                        </button>

                        <span class="px-2 py-2 md:px-4 text-xs md:text-sm text-gray-300"> 
                            Página {{ pagination.current_page }} de {{ pagination.total_pages }} 
                        </span>

                        <button
                            @click="loadPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.total_pages || loading"
                            class="rounded-lg bg-gray-800 px-2 py-2 md:px-3 text-white transition-colors hover:bg-gray-700 disabled:opacity-50"
                        >
                            <font-awesome-icon :icon="['fas', 'chevron-right']" class="h-3 w-3 md:h-4 md:w-4" />
                        </button>
                    </div>
                </div>
            </main>

            <!-- Modals -->
            <MovieDetailsSidebar :is-open="sidebarOpen" :movie="selectedMovie" @update:open="sidebarOpen = $event" />

            <MovieOptionsModal
                :is-open="optionsModalOpen"
                :movie="modalMovie"
                @update:open="optionsModalOpen = $event"
                @create-list="handleCreateList"
                @movie-details="() => modalMovie && handleMovieDetails(modalMovie)"
            />

            <MoveToListModal
                :is-open="moveModalOpen"
                :movie-title="moveModalMovie?.title"
                :movie-count="moveModalMultiple ? selectedMovies.length : 1"
                :current-list-id="customList?.id"
                :current-list-name="customList?.name"
                :current-list-type="customList?.type"
                :available-lists="availableDestinationLists"
                :loading="moveLoading"
                @update:open="moveModalOpen = $event"
                @select-list="handleSelectDestinationList"
                @create-new-list="handleCreateList"
            />

            <CreateEditListModal :is-open="createListModalOpen" :list="editingList" @update:open="createListModalOpen = $event" />

            <ConfirmationModal
                :is-open="confirmationModalOpen"
                :title="confirmationTitle"
                :message="confirmationMessage"
                type="danger"
                confirm-text="Confirmar"
                cancel-text="Cancelar"
                :loading="deleteLoading"
                @update:open="confirmationModalOpen = $event"
                @confirm="handleConfirmDelete"
                @cancel="confirmationModalOpen = false"
            />

            <!-- Toast Container -->
            <ToastContainer />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/modals/ConfirmationModal.vue';
import CreateEditListModal from '@/components/modals/CreateEditListModal.vue';
import MoveToListModal from '@/components/modals/MoveToListModal.vue';
import MovieOptionsModal from '@/components/modals/MovieOptionsModal.vue';
import MovieDetailsSidebar from '@/components/movie/MovieDetailsSidebar.vue';
import MovieListCard from '@/components/movie/MovieListCard.vue';
import MovieListFilters from '@/components/movie/MovieListFilters.vue';
import Button from '@/components/ui/Button.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useToast } from '@/composables/useToastSystem';
import AppLayout from '@/layouts/AppLayout.vue';
import { useMoviesStore } from '@/stores/movies';
import { useUserListsStore, type ListFilters, type MovieList } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faCheck,
    faCheckCircle,
    faCheckSquare,
    faChevronLeft,
    faChevronRight,
    faCompass,
    faEdit,
    faEllipsisH,
    faExchangeAlt,
    faFilm,
    faInfoCircle,
    faList,
    faPlus,
    faSearch,
    faShareAlt,
    faTh,
    faTimes,
    faTrash,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

library.add(
    faList,
    faCompass,
    faChevronLeft,
    faChevronRight,
    faInfoCircle,
    faCheck,
    faExchangeAlt,
    faTrash,
    faFilm,
    faCheckSquare,
    faTh,
    faList,
    faSearch,
    faTimes,
    faPlus,
    faEllipsisH,
    faCheckCircle,
    faEdit,
    faShareAlt,
);

interface Props {
    listId: number;
}

const props = defineProps<Props>();

const userListsStore = useUserListsStore();
const moviesStore = useMoviesStore();
const { success, error: showError } = useToast();

const selectedMovie = ref<Movie | null>(null);
const modalMovie = ref<Movie | null>(null);
const moveModalMovie = ref<Movie | null>(null);
const editingList = ref<MovieList | null>(null);
const sidebarOpen = ref(false);
const optionsModalOpen = ref(false);
const moveModalOpen = ref(false);
const createListModalOpen = ref(false);
const confirmationModalOpen = ref(false);
const moveModalMultiple = ref(false);
const moveLoading = ref(false);
const bulkLoading = ref(false);
const deleteLoading = ref(false);

const selectionMode = ref(false);
const selectedMovies = ref<number[]>([]);

const viewMode = ref<'grid' | 'list'>('grid');

const filters = ref<ListFilters>({
    search: '',
    genre: '',
    sortBy: 'added_date_desc',
    page: 1,
    perPage: 20,
});

const loading = computed(() => userListsStore.loading);
const error = computed(() => userListsStore.error);
const listMovies = computed(() => userListsStore.currentListMovies);
const pagination = computed(() => userListsStore.pagination);
const customList = computed(() => userListsStore.currentList);

const availableGenres = computed(() => moviesStore.genres || []);

const availableDestinationLists = computed(() => userListsStore.lists.filter((list) => list.id !== customList.value?.id));

const resultsSummaryText = computed(() => {
    const total = pagination.value.total_count;
    const showing = listMovies.value.length;

    if (filters.value.search) {
        return `Mostrando ${showing} de ${total} filmes para "${filters.value.search}"`;
    }

    return `Mostrando ${showing} de ${total} filmes`;
});

const confirmationTitle = computed(() => 'Excluir Lista');
const confirmationMessage = computed(() => `Tem certeza que deseja excluir a lista "${customList.value?.name}"? Esta ação não pode ser desfeita.`);

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR');
};

const handleMovieClick = (movie: Movie) => {
    if (!selectionMode.value) {
        handleMovieDetails(movie);
    }
};

const handleMovieDetails = (movie: Movie) => {
    selectedMovie.value = movie;
    sidebarOpen.value = true;
};

const handleShowOptions = (movie: Movie) => {
    modalMovie.value = movie;
    optionsModalOpen.value = true;
};

const handleCreateList = () => {
    editingList.value = null;
    createListModalOpen.value = true;
    optionsModalOpen.value = false;
    moveModalOpen.value = false;
};

const handleEditList = () => {
    editingList.value = customList.value ? { ...customList.value } : null;
    createListModalOpen.value = true;
};

const handleShareList = () => {
    if (customList.value?.is_public) {
        const url = `${window.location.origin}/public-movie-lists/${customList.value.id}`;
        navigator.clipboard.writeText(url);
        success('Link copiado!', 'O link da lista foi copiado para a área de transferência.');
    } else {
        showError('Lista privada', 'Esta lista é privada e não pode ser compartilhada.');
    }
};

const handleDeleteList = () => {
    confirmationModalOpen.value = true;
};

const handleConfirmDelete = async () => {
    if (!customList.value) return;

    deleteLoading.value = true;
    try {
        await userListsStore.deleteCustomList(customList.value.id);
        success('Lista excluída!', 'A lista foi excluída com sucesso.');
        router.visit('/lists/custom');
    } catch (err) {
        console.error('Error deleting list:', err);
        showError('Erro!', 'Não foi possível excluir a lista.');
    } finally {
        deleteLoading.value = false;
        confirmationModalOpen.value = false;
    }
};

const handleRemoveFromList = async (movie: Movie) => {
    if (!customList.value) return;

    try {
        await userListsStore.removeMovieFromList(movie.id, customList.value.id);
        success('Sucesso!', `${movie.title} foi removido da lista.`);
    } catch (err) {
        console.error('Error removing from list:', err);
        showError('Erro!', 'Não foi possível remover o filme da lista.');
    }
};

const handleMarkWatched = async (movie: Movie) => {
    try {
        const result = await userListsStore.markWatched(movie.id);
        if (result.success) {
            const action = result.action === 'added' ? 'marcado como assistido' : 'desmarcado como assistido';
            success('Sucesso!', `${movie.title} foi ${action}.`);
        }
    } catch (err) {
        console.error('Error marking watched:', err);
        showError('Erro!', 'Não foi possível atualizar o status de assistido.');
    }
};

const handleMoveToList = (movie: Movie) => {
    moveModalMovie.value = movie;
    moveModalMultiple.value = false;
    moveModalOpen.value = true;
};

const handleSelectionChange = (movie: Movie, selected: boolean) => {
    if (selected) {
        if (!selectedMovies.value.includes(movie.id)) {
            selectedMovies.value.push(movie.id);
        }
    } else {
        selectedMovies.value = selectedMovies.value.filter((id) => id !== movie.id);
    }
};

const selectAllMovies = () => {
    selectedMovies.value = listMovies.value.map((item) => item.tmdb_movie_id);
};

const clearSelection = () => {
    selectedMovies.value = [];
};

const handleBulkMarkWatched = async () => {
    if (selectedMovies.value.length === 0) return;

    bulkLoading.value = true;
    try {
        const result = await userListsStore.bulkMarkWatched(selectedMovies.value);
        if (result.success) {
            success('Sucesso!', `${result.added_count || 0} filmes foram marcados como assistidos.`);
            clearSelection();
        }
    } catch (err) {
        console.error('Error bulk marking watched:', err);
        showError('Erro!', 'Não foi possível marcar os filmes como assistidos.');
    } finally {
        bulkLoading.value = false;
    }
};

const handleBulkMove = () => {
    if (selectedMovies.value.length === 0) return;

    moveModalMovie.value = null;
    moveModalMultiple.value = true;
    moveModalOpen.value = true;
};

const handleBulkRemove = async () => {
    if (selectedMovies.value.length === 0 || !customList.value) return;

    bulkLoading.value = true;
    try {
        await userListsStore.bulkRemoveMovies(selectedMovies.value, customList.value.id);
        success('Sucesso!', `${selectedMovies.value.length} filmes foram removidos da lista.`);
        clearSelection();
    } catch (err) {
        console.error('Error bulk removing:', err);
        showError('Erro!', 'Não foi possível remover os filmes da lista.');
    } finally {
        bulkLoading.value = false;
    }
};

const handleSelectDestinationList = async (destinationList: any) => {
    if (!customList.value) return;

    moveLoading.value = true;
    try {
        if (moveModalMultiple.value) {
            await userListsStore.bulkMoveMovies(selectedMovies.value, customList.value.id, destinationList.id);
            success('Sucesso!', `${selectedMovies.value.length} filmes foram movidos para "${destinationList.name}".`);
            clearSelection();
        } else if (moveModalMovie.value) {
            await userListsStore.bulkMoveMovies([moveModalMovie.value.id], customList.value.id, destinationList.id);
            success('Sucesso!', `${moveModalMovie.value.title} foi movido para "${destinationList.name}".`);
        }
        moveModalOpen.value = false;
    } catch (err) {
        console.error('Error moving to list:', err);
        showError('Erro!', 'Não foi possível mover os filmes para a lista.');
    } finally {
        moveLoading.value = false;
    }
};

const handleFiltersChanged = () => {
    loadCustomList();
};

const loadCustomList = async () => {
    await userListsStore.fetchListMovies(props.listId, filters.value.page, filters.value);
};

const loadPage = async (page: number) => {
    filters.value.page = page;
    await loadCustomList();
};

watch(selectionMode, (newValue) => {
    if (!newValue) {
        clearSelection();
    }
});

onMounted(async () => {
    await Promise.all([userListsStore.fetchUserLists(), moviesStore.fetchGenres()]);

    await loadCustomList();
});
</script>
