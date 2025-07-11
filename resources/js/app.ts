import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { Toaster } from 'vue-sonner';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

import { library } from '@fortawesome/fontawesome-svg-core';
import {
    faArchive,
    faArrowDown,
    faBars,
    faBookmark,
    faCheck,
    faCheckCircle,
    faCheckSquare,
    faChevronLeft,
    faChevronRight,
    faClock,
    faCopy,
    faEdit,
    faEllipsisH,
    faExclamationCircle,
    faExclamationTriangle,
    faExpand,
    faFilm,
    faFire,
    faHeart,
    faHome,
    faInfoCircle,
    faList,
    faLock,
    faMagnifyingGlass,
    faMaximize,
    faPlay,
    faPlus,
    faRefresh,
    faSearch,
    faShare,
    faSignInAlt,
    faStar,
    faTachometerAlt,
    faTh,
    faThumbsUp,
    faTimes,
    faTrash,
    faUser,
    faUserPlus,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

import { faGoogle } from '@fortawesome/free-brands-svg-icons';

library.add(
    faPlay,
    faPlus,
    faStar,
    faChevronLeft,
    faChevronRight,
    faBars,
    faTimes,
    faHome,
    faUser,
    faSignInAlt,
    faUserPlus,
    faFire,
    faThumbsUp,
    faArrowDown,
    faFilm,
    faHeart,
    faShare,
    faCheckCircle,
    faExclamationCircle,
    faExclamationTriangle,
    faInfoCircle,
    faCopy,
    faClock,
    faTrash,
    faBookmark,
    faExpand,
    faMaximize,
    faEdit,
    faArchive,
    faRefresh,
    faLock,
    faCheck,
    faMagnifyingGlass,
    faTachometerAlt,
    faEllipsisH,
    faSearch,
    faTh,
    faList,
    faCheckSquare,
    faGoogle,
);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            .component('Toaster', Toaster)
            .component('FontAwesomeIcon', FontAwesomeIcon)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
