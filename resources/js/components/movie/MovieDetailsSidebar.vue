<template>
    <div>
        <Teleport to="body">
            <div v-if="showPanel" class="fixed inset-0 z-50" @click="closePanel">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" />

                <!-- Movie Details Panel -->
                <div
                    class="absolute top-0 right-0 h-full w-full max-w-[90vw] overflow-y-auto bg-gray-900 shadow-2xl sm:w-96 lg:w-[28rem]"
                    @click.stop
                    ref="detailsPanel"
                >
                    <!-- Movie Backdrop Header -->
                    <div v-if="backdropImageUrl" class="relative h-48 overflow-hidden">
                        <img
                            :src="backdropImageUrl"
                            :alt="movieDetails.title"
                            class="h-full w-full object-cover"
                            loading="lazy"
                            @error="handleBackdropError"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/50 to-transparent" />

                        <!-- Close Button -->
                        <button
                            @click="closePanel"
                            class="absolute top-4 right-4 cursor-pointer rounded-lg p-2 text-white/80 backdrop-blur-sm transition-colors hover:bg-black/50 hover:text-white"
                            aria-label="Close details"
                        >
                            <font-awesome-icon :icon="['fas', 'times']" class="h-6 w-6" />
                        </button>

                        <!-- Movie Title Overlay -->
                        <div class="absolute right-4 bottom-4 left-4">
                            <h2 class="text-xl leading-tight font-bold text-white">
                                {{ movieDetails?.title }}
                            </h2>
                            <p
                                v-if="movieDetails?.original_title && movieDetails.original_title !== movieDetails.title"
                                class="mt-1 text-sm text-gray-300"
                            >
                                {{ movieDetails.original_title }}
                            </p>
                        </div>
                    </div>

                    <!-- Fallback Header (no backdrop) -->
                    <div v-else class="flex items-center justify-between border-b border-gray-800 p-4">
                        <h2 class="text-xl font-bold text-white">
                            {{ movieDetails?.title || 'Detalhes do Filme' }}
                        </h2>
                        <button
                            @click="closePanel"
                            class="cursor-pointer rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-800 hover:text-white"
                            aria-label="Close details"
                        >
                            <font-awesome-icon :icon="['fas', 'times']" class="h-6 w-6" />
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="flex items-center justify-center p-8">
                        <div class="space-y-4 text-center">
                            <div class="mx-auto h-16 w-16 animate-spin rounded-full border-4 border-red-500 border-t-transparent"></div>
                            <p class="text-gray-400">Carregando detalhes...</p>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="error" class="flex items-center justify-center p-8">
                        <div class="space-y-4 text-center">
                            <div class="text-6xl text-red-500">⚠️</div>
                            <p class="text-gray-400">Erro ao carregar detalhes do filme</p>
                            <button
                                @click="loadMovieDetails"
                                class="flex items-center gap-3 rounded-lg border border-gray-700 px-4 py-3 text-white transition-colors hover:bg-gray-800"
                            >
                                <font-awesome-icon :icon="['fas', 'redo']" class="h-4 w-4" />
                                Tentar novamente
                            </button>
                        </div>
                    </div>

                    <!-- Movie Content -->
                    <div v-else-if="movieDetails" class="space-y-6 p-4">
                        <!-- Movie Poster and Basic Info -->
                        <div class="flex gap-4">
                            <!-- Poster -->
                            <div class="flex-shrink-0">
                                <div v-if="posterImageUrl" class="relative">
                                    <img
                                        :src="posterImageUrl"
                                        :alt="movieDetails.title"
                                        class="h-36 w-24 rounded-lg object-cover shadow-lg"
                                        loading="lazy"
                                        @error="handlePosterError"
                                    />
                                </div>
                                <div v-else class="flex h-36 w-24 items-center justify-center rounded-lg bg-gray-800 shadow-lg">
                                    <div class="text-center">
                                        <font-awesome-icon :icon="['fas', 'film']" class="mb-1 h-6 w-6 text-gray-600" />
                                        <p class="text-xs text-gray-500">Sem imagem</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Info Grid -->
                            <div class="flex-1 space-y-3">
                                <div v-if="movieDetails.release_date" class="flex items-center gap-3 text-sm">
                                    <font-awesome-icon :icon="['fas', 'calendar']" class="h-4 w-4 text-gray-400" />
                                    <span class="text-gray-400">Lançamento:</span>
                                    <span class="text-white">{{ formatDate(movieDetails.release_date) }}</span>
                                </div>

                                <div v-if="movieDetails.vote_average > 0" class="flex items-center gap-3 text-sm">
                                    <font-awesome-icon :icon="['fas', 'star']" class="h-4 w-4 text-yellow-400" />
                                    <span class="text-gray-400">Avaliação:</span>
                                    <span class="text-white">{{ movieDetails.vote_average.toFixed(1) }}</span>
                                    <span class="text-gray-500">({{ movieDetails.vote_count }} votos)</span>
                                </div>

                                <div v-if="movieDetails.runtime" class="flex items-center gap-3 text-sm">
                                    <font-awesome-icon :icon="['fas', 'clock']" class="h-4 w-4 text-gray-400" />
                                    <span class="text-gray-400">Duração:</span>
                                    <span class="text-white">{{ formatRuntime(movieDetails.runtime) }}</span>
                                </div>

                                <div v-if="movieDetails.original_language" class="flex items-center gap-3 text-sm">
                                    <font-awesome-icon :icon="['fas', 'globe']" class="h-4 w-4 text-gray-400" />
                                    <span class="text-gray-400">Idioma:</span>
                                    <span class="text-white">{{ formatLanguage(movieDetails.original_language) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Genres -->
                        <div v-if="genreNames.length > 0" class="space-y-3">
                            <h3 class="flex items-center gap-2 text-sm font-medium text-gray-400">
                                <font-awesome-icon :icon="['fas', 'tags']" class="h-4 w-4" />
                                Gêneros
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="genre in genreNames"
                                    :key="genre"
                                    class="rounded-full border border-red-600/30 bg-red-600/20 px-3 py-1 text-sm text-red-400"
                                >
                                    {{ genre }}
                                </span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-800" />

                        <!-- Overview -->
                        <div v-if="movieDetails.overview" class="space-y-3">
                            <h3 class="flex items-center gap-2 text-sm font-medium text-gray-400">
                                <font-awesome-icon :icon="['fas', 'align-left']" class="h-4 w-4" />
                                Sinopse
                            </h3>
                            <p class="text-sm leading-relaxed text-white">{{ movieDetails.overview }}</p>
                        </div>

                        <!-- Tagline -->
                        <div v-if="movieDetails.tagline" class="space-y-3">
                            <h3 class="flex items-center gap-2 text-sm font-medium text-gray-400">
                                <font-awesome-icon :icon="['fas', 'quote-left']" class="h-4 w-4" />
                                Slogan
                            </h3>
                            <p class="text-sm text-white italic">"{{ movieDetails.tagline }}"</p>
                        </div>

                        <!-- Cast -->
                        <div v-if="mainCast.length > 0" class="space-y-3">
                            <h3 class="flex items-center gap-2 text-sm font-medium text-gray-400">
                                <font-awesome-icon :icon="['fas', 'users']" class="h-4 w-4" />
                                Elenco Principal
                            </h3>
                            <div class="space-y-2">
                                <div
                                    v-for="actor in mainCast.slice(0, 5)"
                                    :key="actor.id"
                                    class="flex items-center justify-between rounded-lg px-3 py-2 transition-colors hover:bg-gray-800"
                                >
                                    <span class="text-sm text-white">{{ actor.name }}</span>
                                    <span class="text-xs text-gray-400">{{ actor.character }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Production Companies -->
                        <div v-if="mainProductionCompanies.length > 0" class="space-y-3">
                            <h3 class="flex items-center gap-2 text-sm font-medium text-gray-400">
                                <font-awesome-icon :icon="['fas', 'building']" class="h-4 w-4" />
                                Produção
                            </h3>
                            <div class="space-y-1">
                                <p
                                    v-for="company in mainProductionCompanies.slice(0, 3)"
                                    :key="company.id"
                                    class="rounded-lg px-3 py-1 text-sm text-white transition-colors hover:bg-gray-800"
                                >
                                    {{ company.name }}
                                </p>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-800" />

                        <!-- Movie Actions-->
                        <div v-if="page.props.auth.user" class="space-y-4">
                            <!-- Quick Actions -->
                            <div class="space-y-3">
                                <h3 class="flex items-center gap-2 text-sm font-medium text-gray-400">
                                    <font-awesome-icon :icon="['fas', 'list']" class="h-4 w-4" />
                                    Ações Rápidas
                                </h3>

                                <div class="space-y-2">
                                    <!-- Like Button -->
                                    <button
                                        @click="handleToggleLike"
                                        :disabled="loadingStates.like"
                                        class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-gray-800"
                                        :class="localIsLiked ? 'text-red-400' : 'text-gray-300'"
                                    >
                                        <font-awesome-icon
                                            :icon="['fas', 'heart']"
                                            class="h-4 w-4"
                                            :class="localIsLiked ? 'text-red-500' : 'text-gray-400'"
                                        />
                                        <span class="flex-1 text-left">
                                            {{ localIsLiked ? 'Remover dos Curtidos' : 'Adicionar aos Curtidos' }}
                                        </span>
                                        <div
                                            v-if="loadingStates.like"
                                            class="h-4 w-4 animate-spin rounded-full border-2 border-red-500 border-t-transparent"
                                        ></div>
                                    </button>

                                    <!-- Watchlist Button -->
                                    <button
                                        @click="handleToggleWatchlist"
                                        :disabled="loadingStates.watchlist"
                                        class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-gray-800"
                                        :class="localIsInWatchlist ? 'text-blue-400' : 'text-gray-300'"
                                    >
                                        <font-awesome-icon
                                            :icon="['fas', 'bookmark']"
                                            class="h-4 w-4"
                                            :class="localIsInWatchlist ? 'text-blue-500' : 'text-gray-400'"
                                        />
                                        <span class="flex-1 text-left">
                                            {{ localIsInWatchlist ? 'Remover da Watchlist' : 'Adicionar à Watchlist' }}
                                        </span>
                                        <div
                                            v-if="loadingStates.watchlist"
                                            class="h-4 w-4 animate-spin rounded-full border-2 border-blue-500 border-t-transparent"
                                        ></div>
                                    </button>

                                    <!-- Watched Button -->
                                    <button
                                        @click="handleMarkWatched"
                                        :disabled="loadingStates.watched"
                                        class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-gray-800"
                                        :class="localIsWatched ? 'text-green-400' : 'text-gray-300'"
                                    >
                                        <font-awesome-icon
                                            :icon="['fas', 'check-circle']"
                                            class="h-4 w-4"
                                            :class="localIsWatched ? 'text-green-500' : 'text-gray-400'"
                                        />
                                        <span class="flex-1 text-left">
                                            {{ localIsWatched ? 'Marcar como Não Assistido' : 'Marcar como Assistido' }}
                                        </span>
                                        <div
                                            v-if="loadingStates.watched"
                                            class="h-4 w-4 animate-spin rounded-full border-2 border-green-500 border-t-transparent"
                                        ></div>
                                    </button>
                                </div>
                            </div>

                            <!-- Custom Lists -->
                            <div v-if="customLists.length > 0" class="space-y-3">
                                <h3 class="flex items-center gap-2 text-sm font-medium text-gray-400">
                                    <font-awesome-icon :icon="['fas', 'folder']" class="h-4 w-4" />
                                    Listas Personalizadas
                                </h3>

                                <div class="max-h-32 space-y-2 overflow-y-auto">
                                    <button
                                        v-for="list in customLists"
                                        :key="list.id"
                                        @click="handleToggleCustomList(list)"
                                        :disabled="loadingStates.custom"
                                        class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-gray-800"
                                        :class="isMovieInCustomList(list) ? 'text-purple-400' : 'text-gray-300'"
                                    >
                                        <font-awesome-icon
                                            :icon="['fas', 'list']"
                                            class="h-4 w-4"
                                            :class="isMovieInCustomList(list) ? 'text-purple-500' : 'text-gray-400'"
                                        />
                                        <span class="flex-1 truncate text-left">
                                            {{ isMovieInCustomList(list) ? 'Remover de' : 'Adicionar à' }} "{{ list.name }}"
                                        </span>
                                        <span v-if="list.movies_count > 0" class="rounded-full bg-gray-700 px-2 py-1 text-xs text-gray-300">
                                            {{ list.movies_count }}
                                        </span>
                                        <div
                                            v-if="loadingStates.custom"
                                            class="h-4 w-4 animate-spin rounded-full border-2 border-purple-500 border-t-transparent"
                                        ></div>
                                    </button>
                                </div>
                            </div>

                            <!-- Additional Actions -->
                            <div class="space-y-2 border-t border-gray-800 pt-3">
                                <button
                                    @click="handleCreateList"
                                    class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition-colors hover:bg-gray-800"
                                >
                                    <font-awesome-icon :icon="['fas', 'plus']" class="h-4 w-4 text-gray-400" />
                                    <span class="flex-1 text-left">Criar Nova Lista</span>
                                </button>
                            </div>
                        </div>

                        <!-- Login CTA -->
                        <div
                            v-if="!page.props.auth.user"
                            class="space-y-3 rounded-lg border border-red-500/30 bg-gradient-to-r from-red-600/20 to-red-500/20 p-4"
                        >
                            <div class="flex items-center gap-3">
                                <font-awesome-icon :icon="['fas', 'lock']" class="h-5 w-5 text-red-400" />
                                <h3 class="font-medium text-white">Quer ver mais?</h3>
                            </div>
                            <p class="text-sm text-gray-300">
                                Faça login para acessar mais detalhes, trailers, elenco completo e adicionar à sua lista de filmes.
                            </p>
                            <div class="flex gap-2">
                                <button
                                    @click="goToLogin"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white transition-colors hover:bg-red-700"
                                >
                                    <font-awesome-icon :icon="['fas', 'sign-in-alt']" class="h-4 w-4" />
                                    Fazer Login
                                </button>
                                <button
                                    @click="goToRegister"
                                    class="flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-700 px-4 py-3 text-sm font-medium text-white transition-colors hover:bg-gray-800"
                                >
                                    <font-awesome-icon :icon="['fas', 'user-plus']" class="h-4 w-4" />
                                    Registrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToastSystem';
import { useMoviesStore } from '@/stores/movies';
import type { MovieList } from '@/stores/userLists';
import { useUserListsStore } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faAlignLeft,
    faBookmark,
    faBuilding,
    faCalendar,
    faCheckCircle,
    faClock,
    faFilm,
    faFolder,
    faGlobe,
    faHeart,
    faList,
    faLock,
    faPlus,
    faQuoteLeft,
    faRedo,
    faSignInAlt,
    faStar,
    faTags,
    faTimes,
    faUserPlus,
    faUsers,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import gsap from 'gsap';
import { computed, nextTick, ref, watch } from 'vue';

library.add(
    faTimes,
    faStar,
    faLock,
    faCalendar,
    faClock,
    faGlobe,
    faTags,
    faAlignLeft,
    faQuoteLeft,
    faUsers,
    faBuilding,
    faSignInAlt,
    faUserPlus,
    faRedo,
    faFilm,
    faHeart,
    faBookmark,
    faCheckCircle,
    faList,
    faPlus,
    faFolder,
);

interface Props {
    isOpen: boolean;
    movie: Movie | null;
}

interface EmitEvents {
    (e: 'update:open', value: boolean): void;
}

const page = usePage();

const props = defineProps<Props>();
const emit = defineEmits<EmitEvents>();

const moviesStore = useMoviesStore();
const userListsStore = useUserListsStore();
const { success, error: showError } = useToast();
const { warning } = useToast();

const loading = ref(false);
const error = ref(false);
const movieDetails = ref<any>(null);
const detailsPanel = ref<HTMLElement | null>(null);
const showPanel = ref(false);

const posterError = ref(false);
const backdropError = ref(false);

const localIsLiked = ref(false);
const localIsInWatchlist = ref(false);
const localIsWatched = ref(false);

const loadingStates = ref({
    like: false,
    watchlist: false,
    watched: false,
    custom: false,
});

const isOpen = computed({
    get: () => props.isOpen,
    set: (value: boolean) => emit('update:open', value),
});

const posterImageUrl = computed(() => {
    if (posterError.value || !movieDetails.value) return null;
    if (movieDetails.value.poster_url) return movieDetails.value.poster_url;
    if (movieDetails.value.poster_path) return `https://image.tmdb.org/t/p/w342${movieDetails.value.poster_path}`;
    if (movieDetails.value.backdrop_url) return movieDetails.value.backdrop_url;
    if (movieDetails.value.backdrop_path) return `https://image.tmdb.org/t/p/w342${movieDetails.value.backdrop_path}`;

    return null;
});

const backdropImageUrl = computed(() => {
    if (backdropError.value || !movieDetails.value) return null;

    if (movieDetails.value.backdrop_url) return movieDetails.value.backdrop_url;
    if (movieDetails.value.backdrop_path) return `https://image.tmdb.org/t/p/w780${movieDetails.value.backdrop_path}`;
    if (movieDetails.value.poster_url) return movieDetails.value.poster_url;
    if (movieDetails.value.poster_path) return `https://image.tmdb.org/t/p/w780${movieDetails.value.poster_path}`;

    return null;
});

watch(
    isOpen,
    async (open) => {
        if (open) {
            showPanel.value = true;
            await nextTick();
            if (detailsPanel.value) {
                gsap.fromTo(detailsPanel.value, { x: 320, opacity: 0 }, { x: 0, opacity: 1, duration: 0.4, ease: 'power3.out' });
            }
        } else {
            if (detailsPanel.value) {
                gsap.to(detailsPanel.value, {
                    x: 320,
                    opacity: 0,
                    duration: 0.3,
                    ease: 'power3.in',
                    onComplete: () => {
                        showPanel.value = false;
                        movieDetails.value = null;
                        error.value = false;
                    },
                });
            } else {
                showPanel.value = false;
                movieDetails.value = null;
                error.value = false;
            }
        }
    },
    { immediate: true },
);

const closePanel = () => {
    isOpen.value = false;
};

const genreNames = computed(() => {
    if (!movieDetails.value?.genre_ids) return [];
    return moviesStore.getGenreNames(movieDetails.value.genre_ids);
});

const mainCast = computed(() => {
    if (!movieDetails.value?.credits?.cast) return [];
    return movieDetails.value.credits.cast;
});

const mainProductionCompanies = computed(() => {
    if (!movieDetails.value?.production_companies) return [];
    return movieDetails.value.production_companies;
});

const loadMovieDetails = async () => {
    if (!props.movie?.id) return;

    loading.value = true;
    error.value = false;
    posterError.value = false;
    backdropError.value = false;

    try {
        const response = await axios.get(`/api/public/tmdb/movies/${props.movie.id}`, {
            params: {
                append_to_response: 'credits,videos',
            },
        });

        movieDetails.value = response.data;
    } catch (err) {
        console.error('Error loading movie details:', err);
        error.value = true;
    } finally {
        loading.value = false;
    }
};

const handlePosterError = () => {
    posterError.value = true;
};

const handleBackdropError = () => {
    backdropError.value = true;
};

const formatDate = (dateString: string): string => {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    } catch {
        return dateString;
    }
};

const formatRuntime = (minutes: number): string => {
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (hours === 0) {
        return `${remainingMinutes}min`;
    }

    return `${hours}h ${remainingMinutes}min`;
};

const formatLanguage = (lang: string): string => {
    const languages: Record<string, string> = {
        en: 'Inglês',
        pt: 'Português',
        es: 'Espanhol',
        fr: 'Francês',
        de: 'Alemão',
        it: 'Italiano',
        ja: 'Japonês',
        ko: 'Coreano',
        zh: 'Chinês',
    };

    return languages[lang] || lang.toUpperCase();
};

const customLists = computed(() => {
    return userListsStore.getCustomLists || [];
});

const isMovieInCustomList = (list: MovieList): boolean => {
    if (!props.movie) return false;
    const items = list.items || list.movies || [];
    return items.some((item) => item.tmdb_movie_id === props.movie!.id);
};

const updateMovieStates = (movie: Movie) => {
    localIsLiked.value = false;
    localIsInWatchlist.value = false;
    localIsWatched.value = false;

    const likedList = userListsStore.getLikedList;
    const watchlist = userListsStore.getWatchlist;
    const watchedList = userListsStore.getWatchedList;

    if (likedList) {
        const items = likedList.items || likedList.movies || [];
        localIsLiked.value = items.some((item) => item.tmdb_movie_id === movie.id);
    }

    if (watchlist) {
        const items = watchlist.items || watchlist.movies || [];
        localIsInWatchlist.value = items.some((item) => item.tmdb_movie_id === movie.id);
    }

    if (watchedList) {
        const items = watchedList.items || watchedList.movies || [];
        localIsWatched.value = items.some((item) => item.tmdb_movie_id === movie.id);
    }
};

const handleToggleLike = async () => {
    if (!props.movie || loadingStates.value.like) return;

    loadingStates.value.like = true;
    try {
        await userListsStore.toggleLike(props.movie.id);
        success(localIsLiked.value ? 'Filme removido dos curtidos' : 'Filme adicionado aos curtidos');
        updateMovieStates(props.movie);
    } catch (err) {
        console.error('Error toggling like:', err);
        showError('Erro ao atualizar lista de curtidos');
    } finally {
        loadingStates.value.like = false;
    }
};

const handleToggleWatchlist = async () => {
    if (!props.movie || loadingStates.value.watchlist) return;

    loadingStates.value.watchlist = true;
    try {
        await userListsStore.toggleMovieInList(props.movie.id, 'watchlist');
        success(localIsInWatchlist.value ? 'Filme removido da watchlist' : 'Filme adicionado à watchlist');
        updateMovieStates(props.movie);
    } catch (err) {
        console.error('Error toggling watchlist:', err);
        showError('Erro ao atualizar watchlist');
    } finally {
        loadingStates.value.watchlist = false;
    }
};

const handleMarkWatched = async () => {
    if (!props.movie || loadingStates.value.watched) return;

    loadingStates.value.watched = true;
    try {
        await userListsStore.markWatched(props.movie.id);
        success(localIsWatched.value ? 'Filme marcado como não assistido' : 'Filme marcado como assistido');
        updateMovieStates(props.movie);
    } catch (err) {
        console.error('Error marking watched:', err);
        showError('Erro ao marcar filme como assistido');
    } finally {
        loadingStates.value.watched = false;
    }
};

const handleToggleCustomList = async (list: MovieList) => {
    if (!props.movie || loadingStates.value.custom) return;

    loadingStates.value.custom = true;
    try {
        const isInList = isMovieInCustomList(list);
        if (isInList) {
            await userListsStore.removeMovieFromList(props.movie.id, list.id);
            success(`Filme removido de "${list.name}"`);
        } else {
            await userListsStore.addMovieToList(props.movie.id, list.id);
            success(`Filme adicionado à "${list.name}"`);
        }
        await userListsStore.fetchUserLists();
    } catch (err) {
        console.error('Error toggling custom list:', err);
        showError('Erro ao atualizar lista personalizada');
    } finally {
        loadingStates.value.custom = false;
    }
};

const handleCreateList = () => {
    warning('Criar lista por aqui ainda não implementado. Acesse a página de listas para criar uma nova lista.');
    // closePanel();
};

const goToLogin = () => {
    closePanel();
    router.visit(route('login'));
};

const goToRegister = () => {
    closePanel();
    router.visit(route('register'));
};

watch(
    [() => props.movie, () => props.isOpen],
    ([newMovie, isOpen]) => {
        if (newMovie && isOpen) {
            loadMovieDetails();
            updateMovieStates(newMovie);

            if (page.props.auth.user) {
                userListsStore.fetchUserLists();
            }
        }
    },
    { immediate: true },
);

watch(
    () => [userListsStore.getLikedList?.items, userListsStore.getWatchlist?.items, userListsStore.getWatchedList?.items],
    () => {
        if (props.movie) {
            updateMovieStates(props.movie);
        }
    },
    { deep: true },
);

watch(
    () => movieDetails.value,
    (newDetails) => {
        if (newDetails && props.movie) {
            updateMovieStates(props.movie);
        }
    },
    { immediate: true },
);
</script>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #1f2937;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #4b5563;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}
</style>
