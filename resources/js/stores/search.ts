import type { Genre, Movie, PaginationInfo } from '@/types/movies';
import axios from 'axios';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

export interface SearchFilters {
    query?: string;
    genreId?: number;
    year?: number;
    sortBy?: string;
    perPage?: number;
    includeAdult?: boolean;
    voteAverageMin?: number;
    voteAverageMax?: number;
    releaseDateMin?: string;
    releaseDateMax?: string;
}

export interface SearchState {
    results: Movie[];
    query: string;
    filters: SearchFilters;
    pagination: PaginationInfo | null;
    loading: boolean;
    error: string | null;
    previewResults: Movie[];
    previewLoading: boolean;
    genres: Genre[];
    genresLoading: boolean;
}

export const useSearchStore = defineStore('search', () => {
    const state = ref<SearchState>({
        results: [],
        query: '',
        filters: {},
        pagination: null,
        loading: false,
        error: null,
        previewResults: [],
        previewLoading: false,
        genres: [],
        genresLoading: false,
    });

    const hasResults = computed(() => state.value.results.length > 0);
    const hasPreviewResults = computed(() => state.value.previewResults.length > 0);
    const isLoading = computed(() => state.value.loading || state.value.previewLoading);
    const totalResults = computed(() => state.value.pagination?.total_results || 0);
    const currentPage = computed(() => state.value.pagination?.current_page || 1);
    const totalPages = computed(() => state.value.pagination?.total_pages || 1);
    const hasNextPage = computed(() => state.value.pagination?.has_next_page || false);
    const hasPreviousPage = computed(() => state.value.pagination?.has_previous_page || false);

    const searchMovies = async (query: string, page = 1, filters: SearchFilters = {}): Promise<void> => {
        if (!query.trim()) {
            clearResults();
            return;
        }

        state.value.loading = true;
        state.value.error = null;

        try {
            const params = {
                query: query.trim(),
                page,
                ...filters,
            };

            const response = await axios.get<{
                success: boolean;
                query: string;
                data: Movie[];
                pagination: PaginationInfo;
                error?: string;
            }>(route('api.tmdb.movies.search'), { params });

            if (response.data.success) {
                state.value.results = response.data.data;
                state.value.query = response.data.query;
                state.value.filters = filters;
                state.value.pagination = response.data.pagination;
            } else {
                throw new Error(response.data.error || 'Erro na busca');
            }
        } catch (error: any) {
            state.value.error = error.response?.data?.error || error.message || 'Erro na busca';
            state.value.results = [];
            state.value.pagination = null;
        } finally {
            state.value.loading = false;
        }
    };

    const searchPreview = async (query: string, limit = 5): Promise<void> => {
        if (!query.trim()) {
            state.value.previewResults = [];
            return;
        }

        state.value.previewLoading = true;

        try {
            const response = await axios.get<{
                success: boolean;
                query: string;
                data: Movie[];
                pagination: PaginationInfo;
                error?: string;
            }>(route('api.tmdb.movies.search'), {
                params: {
                    query: query.trim(),
                    page: 1,
                },
            });

            if (response.data.success) {
                state.value.previewResults = response.data.data.slice(0, limit);
            } else {
                state.value.previewResults = [];
            }
        } catch {
            state.value.previewResults = [];
        } finally {
            state.value.previewLoading = false;
        }
    };

    const searchByGenre = async (genreId: number, page = 1, additionalFilters: SearchFilters = {}): Promise<void> => {
        state.value.loading = true;
        state.value.error = null;

        try {
            const params = {
                page,
                ...additionalFilters,
            };

            const response = await axios.get<{
                success: boolean;
                genre: Genre;
                data: Movie[];
                pagination: PaginationInfo;
                error?: string;
            }>(route('api.tmdb.movies.by-genre', { genreId }), { params });

            if (response.data.success) {
                state.value.results = response.data.data;
                state.value.query = '';
                state.value.filters = { genreId, ...additionalFilters };
                state.value.pagination = response.data.pagination;
            } else {
                throw new Error(response.data.error || 'Erro na busca por gênero');
            }
        } catch (error: any) {
            state.value.error = error.response?.data?.error || error.message || 'Erro na busca por gênero';
            state.value.results = [];
            state.value.pagination = null;
        } finally {
            state.value.loading = false;
        }
    };

    const loadGenres = async (): Promise<void> => {
        if (state.value.genres.length > 0) return;

        state.value.genresLoading = true;

        try {
            const response = await axios.get<{
                success: boolean;
                genres: Genre[];
                error?: string;
            }>(route('api.tmdb.genres'));

            if (response.data.success) {
                state.value.genres = response.data.genres;
            } else {
                throw new Error(response.data.error || 'Erro ao carregar gêneros');
            }
        } catch (error: any) {
            console.error('Erro ao carregar gêneros:', error);
        } finally {
            state.value.genresLoading = false;
        }
    };

    const loadNextPage = async (): Promise<void> => {
        if (!hasNextPage.value) return;

        const nextPage = currentPage.value + 1;

        if (state.value.query) {
            await searchMovies(state.value.query, nextPage, state.value.filters);
        } else if (state.value.filters.genreId) {
            await searchByGenre(state.value.filters.genreId, nextPage, state.value.filters);
        }
    };

    const loadPreviousPage = async (): Promise<void> => {
        if (!hasPreviousPage.value) return;

        const previousPage = currentPage.value - 1;

        if (state.value.query) {
            await searchMovies(state.value.query, previousPage, state.value.filters);
        } else if (state.value.filters.genreId) {
            await searchByGenre(state.value.filters.genreId, previousPage, state.value.filters);
        }
    };

    const loadPage = async (page: number): Promise<void> => {
        if (page < 1 || page > totalPages.value) return;

        if (state.value.query) {
            await searchMovies(state.value.query, page, state.value.filters);
        } else if (state.value.filters.genreId) {
            await searchByGenre(state.value.filters.genreId, page, state.value.filters);
        }
    };

    const clearResults = (): void => {
        state.value.results = [];
        state.value.query = '';
        state.value.filters = {};
        state.value.pagination = null;
        state.value.error = null;
    };

    const clearPreview = (): void => {
        state.value.previewResults = [];
    };

    const updateFilters = async (newFilters: SearchFilters): Promise<void> => {
        state.value.filters = { ...state.value.filters, ...newFilters };

        if (state.value.query) {
            await searchMovies(state.value.query, 1, state.value.filters);
        } else if (state.value.filters.genreId) {
            await searchByGenre(state.value.filters.genreId, 1, state.value.filters);
        }
    };

    const getGenreName = (genreId: number): string => {
        const genre = state.value.genres.find((g) => g.id === genreId);
        return genre?.name || 'Gênero Desconhecido';
    };

    // Initialize
    const init = async (): Promise<void> => {
        await loadGenres();
    };

    return {
        // State (reactive)
        results: computed(() => state.value.results),
        query: computed(() => state.value.query),
        filters: computed(() => state.value.filters),
        pagination: computed(() => state.value.pagination),
        loading: computed(() => state.value.loading),
        error: computed(() => state.value.error),
        previewResults: computed(() => state.value.previewResults),
        previewLoading: computed(() => state.value.previewLoading),
        genres: computed(() => state.value.genres),
        genresLoading: computed(() => state.value.genresLoading),

        // Computed
        hasResults,
        hasPreviewResults,
        isLoading,
        totalResults,
        currentPage,
        totalPages,
        hasNextPage,
        hasPreviousPage,

        // Actions
        searchMovies,
        searchPreview,
        searchByGenre,
        loadGenres,
        loadNextPage,
        loadPreviousPage,
        loadPage,
        clearResults,
        clearPreview,
        updateFilters,
        getGenreName,
        init,
    };
});
