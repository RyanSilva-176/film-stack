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
                                        <font-awesome-icon :icon="['fas', 'film']" class="h-6 w-6 text-gray-600 mb-1" />
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
import { useMoviesStore } from '@/stores/movies';
import type { Movie } from '@/types/movies';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faAlignLeft,
    faBuilding,
    faCalendar,
    faClock,
    faFilm,
    faGlobe,
    faLock,
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
const loading = ref(false);
const error = ref(false);
const movieDetails = ref<any>(null);
const detailsPanel = ref<HTMLElement | null>(null);
const showPanel = ref(false);

// Image error handling
const posterError = ref(false);
const backdropError = ref(false);

const isOpen = computed({
    get: () => props.isOpen,
    set: (value: boolean) => emit('update:open', value),
});

// Image URLs with fallbacks
const posterImageUrl = computed(() => {
    if (posterError.value || !movieDetails.value) return null;
    
    // Prioridade: poster_url > poster_path > backdrop_url > backdrop_path
    if (movieDetails.value.poster_url) return movieDetails.value.poster_url;
    if (movieDetails.value.poster_path) return `https://image.tmdb.org/t/p/w342${movieDetails.value.poster_path}`;
    if (movieDetails.value.backdrop_url) return movieDetails.value.backdrop_url;
    if (movieDetails.value.backdrop_path) return `https://image.tmdb.org/t/p/w342${movieDetails.value.backdrop_path}`;
    
    return null;
});

const backdropImageUrl = computed(() => {
    if (backdropError.value || !movieDetails.value) return null;
    
    // Prioridade: backdrop_url > backdrop_path > poster_url > poster_path
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
    // Reset image errors when loading new movie
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

// Image error handlers
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
