<script setup lang="ts">
import HeroSectionSlideshow from '@/components/HeroSectionSlideshow.vue';
import ConfirmationModal from '@/components/modals/ConfirmationModal.vue';
import CreateEditListModal from '@/components/modals/CreateEditListModal.vue';
import MovieOptionsModal from '@/components/modals/MovieOptionsModal.vue';
import MovieCarousel from '@/components/movie/MovieCarousel.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useToast } from '@/composables/useToastSystem';
import AppLayout from '@/layouts/AppLayout.vue';
import { useMovieDetailsStore } from '@/stores/movieDetails';
import { useMoviesStore } from '@/stores/movies';
import { useUserListsStore, type MovieList } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const moviesStore = useMoviesStore();
const userListsStore = useUserListsStore();
const movieDetailsStore = useMovieDetailsStore();
const { success, error } = useToast();

const movieOptionsModalOpen = ref(false);
const createEditListModalOpen = ref(false);
const confirmationModalOpen = ref(false);

const modalMovie = ref<Movie | null>(null);
const editingList = ref<MovieList | null>(null);
const pendingAction = ref<{
    type: 'removeFromList' | 'deleteList';
    data: any;
} | null>(null);

const trendingMovies = computed(() => moviesStore.trendingWithImages);
const popularMovies = computed(() => moviesStore.popularWithImages);
const upcomingMovies = computed(() => moviesStore.upcomingWithImages);
const discoverMovies = computed(() => moviesStore.discoverWithImages);
const actionMovies = computed(() => moviesStore.actionWithImages);
const comedyMovies = computed(() => moviesStore.comedyWithImages);
const horrorMovies = computed(() => moviesStore.horrorWithImages);
const animationMovies = computed(() => moviesStore.animationWithImages);
const fantasyMovies = computed(() => moviesStore.fantasyWithImages);

const handleMovieDetails = (movie: Movie) => {
    movieDetailsStore.openSidebar(movie);
};

const handleAddToList = (movie: Movie) => {
    modalMovie.value = movie;
    movieOptionsModalOpen.value = true;
};

const handleMoreOptions = (movie: Movie) => {
    modalMovie.value = movie;
    movieOptionsModalOpen.value = true;
};

const handleCreateList = () => {
    editingList.value = null;
    createEditListModalOpen.value = true;
};

const handleConfirmAction = async () => {
    if (!pendingAction.value) return;

    try {
        if (pendingAction.value.type === 'removeFromList') {
            const { listId, movieId } = pendingAction.value.data;
            await userListsStore.removeMovieFromList(movieId, listId);
            success('Filme removido', 'O filme foi removido da lista com sucesso');
        } else if (pendingAction.value.type === 'deleteList') {
            const list = pendingAction.value.data;
            await userListsStore.deleteCustomList(list.id);
            success('Lista excluída', 'A lista foi excluída com sucesso');
        }
    } catch (err) {
        console.error('Error in confirmation action:', err);
        error('Erro', 'Não foi possível completar a ação');
    } finally {
        pendingAction.value = null;
        confirmationModalOpen.value = false;
    }
};

const handleCancelAction = () => {
    pendingAction.value = null;
    confirmationModalOpen.value = false;
};

const confirmationTitle = computed(() => {
    if (!pendingAction.value) return '';

    switch (pendingAction.value.type) {
        case 'removeFromList':
            return 'Remover Filme';
        case 'deleteList':
            return 'Excluir Lista';
        default:
            return 'Confirmar Ação';
    }
});

const confirmationMessage = computed(() => {
    if (!pendingAction.value) return '';

    switch (pendingAction.value.type) {
        case 'removeFromList':
            return 'Tem certeza que deseja remover este filme da lista?';
        case 'deleteList':
            return 'Tem certeza que deseja excluir esta lista? Esta ação não pode ser desfeita.';
        default:
            return 'Tem certeza que deseja continuar?';
    }
});

