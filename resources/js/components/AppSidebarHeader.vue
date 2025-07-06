<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import SearchHeader from '@/components/search/SearchHeader.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-4 border-b border-sidebar-border/70 bg-background px-6 transition-[width,height,left] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4 md:fixed md:top-0 md:z-30 md:backdrop-blur-md md:bg-background/80"
        :class="{
            'md:left-0 md:w-full': true,
            'md:left-64 md:w-[calc(100%-16rem)]': true,
            'md:group-has-data-[state=collapsed]/sidebar-wrapper:left-12 md:group-has-data-[state=collapsed]/sidebar-wrapper:w-[calc(100%-3rem)]': true
        }"
    >
        <!-- Left side: Menu + Breadcrumbs -->
        <div class="flex min-w-0 items-center gap-2">
            <SidebarTrigger class="-ml-1 shrink-0" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" class="hidden sm:block" />
            </template>
        </div>

        <!-- Right side: Search -->
        <div class="flex flex-1 justify-end">
            <SearchHeader />
        </div>
    </header>
    
    <!-- Spacer to prevent content from being hidden under fixed header (desktop only) -->
    <div class="hidden md:block md:h-16 md:shrink-0 md:group-has-data-[collapsible=icon]/sidebar-wrapper:h-12"></div>
</template>
