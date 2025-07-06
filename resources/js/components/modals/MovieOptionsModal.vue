<template>
    <Dialog :open="isOpen" @update:open="emit('update:open', $event)">
        <DialogContent class="border-sidebar-border bg-sidebar text-sidebar-foreground sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-semibold text-sidebar-primary">{{ movie?.title }}</DialogTitle>
                <DialogDescription class="text-sm text-sidebar-accent-foreground"> Escolha uma ação para este filme </DialogDescription>
            </DialogHeader>

            <div class="grid gap-2 py-4">
                <!-- Adicionar às Listas Padrão -->
                <div class="space-y-2">
                    <h4 class="text-sm font-medium text-sidebar-foreground">Listas Rápidas</h4>

                    <Button
                        @click="handleToggleLike"
                        variant="ghost"
                        class="h-10 w-full justify-start text-sidebar-foreground hover:bg-sidebar-accent"
                        :class="localIsLiked ? 'text-red-400' : 'text-sidebar-foreground'"
                        :disabled="loading.like"
                    >
                        <span class="flex items-center gap-2">
                            <font-awesome-icon :icon="['fas', 'heart']" :class="localIsLiked ? 'text-red-500' : 'text-sidebar-accent-foreground'" />
                            {{ localIsLiked ? 'Remover dos Curtidos' : 'Adicionar aos Curtidos' }}
                            <Loader2 v-if="loading.like" class="ml-auto h-4 w-4 animate-spin" />
                        </span>
                    </Button>

                    <Button
                        @click="handleToggleWatchlist"
                        variant="ghost"
                        class="h-10 w-full justify-start text-sidebar-foreground hover:bg-sidebar-accent"
                        :class="localIsInWatchlist ? 'text-blue-400' : 'text-sidebar-foreground'"
                        :disabled="loading.watchlist"
                    >
                        <span class="flex items-center gap-2">
                            <font-awesome-icon
                                :icon="['fas', 'bookmark']"
                                :class="localIsInWatchlist ? 'text-blue-500' : 'text-sidebar-accent-foreground'"
                            />
                            {{ localIsInWatchlist ? 'Remover da Lista da Watchlist' : 'Adicionar à Lista da Watchlist' }}
                            <Loader2 v-if="loading.watchlist" class="ml-auto h-4 w-4 animate-spin" />
                        </span>
                    </Button>

                    <Button
                        @click="handleMarkWatched"
                        variant="ghost"
                        class="h-10 w-full justify-start text-sidebar-foreground hover:bg-sidebar-accent"
                        :class="localIsWatched ? 'text-green-400' : 'text-sidebar-foreground'"
                        :disabled="loading.watched"
                    >
                        <span class="flex items-center gap-2">
                            <font-awesome-icon
                                :icon="['fas', 'check-circle']"
                                :class="localIsWatched ? 'text-green-500' : 'text-sidebar-accent-foreground'"
                            />
                            {{ localIsWatched ? 'Marcar como Não Assistido' : 'Marcar como Assistido' }}
                            <Loader2 v-if="loading.watched" class="ml-auto h-4 w-4 animate-spin" />
                        </span>
                    </Button>
                </div>

                <!-- Listas Personalizadas -->
                <div v-if="customLists.length > 0" class="space-y-2">
                    <h4 class="text-sm font-medium text-sidebar-foreground">Listas Personalizadas</h4>

                    <Button
                        v-for="list in customLists"
                        :key="list.id"
                        @click="handleToggleCustomList(list)"
                        variant="ghost"
                        class="h-10 w-full justify-start gap-3 text-sidebar-foreground hover:bg-sidebar-accent"
                        :disabled="loading.custom"
                    >
                        <span class="flex items-center gap-2">
                            <font-awesome-icon :icon="['fas', 'list']" class="text-purple-400" />
                            {{ list.name }}
                            <span
                                v-if="list.movies_count > 0"
                                class="ml-auto rounded-full bg-sidebar-accent px-2 py-1 text-xs text-sidebar-accent-foreground"
                            >
                                {{ list.movies_count }}
                            </span>
                            <Loader2 v-if="loading.custom" class="ml-auto h-4 w-4 animate-spin" />
                        </span>
                    </Button>
                </div>

                <!-- Ações Adicionais -->
                <div class="space-y-2 border-t border-sidebar-border pt-2">
                    <Button
                        @click="handleCreateList"
                        variant="ghost"
                        class="h-10 w-full justify-start gap-3 text-sidebar-foreground hover:bg-sidebar-accent"
                    >
                        <font-awesome-icon :icon="['fas', 'plus']" class="text-sidebar-accent-foreground" />
                        Criar Nova Lista
                    </Button>

                    <Button
                        @click="handleMovieDetails"
                        variant="ghost"
                        class="h-10 w-full justify-start gap-3 text-sidebar-foreground hover:bg-sidebar-accent"
                    >
                        <font-awesome-icon :icon="['fas', 'info-circle']" class="text-blue-400" />
                        Ver Detalhes
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useToast } from '@/composables/useToastSystem';
import type { MovieList } from '@/stores/userLists';
import { useUserListsStore } from '@/stores/userLists';
import type { Movie } from '@/types/movies';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faBookmark, faCheckCircle, faHeart, faInfoCircle, faList, faPlus } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Loader2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

library.add(faHeart, faBookmark, faCheckCircle, faList, faPlus, faInfoCircle);

