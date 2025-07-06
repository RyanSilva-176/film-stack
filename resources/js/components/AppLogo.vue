<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <!-- Logo -->
        <div class="flex items-center">
            <h1 class="text-2xl font-bold text-white">Film<span class="text-red-500">Stack</span></h1>
        </div>
    </div>
</template>
