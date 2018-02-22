import Vue from 'vue';
import VueRouter from 'vue-router'

import AdminPage from '../admin.vue'
import CollectionsPage from '../pages/collections-page.vue'
import CollectionPage from '../pages/collection-page.vue'
import CollectionEditionPage from '../pages/collection-edition-page.vue'
import ItemsPage from '../pages/items-page.vue'
import ItemPage from '../pages/item-page.vue'
import ItemEditionPage from '../pages/item-edition-page.vue'
import FieldsPage from '../pages/fields-page.vue'
import FiltersPage from '../pages/filters-page.vue'
import CategoriesPage from '../pages/categories-page.vue'
import EventsPage from '../pages/events-page.vue'

import CollectionsList from '../components/collections-list.vue'
import ItemsList from '../components/items-list.vue'
 
Vue.use(VueRouter); 

const routes = [
    { path: '/', component: CollectionsPage, meta: {title: 'Admin Page'} },

    { path: '/collections', component: CollectionsPage, meta: {title: 'Collections Page'} },
    { path: '/collections/new', component: CollectionEditionPage, meta: {title: 'Create Collection'} },
    
    { path: '/collections/:id', component: CollectionPage, meta: {title: 'Collections Page'} },
    { path: '/collections/:id/edit', component: CollectionEditionPage, meta: {title: 'Edit Collection'} },

    { path: '/collections/:id/items/new', component: ItemEditionPage, meta: {title: 'Create Item'} },
    { path: '/collections/:collection_id/items/:id/edit', component: ItemEditionPage, meta: {title: 'Edit Item'} },
    { path: '/collection/:collection_id/items', component: ItemsPage, meta: {title: 'Items Page'} },
    { path: '/collections/:collection_id/items/:id', component: ItemPage, meta: {title: 'Item Page'} },

    { path: '/items', component: ItemsPage, meta: {title: 'Items Page'} },

    { path: '/filters', component: FiltersPage, meta: {title: 'Filters Page'} },

    { path: '/fields', component: FieldsPage, meta: {title: 'Fields Page'} },

    { path: '/categories', component: CategoriesPage, meta: {title: 'Categories Page'} },

    { path: '/events', component: EventsPage, meta: {title: 'Events Page'} },

    { path: '*', redirect: '/'}
]

export default new VueRouter ({
    routes
})