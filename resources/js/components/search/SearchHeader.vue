<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { useSearchStore } from '@/stores/search';
import { router } from '@inertiajs/vue3';
import { Filter, Search, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const searchStore = useSearchStore();

const searchQuery = ref('');
const showPreview = ref(false);
const showFilters = ref(false);
const searchTimeout = ref<number | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);

const selectedGenre = ref<string>('');
const selectedYear = ref<string>('');
const selectedSort = ref('popularity.desc');

const hasQuery = computed(() => searchQuery.value.trim().length > 0);
const hasFilters = computed(() => selectedGenre.value || selectedYear.value || selectedSort.value !== 'popularity.desc');

watch(searchQuery, (newQuery) => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    if (newQuery.trim().length >= 2) {
        searchTimeout.value = setTimeout(() => {
            searchStore.searchPreview(newQuery);
            showPreview.value = true;
        }, 300);
    } else {
        searchStore.clearPreview();
        showPreview.value = false;
    }
});

const handleSearch = () => {
    if (!searchQuery.value.trim()) return;

    const filters: any = {};

    if (selectedGenre.value) {
        filters.genreId = parseInt(selectedGenre.value);
    }

    if (selectedYear.value) {
        filters.year = parseInt(selectedYear.value);
    }

    if (selectedSort.value !== 'popularity.desc') {
        filters.sortBy = selectedSort.value;
    }

    router.visit('/search', {
        method: 'get',
        data: {
            q: searchQuery.value.trim(),
            ...filters,
        },
    });

    hidePreview();
};

const handlePreviewItemClick = (movieId: number) => {
    router.visit(`/movies/${movieId}`);
    hidePreview();
};

const handleGenreSearch = (genreId: number) => {
    router.visit('/search', {
        method: 'get',
        data: {
            genre: genreId,
            sort: selectedSort.value,
        },
    });

    hidePreview();
};

const clearSearch = () => {
    searchQuery.value = '';
    selectedGenre.value = '';
    selectedYear.value = '';
    selectedSort.value = 'popularity.desc';
    hidePreview();
};

const hidePreview = () => {
    showPreview.value = false;
    try {
        if (searchInputRef.value) {
            searchInputRef.value.blur();
        }
    } catch {}
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        handleSearch();
    } else if (event.key === 'Escape') {
        event.preventDefault();
        hidePreview();
    }
};

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

const yearOptions = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let year = currentYear + 1; year >= 1950; year--) {
        years.push(year);
    }
    return years;
});

const sortOptions = [
    { value: 'popularity.desc', label: 'Mais Populares' },
    { value: 'popularity.asc', label: 'Menos Populares' },
    { value: 'vote_average.desc', label: 'Melhor Avaliados' },
    { value: 'vote_average.asc', label: 'Pior Avaliados' },
    { value: 'release_date.desc', label: 'Mais Recentes' },
    { value: 'release_date.asc', label: 'Mais Antigos' },
    { value: 'original_title.asc', label: 'A-Z' },
    { value: 'original_title.desc', label: 'Z-A' },
];

const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('[data-search-container]')) {
        hidePreview();
        showFilters.value = false;
    }
};

