<script setup lang="ts">
import HeroSectionSlideshow from '@/components/HeroSectionSlideshow.vue';
import MovieCarousel from '@/components/movie/MovieCarousel.vue';
import MovieDetailsSidebar from '@/components/movie/MovieDetailsSidebar.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useMoviesStore } from '@/stores/movies';
import { useUserListsStore } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const moviesStore = useMoviesStore();
const userListsStore = useUserListsStore();

const selectedMovie = ref<Movie | null>(null);
const sidebarOpen = ref(false);

const trendingMovies = computed(() => moviesStore.trendingWithImages);
const popularMovies = computed(() => moviesStore.popularWithImages);

const upcomingMovies = computed(() => moviesStore.upcomingWithImages);
const discoverMovies = computed(() => moviesStore.discoverWithImages);
const actionMovies = computed(() => moviesStore.actionWithImages);
const comedyMovies = computed(() => moviesStore.comedyWithImages);
const horrorMovies = computed(() => moviesStore.horrorWithImages);
const animationMovies = computed(() => moviesStore.animationWithImages);
const fantasyMovies = computed(() => moviesStore.fantasyWithImages);

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

onMounted(async () => {
    await Promise.all([
        moviesStore.initializeDashboardData(),
        userListsStore.fetchUserLists(),
    ]);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="min-h-screen bg-gray-950">
            <!-- Hero Section Slideshow -->
            <HeroSectionSlideshow :movies="trendingMovies.slice(0, 5)" @movie-details="handleMovieDetails" @add-to-list="handleAddToList" />

            <!-- Main Content -->
            <main class="container mx-auto space-y-12 px-4 py-8 md:py-12">
                <!-- Trending Movies -->
                <section>
                    <MovieCarousel
                        title="Filmes em Alta"
                        subtitle="Os filmes mais populares do momento"
                        :movies="trendingMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Popular Movies -->
                <section>
                    <MovieCarousel
                        title="Populares"
                        subtitle="Filmes mais bem avaliados"
                        :movies="popularMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Upcoming Movies -->
                <section>
                    <MovieCarousel
                        title="Em Breve"
                        subtitle="Próximos lançamentos"
                        :movies="upcomingMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Action Movies -->
                <section>
                    <MovieCarousel
                        title="Ação & Aventura"
                        subtitle="Filmes cheios de adrenalina"
                        :movies="actionMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Comedy Movies -->
                <section>
                    <MovieCarousel
                        title="Comédia"
                        subtitle="Para dar boas risadas"
                        :movies="comedyMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Horror Movies -->
                <section>
                    <MovieCarousel
                        title="Terror"
                        subtitle="Para quem gosta de emoção forte"
                        :movies="horrorMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Animation Movies -->
                <section>
                    <MovieCarousel
                        title="Animação"
                        subtitle="Diversão para toda a família"
                        :movies="animationMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Fantasy Movies -->
                <section>
                    <MovieCarousel
                        title="Fantasia"
                        subtitle="Mundos mágicos e aventuras épicas"
                        :movies="fantasyMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Discover Movies -->
                <section>
                    <MovieCarousel
                        title="Descobrir"
                        subtitle="Filmes recomendados para você"
                        :movies="discoverMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>
            </main>

            <!-- Movie Details Sidebar -->
            <MovieDetailsSidebar :is-open="sidebarOpen" :movie="selectedMovie" @update:open="sidebarOpen = $event" />

            <!-- Toast Container -->
            <ToastContainer />
        </div>
    </AppLayout>
</template>
