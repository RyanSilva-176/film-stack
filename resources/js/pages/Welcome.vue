<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, computed, ref } from 'vue';
import { useMoviesStore } from '@/stores/movies';
import { useToast } from '@/composables/useToastSystem';
import HeroSection from '@/components/HeroSection.vue';
import HeroSkeleton from '@/components/ui/HeroSkeleton.vue';
import MovieCarousel from '@/components/movie/MovieCarousel.vue';
import MobileMenu from '@/components/layout/MobileMenu.vue';
import Footer from '@/components/layout/Footer.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import Button from '@/components/ui/Button.vue';
import type { Movie } from '@/types/movies';

const moviesStore = useMoviesStore();
const { success, error, info } = useToast();

const isLoading = ref(true);
const isMobileMenuOpen = ref(false);

const featuredMovie = computed(() => {
  const trending = moviesStore.trendingWithImages;
  return trending.length > 0 ? trending[0] : null;
});

const trendingMovies = computed(() => moviesStore.trendingWithImages);
const popularMovies = computed(() => moviesStore.popularWithImages);

const handleMovieClick = (movie: Movie) => {
  success('Filme selecionado', `Você clicou em: ${movie.title}`);
  //TODO: Implementar navegação para detalhes do filme
};

const handleAddToList = (movie: Movie) => {
  success('Adicionado à lista', `${movie.title} foi adicionado à sua lista`);
  // TODO: Implementar adição à lista do usuário
};

const handleMovieDetails = (movie: Movie) => {
  info('Detalhes do filme', `Exibindo detalhes de: ${movie.title}`);
  // TODO: Implementar navegação para página de detalhes
};

const handleMoreOptions = (movie: Movie) => {
  info('Mais opções', `Opções disponíveis para: ${movie.title}`);
  // TODO: Implementar menu de opções (compartilhar, avaliar, etc.)
};

const handleExploreMovies = () => {
  const trendingSection = document.getElementById('trending-section');
  if (trendingSection) {
    trendingSection.scrollIntoView({ behavior: 'smooth' });
  }
};

const handleScrollDown = () => {
  const trendingSection = document.getElementById('trending-section');
  if (trendingSection) {
    trendingSection.scrollIntoView({ behavior: 'smooth' });
  }
};

const handleScrollToPopular = () => {
  const popularSection = document.getElementById('popular-section');
  if (popularSection) {
    popularSection.scrollIntoView({ behavior: 'smooth' });
  }
};

const handleFooterLinkClick = (section: string) => {
  console.log(`Footer link clicked: ${section}`);
  // TODO: Implementar navegação para seções do rodapé
};

onMounted(async () => {
  try {
    await moviesStore.initializeData();
  } catch {
    error('Erro ao carregar dados', 'Não foi possível carregar os filmes. Tente novamente.');
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <Head title="Film Stack - Descubra o melhor do cinema">
    <meta name="description" content="Descubra, organize e acompanhe seus filmes favoritos com o Film Stack. Explore milhares de filmes e crie suas listas personalizadas." />
    <meta name="keywords" content="filmes, cinema, listas, TMDB, streaming, entretenimento" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  </Head>

  <div class="min-h-screen bg-black text-white">
    <!-- Mobile Menu -->
    <MobileMenu
      v-model:is-open="isMobileMenuOpen"
      @scroll-to-trending="handleExploreMovies"
      @scroll-to-popular="handleScrollToPopular"
    />

    <!-- Navigation Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-b from-black/80 to-transparent backdrop-blur-sm transition-all duration-300">
      <nav class="container mx-auto flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <!-- Logo -->
        <div class="flex items-center">
          <h1 class="text-2xl font-bold text-white">
            Film<span class="text-red-500">Stack</span>
          </h1>
        </div>

        <!-- Auth Links -->
        <div class="hidden items-center gap-4 md:flex">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-all duration-200 hover:bg-red-700 hover:scale-105"
          >
            Dashboard
          </Link>
          <template v-else>
            <Link
              :href="route('login')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-white transition-all duration-200 hover:bg-white/10"
            >
              Entrar
            </Link>
            <Link
              :href="route('register')"
              class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-all duration-200 hover:bg-red-700 hover:scale-105"
            >
              Registrar
            </Link>
          </template>
        </div>

        <!-- Mobile Menu Button -->
        <button
          class="rounded-lg p-2 text-white transition-all duration-200 hover:bg-white/10 md:hidden"
          @click="isMobileMenuOpen = true"
          aria-label="Open menu"
        >
          <font-awesome-icon icon="fa-solid fa-bars" class="h-6 w-6" />
        </button>
      </nav>
    </header>

    <!-- Hero Section -->
    <HeroSkeleton v-if="isLoading" />
    <HeroSection
      v-else
      :featured-movie="featuredMovie"
      :loading="isLoading"
      @movie-details="handleMovieClick"
      @add-to-list="handleAddToList"
      @explore-movies="handleExploreMovies"
      @scroll-down="handleScrollDown"
    />

    <!-- Main Content -->
    <main class="relative z-10 space-y-12 bg-black pb-20">
      <!-- Trending Movies Section -->
      <section id="trending-section" class="pt-12">
        <MovieCarousel
          title="Em Alta Hoje"
          icon="fire"
          :movies="trendingMovies"
          :loading="moviesStore.loading.trending"
          size="medium"
          @movie-click="handleMovieClick"
          @movie-details="handleMovieDetails"
          @add-to-list="handleAddToList"
          @more-options="handleMoreOptions"
        />
      </section>

      <!-- Popular Movies Section -->
      <section id="popular-section">
        <MovieCarousel
          title="Populares"
          icon="star"
          :movies="popularMovies"
          :loading="moviesStore.loading.popular"
          size="medium"
          @movie-click="handleMovieClick"
          @movie-details="handleMovieDetails"
          @add-to-list="handleAddToList"
          @more-options="handleMoreOptions"
        />
      </section>

      <!-- CTA -->
      <section class="py-20">
        <div class="container mx-auto px-4 text-center sm:px-6 lg:px-8">
          <div class="mx-auto max-w-3xl">
            <h2 class="text-3xl font-bold text-white sm:text-4xl lg:text-5xl">
              Pronto para começar sua jornada cinematográfica?
            </h2>
            <p class="mt-6 text-lg text-gray-300 sm:text-xl">
              Cadastre-se gratuitamente e comece a organizar seus filmes favoritos, 
              criar listas personalizadas e descobrir novos títulos baseados no seu gosto.
            </p>
            <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-center">
              <Link
                v-if="!$page.props.auth.user"
                :href="route('register')"
                class="rounded-lg bg-red-600 px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:bg-red-700 hover:scale-105"
              >
                Começar Gratuitamente
              </Link>
              <Link
                v-else
                :href="route('dashboard')"
                class="rounded-lg bg-red-600 px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:bg-red-700 hover:scale-105"
              >
                Ir para Dashboard
              </Link>
              <Button
                variant="glass"
                label="Explorar Filmes"
                icon="magnifying-glass"
                @click="handleExploreMovies"
              />
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <Footer @link-click="handleFooterLinkClick" />

    <!-- Toast Notifications -->
    <ToastContainer />
  </div>
</template>

<style scoped>
.hero-section {
  background-attachment: fixed;
}

@media (max-width: 768px) {
  .hero-section {
    background-attachment: scroll;
  }
}

html {
  scroll-behavior: smooth;
}

::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #1f2937;
}

::-webkit-scrollbar-thumb {
  background: #4b5563;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>
