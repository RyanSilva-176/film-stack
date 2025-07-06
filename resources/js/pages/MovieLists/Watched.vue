<template>
  <MovieListPage :page-config="watchedPageConfig" />
</template>

<script setup lang="ts">
import MovieListPage from '@/components/movie/MovieListPage.vue';

const watchedPageConfig = {
  title: 'Filmes Assistidos',
  subtitle: 'Sua história de filmes assistidos',
  listType: 'watched' as const,
  icon: ['fas', 'eye'],
  gradientFrom: 'from-green-600/20',
  gradientTo: 'to-green-500/20',
  borderColor: 'border-green-500/30',
  iconBgColor: 'bg-green-600',
  buttonBgColor: 'bg-green-600',
  buttonHoverColor: 'hover:bg-green-700',
  spinnerColor: 'border-green-500',
  checkboxColor: 'text-green-600',
  checkboxFocusColor: 'focus:ring-green-500',
  loadingText: 'Carregando filmes assistidos...',
  errorText: 'Erro ao carregar filmes assistidos',
  emptyIcon: '🎬',
  emptyTitle: 'Nenhum filme assistido ainda',
  emptyDescription: 'Marque os filmes que você assistiu para manter um histórico!',
  showMarkWatchedButton: false,
};
</script>
