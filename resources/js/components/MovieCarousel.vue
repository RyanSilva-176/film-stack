<template>
    <section class="movie-carousel">
        <!-- Section Header -->
        <div class="mb-6 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <h2 class="flex items-center gap-2 text-xl font-bold text-white sm:text-2xl" ref="titleRef">
                <FontAwesomeIcon v-if="icon" :icon="icon" class="text-red-500" />
                {{ title }}
            </h2>

            <!-- Navigation Arrows - Always visible on top right -->
            <div class="flex items-center gap-2">
                <button
                    :disabled="!canScrollLeft"
                    :class="[
                        'nav-arrow rounded-full border p-2.5 transition-all duration-200',
                        canScrollLeft
                            ? 'border-white/20 bg-white/10 text-white hover:border-red-600 hover:bg-red-600'
                            : 'cursor-not-allowed border-gray-700 bg-gray-800/50 text-gray-600',
                    ]"
                    @click="scrollLeftFn"
                    aria-label="Anterior"
                >
                    <FontAwesomeIcon icon="chevron-left" class="h-4 w-4" />
                </button>

                <button
                    :disabled="!canScrollRight"
                    :class="[
                        'nav-arrow rounded-full border p-2.5 transition-all duration-200',
                        canScrollRight
                            ? 'border-white/20 bg-white/10 text-white hover:border-red-600 hover:bg-red-600'
                            : 'cursor-not-allowed border-gray-700 bg-gray-800/50 text-gray-600',
                    ]"
                    @click="scrollRight"
                    aria-label="Próximo"
                >
                    <FontAwesomeIcon icon="chevron-right" class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Carousel Container -->
        <div class="relative" ref="carouselRef">
            <!-- Loading Skeletons -->
            <div v-if="loading" class="scrollbar-hide flex gap-6 overflow-x-auto px-4 sm:px-6 lg:px-8">
                <MovieCardSkeleton v-for="i in skeletonCount" :key="`skeleton-${i}`" class="flex-shrink-0" />
            </div>

            <!-- Movies Scroll Container -->
            <div
                v-else-if="movies.length > 0"
                ref="scrollContainer"
                class="scrollbar-hide flex gap-6 overflow-x-auto scroll-smooth px-4 pb-4 sm:px-6 lg:px-8"
                @scroll="updateScrollState"
                @mousedown="startDrag"
                @mousemove="drag"
                @mouseup="endDrag"
                @mouseleave="endDrag"
                @touchstart="startDrag"
                @touchmove="drag"
                @touchend="endDrag"
            >
                <div
                    v-for="(movie, index) in movies"
                    :key="movie.id"
                    class="movie-card-wrapper flex-shrink-0"
                    :style="{ animationDelay: `${index * 0.1}s` }"
                >
                    <MovieCard
                        :movie="movie"
                        :size="size"
                        :show-rating="showRating"
                        :show-details="showDetails"
                        :show-genres="showGenres"
                        @click="$emit('movieClick', movie)"
                        @add-to-list="$emit('addToList', movie)"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="flex h-96 w-full items-center justify-center px-4 text-gray-400 sm:px-6 lg:px-8">
                <div class="text-center">
                    <FontAwesomeIcon icon="film" class="mx-auto mb-4 h-16 w-16 text-gray-600" />
                    <p class="text-lg font-medium">Nenhum filme encontrado</p>
                    <p class="mt-2 text-sm">Tente novamente mais tarde</p>
                </div>
            </div>

            <!-- Mobile Scroll Indicators -->
            <div v-if="movies.length > 0" class="mt-4 flex justify-center gap-2 sm:hidden">
                <div
                    v-for="i in Math.min(Math.ceil(movies.length / itemsPerView), 5)"
                    :key="`indicator-${i}`"
                    :class="['h-2 w-2 rounded-full transition-all duration-200', currentPage === i - 1 ? 'w-6 bg-red-500' : 'bg-gray-600']"
                />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import type { Movie } from '../types/movies';
import MovieCard from './MovieCard.vue';
import MovieCardSkeleton from './MovieCardSkeleton.vue';

gsap.registerPlugin(ScrollTrigger);

interface Props {
    title: string;
    movies: Movie[];
    loading?: boolean;
    size?: 'small' | 'medium';
    showRating?: boolean;
    showDetails?: boolean;
    showGenres?: boolean;
    skeletonCount?: number;
    icon?: string;
}

interface Emits {
    (e: 'movieClick', movie: Movie): void;
    (e: 'addToList', movie: Movie): void;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    size: 'medium',
    showRating: true,
    showDetails: true,
    showGenres: true,
    skeletonCount: 6,
});

defineEmits<Emits>();

const scrollContainer = ref<HTMLElement>();
const carouselRef = ref<HTMLElement>();
const titleRef = ref<HTMLElement>();
const canScrollLeft = ref(false);
const canScrollRight = ref(false);
const isDragging = ref(false);
const startX = ref(0);
const scrollLeft = ref(0);

