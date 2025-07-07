<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useSearchStore } from '@/stores/search';
import { Head, router } from '@inertiajs/vue3';
import { Film, Search } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';

const searchStore = useSearchStore();

const breadcrumbs = computed(() => [
    { title: 'Início', href: '/dashboard' },
    { title: 'Gêneros', href: '/genres' },
]);

const popularGenres = computed(() => {
    const popularIds = [28, 12, 16, 35, 80, 18, 27, 878, 9648, 10749];
    return searchStore.genres.filter((genre) => popularIds.includes(genre.id));
});

const otherGenres = computed(() => {
    const popularIds = [28, 12, 16, 35, 80, 18, 27, 878, 9648, 10749];
    return searchStore.genres.filter((genre) => !popularIds.includes(genre.id));
});

const genreDescriptions: Record<number, string> = {
    28: 'Filmes repletos de adrenalina, perseguições e batalhas épicas',
    12: 'Jornadas emocionantes em mundos desconhecidos',
    16: 'Histórias cativantes para todas as idades',
    35: 'Momentos hilários e diversão garantida',
    80: 'Mistérios obscuros e investigações intrigantes',
    18: 'Narrativas profundas e emotivas',
    27: 'Sustos e terror para os corajosos',
    878: 'O futuro e tecnologia em histórias fascinantes',
    9648: 'Enigmas e suspense de tirar o fôlego',
    10749: 'Histórias de amor que tocam o coração',
    14: 'Mundos mágicos e criaturas fantásticas',
    36: 'Fatos reais contados de forma envolvente',
    10752: 'Conflitos históricos e heroísmo',
    10402: 'Melodias e performances inesquecíveis',
    37: 'O velho oeste americano em sua glória',
    53: 'Tensão e reviravoltas constantes',
    10770: 'Produções especiais para televisão',
    99: 'A realidade capturada em sua essência',
};

const handleGenreClick = (genreId: number) => {
    router.visit('/search', {
        method: 'get',
        data: { genre: genreId },
    });
};

const getGenreIcon = (genreId: number) => {
    const icons: Record<number, string> = {
        28: '⚔️', // Action
        12: '🗺️', // Adventure
        16: '🎨', // Animation
        35: '😂', // Comedy
        80: '🔍', // Crime
        18: '🎭', // Drama
        27: '👻', // Horror
        878: '🚀', // Science Fiction
        9648: '🕵️', // Mystery
        10749: '💕', // Romance
        14: '✨', // Fantasy
        36: '📚', // History
        10752: '⚡', // War
        10402: '🎵', // Music
        37: '🤠', // Western
        53: '😰', // Thriller
    };
    return icons[genreId] || '🎬';
};

onMounted(async () => {
    await searchStore.init();
});
</script>

<template>
    <Head title="Gêneros" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto space-y-6 px-4 py-6">
            <!-- Header -->
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-red-600 p-3">
                        <Film class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-sidebar-foreground md:text-3xl">Explorar por Gêneros</h1>
                        <p class="text-sidebar-accent-foreground">Descubra filmes organizados por gênero</p>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="searchStore.genresLoading" class="space-y-6">
                <div>
                    <div class="mb-4 h-6 w-48 animate-pulse rounded bg-sidebar-accent"></div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div v-for="i in 8" :key="i" class="overflow-hidden rounded-lg border border-sidebar-border bg-sidebar">
                            <div class="p-6">
                                <div class="mb-4 h-12 w-12 animate-pulse rounded-full bg-sidebar-accent"></div>
                                <div class="mb-2 h-5 w-24 animate-pulse rounded bg-sidebar-accent"></div>
                                <div class="mb-1 h-3 w-full animate-pulse rounded bg-sidebar-accent"></div>
                                <div class="h-3 w-3/4 animate-pulse rounded bg-sidebar-accent"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Genres Content -->
            <div v-else class="space-y-8">
                <!-- Popular Genres -->
                <div v-if="popularGenres.length > 0">
                    <div class="mb-6 flex items-center gap-2">
                        <h2 class="text-xl font-semibold text-sidebar-foreground">Gêneros Populares</h2>
                        <span class="text-sm text-sidebar-accent-foreground">({{ popularGenres.length }})</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="genre in popularGenres"
                            :key="genre.id"
                            class="group cursor-pointer overflow-hidden rounded-xl border border-sidebar-border bg-sidebar transition-all duration-200 hover:scale-[1.02] hover:border-red-600/50 hover:shadow-lg hover:shadow-red-600/20"
                            @click="handleGenreClick(genre.id)"
                        >
                            <div class="p-6">
                                <div class="flex items-start gap-4">
                                    <div class="text-3xl">{{ getGenreIcon(genre.id) }}</div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="mb-2 text-lg font-semibold text-sidebar-foreground transition-colors group-hover:text-red-400">
                                            {{ genre.name }}
                                        </h3>
                                        <p class="line-clamp-2 text-sm text-sidebar-accent-foreground">
                                            {{ genreDescriptions[genre.id] || 'Explore filmes incríveis deste gênero' }}
                                        </p>
                                        <div class="mt-4">
                                            <button
                                                class="flex items-center gap-2 rounded-lg border border-sidebar-border bg-sidebar-accent px-3 py-1.5 text-sm text-sidebar-accent-foreground transition-colors hover:border-red-600 hover:bg-red-600 hover:text-white"
                                            >
                                                <Search class="h-3 w-3" />
                                                Explorar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other Genres -->
                <div v-if="otherGenres.length > 0">
                    <div class="mb-6 flex items-center gap-2">
                        <h2 class="text-xl font-semibold text-sidebar-foreground">Outros Gêneros</h2>
                        <span class="text-sm text-sidebar-accent-foreground">({{ otherGenres.length }})</span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                        <div
                            v-for="genre in otherGenres"
                            :key="genre.id"
                            class="group cursor-pointer overflow-hidden rounded-lg border border-sidebar-border bg-sidebar transition-all duration-200 hover:scale-[1.02] hover:border-red-600/50 hover:shadow-md hover:shadow-red-600/20"
                            @click="handleGenreClick(genre.id)"
                        >
                            <div class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="text-xl">{{ getGenreIcon(genre.id) }}</div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="truncate font-medium text-sidebar-foreground transition-colors group-hover:text-red-400">
                                            {{ genre.name }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!searchStore.genresLoading && searchStore.genres.length === 0" class="py-12 text-center">
                    <div class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-sidebar-accent">
                        <Film class="h-8 w-8 text-sidebar-accent-foreground" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-sidebar-foreground">Nenhum gênero disponível</h3>
                    <p class="text-sidebar-accent-foreground">Não foi possível carregar os gêneros no momento.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
