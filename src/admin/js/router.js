import Vue from 'vue';
import VueRouter from 'vue-router'

import AdminPage from '../admin.vue'
import CollectionPage from '../pages/collection-page.vue'
import CollectionEditionPage from '../pages/collection-edition-page.vue'
import ItemPage from '../pages/item-page.vue'
import ItemEditionPage from '../pages/item-edition-page.vue'

import CollectionsList from '../components/collections-list.vue'
import ItemsList from '../components/items-list.vue'
 
Vue.use(VueRouter); 

const routes = [
    { path: '/', component: CollectionsList, meta: {title: 'Admin Page'} },
    { path: '/collections', component: CollectionsList, meta: {title: 'Collections List'} },
    { path: '/collections/new', component: CollectionEditionPage, meta: {title: 'Create Collection'} },
    { path: '/collections/:id', component: CollectionPage, meta: {title: 'Collections Page'} },
    { path: '/collections/:id/edit', component: CollectionEditionPage, meta: {title: 'Edit Collection'} },
    { path: '/collections/:id/items/new', component: ItemEditionPage, meta: {title: 'Create Item'} },
    { path: '/collections/:collection_id/items/:id/edit', component: ItemEditionPage, meta: {title: 'Edit Item'} },
    { path: '/collection/:collection_id/items', component: ItemsList, meta: {title: 'Items List'} },
    { path: '/collections/:collection_id/items/:id', component: ItemPage, meta: {title: 'Item Page'} },
    { path: '*', redirect: '/'}
]

export default new VueRouter ({
    routes
})