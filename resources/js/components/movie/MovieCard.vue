<template>
    <div
        class="movie-card group relative cursor-pointer overflow-hidden rounded-lg bg-gray-900 transition-shadow duration-300 w-full h-full flex flex-col"
        :class="[loading ? 'animate-pulse' : '']"
        @click="handleClick"
        ref="cardRef"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
    >
        <!-- Poster Image -->
        <div class="relative aspect-[2/3] overflow-hidden">
            <img
                v-if="movie.poster_url && !imageError"
                :src="movie.poster_url"
                :alt="movie.title"
                class="h-full w-full object-cover"
                loading="lazy"
                @error="imageError = true"
                @load="imageLoaded = true"
                ref="imageRef"
            />

            <!-- Placeholder for missing poster -->
            <div v-else class="flex h-full w-full items-center justify-center bg-gray-800 text-gray-400">
                <FontAwesomeIcon icon="film" class="h-12 w-12" />
            </div>

            <!-- Loading state -->
            <div v-if="loading || !imageLoaded" class="absolute inset-0 flex items-center justify-center bg-gray-800">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-600 border-t-white"></div>
            </div>

            <!-- Rating Badge -->
            <div
                v-if="showRating && movie.vote_average > 0"
                class="absolute top-2 right-2 rounded-full bg-black/80 px-2 py-1 text-xs font-bold text-white backdrop-blur-sm"
            >
                <FontAwesomeIcon icon="star" class="mr-1 text-yellow-400" />
                {{ movie.vote_average.toFixed(1) }}
            </div>

            <!-- Mobile Action Buttons -->
            <div
                class="absolute inset-x-0 bottom-0 flex justify-center gap-2 bg-gradient-to-t from-black/80 via-black/30 to-transparent p-3 md:hidden"
            >
                <Button
                    variant="primary"
                    size="sm"
                    icon="info-circle"
                    rounded="full"
                    @click.stop="handleDetailsClick"
                    aria-label="Ver detalhes"
                    class="h-10 w-10 shadow-lg"
                />
                <Button
                    variant="ghost"
                    size="sm"
                    icon="plus"
                    rounded="full"
                    @click.stop="handleAddToListClick"
                    aria-label="Adicionar à lista"
                    class="h-10 w-10 shadow-lg"
                />
                <!-- <Button
                    variant="ghost"
                    size="sm"
                    icon="ellipsis-h"
                    rounded="full"
                    @click.stop="handleMoreOptionsClick"
                    aria-label="Mais opções"
                    class="h-10 w-10 shadow-lg"
                /> -->
            </div>

            <!-- Desktop Hover Overlay -->
            <div
                class="absolute inset-0 hidden bg-gradient-to-t from-black/90 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 md:block"
                ref="overlayRef"
            >
                <!-- Desktop Action Buttons -->
                <div class="absolute right-4 bottom-4 left-4 flex flex-col gap-2">
                    <Button
                        variant="primary"
                        size="sm"
                        icon="info-circle"
                        label="Ver Detalhes"
                        full-width
                        rounded="md"
                        @click.stop="handleDetailsClick"
                        aria-label="Ver detalhes do filme"
                    />
                    <div class="flex justify-center gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            icon="plus"
                            label="Salvar"
                            rounded="md"
                            @click.stop="handleAddToListClick"
                            aria-label="Adicionar à lista"
                        />
                        <!-- <Button
                            variant="ghost"
                            size="sm"
                            icon="ellipsis-h"
                            rounded="md"
                            @click.stop="handleMoreOptionsClick"
                            aria-label="Mais opções"
                        /> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Movie Info -->
        <div v-if="showDetails" class="flex-grow space-y-2 p-3 flex flex-col">
            <!-- Title -->
            <h3 class="line-clamp-2 text-sm font-semibold text-white transition-colors group-hover:text-red-400 min-h-[2.5rem] flex items-start">
                {{ movie.title }}
            </h3>

            <!-- Release Year -->
            <p class="text-xs text-gray-400">
                {{ releaseYear }}
            </p>

            <!-- Genres -->
            <div v-if="showGenres && movieGenres.length > 0" class="flex gap-1 overflow-x-auto scrollbar-thin scrollbar-thumb-gray-700 scrollbar-track-transparent py-1">
                <span
                    v-for="genre in [...movieGenres].sort((a, b) => a.localeCompare(b))"
                    :key="genre"
                    class="whitespace-nowrap rounded-full bg-gray-700 px-2 py-1 text-xs text-gray-300 flex-shrink-0"
                >
                    {{ genre }}
                </span>
            </div>

            <!-- Overview -->
            <p v-if="movie.overview" class="line-clamp-3 text-xs text-gray-400 flex-grow">
                {{ movie.overview }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { gsap } from 'gsap';
import { computed, onMounted, ref } from 'vue';
import { useMoviesStore } from '@/stores/movies';
import type { Movie } from '@/types/movies';

interface Props {
    movie: Movie;
    size?: 'small' | 'medium';
    showRating?: boolean;
    showDetails?: boolean;
    showGenres?: boolean;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'medium',
    showRating: true,
    showDetails: true,
    showGenres: false,
    loading: false,
});

const emit = defineEmits<{
    click: [movie: Movie];
    details: [movie: Movie];
    addToList: [movie: Movie];
    moreOptions: [movie: Movie];
}>();

const moviesStore = useMoviesStore();

const cardRef = ref<HTMLElement>();
const imageRef = ref<HTMLImageElement>();
const overlayRef = ref<HTMLElement>();
const imageError = ref(false);
const imageLoaded = ref(false);

const releaseYear = computed(() => {
    if (!props.movie.release_date) return 'N/A';
    return new Date(props.movie.release_date).getFullYear();
});

const movieGenres = computed(() => {
    if (!props.movie.genre_ids || props.movie.genre_ids.length === 0) return [];
    return moviesStore.getGenreNames(props.movie.genre_ids);
});

const handleClick = () => {
    emit('click', props.movie);
};

const handleDetailsClick = () => {
    emit('details', props.movie);
};

const handleAddToListClick = () => {
    emit('addToList', props.movie);
};

const handleMoreOptionsClick = () => {
    emit('moreOptions', props.movie);
};

const handleMouseEnter = () => {
    if (cardRef.value && imageRef.value) {
        gsap.to(cardRef.value, {
            scale: 1.05,
            duration: 0.3,
            ease: 'power2.out',
        });

        gsap.to(imageRef.value, {
            scale: 1.1,
            duration: 0.3,
            ease: 'power2.out',
        });
    }
};

const handleMouseLeave = () => {
    if (cardRef.value && imageRef.value) {
        gsap.to(cardRef.value, {
            scale: 1,
            duration: 0.3,
            ease: 'power2.out',
        });

        gsap.to(imageRef.value, {
            scale: 1,
            duration: 0.3,
            ease: 'power2.out',
        });
    }
};

onMounted(() => {
    if (cardRef.value) {
        gsap.fromTo(
            cardRef.value,
            {
                opacity: 0,
                y: 20,
            },
            {
                opacity: 1,
                y: 0,
                duration: 0.5,
                delay: Math.random() * 0.3,
            },
        );
    }
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
