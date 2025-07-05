<template>
    <section class="hero-section relative h-screen w-full overflow-hidden">
        <!-- Bg -->
        <div class="absolute inset-0">
            <img
                v-if="featuredMovie?.backdrop_url"
                :src="featuredMovie.backdrop_url"
                :alt="featuredMovie.title"
                class="hero-background h-full w-full object-cover object-center"
                loading="eager"
            />
            <div v-else class="h-full w-full bg-gradient-to-br from-gray-900 via-gray-800 to-black" />
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent" />
        </div>

        <!-- Content -->
        <div class="relative z-10 flex h-full items-center" ref="heroRef">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <!-- Logo -->
                    <div class="mb-8">
                        <h1 class="text-4xl font-bold text-white sm:text-5xl lg:text-6xl" ref="titleRef">
                            Film
                            <span class="text-red-500">Stack</span>
                        </h1>
                        <p class="mt-2 text-lg text-gray-300 sm:text-xl" ref="subtitleRef">Descubra, organize e acompanhe seus filmes favoritos</p>
                    </div>

                    <!-- Featured Movie Info -->
                    <div v-if="featuredMovie && !loading" class="space-y-4">
                        <div class="flex items-center gap-4">
                            <span class="rounded-full bg-red-600 px-3 py-1 text-sm font-semibold text-white"> Em Destaque </span>
                            <div v-if="featuredMovie.vote_average > 0" class="flex items-center gap-1">
                                <font-awesome-icon icon="star" class="h-5 w-5 text-yellow-400" />
                                <span class="font-medium text-white">{{ featuredMovie.vote_average.toFixed(1) }}</span>
                            </div>
                        </div>

                        <h2 class="text-3xl font-bold text-white sm:text-4xl lg:text-5xl" ref="movieTitleRef">
                            {{ featuredMovie.title }}
                        </h2>

                        <p class="line-clamp-3 text-lg leading-relaxed text-gray-300" ref="movieOverviewRef">
                            {{ featuredMovie.overview }}
                        </p>

                        <!-- Genres -->
                        <div v-if="featuredGenres.length > 0" class="flex flex-wrap gap-2" ref="genresRef">
                            <span
                                v-for="genre in featuredGenres.slice(0, 3)"
                                :key="genre"
                                class="rounded-full bg-white/20 px-3 py-1 text-sm text-white backdrop-blur-sm"
                            >
                                {{ genre }}
                            </span>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-wrap gap-4 pt-4" ref="buttonsRef">
                            <Button
                                variant="primary"
                                size="lg"
                                icon="info-circle"
                                label="Ver Detalhes"
                                @click="$emit('movieDetails', featuredMovie)"
                            />

                            <Button
                                variant="glass"
                                size="lg"
                                icon="plus"
                                label="Minha Lista"
                                @click="$emit('addToList', featuredMovie)"
                            />
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-else-if="loading" class="space-y-4">
                        <div class="space-y-2">
                            <div class="h-4 w-32 animate-pulse rounded bg-gray-700" />
                            <div class="h-12 w-3/4 animate-pulse rounded bg-gray-700" />
                        </div>
                        <div class="space-y-2">
                            <div class="h-4 w-full animate-pulse rounded bg-gray-700" />
                            <div class="h-4 w-2/3 animate-pulse rounded bg-gray-700" />
                        </div>
                        <div class="flex gap-2">
                            <div class="h-8 w-20 animate-pulse rounded-full bg-gray-700" />
                            <div class="h-8 w-16 animate-pulse rounded-full bg-gray-700" />
                        </div>
                        <div class="flex gap-4 pt-4">
                            <div class="h-12 w-32 animate-pulse rounded-lg bg-gray-700" />
                            <div class="h-12 w-28 animate-pulse rounded-lg bg-gray-700" />
                        </div>
                    </div>

                    <!-- CTA -->
                    <div v-else class="space-y-6">
                        <h2 class="text-2xl font-bold text-white sm:text-3xl">Explore o melhor do cinema</h2>
                        <p class="text-lg text-gray-300">Milhares de filmes esperando para serem descobertos</p>
                        <div class="flex gap-4">
                            <button
                                class="rounded-lg bg-red-600 px-6 py-3 font-semibold text-white transition-all duration-200 hover:scale-105 hover:bg-red-700"
                                @click="$emit('exploreMovies')"
                            >
                                Começar Agora
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Arrow -->
        <div class="absolute bottom-8 left-1/2 z-10 -translate-x-1/2 transform">
            <button
                class="animate-bounce cursor-pointer text-white/70 transition-colors duration-200 hover:text-white focus:outline-none"
                @click="$emit('scrollDown')"
                aria-label="Rolar para baixo"
            >
                <font-awesome-icon icon="arrow-down-long" class="h-8 w-8" />
            </button>
        </div>
    </section>
