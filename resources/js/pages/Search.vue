<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useSearchStore } from '@/stores/search';
import { useMovieDetailsStore } from '@/stores/movieDetails';
import { useUserListsStore } from '@/stores/userLists';
import MovieCardWithTags from '@/components/movie/MovieCardWithTags.vue';
import MovieListFilters from '@/components/movie/MovieListFilters.vue';
import SearchResultsHeader from '@/components/search/SearchResultsHeader.vue';
import MovieListPagination from '@/components/movie/MovieListPagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { Search, Filter } from 'lucide-vue-next';

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

const initialLoad = ref(true);

const genreId = computed(() => props.genre ? Number(props.genre) : undefined);
const yearValue = computed(() => props.year ? Number(props.year) : undefined);
const pageValue = computed(() => props.page ? Number(props.page) : 1);

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

const currentFilters = computed(() => ({
    search: props.q || '',
    genre: genreId.value || '',
    year: yearValue.value || '',
    sort: props.sort || 'popularity.desc',
}));

const performSearch = async () => {
    if (props.q) {
        const filters: any = {};
        if (yearValue.value) filters.year = yearValue.value;
        if (props.sort !== 'popularity.desc') filters.sortBy = props.sort;
        
        await searchStore.searchMovies(props.q, pageValue.value, filters);
    } else if (genreId.value) {
        const filters: any = {};
        if (yearValue.value) filters.year = yearValue.value;
        if (props.sort !== 'popularity.desc') filters.sortBy = props.sort;
        
        await searchStore.searchByGenre(genreId.value, pageValue.value, filters);
    }
};

const handleFilterChange = (filters: any) => {
    const params: any = {
        page: 1,
    };

    if (props.q) {
        params.q = props.q;
    }

    if (filters.search && filters.search !== props.q) {
        params.q = filters.search;
        delete params.genre;
    }

    if (filters.genre && !filters.search) {
        params.genre = filters.genre;
        delete params.q;
    }

    if (filters.year) {
        params.year = filters.year;
    }

    if (filters.sort && filters.sort !== 'popularity.desc') {
        params.sort = filters.sort;
    }

    router.visit('/search', {
        method: 'get',
        data: params,
        preserveState: true,
        preserveScroll: true,
    });
};

const handleMovieClick = (movie: any) => {
    movieDetailsStore.openSidebar(movie);
};

const handleMovieDetails = (movie: any) => {
    movieDetailsStore.openSidebar(movie);
};

const handleAddToList = (movie: any) => {
    userListsStore.toggleLike(movie.id);
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

watch(
    () => [props.q, props.genre, props.year, props.sort, props.page],
    async () => {
        await performSearch();
    },
    { immediate: false }
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
        <div class="container mx-auto px-4 py-4 sm:py-6 space-y-4 sm:space-y-6">
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
                        :current-filters="currentFilters"
                        :genres="searchStore.genres"
                        :show-search="true"
                        :show-genre="true"
                        :show-year="true"
                        :show-sort="true"
                        @filter-change="handleFilterChange"
                    />
                </div>
            </div>

            <!-- Results -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Loading State -->
                <div v-if="searchStore.loading && initialLoad" class="space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-4">
                        <Card v-for="i in 12" :key="i" class="overflow-hidden">
                            <CardContent class="p-0">
                                <Skeleton class="aspect-[2/3] w-full" />
                                <div class="p-3 space-y-2">
                                    <Skeleton class="h-4 w-3/4" />
                                    <Skeleton class="h-3 w-1/2" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Results Grid -->
                <div v-else-if="searchStore.hasResults" class="space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-4">
                        <MovieCardWithTags
                            v-for="movie in searchStore.results"
                            :key="movie.id"
                            :movie="movie"
                            :show-rating="true"
                            :show-details="true"
                            @click="(movie: any) => handleMovieClick(movie)"
                            @details="(movie: any) => handleMovieDetails(movie)"
                            @add-to-list="(movie: any) => handleAddToList(movie)"
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
                <div v-else-if="!searchStore.loading" class="text-center py-12">
                    <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-muted flex items-center justify-center">
                        <Search class="w-8 h-8 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Nenhum resultado encontrado</h3>
                    <p class="text-muted-foreground mb-4">
                        <span v-if="props.q">
                            Não encontramos filmes para "<strong>{{ props.q }}</strong>".
                        </span>
                        <span v-else-if="genreId">
                            Não encontramos filmes do gênero "<strong>{{ searchStore.getGenreName(genreId) }}</strong>".
                        </span>
                        <span v-else>
                            Use a busca acima para encontrar filmes.
                        </span>
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Tente:
                    </p>
                    <ul class="text-sm text-muted-foreground mt-2 space-y-1">
                        <li>• Verificar a ortografia</li>
                        <li>• Usar termos mais gerais</li>
                        <li>• Remover filtros</li>
                        <li>• Buscar por gênero</li>
                    </ul>
                </div>

                <!-- Error State -->
                <div v-if="searchStore.error" class="text-center py-12">
                    <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-destructive/10 flex items-center justify-center">
                        <Filter class="w-8 h-8 text-destructive" />
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-destructive">Erro na busca</h3>
                    <p class="text-muted-foreground">
                        {{ searchStore.error }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
