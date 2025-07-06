<template>
    <div
        class="movie-list-card group relative cursor-pointer overflow-hidden rounded-lg bg-gray-900 transition-all duration-300 w-full h-full flex flex-col max-w-full hover:shadow-xl hover:scale-105"
        :class="[loading ? 'animate-pulse' : '', selected ? 'ring-2 ring-red-500' : '']"
        @click="handleClick"
        ref="cardRef"
    >
        <!-- Selection Checkbox -->
        <div v-if="selectionMode" class="absolute top-2 left-2 z-10">
            <input
                type="checkbox"
                :checked="selected"
                @click.stop="handleSelectionChange"
                class="w-5 h-5 text-red-600 bg-gray-800 border-gray-600 rounded focus:ring-red-500 focus:ring-2"
            />
        </div>

        <!-- Poster Image -->
        <div class="relative aspect-[2/3] overflow-hidden">
            <img
                v-if="movie.poster_url && !imageError"
                :src="movie.poster_url"
                :alt="movie.title"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
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
            <div v-if="loading || (!imageLoaded && !imageError)" class="absolute inset-0 flex items-center justify-center bg-gray-800">
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

            <!-- List Type Badge -->
            <div v-if="listType && listType !== 'custom'" class="absolute top-2 left-2 z-0" :class="{ 'left-8': selectionMode }">
                <div class="rounded-full px-2 py-1 text-xs font-medium backdrop-blur-sm"
                     :class="listTypeBadgeClass">
                    <FontAwesomeIcon :icon="listTypeIcon" class="mr-1" />
                    {{ listTypeLabel }}
                </div>
            </div>

            <!-- Action Buttons Overlay -->
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
            >
                <!-- Desktop Action Buttons -->
                <div class="absolute bottom-3 left-3 right-3 hidden md:flex justify-center gap-2">
                    <Button
                        variant="primary"
                        size="sm"
                        icon="info-circle"
                        rounded="full"
                        @click.stop="handleDetailsClick"
                        aria-label="Ver detalhes"
                        class="h-8 w-8 shadow-lg"
                    />
                    
                    <Button
                        v-if="!isWatchedList"
                        variant="ghost"
                        size="sm"
                        icon="check"
                        rounded="full"
                        @click.stop="handleMarkWatched"
                        :disabled="actionLoading.watched"
                        aria-label="Marcar como assistido"
                        class="h-8 w-8 shadow-lg text-green-400 hover:bg-green-400/20"
                    />
                    
                    <Button
                        variant="ghost"
                        size="sm"
                        icon="exchange-alt"
                        rounded="full"
                        @click.stop="handleMoveToList"
                        aria-label="Mover para outra lista"
                        class="h-8 w-8 shadow-lg text-blue-400 hover:bg-blue-400/20"
                    />
                    
                    <Button
                        variant="ghost"
                        size="sm"
                        icon="trash"
                        rounded="full"
                        @click.stop="handleRemoveFromList"
                        :disabled="actionLoading.remove"
                        aria-label="Remover da lista"
                        class="h-8 w-8 shadow-lg text-red-400 hover:bg-red-400/20"
                    />
                </div>
            </div>
        </div>

        <!-- Movie Info -->
        <div class="flex-1 p-3 flex flex-col justify-between">
            <div>
                <h3 class="font-semibold text-white text-sm line-clamp-2 mb-1" :title="movie.title">
                    {{ movie.title }}
                </h3>
                
                <p v-if="movie.release_date" class="text-xs text-gray-400 mb-2">
                    {{ new Date(movie.release_date).getFullYear() }}
                </p>
                
                <div v-if="listItem?.watched_at" class="text-xs text-green-400 mb-2">
                    <FontAwesomeIcon icon="check-circle" class="mr-1" />
                    Assistido em {{ formatDate(listItem.watched_at) }}
                </div>
                
                <div v-if="listItem?.rating" class="text-xs text-yellow-400 mb-2">
                    <FontAwesomeIcon icon="star" class="mr-1" />
                    Sua avaliação: {{ listItem.rating }}/10
                </div>
            </div>

            <!-- Mobile Action Buttons -->
            <div class="flex justify-center gap-1 mt-2 md:hidden">
                <Button
                    variant="primary"
                    size="xs"
                    icon="info-circle"
                    @click.stop="handleDetailsClick"
                    class="flex-1"
                >
                    Detalhes
                </Button>
                
                <Button
                    variant="ghost"
                    size="xs"
                    icon="ellipsis-h"
                    @click.stop="handleShowOptions"
                    class="px-2"
                />
            </div>
        </div>

        <!-- Loading Overlay -->
        <div v-if="actionLoading.remove || actionLoading.watched" 
             class="absolute inset-0 bg-black/50 flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import type { Movie } from '@/types/movies';
import type { MovieListItem } from '@/stores/userLists';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed, ref } from 'vue';

interface Props {
    movie: Movie;
    listItem?: MovieListItem;
    listType?: 'liked' | 'watchlist' | 'watched' | 'custom';
    loading?: boolean;
    showRating?: boolean;
    selectionMode?: boolean;
    selected?: boolean;
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
});

const emit = defineEmits<Emits>();

const cardRef = ref<HTMLElement>();
const imageRef = ref<HTMLImageElement>();
const imageError = ref(false);
const imageLoaded = ref(false);

const actionLoading = ref({
    remove: false,
    watched: false,
});

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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR');
};

const handleClick = () => {
    if (!props.selectionMode) {
        emit('click', props.movie);
    }
};

const handleDetailsClick = () => {
    emit('details', props.movie);
};

const handleRemoveFromList = async () => {
    actionLoading.value.remove = true;
    try {
        emit('remove-from-list', props.movie, props.listItem);
    } finally {
        actionLoading.value.remove = false;
    }
};

const handleMarkWatched = async () => {
    actionLoading.value.watched = true;
    try {
        emit('mark-watched', props.movie, props.listItem);
    } finally {
        actionLoading.value.watched = false;
    }
};

const handleMoveToList = () => {
    emit('move-to-list', props.movie, props.listItem);
};

const handleShowOptions = () => {
    emit('show-options', props.movie, props.listItem);
};

const handleSelectionChange = () => {
    emit('selection-change', props.movie, !props.selected);
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
</style>
