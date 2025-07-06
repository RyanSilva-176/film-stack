<script setup lang="ts">
import { computed } from 'vue';
import { useSearchStore } from '@/stores/search';
import { Search, Filter } from 'lucide-vue-next';

interface Props {
    query?: string;
    genre?: number;
    totalResults: number;
    currentPage: number;
    totalPages: number;
}

const props = withDefaults(defineProps<Props>(), {
    query: '',
    genre: undefined,
});

const searchStore = useSearchStore();

const title = computed(() => {
    if (props.query) {
        return `Resultados para "${props.query}"`;
    } else if (props.genre) {
        const genreName = searchStore.getGenreName(props.genre);
        return `Filmes de ${genreName}`;
    }
    return 'Buscar Filmes';
});

const subtitle = computed(() => {
    if (props.totalResults === 0) {
        return 'Nenhum resultado encontrado';
    } else if (props.totalResults === 1) {
        return '1 filme encontrado';
    } else {
        const start = (props.currentPage - 1) * 20 + 1;
        const end = Math.min(props.currentPage * 20, props.totalResults);
        return `${start}-${end} de ${props.totalResults.toLocaleString()} filmes`;
    }
});
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center gap-2">
            <Search v-if="query" class="h-5 w-5 text-muted-foreground" />
            <Filter v-else-if="genre" class="h-5 w-5 text-muted-foreground" />
            <h1 class="text-2xl font-bold tracking-tight">{{ title }}</h1>
        </div>
        <p class="text-muted-foreground">{{ subtitle }}</p>
    </div>
</template>
