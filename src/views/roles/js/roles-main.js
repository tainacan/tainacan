import { createApp } from 'vue';
import store from '../../admin/js/store/store';
import router from './roles-router';
import VTooltip from 'floating-vue';
import { Snackbar, Modal } from 'buefy';

import { I18NPlugin } from './wp-i18n-plugin';

import RolesPage from '../roles.vue';

export default (element) => {

    function renderTainacanRolePage() {

        // Gets the div with the content of the page
        let pageElement = element ? element : document.getElementById('tainacan-roles-app');

        // Mount only if the div exists and it is not already mounted
        if ( pageElement && pageElement.classList && !pageElement.classList.contains('has-mounted') ) {

            const VueRoles = createApp({
                el: '#tainacan-roles-app',
                render: h => h(RolesPage)
            });

            VueRoles.use(I18NPlugin);
            VueRoles.use(VTooltip, {
                popperTriggers: ['hover'],
                themes: {
                    'taianacan-tooltip': {
                        '$extend': 'tooltip',
                        triggers: ['hover', 'focus', 'touch'],
                        autoHide: true,
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
            
        };
    };

    // This is rendered on the admin page.
    renderTainacanRolePage();
};

