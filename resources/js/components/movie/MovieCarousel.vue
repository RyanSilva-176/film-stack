<template>
    <section class="movie-carousel">
        <!-- Section Header -->
        <div class="mb-6 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <h2 class="flex items-center gap-3 text-xl font-bold text-white sm:text-2xl" ref="titleRef">
                <FontAwesomeIcon v-if="icon" :icon="icon" class="text-lg text-red-500" />
                {{ title }}
            </h2>

            <!-- Navigation Arrows -->
            <div class="flex items-center gap-2">
                <button
                    :disabled="!canScrollLeft"
                    :class="[
                        'nav-arrow rounded-full border p-2.5 shadow-lg transition-all duration-200',
                        canScrollLeft
                            ? 'border-white/20 bg-white/10 text-white hover:border-red-600 hover:bg-red-600 hover:shadow-red-600/25'
                            : 'cursor-not-allowed border-gray-700 bg-gray-800/50 text-gray-600',
                    ]"
                    @click="slideToPrev"
                    aria-label="Anterior"
                >
                    <FontAwesomeIcon icon="chevron-left" class="h-4 w-4" />
                </button>

                <button
                    :disabled="!canScrollRight"
                    :class="[
                        'nav-arrow rounded-full border p-2.5 shadow-lg transition-all duration-200',
                        canScrollRight
                            ? 'border-white/20 bg-white/10 text-white hover:border-red-600 hover:bg-red-600 hover:shadow-red-600/25'
                            : 'cursor-not-allowed border-gray-700 bg-gray-800/50 text-gray-600',
                    ]"
                    @click="slideToNext"
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

            <!-- Vue3 Carousel -->
            <Carousel
                v-else-if="movies.length > 0"
                ref="carousel"
                v-model="currentSlide"
                v-bind="carouselConfig"
                class="carousel-container"
                @update:model-value="onSlideChange"
            >
                <Slide v-for="(movie, index) in movies" :key="movie.id">
                    <div 
                        class="carousel__item"
                        :style="{ animationDelay: `${index * 0.1}s` }"
                    >
                        <MovieCard
                            :movie="movie"
                            :size="size"
                            :show-rating="showRating"
                            :show-details="showDetails"
                            :show-genres="showGenres"
                            @click="emit('movieClick', movie)"
                            @details="emit('movieDetails', movie)"
                            @add-to-list="emit('addToList', movie)"
                            @more-options="emit('moreOptions', movie)"
                        />
                    </div>
                </Slide>

                <!-- Custom pagination for mobile -->
                <template #addons>
                    <div v-if="showMobilePagination" class="mt-6 flex justify-center gap-2 sm:hidden">
                        <div
                            v-for="i in Math.min(totalPages, 8)"
                            :key="`indicator-${i}`"
                            :class="[
                                'h-2 rounded-full transition-all duration-200 cursor-pointer',
                                currentPage === i - 1 ? 'w-6 bg-red-500' : 'w-2 bg-gray-600 hover:bg-gray-500'
                            ]"
                            @click="goToPage(i - 1)"
                        />
                    </div>
                </template>
            </Carousel>

            <!-- Empty State -->
            <div v-else class="flex h-96 w-full items-center justify-center px-4 text-gray-400 sm:px-6 lg:px-8">
                <div class="text-center">
                    <FontAwesomeIcon icon="film" class="mx-auto mb-4 h-16 w-16 text-gray-600" />
                    <p class="text-lg font-medium">Nenhum filme encontrado</p>
                    <p class="mt-2 text-sm">Tente novamente mais tarde</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import MovieCard from '@/components/movie/MovieCard.vue';
import MovieCardSkeleton from '@/components/ui/MovieCardSkeleton.vue';
import type { Movie } from '@/types/movies';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { gsap } from 'gsap';
import { computed, onMounted, ref } from 'vue';
import { Carousel, Slide } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';

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

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    size: 'medium',
    showRating: true,
    showDetails: true,
    showGenres: true,
    skeletonCount: 6,
    icon: undefined,
});

const emit = defineEmits<{
    movieClick: [movie: Movie];
    movieDetails: [movie: Movie];
    addToList: [movie: Movie];
    moreOptions: [movie: Movie];
}>();

const carousel = ref();
const carouselRef = ref<HTMLElement>();
const titleRef = ref<HTMLElement>();
const currentSlide = ref(0);

