import Vue from 'vue';
import VueRouter from 'vue-router'

import AdminPage from '../admin.vue'
import CollectionPage from '../pages/collection-page.vue'
import ItemPage from '../pages/item-page.vue'
import ItemCreationPage from '../pages/item-creation-page.vue'

import CollectionsList from '../components/collections-list.vue'
import ItemsList from '../components/items-list.vue'

Vue.use(VueRouter);

const routes = [
    { path: '/', component: CollectionsList, meta: {title: 'Admin Page'} },
    { path: '/collections', component: CollectionsList, meta: {title: 'Collections List'} },
    { path: '/collections/:id', component: CollectionPage, children: [
            { path: 'items-list', component: ItemsList, meta: {title: 'Items List'} }
        ],
        meta: { title: 'Collection Page' }
    },
    { path: '/collections/:id/items/new', component: ItemCreationPage, meta: {title: 'Create Item'} },
    { path: '/collections/:collection_id/items/:id/edit', component: ItemCreationPage, meta: {title: 'Edit Item'} },
    { path: '/collections/:collection_id/items/:id', component: ItemPage, meta: {title: 'Item Page'} },
    { path: '*', redirect: '/'}
]

export default new VueRouter ({
    routes
})