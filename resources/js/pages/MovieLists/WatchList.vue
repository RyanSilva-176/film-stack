<template>
    <MovieListPage :page-config="watchListPageConfig" />
</template>

<script setup lang="ts">
import MovieListPage from '@/components/movie/MovieListPage.vue';

const watchListPageConfig = {
    title: 'Lista de Desejos',
    subtitle: 'Filmes que você quer assistir',
    listType: 'watchlist' as const,
    icon: ['fas', 'bookmark'],
    gradientFrom: 'from-blue-600/20',
    gradientTo: 'to-blue-500/20',
    borderColor: 'border-blue-500/30',
    iconBgColor: 'bg-blue-600',
    buttonBgColor: 'bg-blue-600',
    buttonHoverColor: 'hover:bg-blue-700',
    spinnerColor: 'border-blue-500',
    checkboxColor: 'text-blue-600',
    checkboxFocusColor: 'focus:ring-blue-500',
    loadingText: 'Carregando lista de desejos...',
    errorText: 'Erro ao carregar lista de desejos',
    emptyIcon: '📋',
    emptyTitle: 'Sua lista de desejos está vazia',
    emptyDescription: 'Adicione filmes que você deseja assistir para não esquecer!',
    showMarkWatchedButton: true,
};
</script>
