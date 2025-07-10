<template>
    <div class="movie-card-wrapper relative" :class="[isSelected ? 'ring-2 ring-red-500 ring-offset-2 ring-offset-gray-900' : '']">
        <!-- Selection Checkbox -->
        <div v-if="selectionMode" class="absolute top-2 left-2 z-10">
            <input
                type="checkbox"
                :checked="isSelected"
                @click.stop="emit('toggleSelection')"
                class="h-5 w-5 rounded border-gray-600 bg-gray-800 text-red-600 focus:ring-2 focus:ring-red-500"
            />
        </div>

        <MovieCard
            :movie="movie"
            :size="size"
            :show-rating="showRating"
            :show-details="showDetails"
            :show-genres="showGenres"
            :list-types="listTags"
            :loading="loading"
            @click="emit('click', movie)"
            @details="emit('details', movie)"
            @add-to-list="emit('addToList', movie)"
            @more-options="emit('moreOptions', movie)"
        />
    </div>
</template>

<script setup lang="ts">
import { useMovieListTags } from '@/composables/useMovieListTags';
import type { Movie } from '@/types/movies';
import MovieCard from './MovieCard.vue';

interface Props {
    movie: Movie;
    size?: 'small' | 'medium';
    showRating?: boolean;
    showDetails?: boolean;
    showGenres?: boolean;
    loading?: boolean;
    selectionMode?: boolean;
    isSelected?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'medium',
    showRating: true,
    showDetails: true,
    showGenres: false,
    loading: false,
    selectionMode: false,
    isSelected: false,
});

const emit = defineEmits<{
    click: [movie: Movie];
    details: [movie: Movie];
    addToList: [movie: Movie];
    moreOptions: [movie: Movie];
    toggleSelection: [];
}>();

const { listTags } = useMovieListTags(props.movie);
</script>