onMounted(async () => {
    await searchStore.init();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative max-w-md flex-1" data-search-container>
        <!-- Search Input -->
        <div class="relative">
            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

            <input
                ref="searchInputRef"
                v-model="searchQuery"
                type="text"
                placeholder="Buscar filmes..."
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 pr-20 pl-10 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                @keydown="handleKeydown"
                @focus="showPreview = hasQuery && searchStore.hasPreviewResults"
            />

            <!-- Action Buttons -->
            <div class="absolute top-1/2 right-2 flex -translate-y-1/2 items-center gap-1">
                <!-- Filters Button -->
                <Button variant="ghost" size="sm" class="h-6 w-6 p-0" :class="{ 'text-primary': hasFilters }" @click="toggleFilters">
                    <Filter class="h-3 w-3" />
                </Button>

                <!-- Clear Button -->
                <Button v-if="hasQuery || hasFilters" variant="ghost" size="sm" class="h-6 w-6 p-0" @click="clearSearch">
                    <X class="h-3 w-3" />
                </Button>
            </div>
        </div>

        <!-- Filters Panel -->
        <Card v-if="showFilters" class="absolute top-full right-0 left-0 z-50 mt-1 border shadow-lg">
            <CardContent class="space-y-4 p-4">
                <div>
                    <label class="mb-2 block text-sm font-medium">Gênero</label>
                    <select v-model="selectedGenre" class="w-full rounded-md border bg-background p-2">
                        <option value="">Todos os gêneros</option>
                        <option v-for="genre in searchStore.genres" :key="genre.id" :value="genre.id.toString()">
                            {{ genre.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Ano</label>
                    <select v-model="selectedYear" class="w-full rounded-md border bg-background p-2">
                        <option value="">Todos os anos</option>
                        <option v-for="year in yearOptions.slice(0, 20)" :key="year" :value="year.toString()">
                            {{ year }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Ordenar por</label>
                    <select v-model="selectedSort" class="w-full rounded-md border bg-background p-2">
                        <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </CardContent>
        </Card>

        <!-- Search Preview -->
        <Card
            v-if="showPreview && (searchStore.hasPreviewResults || searchStore.previewLoading)"
            class="absolute top-full right-0 left-0 z-50 mt-1 max-h-96 overflow-y-auto border shadow-lg"
        >
            <CardContent class="p-0">
                <!-- Loading -->
                <div v-if="searchStore.previewLoading" class="space-y-3 p-4">
                    <div v-for="i in 3" :key="i" class="flex items-center gap-3">
                        <Skeleton class="h-16 w-12 rounded" />
                        <div class="flex-1 space-y-2">
                            <Skeleton class="h-4 w-3/4" />
                            <Skeleton class="h-3 w-1/2" />
                        </div>
                    </div>
                </div>

                <!-- Results -->
                <div v-else-if="searchStore.hasPreviewResults" class="py-2">
                    <div
                        v-for="movie in searchStore.previewResults"
                        :key="movie.id"
                        class="flex cursor-pointer items-center gap-3 p-3 transition-colors hover:bg-muted/50"
                        @click="handlePreviewItemClick(movie.id)"
                    >
                        <img
                            v-if="movie.poster_url || movie.poster_path"
                            :src="movie.poster_url || movie.poster_path || undefined"
                            :alt="movie.title"
                            class="h-16 w-12 rounded object-cover"
                        />
                        <div v-else class="flex h-16 w-12 items-center justify-center rounded bg-muted">
                            <Search class="h-4 w-4 text-muted-foreground" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="truncate text-sm font-medium">{{ movie.title }}</h4>
                            <p class="text-xs text-muted-foreground">
                                {{ new Date(movie.release_date).getFullYear() || 'N/A' }}
                                <span v-if="movie.vote_average" class="ml-2"> ★ {{ movie.vote_average.toFixed(1) }} </span>
                            </p>
                        </div>
                    </div>

                    <!-- Search All Results -->
                    <div class="border-t p-3">
                        <Button variant="ghost" size="sm" class="w-full justify-start" @click="handleSearch">
                            <Search class="mr-2 h-4 w-4" />
                            Ver todos os resultados para "{{ searchQuery }}"
                        </Button>
                    </div>
                </div>

                <!-- No Results -->
                <div v-else class="p-4 text-center text-sm text-muted-foreground">Nenhum resultado encontrado</div>
            </CardContent>
        </Card>

        <!-- Quick Genre Access -->
        <Card
            v-if="showPreview && !hasQuery && !searchStore.previewLoading && searchStore.genres.length > 0"
            class="absolute top-full right-0 left-0 z-50 mt-1 border shadow-lg"
        >
            <CardContent class="p-4">
                <h4 class="mb-3 text-sm font-medium">Buscar por gênero</h4>
                <div class="grid grid-cols-2 gap-2">
                    <Button
                        v-for="genre in searchStore.genres.slice(0, 8)"
                        :key="genre.id"
                        variant="outline"
                        size="sm"
                        class="justify-start text-xs"
                        @click="handleGenreSearch(genre.id)"
                    >
                        {{ genre.name }}
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
