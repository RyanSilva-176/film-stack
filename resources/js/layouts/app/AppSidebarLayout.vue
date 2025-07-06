<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import Footer from '@/components/layout/Footer.vue';
import MovieDetailsSidebar from '@/components/movie/MovieDetailsSidebar.vue';
import { useMovieDetailsStore } from '@/stores/movieDetails';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const movieDetailsStore = useMovieDetailsStore();
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
            <!-- Footer -->
            <Footer />
        </AppContent>

        <!-- Toast Notifications -->
        <ToastContainer />
        
        <!-- Global Movie Details Sidebar -->
        <MovieDetailsSidebar 
            :is-open="movieDetailsStore.isOpen" 
            :movie="movieDetailsStore.selectedMovie" 
            @update:open="movieDetailsStore.closeSidebar" 
        />
    </AppShell>
</template>
