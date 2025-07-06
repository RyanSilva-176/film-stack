<template>
    <section class="relative h-[60vh] min-h-[400px] overflow-hidden md:h-[70vh]">
        <!-- Vue Carousel -->
        <Carousel
            class="h-full w-full"
            ref="carousel"
            v-model="currentSlide"
            :items-to-show="1"
            :wrap-around="true"
            :transition="1000"
            @update:model-value="resetAutoPlay"
        >
            <Slide v-for="movie in movies" :key="movie.id" class="h-full">
                <div class="relative h-full w-full">
                    <!-- Background Image -->
                    <div class="absolute inset-0">
                        <img
                            v-if="movie?.backdrop_url"
                            :src="movie.backdrop_url"
                            :alt="movie.title"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                    </div>

                    <!-- Content -->
                    <div class="relative z-10 flex h-full items-center">
                        <div class="container mx-auto px-4 md:px-6">
                            <div class="max-w-2xl space-y-4 md:space-y-6">
                                <!-- Title -->
                                <h1 class="text-3xl leading-tight font-bold text-white md:text-5xl lg:text-6xl">
                                    {{ movie.title }}
                                </h1>

                                <!-- Original Title -->
                                <p v-if="movie.original_title && movie.original_title !== movie.title" class="text-lg text-gray-300 md:text-xl">
                                    {{ movie.original_title }}
                                </p>

                                <!-- Overview -->
                                <p v-if="movie.overview" class="line-clamp-3 text-base leading-relaxed text-gray-300 md:line-clamp-4 md:text-lg">
                                    {{ movie.overview }}
                                </p>

                                <!-- Movie Info -->
                                <div class="flex flex-wrap items-center gap-4 text-sm md:text-base">
                                    <div v-if="movie.release_date" class="flex items-center gap-2">
                                        <font-awesome-icon :icon="['fas', 'calendar']" class="h-4 w-4 text-gray-400" />
                                        <span class="text-gray-300">{{ formatYear(movie.release_date) }}</span>
                                    </div>

                                    <div v-if="movie.vote_average > 0" class="flex items-center gap-2">
                                        <font-awesome-icon :icon="['fas', 'star']" class="h-4 w-4 text-yellow-400" />
                                        <span class="text-gray-300">{{ movie.vote_average.toFixed(1) }}</span>
                                    </div>

                                    <div v-if="genreNames.length > 0" class="flex items-center gap-2">
                                        <font-awesome-icon :icon="['fas', 'tags']" class="h-4 w-4 text-gray-400" />
                                        <span class="text-gray-300">{{ genreNames.join(', ') }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-3">
                                    <Button @click="handleMovieDetails(movie)" variant="primary" size="lg">
                                        <font-awesome-icon :icon="['fas', 'info-circle']" class="mr-2 h-4 w-4" />
                                        Ver Detalhes
                                    </Button>

                                    <Button @click="handleAddToList(movie)" variant="glass" size="lg">
                                        <font-awesome-icon :icon="['fas', 'plus']" class="mr-2 h-4 w-4" />
                                        Adicionar à Lista
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Slide>

            <!-- Navigation Dots -->
            <template #addons>
                <div class="absolute bottom-4 left-1/2 z-20 -translate-x-1/2 transform">
                    <div class="flex space-x-2">
                        <button
                            v-for="(movie, index) in movies"
                            :key="`dot-${movie.id}`"
                            @click="carousel.slideTo(index)"
                            :class="[
                                'h-2 w-2 cursor-pointer rounded-full transition-all duration-300',
                                currentSlide === index ? 'w-8 bg-white' : 'bg-white/50 hover:bg-white/75',
                            ]"
                            :aria-label="`Ir para slide ${index + 1}`"
                        />
                    </div>
                </div>
            </template>
        </Carousel>

        <!-- Navigation Arrows -->
        <button
            @click="carousel.prev()"
            class="absolute top-1/2 left-4 z-20 -translate-y-1/2 transform cursor-pointer rounded-full bg-black/50 p-2 text-white transition-colors hover:bg-black/70"
            aria-label="Slide anterior"
        >
            <font-awesome-icon :icon="['fas', 'chevron-left']" class="h-5 w-5" />
        </button>

        <button
            @click="carousel.next()"
            class="absolute top-1/2 right-4 z-20 -translate-y-1/2 transform cursor-pointer rounded-full bg-black/50 p-2 text-white transition-colors hover:bg-black/70"
            aria-label="Próximo slide"
        >
            <font-awesome-icon :icon="['fas', 'chevron-right']" class="h-5 w-5" />
        </button>
    </section>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { useMoviesStore } from '@/stores/movies';
import type { Movie } from '@/types/movies';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faCalendar, faChevronLeft, faChevronRight, faInfoCircle, faPlus, faStar, faTags } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Carousel, Slide } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';

library.add(faCalendar, faStar, faTags, faInfoCircle, faPlus, faChevronLeft, faChevronRight);

interface Props {
    movies?: Movie[];
    autoPlay?: boolean;
    autoPlayInterval?: number;
}

interface Emits {
    (e: 'movie-details', movie: Movie): void;
    (e: 'add-to-list', movie: Movie): void;
}

const props = withDefaults(defineProps<Props>(), {
    movies: () => [],
    autoPlay: true,
    autoPlayInterval: 5000,
});

const emit = defineEmits<Emits>();

const moviesStore = useMoviesStore();
const carousel = ref();
const currentSlide = ref(0);
const autoPlayTimer = ref<number | null>(null);

const genreNames = computed(() => {
    if (!props.movies[currentSlide.value]?.genre_ids) return [];
    return moviesStore.getGenreNames(props.movies[currentSlide.value].genre_ids);
});

const formatYear = (dateString: string): string => {
    try {
        return new Date(dateString).getFullYear().toString();
    } catch {
        return '';
    }
};

const startAutoPlay = () => {
    if (props.autoPlay && props.movies.length > 1) {
        autoPlayTimer.value = setInterval(() => {
            carousel.value.next();
        }, props.autoPlayInterval);
    }
};

const stopAutoPlay = () => {
    if (autoPlayTimer.value) {
        clearInterval(autoPlayTimer.value);
        autoPlayTimer.value = null;
    }
};

const resetAutoPlay = () => {
    stopAutoPlay();
    startAutoPlay();
};

const handleMovieDetails = (movie: Movie) => {
    emit('movie-details', movie);
};

const handleAddToList = (movie: Movie) => {
    emit('add-to-list', movie);
};

onMounted(() => {
    startAutoPlay();
});

onUnmounted(() => {
    stopAutoPlay();
});
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

:deep(.carousel) {
    --vc-carousel-height: 100%;
    height: 100%;
}

:deep(.carousel__viewport),
:deep(.carousel__track),
:deep(.carousel__slide) {
    height: 100%;
}
</style>
