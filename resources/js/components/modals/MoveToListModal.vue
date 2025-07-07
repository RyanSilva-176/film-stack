<template>
    <Dialog :open="isOpen" @update:open="emit('update:open', $event)">
        <DialogContent class="border-sidebar-border bg-sidebar text-sidebar-foreground sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-semibold text-sidebar-primary">
                    {{ isMultiple ? `Mover ${movieCount} filmes` : `Mover "${movieTitle}"` }}
                </DialogTitle>
                <DialogDescription class="text-sm text-sidebar-accent-foreground">
                    {{ isMultiple ? 'Selecione a lista de destino para os filmes selecionados' : 'Selecione a lista de destino para este filme' }}
                </DialogDescription>
            </DialogHeader>

            <div class="py-4">
                <!-- Current List Info -->
                <div v-if="currentListName" class="mb-4 p-3 bg-sidebar-accent rounded-lg">
                    <div class="flex items-center gap-2">
                        <FontAwesomeIcon :icon="currentListIcon" class="text-sidebar-accent-foreground" />
                        <span class="text-sm text-sidebar-accent-foreground">
                            Lista atual: <strong>{{ currentListName }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Destination Lists -->
                <div class="space-y-2">
                    <h4 class="text-sm font-medium text-sidebar-foreground mb-3">Escolher lista de destino:</h4>

                    <!-- Standard Lists -->
                    <div class="space-y-1">
                        <button
                            v-for="list in availableStandardLists"
                            :key="list.id"
                            @click="handleSelectList(list)"
                            :disabled="loading || list.id === currentListId"
                            class="w-full flex items-center gap-3 p-3 rounded-lg transition-colors hover:bg-sidebar-accent disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="list.id === currentListId ? 'bg-sidebar-accent/50' : 'hover:bg-sidebar-accent'"
                        >
                            <div class="flex items-center gap-2 flex-1">
                                <FontAwesomeIcon :icon="getListIcon(list.type)" :class="getListIconClass(list.type)" />
                                <span class="text-sidebar-foreground">{{ getListLabel(list.type) }}</span>
                                <span class="text-xs text-sidebar-accent-foreground bg-sidebar-accent px-2 py-1 rounded-full">
                                    {{ list.movies_count }}
                                </span>
                            </div>
                            <div v-if="list.id === currentListId" class="text-xs text-sidebar-accent-foreground">
                                Lista atual
                            </div>
                        </button>
                    </div>

                    <!-- Custom Lists -->
                    <div v-if="availableCustomLists.length > 0" class="mt-4">
                        <h5 class="text-xs font-medium text-sidebar-accent-foreground mb-2">Listas Personalizadas</h5>
                        <div class="space-y-1">
                            <button
                                v-for="list in availableCustomLists"
                                :key="list.id"
                                @click="handleSelectList(list)"
                                :disabled="loading || list.id === currentListId"
                                class="w-full flex items-center gap-3 p-3 rounded-lg transition-colors hover:bg-sidebar-accent disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="list.id === currentListId ? 'bg-sidebar-accent/50' : 'hover:bg-sidebar-accent'"
                            >
                                <div class="flex items-center gap-2 flex-1">
                                    <FontAwesomeIcon icon="list" class="text-purple-400" />
                                    <span class="text-sidebar-foreground">{{ list.name }}</span>
                                    <span class="text-xs text-sidebar-accent-foreground bg-sidebar-accent px-2 py-1 rounded-full">
                                        {{ list.movies_count }}
                                    </span>
                                </div>
                                <div v-if="list.id === currentListId" class="text-xs text-sidebar-accent-foreground">
                                    Lista atual
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Create New List Option -->
                <div class="mt-4 pt-4 border-t border-sidebar-border">
                    <button
                        @click="handleCreateNewList"
                        :disabled="loading"
                        class="w-full flex items-center gap-3 p-3 rounded-lg transition-colors hover:bg-sidebar-accent text-sidebar-foreground disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <FontAwesomeIcon icon="plus" class="text-green-400" />
                        <span>Criar nova lista personalizada</span>
                    </button>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    variant="ghost"
                    @click="emit('update:open', false)"
                    :disabled="loading"
                >
                    Cancelar
                </Button>
            </div>

            <!-- Loading Overlay -->
            <div v-if="loading" class="absolute inset-0 bg-black/50 flex items-center justify-center">
                <div class="w-8 h-8 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { MovieList } from '@/stores/userLists';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed } from 'vue';

interface Props {
    isOpen: boolean;
    movieTitle?: string;
    movieCount?: number;
    currentListId?: number;
    currentListName?: string;
    currentListType?: string;
    availableLists: MovieList[];
    loading?: boolean;
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'select-list', list: MovieList): void;
    (e: 'create-new-list'): void;
}

const props = withDefaults(defineProps<Props>(), {
    movieTitle: '',
    movieCount: 1,
    loading: false,
});

const emit = defineEmits<Emits>();

const isMultiple = computed(() => (props.movieCount || 1) > 1);

const availableStandardLists = computed(() =>
    props.availableLists.filter(list => ['liked', 'watchlist', 'watched'].includes(list.type))
);

const availableCustomLists = computed(() =>
    props.availableLists.filter(list => list.type === 'custom')
);

const currentListIcon = computed(() => {
    return getListIcon(props.currentListType || 'custom');
});

const getListIcon = (type: string) => {
    switch (type) {
        case 'liked':
            return 'heart';
        case 'watchlist':
            return 'bookmark';
        case 'watched':
            return 'check-circle';
        default:
            return 'list';
    }
};

const getListIconClass = (type: string) => {
    switch (type) {
        case 'liked':
            return 'text-red-400';
        case 'watchlist':
            return 'text-blue-400';
        case 'watched':
            return 'text-green-400';
        default:
            return 'text-purple-400';
    }
};

const getListLabel = (type: string) => {
    switch (type) {
        case 'liked':
            return 'Filmes Curtidos';
        case 'watchlist':
            return 'Lista Watchlist';
        case 'watched':
            return 'Filmes Assistidos';
        default:
            return 'Lista Personalizada';
    }
};

const handleSelectList = (list: MovieList) => {
    emit('select-list', list);
};

const handleCreateNewList = () => {
    emit('create-new-list');
};
</script>
