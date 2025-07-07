<script setup lang="ts">
import CreateEditListModal from '@/components/modals/CreateEditListModal.vue';
import MoveToListModal from '@/components/modals/MoveToListModal.vue';
import MovieOptionsModal from '@/components/modals/MovieOptionsModal.vue';
import MovieListCard from '@/components/movie/MovieListCard.vue';
import MovieListFilters from '@/components/movie/MovieListFilters.vue';
import MovieListPagination from '@/components/movie/MovieListPagination.vue';
import SearchResultsHeader from '@/components/search/SearchResultsHeader.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { useToast } from '@/composables/useToastSystem';
import AppLayout from '@/layouts/AppLayout.vue';
import { useMovieDetailsStore } from '@/stores/movieDetails';
import { useSearchStore } from '@/stores/search';
import { useUserListsStore } from '@/stores/userLists';
import { Head, router } from '@inertiajs/vue3';
import { Filter, Search } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

interface Props {
    q?: string;
    genre?: number | string;
    year?: number | string;
    sort?: string;
    page?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
    q: '',
    genre: undefined,
    year: undefined,
    sort: 'popularity.desc',
    page: 1,
});

const searchStore = useSearchStore();
const movieDetailsStore = useMovieDetailsStore();
const userListsStore = useUserListsStore();
const { success, error: showError, warning } = useToast();

const initialLoad = ref(true);
const viewMode = ref<'grid' | 'list'>('grid');
const selectionMode = ref(false);
const selectedMovies = ref<number[]>([]);
const bulkLoading = ref(false);

const movieOptionsModalOpen = ref(false);
const modalMovie = ref<any>(null);

const moveToListModalOpen = ref(false);
const createListModalOpen = ref(false);
const selectedMoviesForMove = ref<number[]>([]);

const genreId = computed(() => (props.genre ? Number(props.genre) : undefined));
const yearValue = computed(() => (props.year ? Number(props.year) : undefined));
const pageValue = computed(() => (props.page ? Number(props.page) : 1));

const pageTitle = computed(() => {
    if (props.q) {
        return `Busca por "${props.q}"`;
    } else if (genreId.value) {
        const genreName = searchStore.getGenreName(genreId.value);
        return `Filmes de ${genreName}`;
    }
    return 'Buscar Filmes';
});

const breadcrumbs = computed(() => [
    { title: 'Início', href: '/dashboard' },
    { title: 'Buscar', href: '/search' },
    ...(props.q ? [{ title: `"${props.q}"`, href: '#' }] : []),
    ...(genreId.value ? [{ title: searchStore.getGenreName(genreId.value), href: '#' }] : []),
]);

const currentFilters = ref({
    search: props.q || '',
    genre: genreId.value ? genreId.value.toString() : '',
    year: yearValue.value ? yearValue.value.toString() : '',
    sort: props.sort || 'popularity.desc',

    //? TODO: Preciso ajustar essa bomba dps no back
    // perPage: '20',
});

const handleFilterChange = (newFilters: any) => {
    const params: any = {};

    if (newFilters.search && newFilters.search.trim()) {
        params.q = newFilters.search.trim();
    }

    if (newFilters.genre && newFilters.genre !== '') {
        params.genre = newFilters.genre;
    }

    if (newFilters.year && newFilters.year !== '') {
        params.year = newFilters.year;
    }

    if (newFilters.sort && newFilters.sort !== 'popularity.desc') {
        params.sort = newFilters.sort;
    }

    if (newFilters.perPage && newFilters.perPage !== '20') {
        params.perPage = newFilters.perPage;
    }

    router.visit('/search', {
        method: 'get',
        data: params,
        preserveState: true,
        preserveScroll: false,
    });
};

const performSearch = async () => {
    if (props.q) {
        const filters: any = {};
        if (yearValue.value) filters.year = yearValue.value;
        if (props.sort !== 'popularity.desc') filters.sortBy = props.sort;
        
        // CRITICAL FIX: Include genre filter in text search when both are present
        if (genreId.value) filters.genreId = genreId.value;

        await searchStore.searchMovies(props.q, pageValue.value, filters);
    } else if (genreId.value) {
        const filters: any = {};
        if (yearValue.value) filters.year = yearValue.value;
        if (props.sort !== 'popularity.desc') filters.sortBy = props.sort;

        await searchStore.searchByGenre(genreId.value, pageValue.value, filters);
    }
};

