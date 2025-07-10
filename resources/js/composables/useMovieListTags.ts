import { useUserListsStore } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { computed } from 'vue';

export function useMovieListTags(movie: Movie) {
    const userListsStore = useUserListsStore();

    const listTags = computed(() => {
        return userListsStore.getMovieListTypes(movie.id);
    });

    return {
        listTags,
    };
}
