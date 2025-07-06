<template>
    <div
        class="movie-card-base group relative cursor-pointer overflow-hidden rounded-lg bg-gray-900 transition-all duration-300 w-full h-full flex flex-col max-w-full"
        :class="[loading ? 'animate-pulse' : '', ...cardClasses]"
        @click="handleClick"
    >
        <!-- Selection Checkbox (if needed) -->
        <div v-if="selectionMode" class="absolute top-2 left-2 z-10">
            <input
                type="checkbox"
                :checked="selected"
                @click.stop="emit('selection-change', movie, !selected)"
                class="w-5 h-5 text-red-600 bg-gray-800 border-gray-600 rounded focus:ring-red-500 focus:ring-2"
            />
        </div>

        <!-- Poster Image -->
        <div class="relative aspect-[2/3] overflow-hidden">
            <img
                v-if="posterUrl && !imageError"
                :src="posterUrl"
                :alt="movie.title"
                class="h-full w-full object-cover transition-transform duration-300"
                :class="imageClasses"
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
            <div v-if="loading || (!imageLoaded && !imageError && posterUrl)" class="absolute inset-0 flex items-center justify-center bg-gray-800">
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

            <!-- List Type Tags -->
            <div v-if="displayTags.length > 0" class="absolute top-2 left-2 flex flex-col gap-1" :class="{ 'left-8': selectionMode }">
                <div
                    v-for="tag in displayTags"
                    :key="tag.type"
                    class="rounded-full px-2 py-1 text-xs font-medium backdrop-blur-sm"
                    :class="tag.class"
                >
                    <FontAwesomeIcon :icon="tag.icon" class="mr-1" />
                    {{ tag.label }}
                </div>
            </div>

            <!-- Action Buttons Overlay -->
            <div v-if="showActions" :class="actionOverlayClasses">
                <slot name="actions" :movie="movie" />
            </div>
        </div>

        <!-- Movie Info -->
        <div class="flex-1 p-3 flex flex-col" :class="infoClasses">
            <slot name="content" :movie="movie" :genres="movieGenres" />
        </div>

        <!-- Loading Overlay -->
        <div v-if="actionLoading" class="absolute inset-0 bg-black/50 flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useMoviesStore } from '@/stores/movies';
import type { Movie } from '@/types/movies';

interface Props {
    movie: Movie;
    loading?: boolean;
    showRating?: boolean;
    showActions?: boolean;
    listTypes?: Array<'liked' | 'watchlist' | 'watched' | 'custom'>;
    selectionMode?: boolean;
    selected?: boolean;
    cardClasses?: string[];
    imageClasses?: string[];
    infoClasses?: string[];
    actionOverlayClasses?: string;
    actionLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    showRating: true,
    showActions: true,
    listTypes: () => [],
    selectionMode: false,
    selected: false,
    cardClasses: () => ['hover:shadow-xl', 'hover:scale-105'],
    imageClasses: () => ['group-hover:scale-110'],
    infoClasses: () => ['justify-between'],
    actionOverlayClasses: 'absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100',
    actionLoading: false,
});

const emit = defineEmits<{
    click: [movie: Movie];
    'selection-change': [movie: Movie, selected: boolean];
}>();

const moviesStore = useMoviesStore();

const imageRef = ref<HTMLImageElement>();
const imageError = ref(false);
const imageLoaded = ref(false);

const posterUrl = computed(() => {
    return props.movie.poster_url || props.movie.poster_path || undefined;
});

const movieGenres = computed(() => {
    if (props.movie.genre_names && Array.isArray(props.movie.genre_names)) {
        return props.movie.genre_names;
    }

    if (props.movie.genre_ids && Array.isArray(props.movie.genre_ids) && props.movie.genre_ids.length > 0) {
        return moviesStore.getGenreNames(props.movie.genre_ids);
    }

    if (props.movie.genres && Array.isArray(props.movie.genres)) {
        return props.movie.genres.map((genre) => genre.name);
    }

    return [];
});

const displayTags = computed(() => {
    if (!props.listTypes || props.listTypes.length === 0) return [];

    return props.listTypes.map(type => {
        switch (type) {
            case 'liked':
                return {
                    type,
                    label: 'Curtido',
                    icon: 'heart',
                    class: 'bg-red-500/80 text-white'
                };
            case 'watchlist':
                return {
                    type,
                    label: 'Lista',
                    icon: 'bookmark',
                    class: 'bg-blue-500/80 text-white'
                };
            case 'watched':
                return {
                    type,
                    label: 'Assistido',
                    icon: 'check-circle',
                    class: 'bg-green-500/80 text-white'
                };
            case 'custom':
                return {
                    type,
                    label: 'Personalizada',
                    icon: 'list',
                    class: 'bg-purple-500/80 text-white'
                };
        }
    });
});

const handleClick = () => {
    if (!props.selectionMode) {
        emit('click', props.movie);
    }
};
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
