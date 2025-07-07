<template>
    <Head :title="pageConfig.title" />

    <AppLayout>
        <div class="min-h-screen overflow-x-hidden bg-gray-950">
            <!-- Page Header -->
            <div :class="`bg-gradient-to-r ${pageConfig.gradientFrom} ${pageConfig.gradientTo} border-b ${pageConfig.borderColor}`">
                <div class="container mx-auto px-4 py-8 md:py-12">
                    <div class="flex items-center gap-4">
                        <div :class="`rounded-full ${pageConfig.iconBgColor} p-3`">
                            <font-awesome-icon :icon="pageConfig.icon" class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white md:text-3xl">{{ pageConfig.title }}</h1>
                            <p class="mt-1 text-gray-300">{{ pageConfig.subtitle }}</p>
                            <p v-if="!loading && currentList" class="mt-1 text-sm text-gray-400">
                                {{ currentList.movies_count }} {{ currentList.movies_count === 1 ? 'filme' : 'filmes' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <MovieListFilters
                v-model:search="filters.search"
                :genre-filter="String(filters.genre || '')"
                v-model:sort-by="filters.sortBy"
                v-model:view-mode="viewMode"
                v-model:selection-mode="selectionMode"
                :selected-count="selectedMovies.length"
                :total-count="movies.length"
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
                @update:genre-filter="(value) => filters.genre = value"
            />

            <!-- Main Content -->
            <main class="container mx-auto max-w-full px-4 py-8 md:py-12">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center">
                        <div :class="`h-16 w-16 border-4 ${pageConfig.spinnerColor} mx-auto animate-spin rounded-full border-t-transparent`"></div>
                        <p class="text-gray-400">{{ pageConfig.loadingText }}</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center">
                        <div class="text-6xl text-red-500">⚠️</div>
                        <p class="text-gray-400">{{ pageConfig.errorText }}</p>
                        <button
                            @click="loadMovies"
                            :class="`px-4 py-2 ${pageConfig.buttonBgColor} rounded-lg text-white ${pageConfig.buttonHoverColor} transition-colors`"
                        >
                            Tentar novamente
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="!movies.length" class="flex items-center justify-center py-12">
                    <div class="max-w-md space-y-4 text-center">
                        <div class="text-6xl text-gray-500">{{ pageConfig.emptyIcon }}</div>
                        <h2 class="text-xl font-semibold text-white">{{ pageConfig.emptyTitle }}</h2>
                        <p class="text-gray-400">{{ pageConfig.emptyDescription }}</p>
                        <Link
                            :href="route('dashboard')"
                            :class="`inline-flex items-center gap-2 px-4 py-2 ${pageConfig.buttonBgColor} rounded-lg text-white ${pageConfig.buttonHoverColor} transition-colors`"
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
                        class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 md:gap-6 lg:grid-cols-5 xl:grid-cols-6"
                    >
                        <MovieListCard
                            v-for="movieItem in movies"
                            :key="movieItem.id"
                            :movie="movieItem.movie!"
                            :list-item="movieItem"
                            :list-type="pageConfig.listType"
                            :selection-mode="selectionMode"
                            :selected="selectedMovies.includes(movieItem.tmdb_movie_id)"
                            :view-mode="viewMode"
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
                    <div v-else class="space-y-3">
                        <MovieListCard
                            v-for="movieItem in movies"
                            :key="movieItem.id"
                            :movie="movieItem.movie!"
                            :list-item="movieItem"
                            :list-type="pageConfig.listType"
                            :selection-mode="selectionMode"
                            :selected="selectedMovies.includes(movieItem.tmdb_movie_id)"
                            :view-mode="viewMode"
                            @click="handleMovieClick"
                            @details="handleMovieDetails"
                            @remove-from-list="handleRemoveFromList"
                            @mark-watched="handleMarkWatched"
                            @move-to-list="handleMoveToList"
                            @show-options="handleShowOptions"
                            @selection-change="handleSelectionChange"
                        />
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.total_pages > 1" class="mt-8 flex justify-center">
                    <div class="flex items-center gap-2">
                        <button
                            @click="loadPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1 || loading"
                            class="rounded-lg bg-gray-800 px-3 py-2 text-white transition-colors hover:bg-gray-700 disabled:opacity-50"
                        >
                            <font-awesome-icon :icon="['fas', 'chevron-left']" class="h-4 w-4" />
                        </button>

                        <span class="px-4 py-2 text-gray-300"> Página {{ pagination.current_page }} de {{ pagination.total_pages }} </span>

                        <button
                            @click="loadPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.total_pages || loading"
                            class="rounded-lg bg-gray-800 px-3 py-2 text-white transition-colors hover:bg-gray-700 disabled:opacity-50"
                        >
                            <font-awesome-icon :icon="['fas', 'chevron-right']" class="h-4 w-4" />
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
                :current-list-id="currentList?.id"
                :current-list-name="currentList?.name"
                :current-list-type="currentList?.type"
                :available-lists="availableDestinationLists"
                :loading="moveLoading"
                @update:open="moveModalOpen = $event"
                @select-list="handleSelectDestinationList"
                @create-new-list="handleCreateList"
            />

            <CreateEditListModal :is-open="createListModalOpen" :list="null" @update:open="createListModalOpen = $event" />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import CreateEditListModal from '@/components/modals/CreateEditListModal.vue';
import MoveToListModal from '@/components/modals/MoveToListModal.vue';
import MovieOptionsModal from '@/components/modals/MovieOptionsModal.vue';
import MovieDetailsSidebar from '@/components/movie/MovieDetailsSidebar.vue';
import MovieListCard from '@/components/movie/MovieListCard.vue';
import MovieListFilters from '@/components/movie/MovieListFilters.vue';
import { useToast } from '@/composables/useToastSystem';
import AppLayout from '@/layouts/AppLayout.vue';
import { useMoviesStore } from '@/stores/movies';
import { useUserListsStore, type ListFilters } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faBookmark,
    faCheck,
    faCheckCircle,
    faCheckSquare,
    faChevronLeft,
    faChevronRight,
    faCompass,
    faEllipsisH,
    faExchangeAlt,
    faEye,
    faFilm,
    faHeart,
    faInfoCircle,
    faList,
    faPlus,
    faSearch,
    faTh,
    faTimes,
    faTrash,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

library.add(
    faHeart,
    faBookmark,
    faEye,
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
);

interface PageConfig {
    title: string;
    subtitle: string;
    listType: 'liked' | 'watchlist' | 'watched';
    icon: string[];
    gradientFrom: string;
    gradientTo: string;
    borderColor: string;
    iconBgColor: string;
    buttonBgColor: string;
    buttonHoverColor: string;
    spinnerColor: string;
    checkboxColor: string;
    checkboxFocusColor: string;
    loadingText: string;
    errorText: string;
    emptyIcon: string;
    emptyTitle: string;
    emptyDescription: string;
    showMarkWatchedButton: boolean;
}

interface Props {
    pageConfig: PageConfig;
}

const props = defineProps<Props>();

const userListsStore = useUserListsStore();
const moviesStore = useMoviesStore();
const { success, error: showError } = useToast();

const selectedMovie = ref<Movie | null>(null);
const modalMovie = ref<Movie | null>(null);
const moveModalMovie = ref<Movie | null>(null);
const sidebarOpen = ref(false);
const optionsModalOpen = ref(false);
const moveModalOpen = ref(false);
const createListModalOpen = ref(false);
const moveModalMultiple = ref(false);
const moveLoading = ref(false);
const bulkLoading = ref(false);

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
const movies = computed(() => userListsStore.currentListMovies);
const pagination = computed(() => userListsStore.pagination);

const currentList = computed(() => {
    switch (props.pageConfig.listType) {
        case 'liked':
            return userListsStore.getLikedList;
        case 'watchlist':
            return userListsStore.getWatchlist;
        case 'watched':
            return userListsStore.getWatchedList;
        default:
            return null;
    }
});

const availableGenres = computed(() => moviesStore.genres || []);

const availableDestinationLists = computed(() => userListsStore.lists.filter((list) => list.id !== currentList.value?.id));

const resultsSummaryText = computed(() => {
    const total = pagination.value.total_count;
    const showing = movies.value.length;

    if (filters.value.search) {
        return `Mostrando ${showing} de ${total} filmes para "${filters.value.search}"`;
    }

    return `Mostrando ${showing} de ${total} filmes`;
});

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
    createListModalOpen.value = true;
    optionsModalOpen.value = false;
    moveModalOpen.value = false;
};

const handleRemoveFromList = async (movie: Movie) => {
    if (!currentList.value) return;

    try {
        await userListsStore.removeMovieFromList(movie.id, currentList.value.id);
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
    selectedMovies.value = movies.value.map((item) => item.tmdb_movie_id);
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
    if (selectedMovies.value.length === 0 || !currentList.value) return;

    bulkLoading.value = true;
    try {
        await userListsStore.bulkRemoveMovies(selectedMovies.value, currentList.value.id);
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
    if (!currentList.value) return;

    moveLoading.value = true;
    try {
        if (moveModalMultiple.value) {
            await userListsStore.bulkMoveMovies(selectedMovies.value, currentList.value.id, destinationList.id);
            success('Sucesso!', `${selectedMovies.value.length} filmes foram movidos para "${destinationList.name}".`);
            clearSelection();
        } else if (moveModalMovie.value) {
            await userListsStore.bulkMoveMovies([moveModalMovie.value.id], currentList.value.id, destinationList.id);
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
    loadMovies();
};

const loadMovies = async () => {
    const listData = currentList.value;
    if (listData) {
        await userListsStore.fetchListMovies(listData.id, filters.value.page, filters.value);
    }
};

const loadPage = async (page: number) => {
    filters.value.page = page;
    await loadMovies();
};

watch(selectionMode, (newValue) => {
    if (!newValue) {
        clearSelection();
    }
});

onMounted(async () => {
    await Promise.all([userListsStore.fetchUserLists(), moviesStore.fetchGenres()]);

    await loadMovies();
});
</script>
