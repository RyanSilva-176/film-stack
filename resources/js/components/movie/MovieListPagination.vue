<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next';

interface Props {
    currentPage: number;
    totalPages: number;
    hasNext: boolean;
    hasPrevious: boolean;
    maxVisiblePages?: number;
}

const props = withDefaults(defineProps<Props>(), {
    maxVisiblePages: 5,
});

const emit = defineEmits<{
    pageChange: [page: number];
}>();

const visiblePages = computed(() => {
    const pages: (number | string)[] = [];
    const { currentPage, totalPages, maxVisiblePages } = props;

    if (totalPages <= maxVisiblePages) {
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i);
        }
    } else {
        const halfVisible = Math.floor(maxVisiblePages / 2);
        let startPage = Math.max(1, currentPage - halfVisible);
        let endPage = Math.min(totalPages, currentPage + halfVisible);

        if (endPage - startPage + 1 < maxVisiblePages) {
            if (startPage === 1) {
                endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            } else {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
        }

        if (startPage > 1) {
            pages.push(1);
            if (startPage > 2) {
                pages.push('...');
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            pages.push(i);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pages.push('...');
            }
            pages.push(totalPages);
        }
    }

    return pages;
});

const handlePageClick = (page: number | string) => {
    if (typeof page === 'number' && page !== props.currentPage) {
        emit('pageChange', page);
    }
};

const handlePrevious = () => {
    if (props.hasPrevious) {
        emit('pageChange', props.currentPage - 1);
    }
};

const handleNext = () => {
    if (props.hasNext) {
        emit('pageChange', props.currentPage + 1);
    }
};
</script>

<template>
    <div class="flex items-center justify-center gap-2 py-4">
        <!-- Previous Button -->
        <Button
            variant="outline"
            size="sm"
            :disabled="!hasPrevious"
            @click="handlePrevious"
            class="gap-1"
        >
            <ChevronLeft class="h-4 w-4" />
            <span class="hidden sm:inline">Anterior</span>
        </Button>

        <!-- Page Numbers -->
        <div class="flex items-center gap-1">
            <template v-for="page in visiblePages" :key="page">
                <!-- Ellipsis -->
                <div
                    v-if="page === '...'"
                    class="flex items-center justify-center w-8 h-8"
                >
                    <MoreHorizontal class="h-4 w-4 text-muted-foreground" />
                </div>

                <!-- Page Number -->
                <Button
                    v-else
                    :variant="page === currentPage ? 'default' : 'outline'"
                    size="sm"
                    class="w-8 h-8 p-0"
                    @click="handlePageClick(page)"
                >
                    {{ page }}
                </Button>
            </template>
        </div>

        <!-- Next Button -->
        <Button
            variant="outline"
            size="sm"
            :disabled="!hasNext"
            @click="handleNext"
            class="gap-1"
        >
            <span class="hidden sm:inline">Próxima</span>
            <ChevronRight class="h-4 w-4" />
        </Button>
    </div>

    <!-- Mobile Summary -->
    <div class="text-center text-sm text-muted-foreground mt-2 sm:hidden">
        Página {{ currentPage }} de {{ totalPages }}
    </div>
</template>
