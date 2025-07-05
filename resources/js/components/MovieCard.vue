<template>
  <div
    class="movie-card group relative overflow-hidden rounded-lg bg-gray-900 transition-shadow duration-300 cursor-pointer"
    :class="[
      size === 'small' ? 'w-40 sm:w-48' : 'w-48 sm:w-56 md:w-64',
      loading ? 'animate-pulse' : ''
    ]"
    @click="handleClick"
    ref="cardRef"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    <!-- Poster Image -->
    <div class="relative aspect-[2/3] overflow-hidden">
      <img
        v-if="movie.poster_url && !imageError"
        :src="movie.poster_url"
        :alt="movie.title"
        class="h-full w-full object-cover"
        loading="lazy"
        @error="imageError = true"
        @load="imageLoaded = true"
        ref="imageRef"
      />
      
      <!-- Placeholder for missing poster -->
      <div
        v-else
        class="flex h-full w-full items-center justify-center bg-gray-800 text-gray-400"
      >
        <svg class="h-12 w-12" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
            clip-rule="evenodd"
          />
        </svg>
      </div>
      
      <!-- Loading state -->
      <div
        v-if="loading || !imageLoaded"
        class="absolute inset-0 flex items-center justify-center bg-gray-800"
      >
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-600 border-t-white"></div>
      </div>
      
      <!-- Rating Badge -->
      <div
        v-if="showRating && movie.vote_average > 0"
        class="absolute top-2 right-2 rounded-full bg-black/80 px-2 py-1 text-xs font-bold text-white backdrop-blur-sm"
      >
        {{ movie.vote_average.toFixed(1) }}
      </div>
      
      <!-- Hover Overlay -->
      <div
        class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
        ref="overlayRef"
      >
        <!-- Play Button -->
        <div class="absolute bottom-4 left-4 right-4">
          <button
            class="flex w-full items-center justify-center rounded-full bg-white/20 py-2 text-white backdrop-blur-sm transition-all duration-200 hover:bg-white/30"
            @click.stop="$emit('play', movie)"
          >
            <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                clip-rule="evenodd"
              />
            </svg>
            Ver Detalhes
          </button>
        </div>
      </div>
    </div>
    
    <!-- Movie Info -->
    <div
      v-if="showDetails"
      class="p-3 text-white"
    >
      <h3
        class="line-clamp-2 text-sm font-semibold leading-tight"
        :class="size === 'small' ? 'text-xs' : 'text-sm'"
      >
        {{ movie.title }}
      </h3>
      
      <div
        v-if="movie.release_date"
        class="mt-1 text-xs text-gray-400"
      >
        {{ formatYear(movie.release_date) }}
      </div>
      
      <!-- Genres -->
      <div
        v-if="showGenres && genreNames.length > 0"
        class="mt-2 flex flex-wrap gap-1"
      >
        <span
          v-for="genre in genreNames.slice(0, 2)"
          :key="genre"
          class="rounded-full bg-gray-700 px-2 py-1 text-xs text-gray-300"
        >
          {{ genre }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import type { Movie } from '@/types/movies';
import { useMoviesStore } from '@/stores/movies';
import { gsap } from 'gsap';

interface Props {
  movie: Movie;
  size?: 'small' | 'medium';
  showRating?: boolean;
  showDetails?: boolean;
  showGenres?: boolean;
  loading?: boolean;
}

interface Emits {
  (e: 'click', movie: Movie): void;
  (e: 'play', movie: Movie): void;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'medium',
  showRating: true,
  showDetails: true,
  showGenres: true,
  loading: false,
});

const emit = defineEmits<Emits>();

const moviesStore = useMoviesStore();

const cardRef = ref<HTMLElement>();
const imageRef = ref<HTMLElement>();
const overlayRef = ref<HTMLElement>();

const imageError = ref(false);
const imageLoaded = ref(false);

const genreNames = computed(() => {
  if (!props.movie.genre_ids || props.movie.genre_ids.length === 0) {
    return [];
  }
  return moviesStore.getGenreNames(props.movie.genre_ids);
});

const formatYear = (date: string): string => {
  return new Date(date).getFullYear().toString();
};

const handleClick = (): void => {
  emit('click', props.movie);
};

const handleMouseEnter = () => {
  if (!cardRef.value) return;
  
  gsap.to(cardRef.value, {
    scale: 1.05,
    y: -10,
    duration: 0.3,
    ease: "power2.out"
  });

  if (imageRef.value) {
    gsap.to(imageRef.value, {
      scale: 1.1,
      duration: 0.5,
      ease: "power2.out"
    });
  }

  if (overlayRef.value) {
    gsap.to(overlayRef.value, {
      opacity: 1,
      duration: 0.3,
      ease: "power2.out"
    });
  }
};

const handleMouseLeave = () => {
  if (!cardRef.value) return;
  
  gsap.to(cardRef.value, {
    scale: 1,
    y: 0,
    duration: 0.3,
    ease: "power2.out"
  });

  if (imageRef.value) {
    gsap.to(imageRef.value, {
      scale: 1,
      duration: 0.5,
      ease: "power2.out"
    });
  }

  if (overlayRef.value) {
    gsap.to(overlayRef.value, {
      opacity: 0,
      duration: 0.3,
      ease: "power2.out"
    });
  }
};
</script>

<style scoped>
.movie-card {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.movie-card:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