</template>

<script setup lang="ts">
import { useMoviesStore } from '@/stores/movies';
import type { Movie } from '@/types/movies';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faArrowDownLong, faCirclePlay, faPlus, faStar } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { gsap } from 'gsap';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import Button from './ui/Button.vue';

library.add(faStar, faCirclePlay, faPlus, faArrowDownLong);

interface Props {
    featuredMovie: Movie | null;
    loading: boolean;
}

const props = defineProps<Props>();

defineEmits<{
    movieDetails: [movie: Movie];
    addToList: [movie: Movie];
    exploreMovies: [];
    scrollDown: [];
}>();

const heroRef = ref<HTMLElement>();
const titleRef = ref<HTMLElement>();
const subtitleRef = ref<HTMLElement>();
const movieTitleRef = ref<HTMLElement>();
const movieOverviewRef = ref<HTMLElement>();
const buttonsRef = ref<HTMLElement>();
const genresRef = ref<HTMLElement>();

const moviesStore = useMoviesStore();

const featuredGenres = computed(() => {
    if (!props.featuredMovie?.genre_ids) return [];
    return moviesStore.getGenreNames(props.featuredMovie.genre_ids);
});


const animateHeroEntry = () => {
    if (!heroRef.value) return;

    const tl = gsap.timeline();

    // Bg
    const backgroundEl = heroRef.value.querySelector('.hero-background');
    if (backgroundEl) {
        tl.fromTo(backgroundEl, { opacity: 0, scale: 1.1 }, { opacity: 1, scale: 1, duration: 1.5, ease: 'power2.out' });
    }

    // Logo
    if (titleRef.value) {
        tl.fromTo(titleRef.value, { opacity: 0, y: 50 }, { opacity: 1, y: 0, duration: 0.8, ease: 'back.out(1.7)' }, '-=1.2');
    }

    if (subtitleRef.value) {
        tl.fromTo(subtitleRef.value, { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, '-=0.6');
    }
};

const animateMovieContent = () => {
    if (!props.featuredMovie) return;

    const tl = gsap.timeline();

    // Title
    if (movieTitleRef.value) {
        tl.fromTo(movieTitleRef.value, { opacity: 0, x: -50 }, { opacity: 1, x: 0, duration: 0.8, ease: 'power2.out' });
    }

    // Overview
    if (movieOverviewRef.value) {
        tl.fromTo(movieOverviewRef.value, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, '-=0.4');
    }

    // Genres
    if (genresRef.value && genresRef.value.children.length > 0) {
        tl.fromTo(
            [...genresRef.value.children],
            { opacity: 0, scale: 0.8 },
            { opacity: 1, scale: 1, duration: 0.4, stagger: 0.1, ease: 'back.out(1.7)' },
            '-=0.3',
        );
    }

    // Buttons
    if (buttonsRef.value && buttonsRef.value.children.length > 0) {
        tl.fromTo(
            [...buttonsRef.value.children],
            { opacity: 0, y: 30 },
            { opacity: 1, y: 0, duration: 0.6, stagger: { each: 0.3, from: 'end' }, ease: 'back.out(1.7)' },
            '+=0.2',
        );
    }
};

onMounted(() => {
    animateHeroEntry();

    const unwatch = watch(
        () => props.featuredMovie,
        (newMovie) => {
            if (newMovie && !props.loading) {
                setTimeout(() => animateMovieContent(), 100);
            }
        },
        { immediate: true },
    );

    onUnmounted(() => {
        unwatch();
    });
});
</script>

<script lang="ts">
export default {
    components: {
        FontAwesomeIcon,
    },
};
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.hero-section {
    background-attachment: fixed;
}

@media (max-width: 768px) {
    .hero-section {
        background-attachment: scroll;
    }
}
</style>
