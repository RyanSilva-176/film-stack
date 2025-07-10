<template>
    <div class="relative h-full w-full overflow-hidden bg-black">
        <!-- Background Poster -->
        <div
            v-if="currentPoster"
            class="absolute inset-0 transition-all duration-1000 ease-in-out"
            :style="{ backgroundImage: `url(${currentPoster.backdrop_url})` }"
            style="background-size: cover; background-position: center; background-repeat: no-repeat"
        >
            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/60 to-black/30" />
        </div>

        <!-- Content Overlay -->
        <div class="relative z-10 flex h-full flex-col justify-end p-8 lg:p-12">
            <!-- Brand Logo -->
            <div class="absolute top-8 left-8 lg:top-12 lg:left-12">
                <h1 class="text-2xl font-bold text-white lg:text-3xl">Film<span class="text-red-500">Stack</span></h1>
            </div>

            <!-- Movie Info -->
            <div v-if="currentPoster" class="max-w-md space-y-4">
                <!-- Title -->
                <h2 class="text-2xl font-bold text-white lg:text-3xl">
                    {{ currentPoster.title }}
                </h2>

                <!-- Overview -->
                <p class="line-clamp-3 text-sm text-gray-300 lg:text-base">
                    {{ currentPoster.overview }}
                </p>

                <!-- Movie Stats -->
                <div class="flex items-center gap-4 text-sm text-gray-400">
                    <div class="flex items-center gap-1">
                        <FontAwesomeIcon icon="star" class="text-yellow-400" />
                        <span>{{ currentPoster.vote_average.toFixed(1) }}</span>
                    </div>
                    <div v-if="currentPoster.release_date">
                        {{ new Date(currentPoster.release_date).getFullYear() }}
                    </div>
                    <div v-if="genreNames.length > 0" class="flex flex-wrap gap-2">
                        <span
                            v-for="genre in genreNames.slice(0, 3)"
                            :key="genre"
                            class="rounded-full bg-white/20 px-3 py-1 text-sm text-white backdrop-blur-sm"
                        >
                            {{ genre }}
                        </span>
                    </div>
                </div>

                <!-- Quote -->
                <blockquote class="mt-6 border-l-4 border-red-500 pl-4">
                    <p class="text-sm text-gray-300 italic lg:text-base">"{{ quote.message }}"</p>
                    <footer class="mt-1 text-xs text-gray-500">— {{ quote.author }}</footer>
                </blockquote>
            </div>

            <!-- Slide Indicators -->
            <div class="mt-6 flex items-center gap-2">
                <div
                    v-for="(movie, index) in movies.slice(0, 5)"
                    :key="movie.id"
                    :class="[
                        'h-1 cursor-pointer rounded-full transition-all duration-300',
                        index === currentIndex ? 'w-8 bg-red-500' : 'w-2 bg-white/30 hover:bg-white/50',
                    ]"
                    @click="goToSlide(index)"
                />
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-black">
            <div class="flex flex-col items-center gap-4 text-white">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-600 border-t-white" />
                <p class="text-sm text-gray-400">Carregando filmes...</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useMoviesStore } from '@/stores/movies';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const moviesStore = useMoviesStore();

const currentIndex = ref(0);
let slideInterval: number | null = null;

const quotes = [
    {
        message: 'O cinema é a mais bela fraude do mundo.',
        author: 'Jean-Luc Godard',
    },
    {
        message: 'Um filme é um sonho que você tem acordado.',
        author: 'Orson Welles',
    },
    {
        message: 'O cinema pode preencher o vazio que só a arte pode preencher.',
        author: 'Andrei Tarkovsky',
    },
    {
        message: 'Fazer filmes é uma forma de vida, não apenas uma profissão.',
        author: 'Martin Scorsese',
    },
    {
        message: 'O cinema é uma janela para a alma humana.',
        author: 'Akira Kurosawa',
    },
];

const movies = computed(() => {
    const trending = moviesStore.trendingWithImages;
    const popular = moviesStore.popularWithImages;
    const allMovies = [...trending, ...popular].filter((movie) => movie.backdrop_url).slice(0, 10);

    return allMovies;
});

const loading = computed(() => moviesStore.loading.trending || moviesStore.loading.popular);

const currentPoster = computed(() => {
    if (movies.value.length === 0) return null;
    return movies.value[currentIndex.value] || movies.value[0];
});

const quote = computed(() => {
    return quotes[currentIndex.value % quotes.length];
});

const genreNames = computed(() => {
    if (!currentPoster.value?.genre_ids) return [];
    return moviesStore.getGenreNames(currentPoster.value.genre_ids);
});

const goToSlide = (index: number) => {
    if (index >= 0 && index < movies.value.length) {
        currentIndex.value = index;
        resetSlideInterval();
    }
};

const nextSlide = () => {
    if (movies.value.length === 0) return;
    currentIndex.value = (currentIndex.value + 1) % Math.min(movies.value.length, 5);
};

const resetSlideInterval = () => {
    if (slideInterval) {
        clearInterval(slideInterval);
    }

    slideInterval = setInterval(nextSlide, 5000);
};

onMounted(async () => {
    if (movies.value.length === 0) {
        await moviesStore.initializeData();
    }

    resetSlideInterval();
});

onUnmounted(() => {
    if (slideInterval) {
        clearInterval(slideInterval);
    }
});
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
