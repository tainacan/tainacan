import { createApp } from 'vue';
import store from '../../admin/js/store/store';
import router from './roles-router';
import FloatingVue from 'floating-vue';
import { Snackbar, Modal } from '@ntohq/buefy-next';

import { I18NPlugin } from './wp-i18n-plugin';

import RolesPage from '../roles.vue';

// import { configureCompat } from 'vue'
// configureCompat({
//     COMPONENT_V_MODEL: false,
//     RENDER_FUNCTION: false,
//     MODE: 3
// })

export default (element) => {

    function renderTainacanRolePage() {

        // Gets the div with the content of the page
        let pageElement = element ? element : document.getElementById('tainacan-roles-app');

        // Mount only if the div exists and it is not already mounted
        if ( pageElement && pageElement.classList && !pageElement.classList.contains('has-mounted') ) {

            const VueRoles = createApp(RolesPage);

            VueRoles.use(I18NPlugin);
            VueRoles.use(FloatingVue, {
                popperTriggers: ['hover'],
                themes: {
                    'tainacan-tooltip': {
                        $extend: 'tooltip',
                        triggers: ['hover', 'focus', 'touch'],
                        html: true,
                    }
                }
            });
            VueRoles.use(Snackbar);
            VueRoles.use(Modal);
            
            // Changing title of pages
            router.beforeEach((to, from, next) => {
                document.title = to.meta.title;
                if (next() != undefined)
                    next();
            });
            
            VueRoles.use(router);
            VueRoles.use(store);

            VueRoles.mount('#tainacan-roles-app');
        };
    };

    // This is rendered on the admin page.
    renderTainacanRolePage();
};