const handleMovieClick = (movie: any) => {
    if (selectionMode.value) {
        toggleMovieSelection(movie.id);
    } else {
        movieDetailsStore.openSidebar(movie);
    }
};

const handleMovieDetails = (movie: any) => {
    movieDetailsStore.openSidebar(movie);
};

const toggleMovieSelection = (movieId: number) => {
    const index = selectedMovies.value.indexOf(movieId);
    if (index > -1) {
        selectedMovies.value.splice(index, 1);
    } else {
        selectedMovies.value.push(movieId);
    }
};

const selectAllMovies = () => {
    if (selectedMovies.value.length === searchStore.results.length) {
        selectedMovies.value = [];
    } else {
        selectedMovies.value = searchStore.results.map((movie) => movie.id);
    }
};

const clearSelection = () => {
    selectedMovies.value = [];
    selectionMode.value = false;
};

const handleBulkMarkWatched = async () => {
    if (selectedMovies.value.length === 0) return;

    bulkLoading.value = true;
    try {
        let successCount = 0;
        let errorCount = 0;

        for (const movieId of selectedMovies.value) {
            try {
                await userListsStore.markWatched(movieId);
                successCount++;
            } catch (err) {
                errorCount++;
                console.error(`Error marking movie ${movieId} as watched:`, err);
            }
        }

        if (successCount > 0) {
            success(
                'Filmes marcados como assistidos',
                `${successCount} filme${successCount > 1 ? 's' : ''} marcado${successCount > 1 ? 's' : ''} como assistido${successCount > 1 ? 's' : ''} com sucesso!`,
            );
        }

        if (errorCount > 0) {
            warning(
                'Alguns filmes não foram processados',
                `${errorCount} filme${errorCount > 1 ? 's' : ''} não pôde${errorCount > 1 ? 'ram' : ''} ser marcado${errorCount > 1 ? 's' : ''} como assistido${errorCount > 1 ? 's' : ''}.`,
            );
        }

        clearSelection();
    } catch (err) {
        console.error('Error in bulk mark watched:', err);
        showError('Erro ao marcar filmes', 'Ocorreu um erro ao marcar os filmes como assistidos.');
    } finally {
        bulkLoading.value = false;
    }
};

const handleBulkMove = () => {
    if (selectedMovies.value.length === 0) return;

    selectedMoviesForMove.value = [...selectedMovies.value];
    moveToListModalOpen.value = true;
};

const handleMoveToListModalClose = () => {
    moveToListModalOpen.value = false;
    selectedMoviesForMove.value = [];
};

const handleMoveToListConfirm = async (targetList: any) => {
    const targetListId = typeof targetList === 'number' ? targetList : targetList.id;

    if (selectedMoviesForMove.value.length === 0) return;

    bulkLoading.value = true;
    try {
        let successCount = 0;
        let errorCount = 0;

        for (const movieId of selectedMoviesForMove.value) {
            try {
                await userListsStore.addMovieToList(movieId, targetListId);
                successCount++;
            } catch (err) {
                errorCount++;
                console.error(`Error adding movie ${movieId} to list ${targetListId}:`, err);
            }
        }

        if (successCount > 0) {
            const targetListObj = userListsStore.getListById(targetListId);
            const listName = targetListObj?.name || 'lista selecionada';

            success(
                'Filmes adicionados à lista',
                `${successCount} filme${successCount > 1 ? 's' : ''} adicionado${successCount > 1 ? 's' : ''} à ${listName} com sucesso!`,
            );
        }

        if (errorCount > 0) {
            warning(
                'Alguns filmes não foram processados',
                `${errorCount} filme${errorCount > 1 ? 's' : ''} não pôde${errorCount > 1 ? 'ram' : ''} ser adicionado${errorCount > 1 ? 's' : ''} à lista.`,
            );
        }

        clearSelection();
        handleMoveToListModalClose();
    } catch (err) {
        console.error('Error in bulk move:', err);
        showError('Erro ao mover filmes', 'Ocorreu um erro ao adicionar os filmes à lista.');
    } finally {
        bulkLoading.value = false;
    }
};

const handleMoreOptions = (movie: any) => {
    modalMovie.value = movie;
    movieOptionsModalOpen.value = true;
};

