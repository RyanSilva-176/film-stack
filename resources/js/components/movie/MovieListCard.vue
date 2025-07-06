<template>
    <div
        class="movie-list-card group relative w-full cursor-pointer overflow-hidden rounded-lg bg-gray-900 transition-all duration-300"
        :class="[
            loading ? 'animate-pulse' : '',
            selected ? 'bg-red-900/30 ring-2 ring-red-500 ring-offset-2 ring-offset-gray-900' : '',
            viewMode === 'list' ? 'flex hover:bg-gray-800/80' : 'flex flex-col hover:scale-105 hover:shadow-xl',
        ]"
        @click="handleClick"
        ref="cardRef"
    >
        <!-- Selection Checkbox -->
        <div v-if="selectionMode" class="absolute top-3 left-3 z-10">
            <input
                type="checkbox"
                :checked="selected"
                @click.stop="handleSelectionChange"
                class="h-5 w-5 rounded border-gray-600 bg-gray-800 text-red-600 focus:ring-2 focus:ring-red-500"
            />
        </div>

        <!-- Poster Image -->
        <div class="relative flex-shrink-0 overflow-hidden" :class="viewMode === 'list' ? 'h-50 w-32' : 'aspect-[2/3] w-full'">
            <img
                v-if="posterImageUrl && !imageError"
                :src="posterImageUrl"
                :alt="movie.title"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                loading="lazy"
                @error="handleImageError"
                @load="handleImageLoad"
                ref="imageRef"
            />

            <!-- Placeholder for missing poster -->
            <div v-else class="flex h-full w-full items-center justify-center bg-gray-800 text-gray-400">
                <div class="text-center">
                    <FontAwesomeIcon icon="film" :class="viewMode === 'list' ? 'h-6 w-6' : 'h-12 w-12'" />
                    <p v-if="viewMode !== 'list'" class="mt-2 text-xs text-gray-500">Sem imagem</p>
                </div>
            </div>

            <!-- Loading state - só mostra se há URL para carregar e ainda não terminou -->
            <div v-if="posterImageUrl && !imageLoaded && !imageError && imageInitialized" class="absolute inset-0 flex items-center justify-center bg-gray-800">
                <div
                    :class="viewMode === 'list' ? 'h-4 w-4 border-2' : 'h-8 w-8 border-4'"
                    class="animate-spin rounded-full border-gray-600 border-t-white"
                ></div>
            </div>

            <!-- Rating Badge -->
            <div
                v-if="showRating && movie.vote_average > 0"
                :class="
                    viewMode === 'list'
                        ? 'absolute top-1 right-1 rounded px-1 py-0.5 text-xs'
                        : 'absolute top-2 right-2 rounded-full px-2 py-1 text-xs'
                "
                class="bg-black/80 font-bold text-white backdrop-blur-sm"
            >
                <FontAwesomeIcon icon="star" :class="viewMode === 'list' ? 'mr-0.5 text-yellow-400' : 'mr-1 text-yellow-400'" />
                {{ movie.vote_average.toFixed(1) }}
            </div>

            <!-- List Type Badge -->
            <div
                v-if="listType && listType !== 'custom'"
                :class="[
                    viewMode === 'list' ? 'absolute bottom-2 left-2' : 'absolute top-2 left-2',
                    { 'left-8': selectionMode && viewMode !== 'list' },
                ]"
            >
                <div
                    :class="[
                        viewMode === 'list' ? 'rounded px-1 py-0.5 text-xs' : 'rounded-full px-2 py-1 text-xs',
                        'font-medium backdrop-blur-sm',
                        listTypeBadgeClass,
                    ]"
                >
                    <FontAwesomeIcon :icon="listTypeIcon" :class="viewMode === 'list' ? 'mr-0.5' : 'mr-1'" />
                    {{ listTypeLabel }}
                </div>
            </div>
        </div>

        <!-- Movie Info -->
        <div class="flex min-w-0 flex-1 flex-col justify-between p-3">
            <div class="space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="line-clamp-2 flex-1 text-base font-semibold text-white" :title="movie.title">
                        {{ movie.title }}
                    </h3>

                    <!-- Action Buttons -->
                    <div class="flex flex-shrink-0 gap-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                        <Button
                            variant="ghost"
                            size="sm"
                            icon="info-circle"
                            @click.stop="handleDetailsClick"
                            aria-label="Ver detalhes"
                            class="h-8 w-8 text-blue-400 hover:bg-blue-400/20"
                        />

                        <!-- <Button
                            v-if="!isWatchedList"
                            variant="ghost"
                            size="sm"
                            icon="check"
                            @click.stop="handleMarkWatched"
                            :disabled="actionLoading.watched"
                            aria-label="Marcar como assistido"
                            class="h-8 w-8 text-green-400 hover:bg-green-400/20"
                        />
                         -->
                        <Button
                            variant="ghost"
                            size="sm"
                            icon="ellipsis-h"
                            @click.stop="handleShowOptions"
                            aria-label="Mais opções"
                            class="h-8 w-8 text-gray-400 hover:bg-gray-400/20"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-4 text-sm text-gray-400">
                    <span v-if="movie.release_date">
                        {{ new Date(movie.release_date).getFullYear() }}
                    </span>

                    <span v-if="movie.runtime" class="flex items-center gap-1">
                        <FontAwesomeIcon icon="clock" class="h-3 w-3" />
                        {{ movie.runtime }} min
                    </span>

                    <span v-if="listItem?.watched_at" class="flex items-center gap-1 text-green-400">
                        <FontAwesomeIcon icon="check-circle" class="h-3 w-3" />
                        Assistido
                    </span>

                    <span v-if="listItem?.rating" class="flex items-center gap-1 text-yellow-400">
                        <FontAwesomeIcon icon="star" class="h-3 w-3" />
                        {{ listItem.rating }}/10
                    </span>
                </div>

                <!-- Genres -->
                <div v-if="movieGenres.length > 0" class="flex flex-wrap gap-1">
                    <span v-for="genre in movieGenres.slice(0, 3)" :key="genre" class="rounded-full bg-gray-700 px-2 py-0.5 text-xs text-gray-300">
                        {{ genre }}
                    </span>
                </div>

                <!-- Overview -->
                <p v-if="movie.overview" class="line-clamp-2 text-sm text-gray-400">
                    {{ movie.overview }}
                </p>
            </div>

            <!-- Mobile Action Buttons -->
            <div class="mt-2 flex justify-center gap-1 md:hidden">
                <Button variant="primary" size="xs" icon="info-circle" @click.stop="handleDetailsClick" class="flex-1"> Detalhes </Button>

                <Button variant="ghost" size="xs" icon="ellipsis-h" @click.stop="handleShowOptions" class="px-2" />
            </div>
        </div>

        <!-- Loading Overlay -->
        <div v-if="actionLoading.remove || actionLoading.watched" class="absolute inset-0 flex items-center justify-center bg-black/50">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-white border-t-transparent"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { useMoviesStore } from '@/stores/movies';
