import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { Movie } from '@/types/movies';

export const useMovieDetailsStore = defineStore('movieDetails', () => {
    const isOpen = ref(false);
    const selectedMovie = ref<Movie | null>(null);

    const openSidebar = (movie: Movie) => {
        selectedMovie.value = movie;
        isOpen.value = true;
    };

    const closeSidebar = () => {
        isOpen.value = false;
        setTimeout(() => {
            selectedMovie.value = null;
        }, 300);
    };

    return {
        isOpen,
        selectedMovie,
        openSidebar,
        closeSidebar,
    };
});