const handlePageChange = (page: number) => {
    const params: any = { page };

    if (props.q) params.q = props.q;
    if (genreId.value) params.genre = genreId.value;
    if (yearValue.value) params.year = yearValue.value;
    if (props.sort && props.sort !== 'popularity.desc') params.sort = props.sort;

    router.visit('/search', {
        method: 'get',
        data: params,
        preserveState: true,
        preserveScroll: false,
    });
};

const handleCreateNewList = () => {
    createListModalOpen.value = true;
};

const handleListCreated = async (newList: any) => {
    await userListsStore.fetchUserLists();

    success('Lista criada!', `A lista "${newList.name}" foi criada com sucesso.`);

    if (selectedMoviesForMove.value.length > 0) {
        bulkLoading.value = true;
        try {
            let successCount = 0;
            let errorCount = 0;

            for (const movieId of selectedMoviesForMove.value) {
                try {
                    await userListsStore.addMovieToList(movieId, newList.id);
                    successCount++;
                } catch (err) {
                    errorCount++;
                    console.error(`Error adding movie ${movieId} to new list:`, err);
                }
            }

            if (successCount > 0) {
                success(
                    'Filmes adicionados à nova lista',
                    `${successCount} filme${successCount > 1 ? 's' : ''} adicionado${successCount > 1 ? 's' : ''} à "${newList.name}" com sucesso!`,
                );
            }

            if (errorCount > 0) {
                warning(
                    'Alguns filmes não foram adicionados',
                    `${errorCount} filme${errorCount > 1 ? 's' : ''} não pôde${errorCount > 1 ? 'ram' : ''} ser adicionado${errorCount > 1 ? 's' : ''} à nova lista.`,
                );
            }

            clearSelection();
            moveToListModalOpen.value = false;
        } catch (err) {
            console.error('Error adding movies to new list:', err);
            showError('Erro ao adicionar filmes', 'Não foi possível adicionar os filmes à nova lista.');
        } finally {
            bulkLoading.value = false;
        }
    }
};
watch(
    () => [props.q, props.genre, props.year, props.sort],
    () => {
        currentFilters.value = {
            search: props.q || '',
            genre: genreId.value ? genreId.value.toString() : '',
            year: yearValue.value ? yearValue.value.toString() : '',
            sort: props.sort || 'popularity.desc',
            // perPage: currentFilters.value.perPage || '20',
        };
    },
    { immediate: true },
);

watch(
    () => [props.q, props.genre, props.year, props.sort, props.page],
    async () => {
        await performSearch();
    },
    { immediate: false },
);

