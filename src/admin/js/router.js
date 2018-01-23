import Vue from 'vue';
import VueRouter from 'vue-router'

import Admin from '../admin.vue'
import CollectionsList from '../components/collections-list.vue'
import ItensList from '../components/itens-list.vue'

Vue.use(VueRouter);

const routes = [
    { path: '/', component: Admin},
    { path: '/collections', component: CollectionsList },
    { path: '/collections/:id', component: CollectionsList, children: [
            { path: 'items-list', component: ItensList }
        ]   
    },
    { path: '*', redirect: '/'}
]

export default new VueRouter ({
    routes
})