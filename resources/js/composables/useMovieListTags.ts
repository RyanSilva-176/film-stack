import { computed } from 'vue';
import { useUserListsStore } from '@/stores/userLists';
import type { Movie } from '@/types/movies';

export function useMovieListTags(movie: Movie) {
    const userListsStore = useUserListsStore();

    const listTags = computed(() => {
        return userListsStore.getMovieListTypes(movie.id);
    });

    return {
        listTags
    };
}
