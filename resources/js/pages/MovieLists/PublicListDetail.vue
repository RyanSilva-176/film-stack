<template>
    <Head :title="customList?.name || 'Lista Pública'" />

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
                                    {{ customList?.name || 'Lista Pública' }}
                                </h1>
                            </div>
                            
                            <div class="ml-10">
                                <p v-if="customList?.description" class="text-sm text-gray-300 mb-1">
                                    {{ customList.description }}
                                </p>
                                <p v-if="!loading && customList" class="text-xs text-gray-400">
                                    {{ customList.movies_count }} {{ customList.movies_count === 1 ? 'filme' : 'filmes' }}
                                    • Lista por {{ listOwner }}
                                </p>
                            </div>
                        </div>

                        <!-- Mobile Share Button -->
                        <div class="flex gap-2">
                            <Button 
                                variant="outline" 
                                size="sm" 
                                icon="share-alt" 
                                @click="handleShareList" 
                                class="text-gray-300 border-gray-600 hover:border-blue-500 hover:text-white whitespace-nowrap flex-shrink-0"
                            >
                                Compartilhar
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
                                    {{ customList?.name || 'Lista Pública' }}
                                </h1>
                                <p v-if="customList?.description" class="mt-1 text-gray-300">
                                    {{ customList.description }}
                                </p>
                                <p v-if="!loading && customList" class="mt-1 text-sm text-gray-400">
                                    {{ customList.movies_count }} {{ customList.movies_count === 1 ? 'filme' : 'filmes' }}
                                    • Lista por {{ listOwner }}
                                </p>
                            </div>
                        </div>

                        <!-- Desktop Share Button -->
                        <div class="flex items-center gap-2">
                            <Button variant="ghost" size="sm" icon="share-alt" @click="handleShareList" class="text-gray-400 hover:text-white">
                                Compartilhar
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
                :selected-count="0"
                :total-count="listMovies.length"
                :available-genres="availableGenres"
                :bulk-loading="false"
                :show-results-summary="true"
                :results-summary-text="resultsSummaryText"
                :show-selection-mode="false"
                :show-bulk-actions="false"
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
                        <p class="text-sm md:text-base text-gray-400">Esta lista ainda não possui filmes.</p>
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
                            list-type="public"
                            :selection-mode="false"
                            :selected="false"
                            @click="handleMovieClick"
                            @details="handleMovieDetails"
                        />
                    </div>

                    <!-- List View -->
                    <div v-else class="space-y-3 md:space-y-4">
                        <div v-for="movieItem in listMovies" :key="movieItem.id" class="flex items-center gap-3 md:gap-4 rounded-lg bg-gray-900 p-3 md:p-4">
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

                            <!-- Public view: Only details button -->
                            <div class="flex items-center">
                                <Button variant="ghost" size="sm" icon="info-circle" @click="handleMovieDetails(movieItem.movie!)" />
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

            <!-- Toast Container -->
            <ToastContainer />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import MovieDetailsSidebar from '@/components/movie/MovieDetailsSidebar.vue';
import MovieListCard from '@/components/movie/MovieListCard.vue';
import MovieListFilters from '@/components/movie/MovieListFilters.vue';
import Button from '@/components/ui/Button.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useToast } from '@/composables/useToastSystem';
import AppLayout from '@/layouts/AppLayout.vue';
import { useMoviesStore } from '@/stores/movies';
import { useUserListsStore, type ListFilters } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faChevronLeft,
    faChevronRight,
    faFilm,
    faInfoCircle,
    faList,
    faShareAlt,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

library.add(
    faList,
    faChevronLeft,
    faChevronRight,
    faInfoCircle,
    faFilm,
    faShareAlt,
);

interface Props {
    listId: number;
    listOwner: string;
}

const props = defineProps<Props>();

const userListsStore = useUserListsStore();
const moviesStore = useMoviesStore();
const { success } = useToast();

const selectedMovie = ref<Movie | null>(null);
const sidebarOpen = ref(false);

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

const resultsSummaryText = computed(() => {
    const total = pagination.value.total_count;
    const showing = listMovies.value.length;

    if (filters.value.search) {
        return `Mostrando ${showing} de ${total} filmes para "${filters.value.search}"`;
    }

    return `Mostrando ${showing} de ${total} filmes`;
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR');
};

const handleMovieClick = (movie: Movie) => {
    handleMovieDetails(movie);
};

const handleMovieDetails = (movie: Movie) => {
    selectedMovie.value = movie;
    sidebarOpen.value = true;
};

const handleShareList = () => {
    const url = window.location.href;
    navigator.clipboard.writeText(url);
    success('Link copiado!', 'O link da lista foi copiado para a área de transferência.');
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

onMounted(async () => {
    await Promise.all([moviesStore.fetchGenres()]);
    await loadCustomList();
});
</script>
