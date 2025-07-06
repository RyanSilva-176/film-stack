<template>
  <Head title="Filmes Curtidos" />

  <AppLayout>
    <div class="min-h-screen bg-gray-950">
      <!-- Page Header -->
      <div class="bg-gradient-to-r from-red-600/20 to-red-500/20 border-b border-red-500/30">
        <div class="container mx-auto px-4 py-8 md:py-12">
          <div class="flex items-center gap-4">
            <div class="rounded-full bg-red-600 p-3">
              <font-awesome-icon :icon="['fas', 'heart']" class="h-6 w-6 text-white" />
            </div>
            <div>
              <h1 class="text-2xl md:text-3xl font-bold text-white">Filmes Curtidos</h1>
              <p class="text-gray-300 mt-1">Seus filmes favoritos em um só lugar</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <main class="container mx-auto px-4 py-8 md:py-12">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="text-center space-y-4">
            <div class="w-16 h-16 border-4 border-red-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="text-gray-400">Carregando filmes curtidos...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="flex items-center justify-center py-12">
          <div class="text-center space-y-4">
            <div class="text-red-500 text-6xl">⚠️</div>
            <p class="text-gray-400">Erro ao carregar filmes curtidos</p>
            <button
              @click="loadLikedMovies"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              Tentar novamente
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!likedMovies.length" class="flex items-center justify-center py-12">
          <div class="text-center space-y-4 max-w-md">
            <div class="text-gray-500 text-6xl">💔</div>
            <h2 class="text-xl font-semibold text-white">Nenhum filme curtido ainda</h2>
            <p class="text-gray-400">
              Explore filmes na dashboard e adicione seus favoritos à sua lista de curtidos!
            </p>
            <router-link
              to="/dashboard"
              class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              <font-awesome-icon :icon="['fas', 'compass']" class="h-4 w-4" />
              Explorar Filmes
            </router-link>
          </div>
        </div>

        <!-- Movies Grid -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6">
          <MovieCard
            v-for="movieItem in likedMovies"
            :key="movieItem.id"
            :movie="movieItem.movie"
            @click="handleMovieDetails(movieItem.movie)"
            @add-to-list="handleAddToList(movieItem.movie)"
            @more-options="handleMoreOptions(movieItem.movie)"
          />
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total_pages > 1" class="flex justify-center mt-8">
          <div class="flex items-center gap-2">
            <button
              @click="loadPage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-2 rounded-lg bg-gray-800 text-white disabled:opacity-50 hover:bg-gray-700 transition-colors"
            >
              <font-awesome-icon :icon="['fas', 'chevron-left']" class="h-4 w-4" />
            </button>
            
            <span class="px-4 py-2 text-gray-300">
              Página {{ pagination.current_page }} de {{ pagination.total_pages }}
            </span>
            
            <button
              @click="loadPage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.total_pages"
              class="px-3 py-2 rounded-lg bg-gray-800 text-white disabled:opacity-50 hover:bg-gray-700 transition-colors"
            >
              <font-awesome-icon :icon="['fas', 'chevron-right']" class="h-4 w-4" />
            </button>
          </div>
        </div>
      </main>

      <!-- Movie Details Sidebar -->
      <MovieDetailsSidebar
        :is-open="sidebarOpen"
        :movie="selectedMovie"
        @update:open="sidebarOpen = $event"
      />

      <!-- Toast Container -->
      <ToastContainer />
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useUserListsStore } from '@/stores/userLists';
import AppLayout from '@/layouts/AppLayout.vue';
import MovieCard from '@/components/movie/MovieCard.vue';
import MovieDetailsSidebar from '@/components/movie/MovieDetailsSidebar.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faHeart, faCompass, faChevronLeft, faChevronRight } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import type { Movie } from '@/types/movies';

library.add(faHeart, faCompass, faChevronLeft, faChevronRight);

const userListsStore = useUserListsStore();

const selectedMovie = ref<Movie | null>(null);
const sidebarOpen = ref(false);

const loading = computed(() => userListsStore.loading);
const error = computed(() => userListsStore.error);
const likedMovies = computed(() => userListsStore.currentListMovies);
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

const loadLikedMovies = async () => {
  const likedList = userListsStore.getLikedList;
  if (likedList) {
    await userListsStore.fetchListMovies(likedList.id);
  }
};

const loadPage = async (page: number) => {
  const likedList = userListsStore.getLikedList;
  if (likedList) {
    await userListsStore.fetchListMovies(likedList.id, page);
  }
};

onMounted(async () => {
  await userListsStore.fetchUserLists();
  
  await loadLikedMovies();
});
</script>
