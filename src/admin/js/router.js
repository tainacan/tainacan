import Vue from 'vue';
import VueRouter from 'vue-router'

import AdminPage from '../admin.vue'
import CollectionsPage from '../pages/lists/collections-page.vue'
import CollectionPage from '../pages/singles/collection-page.vue'
import CollectionEditionPage from '../pages/edition/collection-edition-page.vue'
import ItemsPage from '../pages/lists/items-page.vue'
import ItemPage from '../pages/singles/item-page.vue'
import ItemEditionPage from '../pages/edition/item-edition-page.vue'
import FieldsPage from '../pages/lists/fields-page.vue'
import FiltersPage from '../pages/lists/filters-page.vue'
import FilterEditionPage from '../pages/edition/filter-edition-page.vue'
import CategoriesPage from '../pages/lists/categories-page.vue'
import CategoryEditionPage from '../pages/edition/category-edition-page.vue'
import EventsPage from '../pages/lists/events-page.vue'

import CollectionsList from '../components/collections-list.vue'
import ItemsList from '../components/items-list.vue'
import FiltersList from '../components/filters-list.vue'
import CategoriesList from '../components/categories-list.vue'
import FieldsList from '../components/fields-list.vue'

Vue.use(VueRouter);

const i18nGet = function (key) {
  let string = tainacan_plugin.i18n[key];
  return (string != undefined && string != null && string != '' ) ? string : "ERROR: Invalid i18n key!";
}

const routes = [
    { path: '/', redirect:'/collections' },

    { path: '/collections', name: 'CollectionsPage', component: CollectionsPage, meta: {title: i18nGet('title_collections_page')} },
    { path: '/collections/new', name: 'CollectionEditionPage', component: CollectionEditionPage, meta: {title: i18nGet('title_create_collection')} },
    
    { path: '/collections/:id', name: 'CollectionPage', component: CollectionPage, meta: {title: i18nGet('title_collection_page')}, 
      children: [
        { path: '', redirect: 'items'},
        { path: 'items', component: ItemsPage, name: 'ItemsPage', meta: {title: i18nGet('title_collection_page')} }, 
        { path: 'items/:itemId/edit', name: 'ItemEditionPage', component: ItemEditionPage, meta: {title:  i18nGet('title_item_edition')} },
        { path: 'items/new', name: 'ItemCreatePage', component: ItemEditionPage, meta: {title: i18nGet('title_create_item')} },
        { path: 'items/:itemId', name: 'ItemPage', component: ItemPage, meta: {title: i18nGet('title_item_page')} },   
        { path: 'edit', component: CollectionEditionPage,  name: 'CollectionEditionPage', meta: {title: i18nGet('title_collection_edition')} },
        { path: 'fields', component: FieldsList, name: 'FieldsList', meta: {title: i18nGet('title_collection_fields_edition')} }, 
        { path: 'filters', component: FiltersList, name: 'FiltersList', meta: {title: i18nGet('title_collection_page')} }
      ]
    },

    { path: '/items', name: 'ItemsPage', component: ItemsPage, meta: {title: i18nGet('title_items_page')} },
    { path: '/items/new', name: 'ItemEditionPage', component: ItemEditionPage, meta: {title: i18nGet('title_create_item')} },

    { path: '/filters', name: 'FiltersPage', component: FiltersPage, meta: {title: i18nGet('title_filters_page')} },
    { path: '/filters/new', name: 'FilterEditionPage', component: FilterEditionPage, meta: {title: i18nGet('title_create_filter_page')} },
    { path: '/filters/:id/edit', name: 'FilterEditionPage', component: FilterEditionPage, meta: {title: i18nGet('title_filter_edition_page')} },

    { path: '/fields', name: 'FieldsPage', component: FieldsPage, meta: {title: i18nGet('title_fields_page')} },

    { path: '/categories', name: 'CategoriesPage', component: CategoriesPage, meta: {title: i18nGet('title_categories_page')} },
    { path: '/categories/new', name: 'CategoryEditionPage', component: CategoryEditionPage, meta: {title: i18nGet('title_create_category_page')} },
    { path: '/categories/:id/edit', name: 'CategoryEditionPage', component: CategoryEditionPage, meta: {title: i18nGet('title_category_edition_page')} },

    { path: '/events',  name: 'EventsPage', component: EventsPage, meta: {title: i18nGet('title_events_page')} },

    { path: '*', redirect: '/'}
]

export default new VueRouter ({
    routes
})