import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import type {
    Movie,
    Genre,
    TrendingResponse,
    ApiResponse,
    GenresResponse
} from '@/types/movies';
import { CACHE_DURATION } from '@/types/movies';

interface CacheTimestamps {
    trending: number | null;
    popular: number | null;
    genres: number | null;
}

interface LoadingState {
    global: boolean;
    trending: boolean;
    popular: boolean;
    genres: boolean;
}

interface ApiError {
    message: string;
    code?: string | number;
}

export const useMoviesStore = defineStore('movies', () => {

    //* State
    const movies = ref({
        trending: [] as Movie[],
        popular: [] as Movie[],
    });

    const genres = ref<Genre[]>([]);

    const loading = ref<LoadingState>({
        global: false,
        trending: false,
        popular: false,
        genres: false,
    });

    const error = ref<ApiError | null>(null);

    const lastFetch = ref<CacheTimestamps>({
        trending: null,
        popular: null,
        genres: null,
    });

    // ? Getters
    const isDataStale = computed(() => ({
        trending: !lastFetch.value.trending || (Date.now() - lastFetch.value.trending) > CACHE_DURATION,
        popular: !lastFetch.value.popular || (Date.now() - lastFetch.value.popular) > CACHE_DURATION,
        genres: !lastFetch.value.genres || (Date.now() - lastFetch.value.genres) > CACHE_DURATION,
    }));

    const transformMovieImages = (movie: Movie) => ({
        ...movie,
        poster_url: movie.poster_path ? `https://image.tmdb.org/t/p/w500${movie.poster_path}` : null,
        backdrop_url: movie.backdrop_path ? `https://image.tmdb.org/t/p/w1280${movie.backdrop_path}` : null,
    });

    const moviesWithImages = computed(() => ({
        trending: movies.value.trending.map(transformMovieImages),
        popular: movies.value.popular.map(transformMovieImages),
    }));



    const trendingWithImages = computed(() => moviesWithImages.value.trending);
    const popularWithImages = computed(() => moviesWithImages.value.popular);

    const hasData = computed(() => ({
        trending: movies.value.trending.length > 0,
        popular: movies.value.popular.length > 0,
        genres: genres.value.length > 0,
    }));

    const isLoading = computed(() =>
        loading.value.global ||
        loading.value.trending ||
        loading.value.popular ||
        loading.value.genres
    );

    // ? Helpers
    const handleApiError = (err: unknown, context: string): void => {
        const errorMessage = err instanceof Error ? err.message : 'Erro desconhecido';
        error.value = { message: errorMessage, code: context };
        console.error(`Erro em ${context}:`, err);
    };

    const shouldFetch = (type: keyof CacheTimestamps, force: boolean): boolean => {
        if (force) return true;
        if (isDataStale.value[type]) return true;
        return !hasData.value[type];
    };

    // API Actions
    const fetchTrendingMovies = async (force = false): Promise<void> => {
        if (!shouldFetch('trending', force)) return;

        loading.value.trending = true;
        error.value = null;

        try {
            const response = await axios.get<TrendingResponse>('/api/public/tmdb/movies/trending', {
                params: { time_window: 'day', page: 1 }
            });

            if (response.data.success) {
                movies.value.trending = response.data.data;
                lastFetch.value.trending = Date.now();
            } else {
                throw new Error(response.data.error || 'Erro ao buscar filmes em trending');
            }
        } catch (err) {
            handleApiError(err, 'fetchTrendingMovies');
            throw err;
        } finally {
            loading.value.trending = false;
        }
    };

    const fetchPopularMovies = async (force = false): Promise<void> => {
        if (!shouldFetch('popular', force)) return;

        loading.value.popular = true;
        error.value = null;

        try {
            const response = await axios.get<ApiResponse<Movie>>('/api/public/tmdb/movies/popular', {
                params: { page: 1 }
            });

            if (response.data.success) {
                movies.value.popular = response.data.data;
                lastFetch.value.popular = Date.now();
            } else {
                throw new Error(response.data.error || 'Erro ao buscar filmes populares');
            }
        } catch (err) {
            handleApiError(err, 'fetchPopularMovies');
            throw err;
        } finally {
            loading.value.popular = false;
        }
    };

    const fetchGenres = async (force = false): Promise<void> => {
        if (!shouldFetch('genres', force)) return;

        loading.value.genres = true;
        error.value = null;

        try {
            const response = await axios.get<GenresResponse>('/api/public/tmdb/genres');

            if (response.data.success) {
                genres.value = response.data.genres;
                lastFetch.value.genres = Date.now();
            } else {
                throw new Error(response.data.error || 'Erro ao buscar gêneros');
            }
        } catch (err) {
            handleApiError(err, 'fetchGenres');
            throw err;
        } finally {
            loading.value.genres = false;
        }
    };

    // ? Utility Functions
    const getGenreNames = (genreIds: number[]): string[] => {
        return genreIds
            .map(id => genres.value.find((genre: Genre) => genre.id === id)?.name)
            .filter((name): name is string => !!name);
    };

    const initializeData = async (): Promise<void> => {
        loading.value.global = true;
        error.value = null;

        try {
            await Promise.all([
                fetchTrendingMovies(),
                fetchPopularMovies(),
                fetchGenres(),
            ]);
        } catch (err) {
            handleApiError(err, 'initializeData');
            throw err;
        } finally {
            loading.value.global = false;
        }
    };

    const clearCache = (): void => {
        movies.value.trending = [];
        movies.value.popular = [];
        genres.value = [];
        lastFetch.value = { trending: null, popular: null, genres: null };
        error.value = null;
    };

    const refreshData = async (): Promise<void> => {
        clearCache();
        await initializeData();
    };

    // ? Return Store
    return {
        //* State
        movies: computed(() => movies.value),
        genres: computed(() => genres.value),
        loading: computed(() => loading.value),
        error: computed(() => error.value),
        lastFetch: computed(() => lastFetch.value),

        //* Getters
        isDataStale,
        moviesWithImages,
        trendingWithImages,
        popularWithImages,
        hasData,
        isLoading,

        //* Actions
        fetchTrendingMovies,
        fetchPopularMovies,
        fetchGenres,
        getGenreNames,
        initializeData,
        clearCache,
        refreshData,
    };
});
