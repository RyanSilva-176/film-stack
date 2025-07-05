<template>
  <section class="hero-skeleton relative min-h-screen w-full bg-black">
    <!-- Background Skeleton -->
    <div class="absolute inset-0">
      <div class="h-full w-full bg-gradient-to-r from-gray-800 to-gray-700 animate-pulse"></div>
      <div class="absolute inset-0 bg-gradient-to-t from-black via-black/50 to-transparent"></div>
    </div>

    <!-- Content Skeleton -->
    <div class="relative z-10 flex min-h-screen items-center">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
          <!-- Title Skeleton -->
          <div class="space-y-4">
            <div class="h-16 bg-gray-700 rounded-lg animate-pulse"></div>
            <div class="h-12 bg-gray-700 rounded-lg w-3/4 animate-pulse"></div>
          </div>
          
          <!-- Overview Skeleton -->
          <div class="mt-6 space-y-3">
            <div class="h-4 bg-gray-800 rounded animate-pulse"></div>
            <div class="h-4 bg-gray-800 rounded w-5/6 animate-pulse"></div>
            <div class="h-4 bg-gray-800 rounded w-4/6 animate-pulse"></div>
          </div>
          
          <!-- Buttons Skeleton -->
          <div class="mt-8 flex flex-col gap-4 sm:flex-row">
            <div class="h-14 w-48 bg-gray-700 rounded-lg animate-pulse"></div>
            <div class="h-14 w-40 bg-gray-800 rounded-lg animate-pulse"></div>
          </div>
          
          <!-- Genres Skeleton -->
          <div class="mt-6 flex flex-wrap gap-2">
            <div class="h-8 w-20 bg-gray-800 rounded-full animate-pulse"></div>
            <div class="h-8 w-24 bg-gray-800 rounded-full animate-pulse"></div>
            <div class="h-8 w-16 bg-gray-800 rounded-full animate-pulse"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll Down Indicator Skeleton -->
    <div class="absolute bottom-8 left-1/2 z-10 -translate-x-1/2 animate-pulse">
      <div class="h-12 w-8 bg-gray-700 rounded-full"></div>
    </div>
  </section>
</template>

<script setup lang="ts">
// No props needed for skeleton
</script>

<style scoped>
.hero-skeleton {
  background-attachment: fixed;
}

@media (max-width: 768px) {
  .hero-skeleton {
    background-attachment: scroll;
  }
}
</style>
