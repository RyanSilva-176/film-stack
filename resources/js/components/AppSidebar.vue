<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Bookmark, BookOpen, Eye, Folder, FolderPlus, Heart, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Gêneros',
        href: '/genres',
        icon: Folder,
    },
    {
        title: 'Curtidos',
        href: '/lists/liked',
        icon: Heart,
    },
    {
        title: 'Watchlist',
        href: '/lists/watchlist',
        icon: Bookmark,
    },
    {
        title: 'Assistidos',
        href: '/lists/watched',
        icon: Eye,
    },
    {
        title: 'Listas Personalizadas',
        href: '/lists/custom',
        icon: FolderPlus,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github',
        href: 'https://github.com/RyanSilva-176/film-stack',
        icon: Folder,
    },
    {
        title: 'Documentação',
        href: 'https://github.com/RyanSilva-176/film-stack/blob/main/README.md',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
