import Vue from 'vue';
import VueRouter from 'vue-router'

import Admin from '../admin.vue'
import CollectionsList from '../components/collections-list.vue'
import CollectionPage from '../components/collection-page.vue'
import ItensList from '../components/itens-list.vue'

Vue.use(VueRouter);

const routes = [
    { path: '/', component: CollectionsList },
    { path: '/collections', component: CollectionsList },
    { path: '/collections/:id', component: CollectionPage, children: [
            { path: 'items-list', component: ItensList }
        ]   
    },
    { path: '*', redirect: '/'}
]

export default new VueRouter ({
    routes
})