const confirmationItemDetails = computed(() => {
    if (!pendingAction.value) return undefined;

    if (pendingAction.value.type === 'deleteList') {
        const list = pendingAction.value.data;
        return {
            title: list.name,
            subtitle: list.description || `${list.movies_count || 0} filmes`,
        };
    }

    return undefined;
});

onMounted(async () => {
    await Promise.all([moviesStore.initializeDashboardData(), userListsStore.fetchUserLists()]);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="min-h-screen overflow-x-hidden bg-gray-950">
            <!-- Hero Section Slideshow -->
            <HeroSectionSlideshow :movies="trendingMovies.slice(0, 5)" @movie-details="handleMovieDetails" @add-to-list="handleAddToList" />

            <!-- Main Content -->
            <main class="container mx-auto max-w-full space-y-12 overflow-x-hidden px-4 py-8 md:py-12">
                <!-- Trending Movies -->
                <section class="overflow-hidden">
                    <MovieCarousel
                        title="Filmes em Alta"
                        subtitle="Os filmes mais populares do momento"
                        :movies="trendingMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Popular Movies -->
                <section class="overflow-hidden">
                    <MovieCarousel
                        title="Populares"
                        subtitle="Filmes mais bem avaliados"
                        :movies="popularMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Upcoming Movies -->
                <section class="overflow-hidden">
                    <MovieCarousel
                        title="Em Breve"
                        subtitle="Próximos lançamentos"
                        :movies="upcomingMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Action Movies -->
                <section class="overflow-hidden">
                    <MovieCarousel
                        title="Ação & Aventura"
                        subtitle="Filmes cheios de adrenalina"
                        :movies="actionMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Comedy Movies -->
                <section class="overflow-hidden">
                    <MovieCarousel
                        title="Comédia"
                        subtitle="Para dar boas risadas"
                        :movies="comedyMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Horror Movies -->
                <section class="overflow-hidden">
                    <MovieCarousel
                        title="Terror"
                        subtitle="Para quem gosta de emoção forte"
                        :movies="horrorMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Animation Movies -->
                <section class="overflow-hidden">
                    <MovieCarousel
                        title="Animação"
                        subtitle="Diversão para toda a família"
                        :movies="animationMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Fantasy Movies -->
                <section class="overflow-hidden">
                    <MovieCarousel
                        title="Fantasia"
                        subtitle="Mundos mágicos e aventuras épicas"
                        :movies="fantasyMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>

                <!-- Discover Movies -->
                <section class="overflow-hidden">
                    <MovieCarousel
                        title="Descobrir"
                        subtitle="Filmes recomendados para você"
                        :movies="discoverMovies"
                        @movie-details="handleMovieDetails"
                        @add-to-list="handleAddToList"
                        @more-options="handleMoreOptions"
                    />
                </section>
            </main>

            <!-- Movie Options Modal -->
            <MovieOptionsModal
                :is-open="movieOptionsModalOpen"
                :movie="modalMovie"
                @update:open="movieOptionsModalOpen = $event"
                @create-list="handleCreateList"
                @movie-details="() => modalMovie && handleMovieDetails(modalMovie)"
            />

            <!-- Create/Edit List Modal -->
            <CreateEditListModal :is-open="createEditListModalOpen" :list="editingList" @update:open="createEditListModalOpen = $event" />

            <!-- Confirmation Modal -->
            <ConfirmationModal
                :is-open="confirmationModalOpen"
                :title="confirmationTitle"
                :message="confirmationMessage"
                :type="pendingAction?.type === 'deleteList' ? 'danger' : 'warning'"
                :item-details="confirmationItemDetails"
                confirm-text="Confirmar"
                cancel-text="Cancelar"
                :loading="false"
                @update:open="confirmationModalOpen = $event"
                @confirm="handleConfirmAction"
                @cancel="handleCancelAction"
            />

            <!-- Toast Container -->
            <ToastContainer />
        </div>
    </AppLayout>
</template>
