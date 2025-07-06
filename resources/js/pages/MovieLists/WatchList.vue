<template>
    <Head title="Lista de Desejos" />

    <AppLayout>
        <div class="min-h-screen bg-gray-950">
            <!-- Page Header -->
            <div class="border-b border-blue-500/30 bg-gradient-to-r from-blue-600/20 to-blue-500/20">
                <div class="container mx-auto px-4 py-8 md:py-12">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-blue-600 p-3">
                            <font-awesome-icon :icon="['fas', 'bookmark']" class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white md:text-3xl">Lista de Desejos</h1>
                            <p class="mt-1 text-gray-300">Filmes que você quer assistir</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="container mx-auto px-4 py-8 md:py-12">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center">
                        <div class="mx-auto h-16 w-16 animate-spin rounded-full border-4 border-blue-500 border-t-transparent"></div>
                        <p class="text-gray-400">Carregando lista de desejos...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex items-center justify-center py-12">
                    <div class="space-y-4 text-center">
                        <div class="text-6xl text-red-500">⚠️</div>
                        <p class="text-gray-400">Erro ao carregar lista de desejos</p>
                        <button @click="loadWatchlistMovies" class="rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700">
                            Tentar novamente
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="!watchlistMovies.length" class="flex items-center justify-center py-12">
                    <div class="max-w-md space-y-4 text-center">
                        <div class="text-6xl text-gray-500">📋</div>
                        <h2 class="text-xl font-semibold text-white">Sua lista de desejos está vazia</h2>
                        <p class="text-gray-400">Adicione filmes que você deseja assistir para não esquecer!</p>
                        <router-link
                            to="/dashboard"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                        >
                            <font-awesome-icon :icon="['fas', 'compass']" class="h-4 w-4" />
                            Explorar Filmes
                        </router-link>
                    </div>
                </div>

                <!-- Movies Grid -->
                <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 md:gap-6 lg:grid-cols-5 xl:grid-cols-6">
                    <MovieCard
                        v-for="movieItem in watchlistMovies"
                        :key="movieItem.id"
                        :movie="movieItem.movie"
                        @click="handleMovieDetails(movieItem.movie)"
                        @add-to-list="handleAddToList(movieItem.movie)"
                        @more-options="handleMoreOptions(movieItem.movie)"
                    />
                </div>

                <!-- Pagination -->
                <div v-if="pagination.total_pages > 1" class="mt-8 flex justify-center">
                    <div class="flex items-center gap-2">
                        <button
                            @click="loadPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="rounded-lg bg-gray-800 px-3 py-2 text-white transition-colors hover:bg-gray-700 disabled:opacity-50"
                        >
                            <font-awesome-icon :icon="['fas', 'chevron-left']" class="h-4 w-4" />
                        </button>

                        <span class="px-4 py-2 text-gray-300"> Página {{ pagination.current_page }} de {{ pagination.total_pages }} </span>

                        <button
                            @click="loadPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.total_pages"
                            class="rounded-lg bg-gray-800 px-3 py-2 text-white transition-colors hover:bg-gray-700 disabled:opacity-50"
                        >
                            <font-awesome-icon :icon="['fas', 'chevron-right']" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </main>

            <!-- Movie Details Sidebar -->
            <MovieDetailsSidebar :is-open="sidebarOpen" :movie="selectedMovie" @update:open="sidebarOpen = $event" />

            <!-- Toast Container -->
            <ToastContainer />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import MovieCard from '@/components/movie/MovieCard.vue';
import MovieDetailsSidebar from '@/components/movie/MovieDetailsSidebar.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useUserListsStore } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faBookmark, faChevronLeft, faChevronRight, faCompass } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

library.add(faBookmark, faCompass, faChevronLeft, faChevronRight);

const userListsStore = useUserListsStore();

const selectedMovie = ref<Movie | null>(null);
const sidebarOpen = ref(false);

const loading = computed(() => userListsStore.loading);
const error = computed(() => userListsStore.error);
const watchlistMovies = computed(() => userListsStore.currentListMovies);
const pagination = computed(() => userListsStore.pagination);

const handleMovieDetails = (movie: Movie) => {
    selectedMovie.value = movie;
    sidebarOpen.value = true;
};

const handleAddToList = (movie: Movie) => {
    handleMovieDetails(movie);
};

const handleMoreOptions = (movie: Movie) => {
    handleMovieDetails(movie);
};

const loadWatchlistMovies = async () => {
    const watchlist = userListsStore.getWatchlist;
    if (watchlist) {
        await userListsStore.fetchListMovies(watchlist.id);
    }
};

const loadPage = async (page: number) => {
    const watchlist = userListsStore.getWatchlist;
    if (watchlist) {
        await userListsStore.fetchListMovies(watchlist.id, page);
    }
};

onMounted(async () => {
    await userListsStore.fetchUserLists();
    await loadWatchlistMovies();
});
</script>
