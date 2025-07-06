import { defineStore } from 'pinia';
import axios from 'axios';
import { route } from 'ziggy-js';
import type { Movie } from '@/types/movies';

export interface MovieList {
    id: number;
    user_id: number;
    name: string;
    type: 'liked' | 'watchlist' | 'watched' | 'custom';
    description?: string;
    is_public: boolean;
    sort_order: number;
    movies_count: number;
    created_at: string;
    updated_at: string;
    isPublic?: boolean;
    movies?: MovieListItem[];
    pagination?: {
        current_page: number;
        total_pages: number;
        per_page: number;
        total_count: number;
    };
    loading?: boolean;
    error?: string | null;
}

export interface MovieListItem {
    id: number;
    movie_list_id: number;
    tmdb_movie_id: number;
    watched_at?: string | null;
    rating?: number | null;
    notes?: string | null;
    sort_order: number;
    created_at: string;
    updated_at: string;
    movie?: Movie;
}

export interface UserListsResponse {
    success: boolean;
    data: MovieList[];
}

export interface ListMoviesResponse {
    success: boolean;
    data: {
        list: MovieList;
        movies: MovieListItem[];
        pagination: {
            current_page: number;
            total_pages: number;
            per_page: number;
            total_count: number;
        };
    };
}

export const useUserListsStore = defineStore('userLists', {
    state: () => ({
        lists: [] as MovieList[],
        currentList: null as MovieList | null,
        currentListMovies: [] as MovieListItem[],
        pagination: {
            current_page: 1,
            total_pages: 1,
            per_page: 20,
            total_count: 0,
        },
        loading: false,
        error: null as string | null,
    }),

    getters: {
        getLikedList: (state) => state.lists.find(list => list.type === 'liked'),
        getWatchlist: (state) => state.lists.find(list => list.type === 'watchlist'),
        getWatchedList: (state) => state.lists.find(list => list.type === 'watched'),
        getCustomLists: (state) => state.lists.filter(list => list.type === 'custom'),

        getListById: (state) => (id: number) => state.lists.find(list => list.id === id),

        isMovieInList: (state) => (movieId: number, listType: string) => {
            if (state.currentList?.type === listType) {
                return state.currentListMovies.some(item => item.tmdb_movie_id === movieId);
            }
            return false;
        },
    },

    actions: {
        async fetchUserLists() {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get<UserListsResponse>(route('movie-lists.index'));

                if (response.data.success) {
                    this.lists = response.data.data;
                } else {
                    this.error = 'Erro ao carregar listas do usuário';
                }
            } catch (error) {
                console.error('Error fetching user lists:', error);
                this.error = 'Erro ao carregar listas do usuário';
            } finally {
                this.loading = false;
            }
        },

        async fetchListMovies(listId: number, page: number = 1) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get<ListMoviesResponse>(route('movie-lists.show', { movieList: listId }), {
                    params: { page }
                });

                if (response.data.success) {
                    this.currentList = response.data.data.list;
                    this.currentListMovies = response.data.data.movies;
                    this.pagination = response.data.data.pagination;
                } else {
                    this.error = 'Erro ao carregar filmes da lista';
                }
            } catch (error) {
                console.error('Error fetching list movies:', error);
                this.error = 'Erro ao carregar filmes da lista';
            } finally {
                this.loading = false;
            }
        },

        async addMovieToList(tmdbMovieId: number, listId: number) {
            try {
                const response = await axios.post(route('movie-lists.add-movie', { movieList: listId }), {
                    tmdb_movie_id: tmdbMovieId
                });

                if (response.data.success) {
                    if (this.currentList?.id === listId) {
                        await this.fetchListMovies(listId, this.pagination.current_page);
                    }
                    const list = this.lists.find(l => l.id === listId);
                    if (list && typeof list.movies_count === 'number') {
                        list.movies_count++;
                    }
                }

                return response.data;
            } catch (error) {
                console.error('Error adding movie to list:', error);
                throw error;
            }
        },

        async removeMovieFromList(tmdbMovieId: number, listId: number) {
            try {
                const response = await axios.delete(route('movie-lists.remove-movie', {
                    movieList: listId,
                    tmdbMovieId: tmdbMovieId
                }));

                if (response.data.success) {
                    if (this.currentList?.id === listId) {
                        this.currentListMovies = this.currentListMovies.filter(
                            item => item.tmdb_movie_id !== tmdbMovieId
                        );
                    }

                    const list = this.lists.find(l => l.id === listId);
                    if (list && typeof list.movies_count === 'number' && list.movies_count > 0) {
                        list.movies_count--;
                    }
                }

                return response.data;
            } catch (error) {
                console.error('Error removing movie from list:', error);
                throw error;
            }
        },

        async toggleMovieInList(tmdbMovieId: number, listType: 'liked' | 'watchlist' | 'watched') {
            const list = this.lists.find(l => l.type === listType);
            if (!list) {
                throw new Error(`Lista do tipo ${listType} não encontrada`);
            }

            const isInList = this.isMovieInList(tmdbMovieId, listType);

            if (isInList) {
                return await this.removeMovieFromList(tmdbMovieId, list.id);
            } else {
                return await this.addMovieToList(tmdbMovieId, list.id);
            }
        },

        async toggleLike(tmdbMovieId: number) {
            try {
                const response = await axios.post(route('movies.toggle-like'), {
                    tmdb_movie_id: tmdbMovieId
                });

                if (response.data.success) {
                    const likedList = this.getLikedList;
                    if (likedList && this.currentList?.id === likedList.id) {
                        await this.fetchListMovies(likedList.id, this.pagination.current_page);
                    }

                    if (likedList) {
                        if (response.data.action === 'added') {
                            if (typeof likedList.movies_count === 'number') {
                                likedList.movies_count++;
                            }
                        } else if (response.data.action === 'removed') {
                            if (typeof likedList.movies_count === 'number') {
                                likedList.movies_count = Math.max(0, likedList.movies_count - 1);
                            }
                        }
                    }
                }

                return response.data;
            } catch (error) {
                console.error('Error toggling like:', error);
                throw error;
            }
        },

        async markWatched(tmdbMovieId: number) {
            try {
                const response = await axios.post(route('movies.mark-watched'), {
                    tmdb_movie_id: tmdbMovieId
                });

                if (response.data.success) {
                    const watchedList = this.getWatchedList;
                    if (watchedList && this.currentList?.id === watchedList.id) {
                        await this.fetchListMovies(watchedList.id, this.pagination.current_page);
                    }

                    if (watchedList) {
                        if (response.data.action === 'added') {
                            if (typeof watchedList.movies_count === 'number') {
                                watchedList.movies_count++;
                            }
                        } else if (response.data.action === 'removed') {
                            if (typeof watchedList.movies_count === 'number') {
                                watchedList.movies_count = Math.max(0, watchedList.movies_count - 1);
                            }
                        }
                    }
                }

                return response.data;
            } catch (error) {
                console.error('Error marking watched:', error);
                throw error;
            }
        },

        async createCustomList(name: string, description?: string, isPublic: boolean = false) {
            try {
                const response = await axios.post(route('movie-lists.store'), {
                    name,
                    description,
                    is_public: isPublic,
                    type: 'custom'
                });

                if (response.data.success) {
                    this.lists.push(response.data.data);
                }

                return response.data;
            } catch (error) {
                console.error('Error creating custom list:', error);
                throw error;
            }
        },

        async deleteCustomList(listId: number) {
            try {
                const response = await axios.delete(route('movie-lists.destroy', { movieList: listId }));

                if (response.data.success) {
                    this.lists = this.lists.filter(list => list.id !== listId);
                    if (this.currentList?.id === listId) {
                        this.currentList = null;
                        this.currentListMovies = [];
                        this.pagination = {
                            current_page: 1,
                            total_pages: 1,
                            per_page: 20,
                            total_count: 0,
                        };
                    }
                }

                return response.data;
            } catch (error) {
                console.error('Error deleting custom list:', error);
                throw error;
            }
        },

        clearCurrentList() {
            this.currentList = null;
            this.currentListMovies = [];
            this.pagination = {
                current_page: 1,
                total_pages: 1,
                per_page: 20,
                total_count: 0,
            };
        },

        clearError() {
            this.error = null;
        },
    },
});