const itemsPerView = computed(() => {
    if (typeof window === 'undefined') return 2;

    const width = window.innerWidth;
    if (width < 640) return 2.2;
    if (width < 768) return 2.8;
    if (width < 1024) return 3.5;
    if (width < 1280) return 4.2;
    return 5.2;
});

const currentPage = computed(() => {
    if (!scrollContainer.value || props.movies.length === 0) return 0;
    const scrollWidth = scrollContainer.value.scrollWidth - scrollContainer.value.clientWidth;
    if (scrollWidth <= 0) return 0;
    const scrollPosition = scrollContainer.value.scrollLeft;
    const totalPages = Math.ceil(props.movies.length / itemsPerView.value);
    return Math.round((scrollPosition / scrollWidth) * (totalPages - 1));
});

const updateScrollState = (): void => {
    if (!scrollContainer.value) return;

    const { scrollLeft: scroll, scrollWidth, clientWidth } = scrollContainer.value;
    canScrollLeft.value = scroll > 5;
    canScrollRight.value = scroll < scrollWidth - clientWidth - 5;
};

const scrollLeftFn = (): void => {
    if (!scrollContainer.value) return;

    const scrollAmount = scrollContainer.value.clientWidth * 0.75;
    scrollContainer.value.scrollBy({
        left: -scrollAmount,
        behavior: 'smooth',
    });
};

const scrollRight = (): void => {
    if (!scrollContainer.value) return;

    const scrollAmount = scrollContainer.value.clientWidth * 0.75;
    scrollContainer.value.scrollBy({
        left: scrollAmount,
        behavior: 'smooth',
    });
};

const startDrag = (e: MouseEvent | TouchEvent): void => {
    if (!scrollContainer.value) return;

    isDragging.value = true;
    startX.value = e instanceof MouseEvent ? e.pageX : e.touches[0].pageX;
    scrollLeft.value = scrollContainer.value.scrollLeft;
    e.preventDefault();
    scrollContainer.value.style.cursor = 'grabbing';
};

const drag = (e: MouseEvent | TouchEvent): void => {
    if (!isDragging.value || !scrollContainer.value) return;

    e.preventDefault();
    const x = e instanceof MouseEvent ? e.pageX : e.touches[0].pageX;
    const walk = (x - startX.value) * 1.5;
    scrollContainer.value.scrollLeft = scrollLeft.value - walk;
};

const endDrag = (): void => {
    isDragging.value = false;
    if (scrollContainer.value) {
        scrollContainer.value.style.cursor = 'grab';
    }
};

onMounted(() => {
    updateScrollState();
    const handleResize = () => {
        updateScrollState();
    };

    window.addEventListener('resize', handleResize);
    if (titleRef.value && carouselRef.value) {
        // Title
        gsap.fromTo(
            titleRef.value,
            {
                opacity: 0,
                y: 20,
            },
            {
                opacity: 1,
                y: 0,
                duration: 0.6,
                scrollTrigger: {
                    trigger: titleRef.value,
                    start: 'top 85%',
                    toggleActions: 'play none none reverse',
                },
            },
        );

        // Carousel
        gsap.fromTo(
            carouselRef.value,
            {
                opacity: 0,
                y: 30,
            },
            {
                opacity: 1,
                y: 0,
                duration: 0.8,
                delay: 0.2,
                scrollTrigger: {
                    trigger: carouselRef.value,
                    start: 'top 85%',
                    toggleActions: 'play none none reverse',
                },
            },
        );

        // Navigation
        gsap.fromTo(
            '.nav-arrow',
            {
                opacity: 0,
                scale: 0.8,
            },
            {
                opacity: 1,
                scale: 1,
                duration: 0.4,
                delay: 0.5,
                stagger: 0.1,
                ease: 'back.out(1.7)',
                scrollTrigger: {
                    trigger: carouselRef.value,
                    start: 'top 85%',
                    toggleActions: 'play none none reverse',
                },
            },
        );
    }
    setTimeout(updateScrollState, 100);
});

onUnmounted(() => {
    window.removeEventListener('resize', updateScrollState);
});

defineExpose({
    scrollLeft: scrollLeftFn,
    scrollRight,
    updateScrollState,
});
</script>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.movie-carousel {
    user-select: none;
}

.movie-card-wrapper {
    min-width: 160px;
}

@media (min-width: 640px) {
    .movie-card-wrapper {
        min-width: 180px;
    }
}

@media (min-width: 768px) {
    .movie-card-wrapper {
        min-width: 200px;
    }
}

@media (min-width: 1024px) {
    .movie-card-wrapper {
        min-width: 220px;
    }
}

@media (min-width: 1280px) {
    .movie-card-wrapper {
        min-width: 240px;
    }
}

/* Enhanced touch scrolling for mobile */
@media (max-width: 768px) {
    .scrollbar-hide {
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
}

/* Navigation arrows hover effects */
.nav-arrow:hover:not(:disabled) {
    transform: scale(1.05);
}

.nav-arrow:active:not(:disabled) {
    transform: scale(0.95);
}
</style>
