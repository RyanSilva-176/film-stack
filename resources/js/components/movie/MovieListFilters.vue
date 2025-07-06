<template>
    <div class="bg-gray-900/50 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-10">
        <div class="container mx-auto px-4 py-4">
            <!-- Search and Filters Row -->
            <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                <!-- Search Input -->
                <div v-if="showSearch" class="relative flex-1 max-w-md">
                    <FontAwesomeIcon 
                        icon="fa-solid fa-magnifying-glass" 
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" 
                    />
                    <input
                        v-model="localSearch"
                        type="text"
                        placeholder="Buscar filmes..."
                        class="w-full pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        @input="handleSearchDebounced"
                    />
                    <button
                        v-if="localSearch"
                        @click="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white"
                    >
                        <FontAwesomeIcon icon="fa-solid fa-times" class="h-4 w-4" />
                    </button>
                </div>

                <!-- Filters and Actions -->
                <div class="flex flex-wrap gap-2 items-center">
                    <!-- Genre Filter -->
                    <select
                        v-if="showGenre"
                        v-model="localGenreFilter"
                        @change="handleFilterChange"
                        class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                        <option value="">Todos os gêneros</option>
                        <option v-for="genre in (genres || availableGenres)" :key="genre.id" :value="genre.id">
                            {{ genre.name }}
                        </option>
                    </select>

                    <!-- Year Filter -->
                    <select
                        v-if="showYear"
                        v-model="localYear"
                        @change="handleFilterChange"
                        class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                        <option value="">Todos os anos</option>
                        <option v-for="year in yearOptions" :key="year" :value="year">
                            {{ year }}
                        </option>
                    </select>

                    <!-- Sort Options -->
                    <select
                        v-if="showSort"
                        v-model="localSortBy"
                        @change="handleFilterChange"
                        class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                        <option value="popularity.desc">Mais Populares</option>
                        <option value="popularity.asc">Menos Populares</option>
                        <option value="vote_average.desc">Melhor Avaliados</option>
                        <option value="vote_average.asc">Pior Avaliados</option>
                        <option value="release_date.desc">Mais Recentes</option>
                        <option value="release_date.asc">Mais Antigos</option>
                        <option value="original_title.asc">A-Z</option>
                        <option value="original_title.desc">Z-A</option>
                        <option value="added_date_desc">Adicionado recentemente</option>
                        <option value="added_date_asc">Adicionado há mais tempo</option>
                        <option value="title_asc">Título (A-Z)</option>
                        <option value="title_desc">Título (Z-A)</option>
                        <option v-if="showWatchedSort" value="watched_date_desc">Assistido recentemente</option>
                        <option v-if="showWatchedSort" value="watched_date_asc">Assistido há mais tempo</option>
                    </select>

                    <!-- View Toggle -->
                    <div class="flex rounded-lg overflow-hidden border border-gray-700">
                        <button
                            @click="handleViewChange('grid')"
                            :class="[
                                'px-3 py-2 text-sm transition-colors',
                                localViewMode === 'grid'
                                    ? 'bg-red-600 text-white'
                                    : 'bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700'
                            ]"
                        >
                            <FontAwesomeIcon icon="fa-solid fa-th" />
                        </button>
                        <button
                            @click="handleViewChange('list')"
                            :class="[
                                'px-3 py-2 text-sm transition-colors',
                                localViewMode === 'list'
                                    ? 'bg-red-600 text-white'
                                    : 'bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700'
                            ]"
                        >
                            <FontAwesomeIcon icon="fa-solid fa-list" />
                        </button>
                    </div>

                    <!-- Selection Toggle -->
                    <button
                        @click="toggleSelectionMode"
                        :class="[
                            'px-3 py-2 rounded-lg text-sm transition-colors border',
                            selectionMode
                                ? 'bg-red-600 text-white border-red-600'
                                : 'bg-gray-800 text-gray-400 border-gray-700 hover:text-white hover:bg-gray-700'
                        ]"
                    >
                        <FontAwesomeIcon icon="fa-solid fa-check-square" class="mr-1" />
                        {{ selectionMode ? 'Cancelar' : 'Selecionar' }}
                    </button>
                </div>
            </div>

            <!-- Selection Actions -->
            <div v-if="selectionMode" class="mt-4 p-3 bg-gray-800 rounded-lg">
                <div class="flex flex-wrap gap-2 items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button
                            @click="selectAll"
                            class="text-sm text-blue-400 hover:text-blue-300"
                        >
                            Selecionar todos ({{ totalCount }})
                        </button>
                        <button
                            @click="clearSelection"
                            class="text-sm text-gray-400 hover:text-gray-300"
                        >
                            Limpar seleção
                        </button>
                        <span class="text-sm text-gray-400">
                            {{ selectedCount }} de {{ totalCount }} selecionados
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="!isWatchedList"
                            @click="handleBulkMarkWatched"
                            :disabled="selectedCount === 0 || bulkLoading"
                            class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <FontAwesomeIcon icon="check" class="mr-1" />
                            Marcar como assistido
                        </button>
                        
                        <button
                            @click="handleBulkMove"
                            :disabled="selectedCount === 0 || bulkLoading"
                            class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <FontAwesomeIcon icon="exchange-alt" class="mr-1" />
                            Mover para lista
                        </button>
                        
                        <button
                            @click="handleBulkRemove"
                            :disabled="selectedCount === 0 || bulkLoading"
                            class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <FontAwesomeIcon icon="trash" class="mr-1" />
                            Remover da lista
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results Summary -->
            <div v-if="showResultsSummary" class="mt-3 text-sm text-gray-400">
                {{ resultsSummaryText }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { ref, watch, computed } from 'vue';

// Simple debounce implementation
function debounce(func: (...args: any[]) => void, wait: number) {
    let timeout: ReturnType<typeof setTimeout>;
    return (...args: any[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

interface Genre {
    id: number;
    name: string;
}

interface Props {
    currentFilters?: any;
    genres?: Genre[];
    showSearch?: boolean;
    showGenre?: boolean;
    showYear?: boolean;
    showSort?: boolean;
    search?: string;
    genreFilter?: string;
    sortBy?: string;
    viewMode?: 'grid' | 'list';
    selectionMode?: boolean;
    selectedCount?: number;
    totalCount?: number;
    availableGenres?: Genre[];
    showWatchedSort?: boolean;
    isWatchedList?: boolean;
    bulkLoading?: boolean;
    showResultsSummary?: boolean;
    resultsSummaryText?: string;
}

interface Emits {
    (e: 'filter-change', filters: any): void;
    (e: 'update:search', value: string): void;
    (e: 'update:genreFilter', value: string): void;
    (e: 'update:sortBy', value: string): void;
    (e: 'update:viewMode', value: 'grid' | 'list'): void;
    (e: 'update:selectionMode', value: boolean): void;
    (e: 'select-all'): void;
    (e: 'clear-selection'): void;
    (e: 'bulk-mark-watched'): void;
    (e: 'bulk-move'): void;
    (e: 'bulk-remove'): void;
    (e: 'filters-changed'): void;
}

const props = withDefaults(defineProps<Props>(), {
    currentFilters: () => ({}),
    genres: () => [],
    showSearch: true,
    showGenre: true,
    showYear: false,
    showSort: true,
    search: '',
    genreFilter: '',
    sortBy: 'added_date_desc',
    viewMode: 'grid',
    selectionMode: false,
    selectedCount: 0,
    totalCount: 0,
    availableGenres: () => [],
    showWatchedSort: false,
    isWatchedList: false,
    bulkLoading: false,
    showResultsSummary: false,
    resultsSummaryText: '',
});

const emit = defineEmits<Emits>();

const localSearch = ref(props.currentFilters?.search || props.search || '');
const localGenreFilter = ref(props.currentFilters?.genre || props.genreFilter || '');
const localSortBy = ref(props.currentFilters?.sort || props.sortBy || 'popularity.desc');
const localViewMode = ref(props.viewMode);
const localYear = ref(props.currentFilters?.year || '');

// Generate year options
const yearOptions = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let year = currentYear + 1; year >= 1950; year--) {
        years.push(year);
    }
    return years;
});

// Watch for external changes
watch(() => props.search, (newValue) => {
    localSearch.value = newValue;
});

watch(() => props.genreFilter, (newValue) => {
    localGenreFilter.value = newValue;
});

watch(() => props.sortBy, (newValue) => {
    localSortBy.value = newValue;
});

watch(() => props.viewMode, (newValue) => {
    localViewMode.value = newValue;
});

const handleSearchDebounced = debounce((event: Event) => {
    const target = event.target as HTMLInputElement;
    localSearch.value = target.value;
    emitFilterChange();
}, 300);

const clearSearch = () => {
    localSearch.value = '';
    emitFilterChange();
};

const handleFilterChange = () => {
    emitFilterChange();
};

const emitFilterChange = () => {
    const filters = {
        search: localSearch.value,
        genre: localGenreFilter.value,
        year: localYear.value,
        sort: localSortBy.value,
    };
    
    emit('filter-change', filters);
    emit('update:search', localSearch.value);
    emit('update:genreFilter', localGenreFilter.value);
    emit('update:sortBy', localSortBy.value);
    emit('filters-changed');
};

const handleViewChange = (mode: 'grid' | 'list') => {
    localViewMode.value = mode;
    emit('update:viewMode', mode);
};

const toggleSelectionMode = () => {
    emit('update:selectionMode', !props.selectionMode);
};

const selectAll = () => {
    emit('select-all');
};

const clearSelection = () => {
    emit('clear-selection');
};

const handleBulkMarkWatched = () => {
    emit('bulk-mark-watched');
};

const handleBulkMove = () => {
    emit('bulk-move');
};

const handleBulkRemove = () => {
    emit('bulk-remove');
};
</script>