interface Props {
    isOpen: boolean;
    movie: Movie | null;
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'movie-details'): void;
    (e: 'create-list'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const userListsStore = useUserListsStore();
const { success, error: showError } = useToast();

const localIsLiked = ref(false);
const localIsInWatchlist = ref(false);
const localIsWatched = ref(false);

watch(
    () => props.movie,
    (newMovie) => {
        if (newMovie) {
            // Reset local states when movie changes
            localIsLiked.value = false;
            localIsInWatchlist.value = false;
            localIsWatched.value = false;

            // Check actual states from store
            const likedList = userListsStore.getLikedList;
            const watchlist = userListsStore.getWatchlist;
            const watchedList = userListsStore.getWatchedList;

            if (likedList) {
                localIsLiked.value = userListsStore.currentListMovies.some(
                    item => item.tmdb_movie_id === newMovie.id && item.movie_list_id === likedList.id
                );
            }

            if (watchlist) {
                localIsInWatchlist.value = userListsStore.currentListMovies.some(
                    item => item.tmdb_movie_id === newMovie.id && item.movie_list_id === watchlist.id
                );
            }

            if (watchedList) {
                localIsWatched.value = userListsStore.currentListMovies.some(
                    item => item.tmdb_movie_id === newMovie.id && item.movie_list_id === watchedList.id
                );
            }
        }
    },
    { immediate: true },
);

const handleCreateList = () => {
    emit('create-list');
};

const handleMovieDetails = () => {
    emit('movie-details');
};

const loading = ref({
    like: false,
    watchlist: false,
    watched: false,
    custom: false,
});

const customLists = computed(() => userListsStore.getCustomLists);

const handleToggleLike = async () => {
    if (!props.movie) return;

    const currentLikedState = localIsLiked.value;
    localIsLiked.value = !currentLikedState;

    loading.value.like = true;
    try {
        const result = await userListsStore.toggleLike(props.movie.id);

        if (result.success) {
            const action = result.action === 'added' ? 'adicionado aos' : 'removido dos';
            success('Sucesso!', `${props.movie.title} foi ${action} curtidos.`);
        } else {
            localIsLiked.value = currentLikedState;
        }
    } catch (err) {
        localIsLiked.value = currentLikedState;

        console.error('Error toggling like:', err);
        showError('Erro!', 'Não foi possível atualizar a lista de curtidos.');
    } finally {
        loading.value.like = false;
    }
};

const handleToggleWatchlist = async () => {
    if (!props.movie) return;

    const currentWatchlistState = localIsInWatchlist.value;
    localIsInWatchlist.value = !currentWatchlistState;

    loading.value.watchlist = true;
    try {
        const result = await userListsStore.toggleMovieInList(props.movie.id, 'watchlist');

        if (result.success) {
            const action = currentWatchlistState ? 'removido da' : 'adicionado à';
            success('Sucesso!', `${props.movie.title} foi ${action} lista de watchlist.`);
        } else {
            localIsInWatchlist.value = currentWatchlistState;
            showError('Erro!', 'Não foi possível atualizar a lista de watchlist.');
        }
    } catch (err) {
        localIsInWatchlist.value = currentWatchlistState;

        console.error('Error toggling watchlist:', err);
        showError('Erro!', 'Não foi possível atualizar a lista de watchlist.');
    } finally {
        loading.value.watchlist = false;
    }
};

const handleMarkWatched = async () => {
    if (!props.movie) return;

    const currentWatchedState = localIsWatched.value;
    localIsWatched.value = !currentWatchedState;

    loading.value.watched = true;
    try {
        const result = await userListsStore.markWatched(props.movie.id);

        if (result.success) {
            const action = result.action === 'added' ? 'marcado como assistido' : 'desmarcado como assistido';
            success('Sucesso!', `${props.movie.title} foi ${action}.`);
        } else {
            localIsWatched.value = currentWatchedState;
        }
    } catch (err) {
        localIsWatched.value = currentWatchedState;

        console.error('Error marking watched:', err);
        showError('Erro!', 'Não foi possível atualizar o status de assistido.');
    } finally {
        loading.value.watched = false;
    }
};

const handleToggleCustomList = async (list: MovieList) => {
    if (!props.movie) return;

    // Check if movie is in this specific custom list
    const isInList = userListsStore.currentListMovies.some(
        item => item.tmdb_movie_id === props.movie!.id && item.movie_list_id === list.id
    );

    loading.value.custom = true;
    try {
        if (isInList) {
            const result = await userListsStore.removeMovieFromList(props.movie.id, list.id);
            if (result.success) {
                success('Sucesso!', `${props.movie.title} foi removido de "${list.name}".`);
                list.movies_count = Math.max(0, list.movies_count - 1);
            }
        } else {
            const result = await userListsStore.addMovieToList(props.movie.id, list.id);
            if (result.success) {
                success('Sucesso!', `${props.movie.title} foi adicionado à "${list.name}".`);
                list.movies_count++;
            }
        }
    } catch (err) {
        // Revert optimistic update
        if (isInList) {
            list.movies_count++;
        } else {
            list.movies_count = Math.max(0, list.movies_count - 1);
        }

        console.error('Error toggling custom list:', err);
        showError('Erro!', 'Não foi possível atualizar a lista personalizada.');
    } finally {
        loading.value.custom = false;
    }
};
</script>