import type { MovieListItem } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed, ref, watch } from 'vue';

interface Props {
    movie: Movie;
    listItem?: MovieListItem;
    listType?: 'liked' | 'watchlist' | 'watched' | 'custom' | 'public';
    loading?: boolean;
    showRating?: boolean;
    selectionMode?: boolean;
    selected?: boolean;
    isPublicList?: boolean;
    viewMode?: 'grid' | 'list';
}

interface Emits {
    (e: 'click', movie: Movie): void;
    (e: 'details', movie: Movie): void;
    (e: 'remove-from-list', movie: Movie, listItem?: MovieListItem): void;
    (e: 'mark-watched', movie: Movie, listItem?: MovieListItem): void;
    (e: 'move-to-list', movie: Movie, listItem?: MovieListItem): void;
    (e: 'show-options', movie: Movie, listItem?: MovieListItem): void;
    (e: 'selection-change', movie: Movie, selected: boolean): void;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    showRating: true,
    selectionMode: false,
    selected: false,
    viewMode: 'grid',
});

const emit = defineEmits<Emits>();

const moviesStore = useMoviesStore();

const cardRef = ref<HTMLElement>();
const imageRef = ref<HTMLImageElement>();
const imageError = ref(false);
const imageLoaded = ref(false);
const imageInitialized = ref(false);

const actionLoading = ref({
    remove: false,
    watched: false,
});

const posterImageUrl = computed(() => {
    if (!props.movie || imageError.value) return null;
    
    // Prioridade: poster_url > poster_path > backdrop_url > backdrop_path
    if (props.movie.poster_url) return props.movie.poster_url;
    if (props.movie.poster_path) return `https://image.tmdb.org/t/p/w342${props.movie.poster_path}`;
    if (props.movie.backdrop_url) return props.movie.backdrop_url;
    if (props.movie.backdrop_path) return `https://image.tmdb.org/t/p/w342${props.movie.backdrop_path}`;
    
    return null;
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

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const isWatchedList = computed(() => props.listType === 'watched');

const listTypeBadgeClass = computed(() => {
    switch (props.listType) {
        case 'liked':
            return 'bg-red-500/80 text-white';
        case 'watchlist':
            return 'bg-blue-500/80 text-white';
        case 'watched':
            return 'bg-green-500/80 text-white';
        default:
            return 'bg-purple-500/80 text-white';
    }
});

const listTypeIcon = computed(() => {
    switch (props.listType) {
        case 'liked':
            return 'heart';
        case 'watchlist':
            return 'bookmark';
        case 'watched':
            return 'check-circle';
        default:
            return 'list';
    }
});

const listTypeLabel = computed(() => {
    switch (props.listType) {
        case 'liked':
            return 'Curtido';
        case 'watchlist':
            return 'Lista';
        case 'watched':
            return 'Assistido';
        default:
            return 'Personalizada';
    }
});

const handleClick = () => {
    if (!props.selectionMode) {
        emit('click', props.movie);
    }
};

const handleDetailsClick = () => {
    emit('details', props.movie);
};

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const handleMarkWatched = async () => {
    actionLoading.value.watched = true;
    try {
        emit('mark-watched', props.movie, props.listItem);
    } finally {
        actionLoading.value.watched = false;
    }
};

const handleShowOptions = () => {
    emit('show-options', props.movie, props.listItem);
};

const handleSelectionChange = () => {
    emit('selection-change', props.movie, !props.selected);
};

// Image error handlers
const handleImageError = () => {
    imageError.value = true;
    imageLoaded.value = false;
    imageInitialized.value = true;
};

const handleImageLoad = () => {
    imageLoaded.value = true;
    imageError.value = false;
    imageInitialized.value = true;
};

// Reset image states when movie changes
watch(
    () => props.movie,
    () => {
        imageError.value = false;
        imageLoaded.value = false;
        imageInitialized.value = false;
    },
);

// Watch para inicializar o estado da imagem
watch(
    posterImageUrl,
    (newUrl) => {
        if (newUrl) {
            imageInitialized.value = true;
        } else {
            // Se não há URL, marca como "carregado" para evitar loading infinito
            imageLoaded.value = true;
            imageError.value = false;
            imageInitialized.value = true;
        }
    },
    { immediate: true }
);
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
