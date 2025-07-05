<template>
    <div
        class="movie-card group relative cursor-pointer overflow-hidden rounded-lg bg-gray-900 transition-shadow duration-300"
        :class="[size === 'small' ? 'w-40 sm:w-48' : 'w-48 sm:w-56 md:w-64', loading ? 'animate-pulse' : '']"
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
                <FontAwesomeIcon :icon="['far', 'image']" class="h-12 w-12" />
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
                {{ movie.vote_average.toFixed(1) }}
            </div>

            <!-- Hover Overlay (desktop) -->
            <div
                class="absolute inset-0 hidden bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 sm:block"
                ref="overlayRef"
            >
                <!-- Action Buttons Container -->
                <div class="absolute right-3 bottom-3 left-3 space-y-2">
                    <!-- Primary Action - Play/Watch -->
                    <button
                        class="flex w-full items-center justify-center rounded-lg bg-red-600 py-2.5 px-4 text-white font-medium transition-all duration-200 hover:bg-red-700 active:scale-98"
                        @click.stop="$emit('play', movie)"
                    >
                        <FontAwesomeIcon :icon="['fas', 'play']" class="mr-2 h-4 w-4" />
                        Assistir
                    </button>
                    
                    <!-- Secondary Actions -->
                    <div class="flex gap-2">
                        <button
                            class="flex flex-1 items-center justify-center rounded-lg bg-white/20 py-2 px-3 text-white backdrop-blur-sm transition-all duration-200 hover:bg-white/30"
                            @click.stop="handleClick"
                            title="Ver detalhes"
                        >
                            <FontAwesomeIcon :icon="['fas', 'info-circle']" class="mr-1.5 h-3.5 w-3.5" />
                            <span class="text-sm">Detalhes</span>
                        </button>
                        
                        <button
                            class="flex flex-1 items-center justify-center rounded-lg bg-white/20 py-2 px-3 text-white backdrop-blur-sm transition-all duration-200 hover:bg-white/30"
                            @click.stop="$emit('add-to-list', movie)"
                            title="Adicionar à lista"
                        >
                            <FontAwesomeIcon :icon="['fas', 'plus']" class="mr-1.5 h-3.5 w-3.5" />
                            <span class="text-sm">Lista</span>
                        </button>
                        
                        <button
                            class="flex items-center justify-center rounded-lg bg-white/20 p-2 text-white backdrop-blur-sm transition-all duration-200 hover:bg-white/30"
                            @click.stop="$emit('more-options', movie)"
                            title="Mais opções"
                        >
                            <FontAwesomeIcon :icon="['fas', 'ellipsis-h']" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Action Buttons -->
            <div
                class="absolute inset-x-0 bottom-0 flex justify-center gap-2 bg-gradient-to-t from-black/90 via-black/50 to-transparent pt-3 pb-3 sm:hidden"
            >
                <!-- Ver Detalhes Button -->
                <button
                    class="flex items-center justify-center rounded-full bg-white/20 p-2.5 text-white backdrop-blur-sm transition-all duration-200 hover:bg-white/30 active:scale-95"
                    @click.stop="handleClick"
                    title="Ver detalhes"
                >
                    <FontAwesomeIcon :icon="['fas', 'info-circle']" class="h-4 w-4" />
                </button>
                
                <!-- Play/Watch Button -->
                <button
                    class="flex items-center justify-center rounded-full bg-red-600/80 p-2.5 text-white backdrop-blur-sm transition-all duration-200 hover:bg-red-600 active:scale-95"
                    @click.stop="$emit('play', movie)"
                    title="Assistir"
                >
                    <FontAwesomeIcon :icon="['fas', 'play']" class="h-4 w-4" />
                </button>
                
                <!-- Add to List Button -->
                <button
                    class="flex items-center justify-center rounded-full bg-white/20 p-2.5 text-white backdrop-blur-sm transition-all duration-200 hover:bg-white/30 active:scale-95"
                    @click.stop="$emit('add-to-list', movie)"
                    title="Adicionar à lista"
                >
                    <FontAwesomeIcon :icon="['fas', 'plus']" class="h-4 w-4" />
                </button>
                
                <!-- More Options Button -->
                <button
                    class="flex items-center justify-center rounded-full bg-white/20 p-2.5 text-white backdrop-blur-sm transition-all duration-200 hover:bg-white/30 active:scale-95"
                    @click.stop="$emit('more-options', movie)"
                    title="Mais opções"
                >
                    <FontAwesomeIcon :icon="['fas', 'ellipsis-h']" class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Movie Info -->
        <div v-if="showDetails" class="p-3 text-white">
            <h3 class="line-clamp-2 text-sm leading-tight font-semibold" :class="size === 'small' ? 'text-xs' : 'text-sm'">
                {{ movie.title }}
            </h3>

            <div v-if="movie.release_date" class="mt-1 text-xs text-gray-400">
                {{ formatYear(movie.release_date) }}
            </div>

            <!-- Genres -->
            <div v-if="showGenres && genreNames.length > 0" class="mt-2 flex flex-wrap gap-1">
                <span v-for="genre in genreNames.slice(0, 2)" :key="genre" class="rounded-full bg-gray-700 px-2 py-1 text-xs text-gray-300">
                    {{ genre }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useMoviesStore } from '@/stores/movies';
import type { Movie } from '@/types/movies';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { gsap } from 'gsap';
import { computed, ref } from 'vue';

interface Props {
    movie: Movie;
    size?: 'small' | 'medium';
    showRating?: boolean;
    showDetails?: boolean;
    showGenres?: boolean;
    loading?: boolean;
}

interface Emits {
    (e: 'click', movie: Movie): void;
    (e: 'play', movie: Movie): void;
    (e: 'add-to-list', movie: Movie): void;
    (e: 'more-options', movie: Movie): void;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'medium',
    showRating: true,
    showDetails: true,
    showGenres: true,
    loading: false,
});

const emit = defineEmits<Emits>();

const moviesStore = useMoviesStore();

const cardRef = ref<HTMLElement>();
const imageRef = ref<HTMLElement>();
const overlayRef = ref<HTMLElement>();

const imageError = ref(false);
const imageLoaded = ref(false);

const genreNames = computed(() => {
    if (!props.movie.genre_ids || props.movie.genre_ids.length === 0) {
        return [];
    }
    return moviesStore.getGenreNames(props.movie.genre_ids);
});

const formatYear = (date: string): string => {
    return new Date(date).getFullYear().toString();
};

const handleClick = (): void => {
    emit('click', props.movie);
};

const handleMouseEnter = () => {
    if (!cardRef.value) return;

    gsap.to(cardRef.value, {
        scale: 1.05,
        y: -10,
        duration: 0.3,
        ease: 'power2.out',
    });

    if (imageRef.value) {
        gsap.to(imageRef.value, {
            scale: 1.1,
            duration: 0.5,
            ease: 'power2.out',
        });
    }

    if (overlayRef.value) {
        gsap.to(overlayRef.value, {
            opacity: 1,
            duration: 0.3,
            ease: 'power2.out',
        });
    }
};

const handleMouseLeave = () => {
    if (!cardRef.value) return;

    gsap.to(cardRef.value, {
        scale: 1,
        y: 0,
        duration: 0.3,
        ease: 'power2.out',
    });

    if (imageRef.value) {
        gsap.to(imageRef.value, {
            scale: 1,
            duration: 0.5,
            ease: 'power2.out',
        });
    }

    if (overlayRef.value) {
        gsap.to(overlayRef.value, {
            opacity: 0,
            duration: 0.3,
            ease: 'power2.out',
        });
    }
};
</script>
