<template>
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

const { listTags } = useMovieListTags(props.movie);
</script>