onMounted(async () => {
    await searchStore.init();
    await userListsStore.fetchUserLists();
    await performSearch();
    initialLoad.value = false;
});
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto space-y-4 px-4 py-4 sm:space-y-6 sm:py-6">
            <!-- Header -->
            <SearchResultsHeader
                :query="props.q"
                :genre="genreId"
                :total-results="searchStore.totalResults"
                :current-page="searchStore.currentPage"
                :total-pages="searchStore.totalPages"
            />

            <!-- Filters -->
            <div class="flex flex-col gap-4">
                <div class="w-full">
                    <MovieListFilters
                        v-model:search="currentFilters.search"
                        v-model:genre-filter="currentFilters.genre"
                        v-model:sort-by="currentFilters.sort"
                        v-model:view-mode="viewMode"
                        v-model:selection-mode="selectionMode"
                        :year="currentFilters.year"
                        :selected-count="selectedMovies.length"
                        :total-count="searchStore.results.length"
                        :available-genres="searchStore.genres"
                        :bulk-loading="bulkLoading"
                        :show-results-summary="true"
                        :results-summary-text="`${searchStore.totalResults} resultado${searchStore.totalResults !== 1 ? 's' : ''} encontrado${searchStore.totalResults !== 1 ? 's' : ''}`"
                        :show-search="true"
                        :show-genre="true"
                        :show-year="true"
                        :show-sort="true"
                        :show-search-sort="true"
                        :show-per-page="false"
                        :show-bulk-remove="false"
                        @select-all="selectAllMovies"
                        @clear-selection="clearSelection"
                        @bulk-mark-watched="handleBulkMarkWatched"
                        @bulk-move="handleBulkMove"
                        @filter-change="handleFilterChange"
                    />
                </div>
            </div>

            <!-- Results -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Loading State -->
                <div v-if="searchStore.loading && initialLoad" class="space-y-6">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                        <Card v-for="i in 12" :key="i" class="overflow-hidden">
                            <CardContent class="p-0">
                                <Skeleton class="aspect-[2/3] w-full" />
                                <div class="space-y-2 p-3">
                                    <Skeleton class="h-4 w-3/4" />
                                    <Skeleton class="h-3 w-1/2" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Results Grid -->
                <div v-else-if="searchStore.hasResults" class="space-y-6">
                    <!-- Grid View -->
                    <div
                        v-if="viewMode === 'grid'"
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6"
                    >
                        <MovieListCard
                            v-for="movie in searchStore.results"
                            :key="movie.id"
                            :movie="movie"
                            :show-rating="true"
                            :selection-mode="selectionMode"
                            :selected="selectedMovies.includes(movie.id)"
                            :view-mode="viewMode"
                            @click="(movie: any) => handleMovieClick(movie)"
                            @details="(movie: any) => handleMovieDetails(movie)"
                            @show-options="(movie: any) => handleMoreOptions(movie)"
                            @selection-change="() => toggleMovieSelection(movie.id)"
                        />
                    </div>

                    <!-- List View -->
                    <div v-else-if="viewMode === 'list'" class="space-y-3">
                        <MovieListCard
                            v-for="movie in searchStore.results"
                            :key="movie.id"
                            :movie="movie"
                            :show-rating="true"
                            :selection-mode="selectionMode"
                            :selected="selectedMovies.includes(movie.id)"
                            :view-mode="viewMode"
                            @click="(movie: any) => handleMovieClick(movie)"
                            @details="(movie: any) => handleMovieDetails(movie)"
                            @show-options="(movie: any) => handleMoreOptions(movie)"
                            @selection-change="() => toggleMovieSelection(movie.id)"
                        />
                    </div>

                    <!-- Pagination -->
                    <MovieListPagination
                        :current-page="searchStore.currentPage"
                        :total-pages="searchStore.totalPages"
                        :has-next="searchStore.hasNextPage"
                        :has-previous="searchStore.hasPreviousPage"
                        @page-change="handlePageChange"
                    />
                </div>

                <!-- No Results -->
                <div v-else-if="!searchStore.loading" class="py-12 text-center">
                    <div class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-muted">
                        <Search class="h-8 w-8 text-muted-foreground" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold">Nenhum resultado encontrado</h3>
                    <p class="mb-4 text-muted-foreground">
                        <span v-if="props.q">
                            Não encontramos filmes para "<strong>{{ props.q }}</strong
                            >".
                        </span>
                        <span v-else-if="genreId">
                            Não encontramos filmes do gênero "<strong>{{ searchStore.getGenreName(genreId) }}</strong
                            >".
                        </span>
                        <span v-else> Use a busca acima para encontrar filmes. </span>
                    </p>
                    <p class="text-sm text-muted-foreground">Tente:</p>
                    <ul class="mt-2 space-y-1 text-sm text-muted-foreground">
                        <li>• Verificar a ortografia</li>
                        <li>• Usar termos mais gerais</li>
                        <li>• Remover filtros</li>
                        <li>• Buscar por gênero</li>
                    </ul>
                </div>

                <!-- Error State -->
                <div v-if="searchStore.error" class="py-12 text-center">
                    <div class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-destructive/10">
                        <Filter class="h-8 w-8 text-destructive" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-destructive">Erro na busca</h3>
                    <p class="text-muted-foreground">
                        {{ searchStore.error }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Movie Options Modal -->
        <MovieOptionsModal :is-open="movieOptionsModalOpen" :movie="modalMovie" @update:open="movieOptionsModalOpen = $event" />

        <!-- Move To List Modal -->
        <MoveToListModal
            :is-open="moveToListModalOpen"
            :movie-count="selectedMoviesForMove.length"
            :available-lists="userListsStore.lists"
            :loading="bulkLoading"
            @update:open="moveToListModalOpen = $event"
            @select-list="handleMoveToListConfirm"
            @create-new-list="handleCreateNewList"
        />

        <!-- Create List Modal -->
        <CreateEditListModal :is-open="createListModalOpen" @update:open="createListModalOpen = $event" @success="handleListCreated" />
    </AppLayout>
</template>
