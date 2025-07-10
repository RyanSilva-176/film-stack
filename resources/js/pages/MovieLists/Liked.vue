<template>
    <MovieListPage :page-config="likedPageConfig" />
</template>

<script setup lang="ts">
import MovieListPage from '@/components/movie/MovieListPage.vue';

const likedPageConfig = {
    title: 'Filmes Curtidos',
    subtitle: 'Seus filmes favoritos em um só lugar',
    listType: 'liked' as const,
    icon: ['fas', 'heart'],
    gradientFrom: 'from-red-600/20',
    gradientTo: 'to-red-500/20',
    borderColor: 'border-red-500/30',
    iconBgColor: 'bg-red-600',
    buttonBgColor: 'bg-red-600',
    buttonHoverColor: 'hover:bg-red-700',
    spinnerColor: 'border-red-500',
    checkboxColor: 'text-red-600',
    checkboxFocusColor: 'focus:ring-red-500',
    loadingText: 'Carregando filmes curtidos...',
    errorText: 'Erro ao carregar filmes curtidos',
    emptyIcon: '💔',
    emptyTitle: 'Nenhum filme curtido ainda',
    emptyDescription: 'Explore filmes na dashboard e adicione seus favoritos à sua lista de curtidos!',
    showMarkWatchedButton: true,
};
</script>
