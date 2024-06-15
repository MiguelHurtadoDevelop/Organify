import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Importa FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons'; // Importa todos los iconos sólidos
import { fab } from '@fortawesome/free-brands-svg-icons'; // Importa todos los iconos de marcas


library.add(fas); // Añade todos los iconos a la biblioteca
library.add(fab); // Añade todos los iconos de marcas a la biblioteca


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        app.component('font-awesome-icon', FontAwesomeIcon); // Registra el componente FontAwesomeIcon globalmente

        return app.mount(el);
    },
    progress: {
        color: 'green',
    },
});