const carouselConfig = computed(() => ({
    itemsToShow: 1.5,
    itemsToScroll: 1,
    snapAlign: 'start' as const,
    wrapAround: false,
    transition: 500,
    mouseDrag: true,
    touchDrag: true,
    dir: 'ltr' as const,
    breakpoints: {
        640: {
            itemsToShow: 2.5,
            itemsToScroll: 1,
            snapAlign: 'start' as const,
        },
        768: {
            itemsToShow: 3.5,
            itemsToScroll: 2,
            snapAlign: 'start' as const,
        },
        1024: {
            itemsToShow: 4.5,
            itemsToScroll: 2,
            snapAlign: 'start' as const,
        },
        1280: {
            itemsToShow: 5.5,
            itemsToScroll: 3,
            snapAlign: 'start' as const,
        },
    },
}));

const mobileItemsPerView = computed(() => {
    if (typeof window === 'undefined') return 1.5;
    
    const width = window.innerWidth;
    if (width >= 640) return 2.5; 
    return 1.5;
});

const totalPages = computed(() => {
    if (!props.movies.length) return 0;
    return Math.ceil(props.movies.length / mobileItemsPerView.value);
});

const currentPage = computed(() => {
    const itemsPerView = mobileItemsPerView.value;
    return Math.floor(currentSlide.value / itemsPerView);
});

const showMobilePagination = computed(() => {
    return props.movies.length > 3;
});

const getCurrentItemsToShow = (): number => {
    if (typeof window === 'undefined') return 1.5;

    const width = window.innerWidth;
    if (width >= 1280) return 5.5;
    if (width >= 1024) return 4.5;
    if (width >= 768) return 3.5;
    if (width >= 640) return 2.5;
    return 1.5;
};

const canScrollLeft = computed(() => {
    return currentSlide.value > 0;
});

const canScrollRight = computed(() => {
    if (!carousel.value || !props.movies.length) return false;
    
    const currentItemsToShow = getCurrentItemsToShow();
    const maxSlides = Math.max(0, props.movies.length - Math.floor(currentItemsToShow));
    return currentSlide.value < maxSlides;
});

const slideToPrev = () => {
    if (carousel.value && canScrollLeft.value) {
        carousel.value.prev();
    }
};

const slideToNext = () => {
    if (carousel.value && canScrollRight.value) {
        carousel.value.next();
    }
};

const goToPage = (pageIndex: number) => {
    if (carousel.value) {
        const itemsPerView = mobileItemsPerView.value;
        const targetSlide = Math.floor(pageIndex * itemsPerView);
        carousel.value.slideTo(targetSlide);
    }
};

const onSlideChange = (newSlide: number) => {
    currentSlide.value = newSlide;
};

onMounted(() => {
    if (titleRef.value) {
        gsap.fromTo(
            titleRef.value,
            { opacity: 0, x: -50 },
            { opacity: 1, x: 0, duration: 0.6, ease: 'power2.out' }
        );
    }
    if (carouselRef.value) {
        gsap.fromTo(
            carouselRef.value,
            { opacity: 0, y: 30 },
            { opacity: 1, y: 0, duration: 0.8, delay: 0.2, ease: 'power2.out' }
        );
    }
});
</script>

<style scoped>
.movie-carousel {
    user-select: none;
    overflow: hidden;
}

.carousel-container {
    padding-left: 1rem;
    padding-right: 1rem;
}

@media (min-width: 640px) {
    .carousel-container {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
}

@media (min-width: 1024px) {
    .carousel-container {
        padding-left: 2rem;
        padding-right: 2rem;
    }
}

:deep(.carousel__viewport) {
    overflow: visible;
    perspective: 1000px;
}

:deep(.carousel__track) {
    align-items: stretch; /* Garante que todos os slides tenham a mesma altura */
    gap: 1.5rem;
}

:deep(.carousel__slide) {
    padding: 0;
    min-height: 0;
    flex: 0 0 auto;
    display: flex; /* Torna o slide um flex container */
}

:deep(.carousel__pagination) {
    display: none;
}

/* Item do carrossel com altura consistente */
.carousel__item {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    animation: fadeInUp 0.6s ease-out both;
    animation-fill-mode: both;
    min-height: 420px; /* Altura mínima para mobile */
}

/* Altura responsiva dos cards */
@media (min-width: 640px) {
    .carousel__item {
        min-height: 450px; /* Tablet */
    }
}

@media (min-width: 1024px) {
    .carousel__item {
        min-height: 480px; /* Desktop */
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.nav-arrow:hover:not(:disabled) {
    transform: scale(1.05);
}

.nav-arrow:active:not(:disabled) {
    transform: scale(0.95);
}

@media (max-width: 768px) {
    :deep(.carousel__viewport) {
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }
    
    :deep(.carousel__track) {
        gap: 1rem; 
    }
}

@media (min-width: 640px) {
    :deep(.carousel__track) {
        gap: 1.5rem;
    }
}

@media (min-width: 1024px) {
    :deep(.carousel__track) {
        gap: 2rem;
    }
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
