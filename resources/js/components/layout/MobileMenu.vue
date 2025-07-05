<script setup lang="ts">
import { library } from '@fortawesome/fontawesome-svg-core';
import { faBolt } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Link, usePage } from '@inertiajs/vue3';
import gsap from 'gsap';
import { computed, nextTick, ref, watch } from 'vue';

library.add(faBolt);

interface Props {
    isOpen?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isOpen: false,
});

const emit = defineEmits<{
    'update:isOpen': [value: boolean];
    'scroll-to-trending': [];
    'scroll-to-popular': [];
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const isOpen = computed({
    get: () => props.isOpen,
    set: (value: boolean) => emit('update:isOpen', value),
});

const menuPanel = ref<HTMLElement | null>(null);
const showPanel = ref(false);

watch(
    isOpen,
    async (open) => {
        if (open) {
            showPanel.value = true;
            await nextTick();
            if (menuPanel.value) {
                gsap.fromTo(menuPanel.value, { x: 320, opacity: 0 }, { x: 0, opacity: 1, duration: 0.4, ease: 'power3.out' });
            }
        } else {
            if (menuPanel.value) {
                gsap.to(menuPanel.value, {
                    x: 320,
                    opacity: 0,
                    duration: 0.3,
                    ease: 'power3.in',
                    onComplete: () => {
                        showPanel.value = false;
                    },
                });
            } else {
                showPanel.value = false;
            }
        }
    },
    { immediate: true },
);

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
};

const closeMenu = () => {
    isOpen.value = false;
};

const scrollToTrending = () => {
    emit('scroll-to-trending');
    closeMenu();
};

const scrollToPopular = () => {
    emit('scroll-to-popular');
    closeMenu();
};
</script>

<template>
    <div>
        <button
            v-if="!isOpen"
            @click="toggleMenu"
            class="fixed top-4 right-4 z-50 cursor-pointer rounded-lg bg-black/80 p-2 text-white backdrop-blur-sm md:hidden"
            aria-label="Open menu"
        >
            <font-awesome-icon :icon="['fas', 'bars']" class="h-6 w-6" />
        </button>
        <Teleport to="body">
            <div v-if="showPanel" class="fixed inset-0 z-50 md:hidden" @click="closeMenu">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" />

                <!-- Menu Panel -->
                <div class="absolute top-0 right-0 h-full w-80 max-w-[90vw] bg-gray-900 shadow-2xl" @click.stop ref="menuPanel">
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-gray-800 p-4">
                        <h2 class="text-xl font-bold text-white">Film<span class="text-red-500">Stack</span></h2>
                        <button
                            @click="closeMenu"
                            class="cursor-pointer rounded-lg p-2 text-gray-400 hover:bg-gray-800 hover:text-white"
                            aria-label="Close menu"
                        >
                            <font-awesome-icon :icon="['fas', 'times']" class="h-6 w-6" />
                        </button>
                    </div>

                    <!-- Menu Items -->
                    <nav class="flex flex-col p-4">
                        <template v-if="user">
                            <Link
                                :href="route('dashboard')"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white transition-colors hover:bg-gray-800"
                                @click="closeMenu"
                            >
                                <font-awesome-icon :icon="['fas', 'tachometer-alt']" class="h-5 w-5" />
                                Dashboard
                            </Link>

                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white transition-colors hover:bg-gray-800"
                                @click="closeMenu"
                            >
                                <font-awesome-icon :icon="['fas', 'user']" class="h-5 w-5" />
                                Perfil
                            </Link>
                        </template>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white transition-colors hover:bg-gray-800"
                                @click="closeMenu"
                            >
                                <font-awesome-icon :icon="['fas', 'sign-in-alt']" class="h-5 w-5" />
                                Entrar
                            </Link>

                            <Link
                                :href="route('register')"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white transition-colors hover:bg-gray-800"
                                @click="closeMenu"
                            >
                                <font-awesome-icon :icon="['fas', 'user-plus']" class="h-5 w-5" />
                                Registrar
                            </Link>
                        </template>

                        <!-- Divider -->
                        <div class="my-4 border-t border-gray-800" />

                        <!-- Quick Actions -->
                        <button
                            class="flex items-center gap-3 rounded-lg px-4 py-3 text-left text-white transition-colors hover:bg-gray-800"
                            @click="scrollToTrending"
                        >
                            <font-awesome-icon :icon="['fas', 'bolt']" class="h-5 w-5" />
                            Filmes em Alta
                        </button>

                        <button
                            class="flex items-center gap-3 rounded-lg px-4 py-3 text-left text-white transition-colors hover:bg-gray-800"
                            @click="scrollToPopular"
                        >
                            <font-awesome-icon :icon="['fas', 'star']" class="h-5 w-5" />
                            Populares
                        </button>
                    </nav>
                </div>
            </div>
        </Teleport>
    </div>
</template>
