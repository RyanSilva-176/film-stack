import type { ApiResponse, Genre, GenresResponse, Movie, TrendingResponse } from '@/types/movies';
import { CACHE_DURATION } from '@/types/movies';
import axios from 'axios';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

interface CacheTimestamps {
    trending: number | null;
    popular: number | null;
    upcoming: number | null;
    discover: number | null;
    action: number | null;
    comedy: number | null;
    horror: number | null;
    animation: number | null;
    fantasy: number | null;
    genres: number | null;
}

interface LoadingState {
    global: boolean;
    trending: boolean;
    popular: boolean;
    upcoming: boolean;
    discover: boolean;
    action: boolean;
    comedy: boolean;
    horror: boolean;
    animation: boolean;
    fantasy: boolean;
    genres: boolean;
}

interface ApiError {
    message: string;
    code?: string | number;
}

const GENRE_IDS = {
    ACTION: 28,
    COMEDY: 35,
    HORROR: 27,
    ANIMATION: 16,
    FANTASY: 14,
} as const;

export const useMoviesStore = defineStore('movies', () => {
    //* State
    const movies = ref({
        trending: [] as Movie[],
        popular: [] as Movie[],
        upcoming: [] as Movie[],
        discover: [] as Movie[],
        action: [] as Movie[],
        comedy: [] as Movie[],
        horror: [] as Movie[],
        animation: [] as Movie[],
        fantasy: [] as Movie[],
    });

    const genres = ref<Genre[]>([]);

    const loading = ref<LoadingState>({
        global: false,
        trending: false,
        popular: false,
        upcoming: false,
        discover: false,
        action: false,
        comedy: false,
        horror: false,
        animation: false,
        fantasy: false,
        genres: false,
    });

    const error = ref<ApiError | null>(null);

    const lastFetch = ref<CacheTimestamps>({
        trending: null,
        popular: null,
        upcoming: null,
        discover: null,
        action: null,
        comedy: null,
        horror: null,
        animation: null,
        fantasy: null,
        genres: null,
    });

    // ? Getters
    const isDataStale = computed(() => ({
        trending: !lastFetch.value.trending || Date.now() - lastFetch.value.trending > CACHE_DURATION,
        popular: !lastFetch.value.popular || Date.now() - lastFetch.value.popular > CACHE_DURATION,
        upcoming: !lastFetch.value.upcoming || Date.now() - lastFetch.value.upcoming > CACHE_DURATION,
        discover: !lastFetch.value.discover || Date.now() - lastFetch.value.discover > CACHE_DURATION,
        action: !lastFetch.value.action || Date.now() - lastFetch.value.action > CACHE_DURATION,
        comedy: !lastFetch.value.comedy || Date.now() - lastFetch.value.comedy > CACHE_DURATION,
        horror: !lastFetch.value.horror || Date.now() - lastFetch.value.horror > CACHE_DURATION,
        animation: !lastFetch.value.animation || Date.now() - lastFetch.value.animation > CACHE_DURATION,
        fantasy: !lastFetch.value.fantasy || Date.now() - lastFetch.value.fantasy > CACHE_DURATION,
        genres: !lastFetch.value.genres || Date.now() - lastFetch.value.genres > CACHE_DURATION,
    }));

    const transformMovieImages = (movie: Movie) => ({
        ...movie,
        poster_url: movie.poster_path ? `https://image.tmdb.org/t/p/w500${movie.poster_path}` : null,
        backdrop_url: movie.backdrop_path ? `https://image.tmdb.org/t/p/w1280${movie.backdrop_path}` : null,
    });

    const moviesWithImages = computed(() => ({
        trending: movies.value.trending.map(transformMovieImages),
        popular: movies.value.popular.map(transformMovieImages),
        upcoming: movies.value.upcoming.map(transformMovieImages),
        discover: movies.value.discover.map(transformMovieImages),
        action: movies.value.action.map(transformMovieImages),
        comedy: movies.value.comedy.map(transformMovieImages),
        horror: movies.value.horror.map(transformMovieImages),
        animation: movies.value.animation.map(transformMovieImages),
        fantasy: movies.value.fantasy.map(transformMovieImages),
    }));

    const trendingWithImages = computed(() => moviesWithImages.value.trending);
    const popularWithImages = computed(() => moviesWithImages.value.popular);
    const upcomingWithImages = computed(() => moviesWithImages.value.upcoming);
    const discoverWithImages = computed(() => moviesWithImages.value.discover);
    const actionWithImages = computed(() => moviesWithImages.value.action);
    const comedyWithImages = computed(() => moviesWithImages.value.comedy);
    const horrorWithImages = computed(() => moviesWithImages.value.horror);
    const animationWithImages = computed(() => moviesWithImages.value.animation);
    const fantasyWithImages = computed(() => moviesWithImages.value.fantasy);

    const hasData = computed(() => ({
        trending: movies.value.trending.length > 0,
        popular: movies.value.popular.length > 0,
        upcoming: movies.value.upcoming.length > 0,
        discover: movies.value.discover.length > 0,
        action: movies.value.action.length > 0,
        comedy: movies.value.comedy.length > 0,
        horror: movies.value.horror.length > 0,
        animation: movies.value.animation.length > 0,
        fantasy: movies.value.fantasy.length > 0,
        genres: genres.value.length > 0,
    }));

    const isLoading = computed(
        () =>
            loading.value.global ||
            loading.value.trending ||
            loading.value.popular ||
            loading.value.upcoming ||
            loading.value.discover ||
            loading.value.action ||
            loading.value.comedy ||
            loading.value.horror ||
            loading.value.animation ||
            loading.value.fantasy ||
            loading.value.genres,
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

    //? API Actions
    const fetchTrendingMovies = async (force = false): Promise<void> => {
        if (!shouldFetch('trending', force)) return;

        loading.value.trending = true;
        error.value = null;

        try {
            const response = await axios.get<TrendingResponse>(route('api.tmdb.movies.trending'), {
                params: { time_window: 'day', page: 1 },
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
            const response = await axios.get<ApiResponse<Movie>>(route('api.tmdb.movies.popular'), {
                params: { page: 1 },
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

    const fetchUpcomingMovies = async (force = false): Promise<void> => {
        if (!shouldFetch('upcoming', force)) return;

        loading.value.upcoming = true;
        error.value = null;

        try {
            const response = await axios.get<ApiResponse<Movie>>(route('api.tmdb.movies.discover'), {
                params: {
                    'primary_release_date.gte': new Date().toISOString().split('T')[0],
                    sort_by: 'popularity.desc',
                    page: 1,
                },
            });

            if (response.data.success) {
                movies.value.upcoming = response.data.data;
                lastFetch.value.upcoming = Date.now();
            } else {
                throw new Error(response.data.error || 'Erro ao buscar próximos lançamentos');
            }
        } catch (err) {
            handleApiError(err, 'fetchUpcomingMovies');
            throw err;
        } finally {
            loading.value.upcoming = false;
        }
    };

    const fetchDiscoverMovies = async (force = false): Promise<void> => {
        if (!shouldFetch('discover', force)) return;

        loading.value.discover = true;
        error.value = null;

        try {
            const response = await axios.get<ApiResponse<Movie>>(route('api.tmdb.movies.discover'), {
                params: {
                    sort_by: 'vote_average.desc',
                    'vote_count.gte': 1000,
                    page: 1,
                },
            });

            if (response.data.success) {
                movies.value.discover = response.data.data;
                lastFetch.value.discover = Date.now();
            } else {
                throw new Error(response.data.error || 'Erro ao buscar filmes para descobrir');
            }
        } catch (err) {
            handleApiError(err, 'fetchDiscoverMovies');
            throw err;
        } finally {
            loading.value.discover = false;
        }
    };

    const fetchMoviesByGenre = async (genreType: 'action' | 'comedy' | 'horror' | 'animation' | 'fantasy', force = false): Promise<void> => {
        if (!shouldFetch(genreType, force)) return;

        loading.value[genreType] = true;
        error.value = null;

        const genreId = GENRE_IDS[genreType.toUpperCase() as keyof typeof GENRE_IDS];

        try {
            const response = await axios.get<ApiResponse<Movie>>(route('api.tmdb.movies.by-genre', { genreId }), {
                params: { page: 1 },
            });

            if (response.data.success) {
                movies.value[genreType] = response.data.data;
                lastFetch.value[genreType] = Date.now();
            } else {
                throw new Error(response.data.error || `Erro ao buscar filmes de ${genreType}`);
            }
        } catch (err) {
            handleApiError(err, `fetchMoviesByGenre-${genreType}`);
            throw err;
        } finally {
            loading.value[genreType] = false;
        }
    };

    const fetchGenres = async (force = false): Promise<void> => {
        if (!shouldFetch('genres', force)) return;

        loading.value.genres = true;
        error.value = null;

        try {
            const response = await axios.get<GenresResponse>(route('api.tmdb.genres'));

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
        return genreIds.map((id) => genres.value.find((genre: Genre) => genre.id === id)?.name).filter((name): name is string => !!name);
    };

    const initializeData = async (): Promise<void> => {
        loading.value.global = true;
        error.value = null;

        try {
            await Promise.all([fetchTrendingMovies(), fetchPopularMovies(), fetchGenres()]);
        } catch (err) {
            handleApiError(err, 'initializeData');
            throw err;
        } finally {
            loading.value.global = false;
        }
    };

    const initializeDashboardData = async (): Promise<void> => {
        loading.value.global = true;
        error.value = null;

        try {
            await Promise.all([
                fetchTrendingMovies(),
                fetchPopularMovies(),
                fetchUpcomingMovies(),
                fetchDiscoverMovies(),
                fetchMoviesByGenre('action'),
                fetchMoviesByGenre('comedy'),
                fetchMoviesByGenre('horror'),
                fetchMoviesByGenre('animation'),
                fetchMoviesByGenre('fantasy'),
                fetchGenres(),
            ]);
        } catch (err) {
            handleApiError(err, 'initializeDashboardData');
            throw err;
        } finally {
            loading.value.global = false;
        }
    };

    const clearCache = (): void => {
        movies.value = {
            trending: [],
            popular: [],
            upcoming: [],
            discover: [],
            action: [],
            comedy: [],
            horror: [],
            animation: [],
            fantasy: [],
        };
        genres.value = [];
        lastFetch.value = {
            trending: null,
            popular: null,
            upcoming: null,
            discover: null,
            action: null,
            comedy: null,
            horror: null,
            animation: null,
            fantasy: null,
            genres: null,
        };
        error.value = null;
    };

    const refreshData = async (): Promise<void> => {
        clearCache();
        await initializeData();
    };

    // ? Return Store
    return {
        //* State
        movies,
        genres,
        loading,
        error,
        lastFetch,

        //* Getters
        isDataStale,
        moviesWithImages,
        trendingWithImages,
        popularWithImages,
        upcomingWithImages,
        discoverWithImages,
        actionWithImages,
        comedyWithImages,
        horrorWithImages,
        animationWithImages,
        fantasyWithImages,
        hasData,
        isLoading,

        //* Actions
        fetchTrendingMovies,
        fetchPopularMovies,
        fetchUpcomingMovies,
        fetchDiscoverMovies,
        fetchMoviesByGenre,
        fetchGenres,
        getGenreNames,
        initializeData,
        initializeDashboardData,
        clearCache,
        refreshData,
    };
});
