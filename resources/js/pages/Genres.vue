<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useSearchStore } from '@/stores/search';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { Film, Search } from 'lucide-vue-next';

const searchStore = useSearchStore();

const breadcrumbs = computed(() => [
    { title: 'Início', href: '/dashboard' },
    { title: 'Gêneros', href: '/genres' },
]);

const popularGenres = computed(() => {
    const popularIds = [28, 12, 16, 35, 80, 18, 27, 878, 9648, 10749];
    return searchStore.genres.filter(genre => popularIds.includes(genre.id));
});

const otherGenres = computed(() => {
    const popularIds = [28, 12, 16, 35, 80, 18, 27, 878, 9648, 10749];
    return searchStore.genres.filter(genre => !popularIds.includes(genre.id));
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
        <div class="container mx-auto px-4 py-6 space-y-8">
            <!-- Header -->
            <div class="text-center space-y-4">
                <div class="flex items-center justify-center gap-2">
                    <Film class="h-8 w-8 text-primary" />
                    <h1 class="text-3xl font-bold tracking-tight">Explorar por Gêneros</h1>
                </div>
                <p class="text-muted-foreground max-w-2xl mx-auto">
                    Descubra filmes organizados por gênero. Encontre exatamente o tipo de filme que você está procurando.
                </p>
            </div>

            <!-- Loading State -->
            <div v-if="searchStore.genresLoading" class="space-y-6">
                <div>
                    <Skeleton class="h-6 w-48 mb-4" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <Card v-for="i in 8" :key="i" class="overflow-hidden">
                            <CardContent class="p-6">
                                <Skeleton class="h-12 w-12 rounded-full mb-4" />
                                <Skeleton class="h-5 w-24 mb-2" />
                                <Skeleton class="h-3 w-full mb-1" />
                                <Skeleton class="h-3 w-3/4" />
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            <!-- Genres Content -->
            <div v-else class="space-y-8">
                <!-- Popular Genres -->
                <div v-if="popularGenres.length > 0">
                    <div class="flex items-center gap-2 mb-6">
                        <h2 class="text-2xl font-semibold">Gêneros Populares</h2>
                        <span class="text-sm text-muted-foreground">({{ popularGenres.length }})</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <Card
                            v-for="genre in popularGenres"
                            :key="genre.id"
                            class="group cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-[1.02] border-2 hover:border-primary/20"
                            @click="handleGenreClick(genre.id)"
                        >
                            <CardContent class="p-6">
                                <div class="flex items-start gap-4">
                                    <div class="text-3xl">{{ getGenreIcon(genre.id) }}</div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-lg mb-2 group-hover:text-primary transition-colors">
                                            {{ genre.name }}
                                        </h3>
                                        <p class="text-sm text-muted-foreground line-clamp-2">
                                            {{ genreDescriptions[genre.id] || 'Explore filmes incríveis deste gênero' }}
                                        </p>
                                        <div class="mt-4">
                                            <Button variant="outline" size="sm" class="group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                                                <Search class="h-3 w-3 mr-2" />
                                                Explorar
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Other Genres -->
                <div v-if="otherGenres.length > 0">
                    <div class="flex items-center gap-2 mb-6">
                        <h2 class="text-2xl font-semibold">Outros Gêneros</h2>
                        <span class="text-sm text-muted-foreground">({{ otherGenres.length }})</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
                        <Card
                            v-for="genre in otherGenres"
                            :key="genre.id"
                            class="group cursor-pointer transition-all duration-200 hover:shadow-md hover:scale-[1.02] hover:border-primary/20"
                            @click="handleGenreClick(genre.id)"
                        >
                            <CardContent class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="text-xl">{{ getGenreIcon(genre.id) }}</div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-medium group-hover:text-primary transition-colors truncate">
                                            {{ genre.name }}
                                        </h3>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!searchStore.genresLoading && searchStore.genres.length === 0" class="text-center py-12">
                    <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-muted flex items-center justify-center">
                        <Film class="w-8 h-8 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Nenhum gênero disponível</h3>
                    <p class="text-muted-foreground">
                        Não foi possível carregar os gêneros no momento.
                    </p>
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
