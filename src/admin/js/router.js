import Vue from 'vue';
import VueRouter from 'vue-router'

import AdminPage from '../admin.vue'
import CollectionPage from '../pages/collection-page.vue'
import ItemCreationPage from '../pages/item-creation-page.vue'

import CollectionsList from '../components/collections-list.vue'
import ItensList from '../components/itens-list.vue'

Vue.use(VueRouter);

const routes = [
    { path: '/', component: CollectionsList },
    { path: '/collections', component: CollectionsList },
    { path: '/collections/:id', component: CollectionPage, children: [
            { path: 'itens-list', component: ItensList }
            
        ]   
    },
    { path: '/collections/:id/itens/create', component: ItemCreationPage },
    { path: '*', redirect: '/'}
]

export default new VueRouter ({
    routes
})