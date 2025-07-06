import { computed } from 'vue';
import { useUserListsStore } from '@/stores/userLists';
import type { Movie } from '@/types/movies';

export function useMovieListTags(movie: Movie) {
    const userListsStore = useUserListsStore();

    const listTags = computed(() => {
        const tags: Array<'liked' | 'watchlist' | 'watched' | 'custom'> = [];
        
        const likedList = userListsStore.getLikedList;
        if (likedList?.movies?.some(item => item.tmdb_movie_id === movie.id)) {
            tags.push('liked');
        }

        const watchlist = userListsStore.getWatchlist;
        if (watchlist?.movies?.some(item => item.tmdb_movie_id === movie.id)) {
            tags.push('watchlist');
        }

        const watchedList = userListsStore.getWatchedList;
        if (watchedList?.movies?.some(item => item.tmdb_movie_id === movie.id)) {
            tags.push('watched');
        }

        const customLists = userListsStore.getCustomLists;
        if (customLists.some(list => list.movies?.some(item => item.tmdb_movie_id === movie.id))) {
            tags.push('custom');
        }

        return tags;
    });

    return {
        listTags
    };
}
