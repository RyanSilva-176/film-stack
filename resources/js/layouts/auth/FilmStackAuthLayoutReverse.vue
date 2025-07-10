<template>
    <div class="min-h-screen bg-black lg:grid lg:grid-cols-2">
        <!-- Left Side - Auth Form -->
        <div class="flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:order-1 lg:px-8">
            <div class="w-full max-w-md space-y-8">
                <!-- Mobile Logo (visible only on mobile) -->
                <div class="flex justify-center lg:hidden">
                    <h1 class="text-3xl font-bold text-white">Film<span class="text-red-500">Stack</span></h1>
                </div>

                <!-- Header -->
                <div class="text-center lg:text-left">
                    <h2 v-if="title" class="text-2xl font-bold text-white sm:text-3xl">
                        {{ title }}
                    </h2>
                    <p v-if="description" class="mt-2 text-sm text-gray-400 sm:text-base">
                        {{ description }}
                    </p>
                </div>

                <!-- Form Content Slot -->
                <div class="mt-8">
                    <slot />
                </div>

                <!-- Footer -->
                <div class="text-center text-xs text-gray-500">
                    <p>&copy; 2025 FilmStack. Todos os direitos reservados.</p>
                    <p class="mt-1">Dados fornecidos por The Movie Database (TMDB)</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Poster Slide (Hidden on mobile) -->
        <div class="hidden lg:order-2 lg:flex lg:flex-col">
            <PosterSlide />
        </div>
    </div>
</template>

<script setup lang="ts">
import PosterSlide from '@/components/auth/PosterSlide.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<style scoped>
/* Ensure full height on mobile */
@media (max-width: 1023px) {
    .min-h-screen {
        min-height: 100vh;
        min-height: 100dvh;
    }
}
</style>
